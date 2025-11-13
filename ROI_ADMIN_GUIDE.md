# ROI System Admin Guide - VIP & Standard Plans

## Quick Access

### Admin Routes
1. **ROI Percentage Settings**: `/roi-settings`
   - View current Standard and VIP percentages
   - Update percentages for both plans
   - See calculation examples

2. **User Plan Assignment**: `/roi-settings/user-plans`
   - View all users with investments
   - Assign VIP or Standard plan to users
   - See statistics by plan type

---

## Using the Admin Interface

### 1. Setting ROI Percentages

**Step 1**: Navigate to ROI Settings
- Login as admin
- Go to: Settings → ROI Settings
- Or directly: `http://your-domain.com/roi-settings`

**Step 2**: View Current Settings
- Top cards show current percentages for both plans
- Standard Plan (Blue card)
- VIP Plan (Yellow card with star icon)

**Step 3**: Update Percentages
- Scroll to "Update ROI Percentages" form
- Enter new percentage for Standard Plan (e.g., 3.00)
- Enter new percentage for VIP Plan (e.g., 5.00)
- Review the calculation examples below the form
- Click "Update Percentages" button

**Step 4**: Confirmation
- Green success message appears
- Cards update with new percentages
- Changes take effect on next ROI generation

---

### 2. Assigning Plans to Users

**Step 1**: Navigate to User Plans
- From ROI Settings page, click "Manage User Plans" button
- Or directly: `http://your-domain.com/roi-settings/user-plans`

**Step 2**: View User Statistics
- Top stats cards show:
  - Total users with investments
  - Standard plan users count
  - VIP plan users count

**Step 3**: Assign Plan to User
- Browse the user list (shows 50 users per page)
- Each user shows:
  - User ID and name
  - Email address
  - Investment amount
  - Current plan (badge)
  - Action buttons

**Step 4**: Change User Plan
- Click "Set Standard" to assign Standard plan
- Click "Set VIP" to assign VIP plan
- Confirm the action in popup
- Page reloads with success message
- User's badge updates to show new plan

---

## Understanding the Interface

### ROI Settings Page Elements

**Stats Cards (Top Section)**:
```
┌─────────────────────────────┐  ┌─────────────────────────────┐
│ Standard Plan               │  │ VIP Plan                    │
│ Daily ROI Percentage for    │  │ Daily ROI Percentage for    │
│ Regular Users               │  │ Premium Users               │
│                             │  │                             │
│ 3.00%                       │  │ 5.00%                       │
└─────────────────────────────┘  └─────────────────────────────┘
```

**Update Form**:
- Two input fields with percentage symbol
- Min: 0%, Max: 100%
- Step: 0.01 (allows decimals like 3.50%)
- Real-time validation

**Calculation Examples**:
- Shows daily ROI amount for $1,000 investment
- Shows days to reach 2X commitment
- Updates based on entered percentages

### User Plans Page Elements

**User Table Columns**:
1. **ID**: User's database ID
2. **User**: Name with avatar and username
3. **Email**: User's email address
4. **Investment**: Total ROI-eligible investment amount
5. **Current Plan**: Badge showing Standard or VIP
6. **Actions**: Buttons to change plan

**Plan Badges**:
- Standard: Blue badge with user icon
- VIP: Yellow badge with star icon

---

## How Plans Affect ROI

### Standard Plan (Default)
- **Default for all users**: New users automatically get Standard
- **Lower percentage**: Typically 3-4% daily
- **Suitable for**: Regular members

### VIP Plan (Premium)
- **Admin assigned**: Must be manually assigned by admin
- **Higher percentage**: Typically 5-7% daily
- **Suitable for**: Premium members, high investors

### Real Example

**User Investment**: $1,000

| Plan     | Daily %  | Daily ROI | Days to 2X |
|----------|----------|-----------|------------|
| Standard | 3.00%    | $30.00    | ~67 days   |
| VIP      | 5.00%    | $50.00    | ~40 days   |

---

## Important Notes

### ⚠️ Critical Information

1. **Existing Investments**:
   - Changing a user's plan affects NEW investments only
   - Existing active investments continue with their original plan percentage
   - This is tracked in `user_investments.user_plan_at_time`

