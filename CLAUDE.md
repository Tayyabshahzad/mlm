# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview
This is a Laravel 11 MLM (Multi-Level Marketing) application called "Global Visioners International" with Vue.js/Inertia frontend. The system manages user registrations, referral hierarchies, commission calculations, ROI distributions, and wallet transactions.

## Development Commands

### Core Laravel Commands
```bash
# Start development environment (includes server, queue, and Vite)
composer run dev

# Individual services
php artisan serve                    # Start Laravel development server
php artisan queue:listen --tries=1  # Start queue worker
npm run dev                         # Start Vite development server
npm run build                       # Build assets for production

# Database operations
php artisan migrate                 # Run migrations
php artisan migrate:fresh --seed    # Fresh migration with seeders
php artisan db:seed                 # Run database seeders

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

### Custom Artisan Commands
```bash
# Commission and ROI processing
php artisan commissions:process-all                    # Process all user commissions
php artisan commissions:process-all --user-id=123     # Process specific user
php artisan commissions:process-all --dry-run         # Preview without processing
php artisan roi:generate-weekly                       # Generate weekly ROI payments
php artisan roi:schedule                              # Schedule ROI payments

# Testing and utilities
php artisan test:scheduler                            # Test scheduler functionality
```

### Testing
```bash
# Run PHP tests (uses Pest)
php artisan test
./vendor/bin/pest

# Run specific tests
php artisan test tests/Feature/Auth/
php artisan test --filter=UserRegistrationTest
```

### Code Quality
```bash
# Laravel Pint (code formatting)
./vendor/bin/pint
```

## Application Architecture

### Core Business Logic
The application is built around MLM concepts with these key components:

1. **User Hierarchy System**
   - Sponsor-referral relationships tracked via `sponsor_id`
   - Multi-level genealogy with ancestor/descendant tracking
   - Team structure calculations for commission eligibility

2. **Commission System**
   - 7-level deep commission structure (5%, 2%, 1.5%, 1.25%, 1%, 0.75%, 0.5%)
   - Team size requirements for each commission level
   - Real-time commission calculation via `CommissionService`

3. **ROI (Return on Investment) System**
   - Weekly ROI payments to eligible users
   - Investment slabs with different ROI percentages
   - ROI monitoring and payment scheduling

4. **Wallet System**
   - Multiple wallet types: PV, Direct/Indirect commissions, ROI, rewards
   - USD-PV conversion tracking
   - Transaction logging and audit trails

### Key Services
- `CommissionService`: Handles multi-level commission calculations
- `ROIService` & `ROICommissionService`: Manages ROI distributions
- `WalletService`: Wallet operations and balance management
- `PVService`: PV (Point Value) transactions and conversions
- `RewardService`: Reward level achievements
- `InvestmentSlabService`: Investment tier management

### Frontend Stack
- Vue.js 3 with Inertia.js for SPA experience
- Bootstrap 5 for UI components
- Metronic admin theme integration
- Tailwind CSS for additional styling

### Database Schema Highlights
- `users`: Extended with MLM fields (sponsor_id, roi_eligible_investment_amount, etc.)
- `referral_trees`: Genealogy hierarchy tracking
- `wallets`: Multi-type wallet system with source tracking
- `commission_logs`: Commission payment history
- `roi_transactions`: ROI payment records
- `user_investments`: Investment tracking per user
- `weeks`: ROI percentage configuration by week

### Security & Permissions
- Spatie Permission package for role-based access
- Custom middleware: `CheckUserStatus`, `CheckBlockedUser`, `CheckRole`
- User account freezing and blocking capabilities
- Email verification and OTP system

### Configuration
- Commission rates in `config/commission.php`
- Custom settings stored in `settings` table (accessible via admin)
- Environment-specific configurations for timezone, currency rates

### Queue Jobs
The application uses Laravel queues for:
- Commission processing
- ROI calculations
- Email notifications (welcome emails, invoices, agreements)

### File Structure Notes
- Controllers organized by functionality (Auth/, genealogy, wallets, etc.)
- Services in `app/Services/` for business logic separation
- Custom commands in `app/Console/Commands/`
- Vue components in `resources/js/Components/`
- Blade views for email templates and legacy pages

### Development Notes
- Uses Laragon for local development environment
- Telescope enabled for debugging (disabled in production)
- Log viewer available at `/log-viewer` (authenticated users only)
- Media library integration for file uploads
- Excel export functionality for reports and genealogy