# Commission & Profit Share Separation Implementation

## Date: December 23, 2025

## Client Requirements

The client requested clear separation of two different commission systems:

### 1. **Direct Investment Commission** (SAME for both VIP & Standard)
```
Level 1: 7.00%
Level 2: 2.00%
Level 3: 1.50%
Level 4: 1.25%
Level 5: 1.00%
Level 6: 0.75%
Level 7: 0.50%
```
- Applied when a user makes an investment
- Both VIP and Standard users receive the SAME commission rates
- "کمیشن دونوں میں ایک جیسا جائے گا" (Commission will be same in both)

### 2. **Profit Share Commission** (DIFFERENT for VIP & Standard)

**Standard Plan** (Full profit share):
```
Level 1: 7.00%
Level 2: 6.00%
Level 3: 5.00%
Level 4: 4.00%
Level 5: 3.00%
Level 6: 2.00%
Level 7: 1.00%
```

**VIP Plan** (Half profit share):
```
Level 1: 3.50%
Level 2: 3.00%
Level 3: 2.50%
Level 4: 2.00%
Level 5: 1.50%
Level 6: 1.00%
Level 7: 0.50%
```
- Applied when downline receives ROI payments
- VIP gets HALF of what Standard gets
- "پرافٹ شیئر اسٹینڈرڈ میں پورا جنکہ Vip میں آدھا جائے گا" (Profit share full in Standard, half in VIP)
- This balances because VIP users already get higher ROI percentages on their own investments

---

## Changes Implemented

### 1. **Database Migration** ✅
**File**: [database/migrations/2025_12_23_165657_add_profit_share_columns_to_settings_table.php](d:\laragon\www\mlm\database\migrations\2025_12_23_165657_add_profit_share_columns_to_settings_table.php)

Added 14 new columns to `settings` table:
- `standard_profit_l1` through `standard_profit_l7` (defaults: 7, 6, 5, 4, 3, 2, 1)
- `vip_profit_l1` through `vip_profit_l7` (defaults: 3.5, 3, 2.5, 2, 1.5, 1, 0.5)

**Status**: ✅ Migration executed successfully

---

### 2. **ROICommissionService Update** ✅
**File**: [app/Services/ROICommissionService.php](d:\laragon\www\mlm\app\Services\ROICommissionService.php)

**Changes**:
- Updated `calculateCommissionAmount()` method to accept `$level` parameter
- Added logic to read profit share percentages from database based on user plan
- Standard users get full profit share rates from `standard_profit_l*` fields
- VIP users get half profit share rates from `vip_profit_l*` fields
- Profit share is independent of 2X/7X limits (as per previous fix)
- Updated method calls in `processCommissionForAllUsersAtLevel()` and `processAllEligibleCommissionsEnhanced()`

**Key Code Added**:
```php
private function calculateCommissionAmount(User $ancestor, float $roiAmount, float $percentage, int $level): float
{
    // Get dynamic profit share percentage from settings based on user plan
    $setting = \App\Models\Setting::first();
    $userPlan = $ancestor->user_plan ?? 'standard';

    // Get profit share percentage based on plan and level
    if ($userPlan === 'vip') {
        $fieldName = "vip_profit_l{$level}";
        $actualPercentage = $setting->$fieldName ?? ($percentage / 2); // VIP gets half
    } else {
        $fieldName = "standard_profit_l{$level}";
        $actualPercentage = $setting->$fieldName ?? $percentage; // Standard gets full
    }

    $baseCommission = ($roiAmount * $actualPercentage) / 100;

    Log::info("Calculating profit share commission for ancestor {$ancestor->id} ({$userPlan}): {$baseCommission} at {$actualPercentage}% (Level {$level}, Full amount, not limited by 2X)");

    return $baseCommission;
}
```

---

### 3. **CommissionService Update** ✅
**File**: [app/Services/CommissionService.php](d:\laragon\www\mlm\app\Services\CommissionService.php)

**Changes**:
- Modified `calculateCommission()` to use SAME rates for both VIP and Standard
- Now only reads from `standard_commission_l*` fields for both plans
- Removed VIP/Standard differentiation for direct investment commissions
- Both plans get identical commission when downline invests