2. **Percentage Changes**:
   - Changes take effect from next ROI generation cycle
   - Usually runs daily via cron: `php artisan roi:generate-weekly`
   - Does NOT retroactively change existing investments

3. **2X Commitment**:
   - Each investment tracks its own 2X limit
   - When investment reaches 2X, ROI stops for that investment only
   - User can top-up to create new investment with new ROI cycle

4. **Plan Assignment Best Practices**:
   - Review user's total investment before assigning VIP
   - Consider user's history and loyalty
   - Document your plan assignment criteria

---

## Common Admin Tasks

### Task 1: Promote User to VIP
```
1. Go to: /roi-settings/user-plans
2. Find user in list (use search if needed)
3. Click "Set VIP" button next to user
4. Confirm action
5. User's next investment will use VIP percentage
```

### Task 2: Adjust ROI Rates
```
1. Go to: /roi-settings
2. Enter new percentages:
   - Standard: 3.50%
   - VIP: 5.50%
3. Review examples to verify math
4. Click "Update Percentages"
5. Changes apply to next ROI run
```

### Task 3: Check Plan Distribution
```
1. Go to: /roi-settings/user-plans
2. View top stats cards:
   - Total: 500 users
   - Standard: 450 users (90%)
   - VIP: 50 users (10%)
3. This helps monitor VIP adoption rate
```

---

## Troubleshooting

### Issue: User not receiving VIP percentage after assignment

**Solution**:
1. Check when user was assigned VIP plan
2. Check if they have active investments BEFORE plan change
3. Those old investments continue with old percentage
4. User needs to make NEW investment/topup to get VIP rate

**To Verify**:
```sql
-- Check user's current plan
SELECT id, name, user_plan FROM users WHERE id = 123;

-- Check user's investments
SELECT id, amount, user_plan_at_time, roi_status, created_at
FROM user_investments
WHERE user_id = 123;
```

### Issue: Percentage changes not applying

**Solution**:
1. Verify percentage was saved: Check `/roi-settings` page
2. Check if ROI command has run since change
3. Run manually: `php artisan roi:generate-weekly`
4. Check logs: `storage/logs/laravel.log`

---

## Testing the System

### Test Scenario 1: Assign VIP Plan
```
User: test@example.com
Current Plan: Standard (3%)
Action: Assign VIP (5%)

Steps:
1. User has $100 investment at Standard (3%)
2. Admin assigns VIP plan
3. User tops up $50
4. New investment ($50) uses VIP rate (5%)
5. Old investment ($100) continues at Standard (3%)

Expected Daily ROI:
- Old investment: $100 × 3% = $3
- New investment: $50 × 5% = $2.50
- Total: $5.50 per day
```

### Test Scenario 2: Change Global Percentages
```
Before: Standard = 3%, VIP = 5%
After: Standard = 3.5%, VIP = 6%

Steps:
1. Update percentages via admin panel
2. Run ROI command: php artisan roi:generate-weekly
3. Check user ROI amounts in wallets table
4. Verify calculations match new percentages

Note: Only NEW ROI payments use new rates
```

---

## Database Reference

### Key Tables

**weeks** - Stores ROI percentages
```sql
id | week_name | standard_percentage | vip_percentage
1  | Week 1    | 3.00               | 5.00
```

**users** - Stores user's assigned plan
```sql
id | name      | user_plan | roi_eligible_investment_amount
1  | John Doe  | vip       | 1000.00
2  | Jane Doe  | standard  | 500.00
```

**user_investments** - Tracks per-investment details
```sql
id | user_id | amount | committed_amount | total_earnings | roi_status | user_plan_at_time
1  | 1       | 100    | 200             | 50            | active     | standard
2  | 1       | 50     | 100             | 0             | active     | vip
```

---

## Quick Reference Commands

```bash
# Run ROI generation manually
php artisan roi:generate-weekly

# Check user's active investments
php artisan tinker
>>> $user = User::find(1);
>>> $user->investments()->active()->get();

# Check current ROI settings
>>> Week::first();

# Update user plan via console (if needed)
>>> $user = User::find(1);
>>> $user->update(['user_plan' => 'vip']);
```

---

## Need Help?

**Documentation**: See `ROI_SYSTEM_IMPLEMENTATION.md` for technical details

**Support**: Contact system administrator

**Logs**: Check `storage/logs/laravel.log` for ROI processing logs
