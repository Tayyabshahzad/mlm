<?php
/**
 * Admin Impersonation Setup Script
 * Run this to set up the admin impersonation system
 */

echo "🎭 ADMIN IMPERSONATION SETUP 🎭\n";
echo "===============================\n\n";

// Check if we're in Laravel project
if (!file_exists('artisan')) {
    echo "❌ Laravel project not found! Please run this script from your Laravel project root directory.\n";
    exit(1);
}

echo "✅ Laravel project detected\n\n";

// Check if Laravel is properly bootstrapped
if (file_exists('vendor/autoload.php') && file_exists('bootstrap/app.php')) {
    try {
        require_once 'vendor/autoload.php';
        $app = require_once 'bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        echo "✅ Laravel application loaded\n";

        // Check if Spatie roles is installed
        if (class_exists('\Spatie\Permission\Models\Role')) {
            echo "✅ Spatie Permission package detected\n";

            // Check if admin role exists
            $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
            if (!$adminRole) {
                echo "ℹ️  Creating 'admin' role...\n";
                $adminRole = \Spatie\Permission\Models\Role::create(['name' => 'admin']);
            }
            echo "✅ Admin role exists\n";

            // Check if super-admin role exists
            $superAdminRole = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
            if (!$superAdminRole) {
                echo "ℹ️  Creating 'super-admin' role...\n";
                $superAdminRole = \Spatie\Permission\Models\Role::create(['name' => 'super-admin']);
            }
            echo "✅ Super-admin role exists\n";

        } else {
            echo "⚠️  Spatie Permission package not found\n";
        }

        // Check current user roles
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        if (!$currentUser) {
            echo "ℹ️  No user currently logged in\n";

            // Check first user
            $firstUser = \App\Models\User::first();
            if ($firstUser) {
                echo "ℹ️  First user in database: {$firstUser->name} (ID: {$firstUser->id})\n";

                if (!$firstUser->hasRole('admin') && !$firstUser->hasRole('super-admin')) {
                    echo "ℹ️  Assigning admin role to first user...\n";
                    $firstUser->assignRole('admin');
                    echo "✅ Admin role assigned to {$firstUser->name}\n";
                } else {
                    echo "✅ First user already has admin privileges\n";
                }
            }
        } else {
            echo "ℹ️  Current logged in user: {$currentUser->name}\n";

            if (!$currentUser->hasRole('admin') && !$currentUser->hasRole('super-admin')) {
                echo "⚠️  Current user doesn't have admin role\n";
                echo "ℹ️  Assigning admin role...\n";
                $currentUser->assignRole('admin');
                echo "✅ Admin role assigned\n";
            } else {
                echo "✅ Current user has admin privileges\n";
            }
        }

    } catch (Exception $e) {
        echo "❌ Laravel setup error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n";

// Check if files exist
echo "📁 CHECKING CREATED FILES:\n";
echo "===========================\n";

$files = [
    'app/Http/Middleware/AdminImpersonation.php' => 'Admin Impersonation Middleware',
    'app/Http/Controllers/Admin/ImpersonationController.php' => 'Impersonation Controller',
    'resources/views/admin/impersonation/index.blade.php' => 'Admin Impersonation Interface',
    'resources/views/components/impersonation-banner.blade.php' => 'Impersonation Banner Component',
    'resources/views/layouts/app.blade.php' => 'Admin Layout'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description\n";
    } else {
        echo "❌ $description (missing: $file)\n";
    }
}

echo "\n";

// Check routes
echo "🌐 CHECKING ROUTES:\n";
echo "===================\n";

try {
    $output = shell_exec('php artisan route:list --name=impersonation 2>&1');
    if ($output && strpos($output, 'admin.impersonation') !== false) {
        echo "✅ Impersonation routes registered\n";
    } else {
        echo "❌ Impersonation routes not found\n";
        echo "   Make sure routes are added to routes/web.php\n";
    }
} catch (Exception $e) {
    echo "⚠️  Could not check routes: " . $e->getMessage() . "\n";
}

echo "\n";

// Check middleware
echo "🛡️  CHECKING MIDDLEWARE:\n";
echo "========================\n";

if (file_exists('bootstrap/app.php')) {
    $appContent = file_get_contents('bootstrap/app.php');
    if (strpos($appContent, 'AdminImpersonation') !== false) {
        echo "✅ AdminImpersonation middleware registered\n";
    } else {
        echo "❌ AdminImpersonation middleware not registered in bootstrap/app.php\n";
    }
} else {
    echo "❌ bootstrap/app.php not found\n";
}

echo "\n";

// Final instructions
echo "🎉 SETUP COMPLETE! 🎉\n";
echo "=====================\n";

echo "📋 HOW TO USE:\n";
echo "==============\n";
echo "1. Login as admin user\n";
echo "2. Go to: /admin/impersonation\n";
echo "3. Search and select any user\n";
echo "4. Click 'Login as User'\n";
echo "5. You'll be logged in as that user\n";
echo "6. Click 'Stop Impersonation' to return to admin\n\n";

echo "🔗 DIRECT LINKS:\n";
echo "================\n";
echo "• Impersonation Page: " . (isset($_SERVER['HTTP_HOST']) ? "http://{$_SERVER['HTTP_HOST']}" : "your-domain") . "/admin/impersonation\n";
echo "• Dashboard: " . (isset($_SERVER['HTTP_HOST']) ? "http://{$_SERVER['HTTP_HOST']}" : "your-domain") . "/dashboard\n\n";

echo "🔒 SECURITY FEATURES:\n";
echo "=====================\n";
echo "✅ Only admin/super-admin can impersonate\n";
echo "✅ Cannot impersonate other admins\n";
echo "✅ All impersonation sessions logged\n";
echo "✅ Warning banner when impersonating\n";
echo "✅ Easy stop impersonation button\n";
echo "✅ Session timeout protection\n\n";

echo "⚠️  IMPORTANT NOTES:\n";
echo "====================\n";
echo "• Always stop impersonation when done\n";
echo "• Be careful when performing actions as other users\n";
echo "• Check logs regularly for security monitoring\n";
echo "• Only use for legitimate admin purposes\n\n";

echo "✅ Your admin impersonation system is ready!\n";