**Key Code Changed**:
```php
private function calculateCommission(int $level, float $investmentAmount, User $receivingUser = null): array
{
    // Get dynamic commission rate from settings
    // Commission is SAME for both VIP and Standard users (direct investment commission)
    $setting = \App\Models\Setting::first();
    $userPlan = $receivingUser?->user_plan ?? 'standard';

    // Use standard commission rates for BOTH plans (client requirement)
    $fieldName = "standard_commission_l{$level}";
    $percentage = $setting->$fieldName ?? self::COMMISSION_RATES[$level] ?? 0;

    $amount = ($investmentAmount * $percentage) / 100;

    return [
        'percentage' => $percentage,
        'amount' => round($amount, 2),
        'user_plan' => $userPlan
    ];
}
```

---

### 4. **New Admin Panel - Profit Share Settings** ✅
**File**: [resources/views/admin/roi-settings/profit-share-settings.blade.php](d:\laragon\www\mlm\resources\views\admin\roi-settings\profit-share-settings.blade.php)

**Features**:
- Separate dedicated page for configuring profit share percentages
- Shows Standard and VIP settings side by side
- Includes comparison table showing the difference
- Shows required users per level (10, 50, 150, 400, 1000, 2000, 4000)
- Clear documentation about the difference between plans
- Default values displayed for quick reference
- Visual badges showing "Full Profit Share" vs "Half Profit Share"

**Route**: `/roi-settings/profit-share-settings`

**Visual Structure**:
1. Info alert explaining profit share concept
2. Standard Package settings card (7 level inputs)
3. VIP Package settings card (7 level inputs)
4. Comparison table (side-by-side view)
5. Save button

---

### 5. **ROISettingsController Update** ✅
**File**: [app/Http/Controllers/Admin/ROISettingsController.php](d:\laragon\www\mlm\app\Http\Controllers\Admin\ROISettingsController.php)

**New Methods Added**:

```php
/**
 * Show profit share settings page
 */
public function profitShareSettings()
{
    $setting = \App\Models\Setting::first();

    if (!$setting) {
        return back()->with('error', 'Settings not found!');
    }

    return view('admin.roi-settings.profit-share-settings', compact('setting'));
}

/**
 * Update profit share settings
 */
public function updateProfitShare(Request $request)
{
    $rules = [];

    // Add validation for all 14 profit share levels
    for ($i = 1; $i <= 7; $i++) {
        $rules["standard_profit_l{$i}"] = 'required|numeric|min:0|max:100';
        $rules["vip_profit_l{$i}"] = 'required|numeric|min:0|max:100';
    }

    $validated = $request->validate($rules);

    $setting = \App\Models\Setting::first();

    if (!$setting) {
        return back()->with('error', 'Settings not found!');
    }

    $setting->update($validated);

    return back()->with('success', 'Profit share settings updated successfully!');
}
```

---

### 6. **Routes Added** ✅
**File**: [routes/web.php](d:\laragon\www\mlm\routes\web.php)

```php
Route::get('/profit-share-settings', 'profitShareSettings')
    ->name('admin.roi-settings.profit-share-settings');
Route::post('/profit-share-settings/update', 'updateProfitShare')
    ->name('admin.roi-settings.update-profit-share');
```

---

### 7. **Setting Model Update** ✅
**File**: [app/Models/Setting.php](d:\laragon\www\mlm\app\Models\Setting.php)

Added to `$fillable` array:
```php
'standard_profit_l1','standard_profit_l2','standard_profit_l3','standard_profit_l4',
'standard_profit_l5','standard_profit_l6','standard_profit_l7',
'vip_profit_l1','vip_profit_l2','vip_profit_l3','vip_profit_l4',
'vip_profit_l5','vip_profit_l6','vip_profit_l7'
```

---

### 8. **Database Values Updated** ✅

Executed via Tinker to correct commission values:
```php
$setting->update([
    // Both VIP and Standard get SAME commission rates
    'standard_commission_l1' => 7.00,
    'standard_commission_l2' => 2.00,
    'standard_commission_l3' => 1.50,
    'standard_commission_l4' => 1.25,
    'standard_commission_l5' => 1.00,
    'standard_commission_l6' => 0.75,
    'standard_commission_l7' => 0.50,
    'vip_commission_l1' => 7.00,
    'vip_commission_l2' => 2.00,
    'vip_commission_l3' => 1.50,
    'vip_commission_l4' => 1.25,
    'vip_commission_l5' => 1.00,
    'vip_commission_l6' => 0.75,
    'vip_commission_l7' => 0.50,
]);
```

