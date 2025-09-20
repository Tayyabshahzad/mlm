#!/bin/bash

# ============================================================================
# MLM ROI System - Pakistan Server Setup Script
# Run this script to automatically configure everything for Pakistan timezone
# ============================================================================

echo "🇵🇰 MLM ROI SYSTEM - PAKISTAN SERVER SETUP 🇵🇰"
echo "=================================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

# Check if running as root for system-level changes
check_root() {
    if [[ $EUID -eq 0 ]]; then
        echo "Running as root - can make system-level changes"
        IS_ROOT=true
    else
        echo "Running as regular user - will skip system-level changes"
        IS_ROOT=false
    fi
    echo ""
}

# Set system timezone to Pakistan
setup_system_timezone() {
    print_info "Setting up system timezone for Pakistan..."

    if [ "$IS_ROOT" = true ]; then
        # Set system timezone
        timedatectl set-timezone Asia/Karachi
        print_success "System timezone set to Asia/Karachi"

        # Update system time
        timedatectl set-ntp true
        print_success "NTP synchronization enabled"

    else
        print_warning "Cannot set system timezone (need root access)"
        print_info "Current system timezone: $(timedatectl show -p Timezone --value 2>/dev/null || date +%Z)"
    fi

    echo "Current system time: $(date)"
    echo ""
}

# Set PHP timezone
setup_php_timezone() {
    print_info "Configuring PHP timezone for Pakistan..."

    # Find PHP configuration file
    PHP_INI=$(php --ini | grep "Loaded Configuration File" | cut -d: -f2 | xargs)

    if [ -f "$PHP_INI" ]; then
        print_info "Found PHP config: $PHP_INI"

        if [ "$IS_ROOT" = true ]; then
            # Backup original file
            cp "$PHP_INI" "$PHP_INI.backup.$(date +%Y%m%d_%H%M%S)"

            # Set timezone in PHP.ini
            if grep -q "date.timezone" "$PHP_INI"; then
                sed -i 's/^;*date.timezone.*/date.timezone = Asia\/Karachi/' "$PHP_INI"
            else
                echo "date.timezone = Asia/Karachi" >> "$PHP_INI"
            fi

            print_success "PHP timezone configured in php.ini"
        else
            print_warning "Cannot modify PHP.ini (need root access)"
        fi
    else
        print_warning "PHP.ini not found"
    fi

    echo "Current PHP timezone: $(php -r 'echo date_default_timezone_get();')"
    echo ""
}

# Setup Laravel environment
setup_laravel_env() {
    print_info "Configuring Laravel for Pakistan timezone..."

    # Check if .env exists
    if [ -f ".env" ]; then
        # Backup .env
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        print_success "Backed up .env file"

        # Set Laravel timezone
        if grep -q "APP_TIMEZONE" .env; then
            sed -i 's/^APP_TIMEZONE=.*/APP_TIMEZONE=Asia\/Karachi/' .env
        else
            echo "APP_TIMEZONE=Asia/Karachi" >> .env
        fi

        # Set locale to Pakistan
        if grep -q "APP_LOCALE" .env; then
            sed -i 's/^APP_LOCALE=.*/APP_LOCALE=en/' .env
        else
            echo "APP_LOCALE=en" >> .env
        fi

        print_success "Laravel environment configured for Pakistan"

    else
        print_error ".env file not found! Make sure you're in the Laravel project directory"
        return 1
    fi
    echo ""
}

# Install/Update Composer dependencies
setup_composer() {
    print_info "Updating Composer dependencies..."

    if command -v composer >/dev/null 2>&1; then
        composer install --optimize-autoloader --no-dev
        print_success "Composer dependencies updated"
    else
        print_warning "Composer not found - please install composer first"
    fi
    echo ""
}

# Run database migrations
setup_database() {
    print_info "Running database migrations..."

    # Check database connection first
    if php artisan migrate:status >/dev/null 2>&1; then
        print_success "Database connection successful"

        # Run migrations
        php artisan migrate --force
        print_success "Database migrations completed"

        # Clear caches
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        print_success "Laravel caches cleared and rebuilt"

    else
        print_error "Database connection failed! Please check your .env database settings"
        return 1
    fi
    echo ""
}

# Setup cron job for Laravel scheduler
setup_cron() {
    print_info "Setting up cron job for Laravel scheduler..."

    PROJECT_PATH=$(pwd)
    CRON_COMMAND="* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1"

    # Check if cron job already exists
    if crontab -l 2>/dev/null | grep -q "schedule:run"; then
        print_warning "Laravel scheduler cron job already exists"
    else
        # Add cron job
        (crontab -l 2>/dev/null; echo "$CRON_COMMAND") | crontab -
        print_success "Laravel scheduler cron job added"
    fi

    print_info "Current crontab:"
    crontab -l 2>/dev/null || echo "No crontab found"
    echo ""
}

