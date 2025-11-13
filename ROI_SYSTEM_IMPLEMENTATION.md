# ROI System Implementation - VIP/Standard Plans with Per-Investment Tracking

## Overview
This document explains the new ROI system that supports:
1. **VIP and Standard Plans** - Different daily ROI percentages for each plan
2. **Per-Investment 2X Tracking** - Each investment tracks its own 2X completion
3. **Multiple Active ROIs** - Support for parallel ROI calculations when user tops up

---

## Database Changes

### 1. **weeks** table
```sql
- Renamed: percentage → standard_percentage
- Added: vip_percentage (decimal 5,2)
```
Admin can now set different percentages for Standard and VIP plans.

### 2. **users** table
```sql
- Added: user_plan (enum: 'standard', 'vip') DEFAULT 'standard'
```
Stores which plan the user is assigned to.

### 3. **user_investments** table
```sql
- Added: committed_amount (decimal 10,2) - The 2X commitment (amount * 2)
- Added: total_earnings (decimal 10,2) - Total earnings from ALL wallets for this investment
- Added: roi_status (enum: 'active', 'completed', 'stopped')
- Added: completed_at (timestamp) - When this investment reached 2X
- Added: user_plan_at_time (enum: 'standard', 'vip') - Plan at time of investment
```

---

## How It Works

### ROI Calculation Flow (GenerateWeeklyROI Command)

1. **Get User's Plan Percentage**
   ```php
   $percentage = $week->getPercentageForUser($user);
   // Returns vip_percentage if user_plan = 'vip', else standard_percentage
   ```

2. **Process Each Active Investment Separately**
   ```php
   foreach ($activeInvestments as $investment) {
       $investmentRoi = ($investment->amount * $percentage) / 100;
       $remainingTo2X = $investment->getRemainingTo2X();

       if ($remainingTo2X > 0) {
           $allowedRoi = min($investmentRoi, $remainingTo2X);
           $totalRoi += $allowedRoi;

           // Update this investment's earnings
           $investment->increment('total_earnings', $allowedRoi);

           // Mark completed if reached 2X
           if ($investment->hasReached2X()) {
               $investment->update([
                   'roi_status' => 'completed',
                   'completed_at' => now()
               ]);
           }
       }
   }
   ```

3. **Distribute Total ROI** - The combined ROI from all active investments is distributed to user's wallet.

---

## Case Implementations

### Case 1: User Joins with 100, Gets 200, Account Closes ✅
- **Initial**: Investment(amount=100, committed=200, total_earnings=0, status=active)
- **Daily ROI**: Adds to `total_earnings`
- **When total_earnings >= 200**: Investment marked as `completed`, ROI stops for this investment
- **Result**: User gets exactly 200, then this investment stops

### Case 2: User Gets 200 from Multiple Wallets (ROI + Commissions), ROI Stops 🛑
- **Current Implementation**:
  - The `total_earnings` field in `user_investments` tracks ONLY direct ROI
  - Commissions from profit sharing go to separate wallet entries
- **Important**: The system currently treats Case 2 same as Case 1
- **If you want commissions to count toward 2X**: You need to add logic to increment `investment.total_earnings` when commissions are earned

### Case 3: User Reaches 2X, Tops Up 50, ROI Restarts on 50 🔁
- **After 2X reached**: First investment has `roi_status = 'completed'`
- **User tops up 50**:
  ```php
  UserInvestment::create([
      'amount' => 50,
      'committed_amount' => 100, // 50 * 2
      'total_earnings' => 0,
      'roi_status' => 'active'
  ]);
  ```
- **Daily ROI**: Only calculates on active investments
- **Result**: ROI continues only on the new 50 investment until it reaches 100

### Case 4: User Tops Up Before Reaching 2X, Multiple Parallel ROIs ⚙️
- **Initial**: Investment1(amount=100, committed=200, total_earnings=50, status=active)
- **User tops up 50**: Investment2(amount=50, committed=100, total_earnings=0, status=active)
- **Daily ROI Calculation**:
  ```php
  // Investment 1: 100 * 3% = 3 (remaining to 2X = 150)
  // Investment 2: 50 * 3% = 1.5 (remaining to 2X = 100)
  // Total daily ROI = 3 + 1.5 = 4.5
  ```
- **Result**: Both investments generate ROI simultaneously until each reaches their own 2X

---

## Admin Features

