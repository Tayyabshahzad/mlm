<?php
/**
 * Server Deployment Verification Script
 * Run this on your server after deployment to verify everything is working
 */

echo "=== MLM ROI SYSTEM - SERVER VERIFICATION ===\n\n";

// 1. PHP Configuration
echo "1. PHP CONFIGURATION:\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   PHP Timezone: " . date_default_timezone_get() . "\n";
echo "   Current PHP Time: " . date('Y-m-d H:i:s T') . "\n\n";

// 2. System Configuration
echo "2. SYSTEM CONFIGURATION:\n";
$timezone = exec('timedatectl show -p Timezone --value 2>/dev/null || date +%Z');
echo "   System Timezone: " . $timezone . "\n";
echo "   System Time: " . exec('date') . "\n\n";

// 3. Laravel Application Check (if running within Laravel)
if (file_exists('vendor/autoload.php') && file_exists('bootstrap/app.php')) {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "3. LARAVEL CONFIGURATION:\n";
    echo "   App Timezone: " . config('app.timezone') . "\n";
    echo "   Laravel Time: " . now()->format('Y-m-d H:i:s T') . "\n";
    echo "   Pakistan Time: " . now()->setTimezone('Asia/Karachi')->format('Y-m-d H:i:s T') . "\n\n";

    // 4. Database Connection
    echo "4. DATABASE CONNECTION:\n";
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "   ✅ Database Connected\n";

        $userCount = \App\Models\User::count();
        echo "   Users in database: " . $userCount . "\n";

        $roiCount = \App\Models\ROITransaction::count();
        echo "   ROI transactions: " . $roiCount . "\n";

    } catch (Exception $e) {
        echo "   ❌ Database Error: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // 5. Command Availability
    echo "5. ARTISAN COMMANDS:\n";
    $commands = [
        'roi:generate-weekly',
        'roi:process-automated',
        'roi:generate-historical'
    ];

    foreach ($commands as $command) {
        try {
            $output = shell_exec("php artisan {$command} --help 2>&1");
            if (strpos($output, 'Description:') !== false) {
                echo "   ✅ {$command} - Available\n";
            } else {
                echo "   ❌ {$command} - Not found\n";
            }
        } catch (Exception $e) {
            echo "   ❌ {$command} - Error: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // 6. Scheduler Check
    echo "6. LARAVEL SCHEDULER:\n";
    try {
        $output = shell_exec('php artisan schedule:list 2>&1');
        if (strpos($output, 'roi:generate-weekly') !== false) {
            echo "   ✅ ROI scheduler configured\n";
            if (strpos($output, '23:40') !== false) {
                echo "   ✅ Scheduled for 11:40 PM\n";
            }
            if (strpos($output, 'Asia/Karachi') !== false) {
                echo "   ✅ Pakistan timezone configured\n";
            }
        } else {
            echo "   ❌ ROI scheduler not found\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Scheduler check failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

} else {
    echo "3. LARAVEL APPLICATION: Not detected (run this script from Laravel root)\n\n";
}

// 7. Cron Job Check
echo "7. CRON JOB CHECK:\n";
$crontab = shell_exec('crontab -l 2>/dev/null');
if ($crontab && strpos($crontab, 'schedule:run') !== false) {
    echo "   ✅ Laravel scheduler cron job found\n";
} else {
    echo "   ❌ Laravel scheduler cron job not found\n";
    echo "   Add this to crontab: * * * * * cd " . getcwd() . " && php artisan schedule:run >> /dev/null 2>&1\n";
}
echo "\n";

// 8. File Permissions
echo "8. FILE PERMISSIONS:\n";
$directories = ['storage', 'bootstrap/cache'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir);
        echo "   {$dir}: {$perms} " . ($writable ? "✅ Writable" : "❌ Not writable") . "\n";
    }
}
echo "\n";

// 9. Next ROI Schedule
echo "9. NEXT ROI SCHEDULE:\n";
if (class_exists('Carbon\Carbon')) {
    $now = \Carbon\Carbon::now('Asia/Karachi');
    $nextRoi = \Carbon\Carbon::now('Asia/Karachi')->setTime(23, 40, 0);

    // If it's past 11:40 PM today, move to next valid day
    if ($now->greaterThan($nextRoi)) {
        $nextRoi->addDay();
    }

    // Skip Friday
    while ($nextRoi->dayOfWeek === \Carbon\Carbon::FRIDAY) {
        $nextRoi->addDay();
    }

    echo "   Current time: " . $now->format('Y-m-d H:i:s T') . "\n";
    echo "   Next ROI: " . $nextRoi->format('Y-m-d H:i:s T') . " (" . $nextRoi->dayName . ")\n";
    echo "   Time until next ROI: " . $now->diffForHumans($nextRoi) . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
echo "If all items show ✅, your system is ready for production!\n";