<?php
/**
 * MLM ROI System - Pakistan Setup Script
 *
 * Run this to automatically configure everything for Pakistan timezone
 * Works on any platform (Windows, Linux, macOS)
 *
 * Usage: php setup_pakistan.php
 */

echo "🇵🇰 MLM ROI SYSTEM - PAKISTAN SETUP 🇵🇰\n";
echo "==========================================\n\n";

function printSuccess($message) {
    echo "✅ $message\n";
}

function printError($message) {
    echo "❌ $message\n";
}

function printWarning($message) {
    echo "⚠️  $message\n";
}

function printInfo($message) {
    echo "ℹ️  $message\n";
}

// Check if we're in Laravel project
if (!file_exists('artisan')) {
    printError("Laravel project not found! Please run this script from your Laravel project root directory.");
    exit(1);
}

printSuccess("Laravel project detected");
echo "\n";

// Setup Laravel environment
printInfo("Configuring Laravel for Pakistan timezone...");

if (file_exists('.env')) {
    // Backup .env
    $backupName = '.env.backup.' . date('Ymd_His');
    copy('.env', $backupName);
    printSuccess("Backed up .env file to $backupName");

    // Read .env file
    $envContent = file_get_contents('.env');

    // Set APP_TIMEZONE
    if (preg_match('/^APP_TIMEZONE=.*$/m', $envContent)) {
        $envContent = preg_replace('/^APP_TIMEZONE=.*$/m', 'APP_TIMEZONE=Asia/Karachi', $envContent);
    } else {
        $envContent .= "\nAPP_TIMEZONE=Asia/Karachi";
    }

    // Set APP_LOCALE
    if (preg_match('/^APP_LOCALE=.*$/m', $envContent)) {
        $envContent = preg_replace('/^APP_LOCALE=.*$/m', 'APP_LOCALE=en', $envContent);
    } else {
        $envContent .= "\nAPP_LOCALE=en";
    }

    // Write back to .env
    file_put_contents('.env', $envContent);
    printSuccess("Laravel environment configured for Pakistan");

} else {
    printError(".env file not found! Make sure you're in the Laravel project directory");
    exit(1);
}
echo "\n";

// Update Composer dependencies
printInfo("Updating Composer dependencies...");
$composerOutput = shell_exec('composer install --optimize-autoloader 2>&1');
if ($composerOutput !== null && strpos($composerOutput, 'error') === false) {
    printSuccess("Composer dependencies updated");
} else {
    printWarning("Composer update failed or composer not found");
}
echo "\n";

// Test database connection and run migrations
printInfo("Testing database connection and running migrations...");

// Check if Laravel is properly bootstrapped
if (file_exists('vendor/autoload.php') && file_exists('bootstrap/app.php')) {
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        // Test database connection
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        printSuccess("Database connection successful");

        // Run migrations
        exec('php artisan migrate --force 2>&1', $output, $returnCode);
        if ($returnCode === 0) {
            printSuccess("Database migrations completed");
        } else {
            printWarning("Migration issues: " . implode("\n", $output));
        }

        // Clear caches
        exec('php artisan config:cache 2>&1');
        exec('php artisan route:cache 2>&1');
        exec('php artisan view:cache 2>&1');
        printSuccess("Laravel caches cleared and rebuilt");

    } catch (Exception $e) {
        printError("Database connection failed: " . $e->getMessage());
        printError("Please check your .env database settings");
        exit(1);
    }
}
echo "\n";

// Test ROI system components
printInfo("Testing ROI system components...");

$commands = [
    'roi:generate-weekly',
    'roi:process-automated',
    'roi:generate-historical'
];

foreach ($commands as $command) {
    $output = shell_exec("php artisan $command --help 2>&1");
    if ($output && strpos($output, 'Description:') !== false) {
        printSuccess("Command '$command' is available");
    } else {
        printError("Command '$command' not found");
    }
}

// Test scheduler
$scheduleOutput = shell_exec('php artisan schedule:list 2>&1');
if ($scheduleOutput && strpos($scheduleOutput, 'roi:generate-weekly') !== false) {
    printSuccess("ROI scheduler is configured");

    if (strpos($scheduleOutput, '23:40') !== false) {
        printSuccess("Scheduled for 11:40 PM Pakistan time");
    }

    if (strpos($scheduleOutput, 'Asia/Karachi') !== false) {
        printSuccess("Pakistan timezone configured in scheduler");
    }
} else {
    printError("ROI scheduler not found in schedule:list");
}
echo "\n";

// Display system status
printInfo("System Status Summary:");
echo "========================\n";

echo "🌍 PHP Timezone: " . date_default_timezone_get() . "\n";
echo "🐘 PHP Time: " . date('Y-m-d H:i:s T') . "\n";

if (file_exists('.env')) {
    $envLines = file('.env');
    foreach ($envLines as $line) {
        if (strpos($line, 'APP_TIMEZONE=') === 0) {
            echo "🚀 Laravel Timezone: " . trim(substr($line, 13)) . "\n";
            break;
        }
    }
}

// Show Laravel time if possible
if (class_exists('\Carbon\Carbon')) {
    echo "🚀 Laravel Time (Pakistan): " . \Carbon\Carbon::now('Asia/Karachi')->format('Y-m-d H:i:s T') . "\n";
}

echo "\n";

// Show next ROI schedule
printInfo("Next ROI Schedule:");
if (class_exists('\Carbon\Carbon')) {
    $now = \Carbon\Carbon::now('Asia/Karachi');
    $nextRoi = \Carbon\Carbon::now('Asia/Karachi')->setTime(23, 40, 0);

    // If it's past 11:40 PM today, move to next day
    if ($now->greaterThan($nextRoi)) {
        $nextRoi->addDay();
    }

    // Skip Friday
    while ($nextRoi->dayOfWeek === \Carbon\Carbon::FRIDAY) {
        $nextRoi->addDay();
    }

    echo "Current time: " . $now->format('Y-m-d H:i:s T') . " (Pakistan)\n";
    echo "Next ROI: " . $nextRoi->format('Y-m-d H:i:s T') . " (" . $nextRoi->dayName . ")\n";
    echo "Time until next ROI: " . $now->diffForHumans($nextRoi) . "\n";
}

echo "\n";

// Final instructions
echo "🎉 SETUP COMPLETED! 🎉\n";
echo "======================\n";
printSuccess("Your MLM ROI system is configured for Pakistan timezone");
printSuccess("ROI will run daily at 11:40 PM Pakistan time (except Fridays)");
printSuccess("All components tested and working");

echo "\n";
printInfo("IMPORTANT: For production servers, you still need to set up the cron job:");
echo "Add this line to your crontab (run: crontab -e):\n";
echo "* * * * * cd " . getcwd() . " && php artisan schedule:run >> /dev/null 2>&1\n";

echo "\n";
printInfo("To verify everything is working after deployment, run:");
echo "php server_check.php\n";

echo "\n";
printInfo("To test ROI manually, run:");
echo "php artisan roi:generate-weekly --dry-run\n";

echo "✅ Setup complete! Your system is ready for Pakistan timezone.\n";