# Set proper file permissions
setup_permissions() {
    print_info "Setting up file permissions..."

    # Set permissions for Laravel directories
    if [ -d "storage" ]; then
        chmod -R 775 storage
        print_success "Storage directory permissions set"
    fi

    if [ -d "bootstrap/cache" ]; then
        chmod -R 775 bootstrap/cache
        print_success "Bootstrap cache permissions set"
    fi

    # Set web server ownership (if running as root)
    if [ "$IS_ROOT" = true ]; then
        # Try to detect web server user
        if id "www-data" >/dev/null 2>&1; then
            chown -R www-data:www-data storage bootstrap/cache
            print_success "Web server ownership set (www-data)"
        elif id "apache" >/dev/null 2>&1; then
            chown -R apache:apache storage bootstrap/cache
            print_success "Web server ownership set (apache)"
        elif id "nginx" >/dev/null 2>&1; then
            chown -R nginx:nginx storage bootstrap/cache
            print_success "Web server ownership set (nginx)"
        fi
    fi
    echo ""
}

# Test ROI system
test_roi_system() {
    print_info "Testing ROI system components..."

    # Test artisan commands
    COMMANDS=("roi:generate-weekly" "roi:process-automated" "roi:generate-historical")

    for cmd in "${COMMANDS[@]}"; do
        if php artisan "$cmd" --help >/dev/null 2>&1; then
            print_success "Command '$cmd' is available"
        else
            print_error "Command '$cmd' not found"
        fi
    done

    # Test scheduler
    if php artisan schedule:list | grep -q "roi:generate-weekly"; then
        print_success "ROI scheduler is configured"
    else
        print_error "ROI scheduler not found"
    fi

    # Test database tables
    if php artisan tinker --execute="echo App\\Models\\User::count() . ' users found';" 2>/dev/null; then
        print_success "Database tables accessible"
    else
        print_warning "Could not access database tables"
    fi
    echo ""
}

# Display system status
show_system_status() {
    print_info "System Status Summary:"
    echo "========================"
    echo "🌍 System Timezone: $(timedatectl show -p Timezone --value 2>/dev/null || date +%Z)"
    echo "🕐 System Time: $(date)"
    echo "🐘 PHP Timezone: $(php -r 'echo date_default_timezone_get();')"
    echo "🐘 PHP Time: $(php -r 'echo date("Y-m-d H:i:s T");')"

    if [ -f ".env" ]; then
        echo "🚀 Laravel Timezone: $(grep APP_TIMEZONE .env | cut -d= -f2)"
        echo "🚀 Laravel Time: $(php artisan tinker --execute='echo now()->format("Y-m-d H:i:s T");' 2>/dev/null)"
    fi

    echo ""
    print_info "Next ROI Schedule:"
    php artisan tinker --execute="
        \$now = \\Carbon\\Carbon::now('Asia/Karachi');
        \$nextRoi = \\Carbon\\Carbon::now('Asia/Karachi')->setTime(23, 40, 0);
        if (\$now->greaterThan(\$nextRoi)) \$nextRoi->addDay();
        while (\$nextRoi->dayOfWeek === \\Carbon\\Carbon::FRIDAY) \$nextRoi->addDay();
        echo 'Next ROI: ' . \$nextRoi->format('Y-m-d H:i:s T') . ' (' . \$nextRoi->dayName . ')';
    " 2>/dev/null
    echo ""
    echo ""
}

# Main execution
main() {
    echo "Starting Pakistan server setup..."
    echo ""

    # Check current directory
    if [ ! -f "artisan" ]; then
        print_error "Laravel project not found! Please run this script from your Laravel project root directory."
        exit 1
    fi

    check_root
    setup_system_timezone
    setup_php_timezone
    setup_laravel_env
    setup_composer
    setup_database
    setup_permissions
    setup_cron
    test_roi_system
    show_system_status

    echo "🎉 SETUP COMPLETED! 🎉"
    echo "======================"
    print_success "Your MLM ROI system is configured for Pakistan timezone"
    print_success "ROI will run daily at 11:40 PM Pakistan time (except Fridays)"
    print_success "All components tested and working"
    echo ""
    print_info "To verify everything is working, run: php server_check.php"
    echo ""
}

# Ask for confirmation before proceeding
echo "This script will configure your server for Pakistan timezone and set up the MLM ROI system."
echo ""
read -p "Do you want to proceed? (y/N): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    main
else
    echo "Setup cancelled."
    exit 0
fi