Profit share columns automatically set by migration defaults (7,6,5,4,3,2,1 for Standard; 3.5,3,2.5,2,1.5,1,0.5 for VIP)

---

### 9. **ROI Settings Index Updated** ✅
**File**: [resources/views/admin/roi-settings/index.blade.php](d:\laragon\www\mlm\resources\views\admin\roi-settings\index.blade.php)

Added navigation buttons in header:
```html
<a href="{{ route('admin.roi-settings.profit-share-settings') }}" class="btn btn-light-success font-weight-bolder mr-3">
    <i class="la la-percentage"></i> Profit Share Settings
</a>
<a href="{{ route('admin.roi-settings.commission-bonuses') }}" class="btn btn-light-warning font-weight-bolder mr-3">
    <i class="la la-money-bill-wave"></i> Commission Settings
</a>
<a href="{{ route('admin.roi-settings.user-plans') }}" class="btn btn-light-primary font-weight-bolder">
    <i class="la la-users"></i> Manage User Plans
</a>
```

---

## How It Works Now

### Scenario 1: When User Invests (Direct Commission)
```
User A invests $100
    ↓
CommissionService::assignCommissions() triggered
    ↓
For each upline (Level 1-7):
    ↓
    Read from standard_commission_l{level}
    (SAME for both VIP and Standard upline)
    ↓
    Example: Level 1 sponsor gets $7 (7% of $100)
    Whether sponsor is VIP or Standard doesn't matter
```

### Scenario 2: When User Receives ROI (Profit Share)
```
User A receives $10 ROI payment
    ↓
ROICommissionService::generateCommissions() triggered
    ↓
For each upline (Level 1-7):
    ↓
    Check upline's user_plan (VIP or Standard)
    ↓
    If Standard:
        Read from standard_profit_l{level}
        Example L1: 7% of $10 = $0.70
    ↓
    If VIP:
        Read from vip_profit_l{level}
        Example L1: 3.5% of $10 = $0.35
    ↓
    VIP gets HALF because they already get higher ROI on own investments
```

---

## Admin Usage Guide

### To Configure Direct Investment Commissions:
1. Go to: **ROI Settings** → **Commission Settings**
2. Update rates for Standard (L1-L7)
3. Update rates for VIP (L1-L7)
4. **Important**: Both should be kept SAME as per client requirement
5. Click "Save All Bonuses"

**Current Values** (Both Same):
- L1: 7%, L2: 2%, L3: 1.5%, L4: 1.25%, L5: 1%, L6: 0.75%, L7: 0.5%

### To Configure Profit Share (ROI Commissions):
1. Go to: **ROI Settings** → **Profit Share Settings** (NEW PAGE)
2. Update Standard rates (full percentages)
   - Default: 7%, 6%, 5%, 4%, 3%, 2%, 1%
3. Update VIP rates (half percentages)
   - Default: 3.5%, 3%, 2.5%, 2%, 1.5%, 1%, 0.5%
4. Click "Save Profit Share Settings"

---

## Verification

### Current Database State:

**Commission Settings (Same for Both):**
```
Standard L1-L7: 7.00%, 2.00%, 1.50%, 1.25%, 1.00%, 0.75%, 0.50%
VIP L1-L7:      7.00%, 2.00%, 1.50%, 1.25%, 1.00%, 0.75%, 0.50%
                ✅ IDENTICAL
```

**Profit Share Settings (Different):**
```
Standard L1-L7: 7.00%, 6.00%, 5.00%, 4.00%, 3.00%, 2.00%, 1.00%
VIP L1-L7:      3.50%, 3.00%, 2.50%, 2.00%, 1.50%, 1.00%, 0.50%
                ✅ VIP = Standard ÷ 2
```

---

## Files Modified Summary

| File | Status | Changes |
|------|--------|---------|
| `database/migrations/2025_12_23_165657_add_profit_share_columns_to_settings_table.php` | ✅ NEW | Created profit share columns |
| `app/Services/ROICommissionService.php` | ✅ MODIFIED | Read profit share from DB, VIP/Standard differentiation |
| `app/Services/CommissionService.php` | ✅ MODIFIED | Use same rates for both plans |
| `resources/views/admin/roi-settings/profit-share-settings.blade.php` | ✅ NEW | Admin page for profit share config |
| `app/Http/Controllers/Admin/ROISettingsController.php` | ✅ MODIFIED | Added profitShareSettings() and updateProfitShare() |
| `routes/web.php` | ✅ MODIFIED | Added 2 new routes |
| `app/Models/Setting.php` | ✅ MODIFIED | Added profit share fields to fillable |
| `resources/views/admin/roi-settings/index.blade.php` | ✅ MODIFIED | Added navigation buttons |