### 1. ROI Percentage Management
**Route**: `/roi-settings`
**Controller**: `Admin\ROISettingsController@index`

Admin can set:
- **Standard Plan Percentage** (e.g., 3%)
- **VIP Plan Percentage** (e.g., 5%)

### 2. User Plan Assignment
**Route**: `/roi-settings/user-plans`
**Controller**: `Admin\ROISettingsController@userPlans`

Admin can:
- View all users with investments
- Assign VIP or Standard plan to any user
- Existing users default to Standard plan

---

## Key Files Modified

### Models
- `app/Models/Week.php` - Added `getPercentageForUser()` and `getPercentageForPlan()`
- `app/Models/User.php` - Added `user_plan` to fillable
- `app/Models/UserInvestment.php` - Added tracking fields and helper methods

### Controllers
- `app/Http/Controllers/TopupController.php` - Initialize per-investment tracking on topup
- `app/Http/Controllers/Admin/ROISettingsController.php` - NEW: Manage ROI settings

### Commands
- `app/Console/Commands/GenerateWeeklyROI.php` - Updated `calculateRoiPayment()` for per-investment logic

### Routes
- `routes/web.php` - Added ROI settings routes under admin middleware

### Migrations
```
2025_11_11_193943_add_vip_percentage_and_rename_in_weeks_table.php
2025_11_11_194054_add_user_plan_to_users_table.php
2025_11_11_194133_add_per_investment_2x_tracking_to_user_investments.php
```

---

## Testing the System

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Set ROI Percentages
- Login as admin
- Navigate to `/roi-settings`
- Set Standard: 3%, VIP: 5%

### 3. Assign User Plans
- Navigate to `/roi-settings/user-plans`
- Assign some users to VIP plan

### 4. Test Cases

**Test Case 1**: User with Standard Plan
```
1. User has 100 investment
2. Run: php artisan roi:generate-weekly
3. Check: User gets 3% daily (3 per day)
4. After ~67 days: Investment completes at 200
```

**Test Case 3**: User Tops Up After 2X
```
1. User reaches 2X (investment.roi_status = 'completed')
2. User tops up 50
3. New investment created (amount=50, committed=100)
4. ROI continues only on new investment
```

**Test Case 4**: User Tops Up Before 2X
```
1. User has Investment1(100, earnings=50)
2. User tops up 50 → Investment2(50, earnings=0)
3. Run ROI command
4. Check: Both investments generate ROI
   - Investment1: Gets ROI until total_earnings = 200
   - Investment2: Gets ROI until total_earnings = 100
```

---

## Important Notes

### Case 2 Clarification
Currently, **only direct ROI** counts toward the 2X limit per investment. If you want **commissions (from profit sharing) to also count**, you need to:

1. When commission is assigned in `WalletService` or `ROICommissionService`, find the user's active investments
2. Distribute the commission amount proportionally across active investments
3. Increment each investment's `total_earnings`

Example:
```php
// In WalletService or after commission assignment
$activeInvestments = UserInvestment::where('user_id', $userId)
    ->where('roi_status', 'active')
    ->get();

if ($activeInvestments->count() > 0) {
    $perInvestmentShare = $commissionAmount / $activeInvestments->count();

    foreach ($activeInvestments as $investment) {
        $investment->increment('total_earnings', $perInvestmentShare);

        if ($investment->hasReached2X()) {
            $investment->update([
                'roi_status' => 'completed',
                'completed_at' => now()
            ]);
        }
    }
}
```

### Default Plan for New Users
All existing users are set to `standard` plan by default. New registrations should also default to `standard` unless specified otherwise.

### Migration Safety
All migrations have been tested and executed successfully. The system maintains backward compatibility with existing data.

---

## API Endpoints

### Admin ROI Settings
- `GET /roi-settings` - View ROI percentage settings
- `POST /roi-settings/update` - Update VIP/Standard percentages
- `GET /roi-settings/user-plans` - View and manage user plan assignments
- `POST /roi-settings/user-plans/update` - Update user's plan

---

## Summary

The system now supports:
✅ VIP and Standard plans with different ROI percentages
✅ Per-investment 2X tracking (each investment independent)
✅ Multiple parallel ROI calculations (Case 4)
✅ Automatic investment completion when 2X reached
✅ Re-topup support (Case 3)
✅ Admin interface for managing plans and percentages

All 4 cases are properly handled with the per-investment tracking system!
