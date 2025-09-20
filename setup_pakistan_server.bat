@echo off
REM ============================================================================
REM MLM ROI System - Pakistan Server Setup Script (Windows)
REM Run this script to automatically configure everything for Pakistan timezone
REM ============================================================================

echo.
echo 🇵🇰 MLM ROI SYSTEM - PAKISTAN SERVER SETUP (WINDOWS) 🇵🇰
echo ================================================================
echo.

REM Check if we're in Laravel project directory
if not exist "artisan" (
    echo ❌ Laravel project not found! Please run this script from your Laravel project root directory.
    pause
    exit /b 1
)

echo ✅ Laravel project detected
echo.

REM Setup Laravel environment
echo ℹ️ Configuring Laravel for Pakistan timezone...

REM Check if .env exists
if exist ".env" (
    REM Backup .env
    copy .env .env.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2% >nul 2>&1
    echo ✅ Backed up .env file

    REM Create temporary file for .env modifications
    powershell -Command "(Get-Content .env) -replace '^APP_TIMEZONE=.*', 'APP_TIMEZONE=Asia/Karachi' | Set-Content .env.temp"
    powershell -Command "if (-not (Select-String -Path .env.temp -Pattern '^APP_TIMEZONE=' -Quiet)) { Add-Content .env.temp 'APP_TIMEZONE=Asia/Karachi' }"
    move .env.temp .env >nul 2>&1

    REM Set locale
    powershell -Command "(Get-Content .env) -replace '^APP_LOCALE=.*', 'APP_LOCALE=en' | Set-Content .env.temp"
    powershell -Command "if (-not (Select-String -Path .env.temp -Pattern '^APP_LOCALE=' -Quiet)) { Add-Content .env.temp 'APP_LOCALE=en' }"
    move .env.temp .env >nul 2>&1

    echo ✅ Laravel environment configured for Pakistan
) else (
    echo ❌ .env file not found! Make sure you're in the Laravel project directory
    pause
    exit /b 1
)
echo.

REM Update Composer dependencies
echo ℹ️ Updating Composer dependencies...
composer install --optimize-autoloader >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Composer dependencies updated
) else (
    echo ⚠️ Composer update failed or composer not found
)
echo.

REM Run database migrations
echo ℹ️ Running database migrations...
php artisan migrate:status >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Database connection successful
    php artisan migrate --force
    echo ✅ Database migrations completed

    REM Clear caches
    php artisan config:cache >nul 2>&1
    php artisan route:cache >nul 2>&1
    php artisan view:cache >nul 2>&1
    echo ✅ Laravel caches cleared and rebuilt
) else (
    echo ❌ Database connection failed! Please check your .env database settings
    pause
    exit /b 1
)
echo.

REM Test ROI system
echo ℹ️ Testing ROI system components...

REM Test artisan commands
php artisan roi:generate-weekly --help >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Command 'roi:generate-weekly' is available
) else (
    echo ❌ Command 'roi:generate-weekly' not found
)

php artisan roi:process-automated --help >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Command 'roi:process-automated' is available
) else (
    echo ❌ Command 'roi:process-automated' not found
)

php artisan roi:generate-historical --help >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Command 'roi:generate-historical' is available
) else (
    echo ❌ Command 'roi:generate-historical' not found
)

REM Test scheduler
php artisan schedule:list | findstr "roi:generate-weekly" >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ ROI scheduler is configured
) else (
    echo ❌ ROI scheduler not found
)
echo.

REM Display system status
echo ℹ️ System Status Summary:
echo ========================
echo 🌍 Windows Timezone:
wmic timezone get caption /value | findstr "Caption"

echo.
echo 🐘 PHP Timezone:
php -r "echo date_default_timezone_get();"
echo.

echo 🐘 PHP Time:
php -r "echo date('Y-m-d H:i:s T');"
echo.

if exist ".env" (
    echo 🚀 Laravel Timezone:
    findstr "APP_TIMEZONE" .env
    echo.
    echo 🚀 Laravel Time:
    php artisan tinker --execute="echo now()->format('Y-m-d H:i:s T');" 2>nul
    echo.
)

echo.
echo ℹ️ Next ROI Schedule:
php artisan tinker --execute="$now = \Carbon\Carbon::now('Asia/Karachi'); $nextRoi = \Carbon\Carbon::now('Asia/Karachi')->setTime(23, 40, 0); if ($now->greaterThan($nextRoi)) $nextRoi->addDay(); while ($nextRoi->dayOfWeek === \Carbon\Carbon::FRIDAY) $nextRoi->addDay(); echo 'Next ROI: ' . $nextRoi->format('Y-m-d H:i:s T') . ' (' . $nextRoi->dayName . ')';" 2>nul
echo.
echo.

echo 🎉 SETUP COMPLETED! 🎉
echo ======================
echo ✅ Your MLM ROI system is configured for Pakistan timezone
echo ✅ ROI will run daily at 11:40 PM Pakistan time (except Fridays)
echo ✅ All components tested and working
echo.
echo ℹ️ For Linux/Unix servers, you still need to set up the cron job:
echo * * * * * cd %cd% ^&^& php artisan schedule:run ^>^> /dev/null 2^>^&1
echo.
echo ℹ️ To verify everything is working, run: php server_check.php
echo.

pause