---

## Testing Checklist

- [x] Migration executed successfully
- [x] Database columns created with correct defaults
- [x] Commission values updated (both VIP/Standard same)
- [x] Profit share values set correctly (VIP = Standard ÷ 2)
- [x] ROICommissionService reads from database
- [x] CommissionService uses same rates for both plans
- [x] New admin page accessible at `/roi-settings/profit-share-settings`
- [x] Routes working correctly
- [x] Setting model fillable array updated
- [x] Navigation buttons added to ROI Settings index

---

## Client Requirements: ✅ ALL COMPLETED

1. ✅ **"Commission setting algg dena"** (Separate commission settings)
   - Commission settings at `/roi-settings/commission-bonuses`
   - Profit share settings at `/roi-settings/profit-share-settings`

2. ✅ **"Aur profit sharing algg"** (And profit sharing separate)
   - Completely separate admin pages
   - Different database columns
   - Different service logic

3. ✅ **"کمیشن دونوں میں ایک جیسا جائے گا"** (Commission same in both)
   - Both VIP and Standard: 7%, 2%, 1.5%, 1.25%, 1%, 0.75%, 0.5%
   - CommissionService no longer differentiates by plan

4. ✅ **"پرافٹ شیئر اسٹینڈرڈ میں پورا جنکہ Vip میں آدھا جائے گا"** (Profit share full in Standard, half in VIP)
   - Standard: 7%, 6%, 5%, 4%, 3%, 2%, 1%
   - VIP: 3.5%, 3%, 2.5%, 2%, 1.5%, 1%, 0.5% (exactly half)

5. ✅ **New admin tab to configure profit share**
   - New dedicated page created
   - Easy to configure both plans
   - Visual comparison table
   - Clear documentation

---

## Important Notes

### Why VIP Gets Lower Profit Share?
- VIP users already receive **higher ROI percentages** on their own investments
- To balance the system, they receive **half the profit share** from downline ROI
- Standard users get full profit share to compensate for lower ROI on own investments

### Backward Compatibility
- Existing data preserved
- New columns added without affecting old records
- Default values ensure system works even if admin doesn't configure
- Fallback logic in services (uses constants if DB value missing)

### Independence from 2X/7X Limits
- Profit sharing continues even after user's ROI stopped at 2X
- This was a previous fix and is maintained
- Users can keep earning profit share from downline ROI regardless of their own ROI status

---

## Future Enhancements (Optional)

1. **Export/Import Settings**
   - Export current profit share configuration
   - Import from JSON/Excel file

2. **Historical Tracking**
   - Log changes to profit share percentages
   - Show who changed what and when

3. **A/B Testing**
   - Temporarily test different percentages
   - Compare performance

4. **Auto-Calculate VIP Rates**
   - When Standard rate changes, auto-set VIP to half
   - Checkbox to enable/disable auto-sync

5. **Bulk Operations**
   - Apply percentage changes to historical data
   - Recalculate past commissions (if needed)

---

## Support & Maintenance

### If Issues Arise:

**Profit share not working:**
1. Check database values: `SELECT standard_profit_l1, vip_profit_l1 FROM settings`
2. Check user's plan: `SELECT id, username, user_plan FROM users WHERE id = X`
3. Check logs: Look for "Calculating profit share commission" entries

**Commission not same for both:**
1. Check database: `SELECT standard_commission_l1, vip_commission_l1 FROM settings`
2. Should be identical
3. Update if different

**Admin page not accessible:**
1. Check routes: `php artisan route:list | grep profit-share`
2. Clear cache: `php artisan route:clear && php artisan config:clear`
3. Check middleware: Ensure admin role

---

## Conclusion

This implementation successfully separates direct investment commissions from profit share (ROI) commissions, meeting all client requirements:

- ✅ Separate admin panels for each system
- ✅ Commission same for both plans
- ✅ Profit share different (Standard full, VIP half)
- ✅ Easy to configure via admin panel
- ✅ Database-driven (no hardcoded values in ROI service)
- ✅ Backward compatible
- ✅ Well-documented

The system is **production-ready** and meets the exact specifications provided by the client.
