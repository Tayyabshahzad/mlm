# ROI Reversal System - Complete Guide

## Overview
This system allows you to reverse incorrect ROI distributions that were sent due to misconfigured percentages. It includes both a **command-line tool** for quick reversals and an **admin web interface** for a visual approach.

---

## Problem Description

**What Happened:**
- Standard Plan ROI percentage was mistakenly set to **42%** instead of **0.42%**
- This ran for **2 days**
- Result: Users received **100x more ROI** than intended
- **Solution:** Reverse **83.16%** of the distributed ROI for Standard plan users

---

## Solution 1: Command Line Tool (Fastest)

### Basic Usage

```bash
# DRY RUN (Preview only - recommended first)
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share --dry-run

# LIVE EXECUTION (Actually reverses the ROI)
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share
```

### Command Options

| Option | Description | Default |
|--------|-------------|---------|
| `--percentage` | Percentage of ROI to reverse | 83.16 |
| `--plan` | Target plan (standard/vip/all) | standard |
| `--days` | How many days to look back | 2 |
| `--include-profit-share` | Also reverse profit sharing | false |
| `--dry-run` | Preview without making changes | false |

### Examples

**1. Preview reversal for Standard plan only:**
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --dry-run
```

**2. Execute reversal for Standard plan with profit share adjustment:**
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share
```

**3. Reverse 50% of VIP plan ROI from last 7 days:**
```bash
php artisan roi:reverse --percentage=50 --plan=vip --days=7 --dry-run
```

**4. Reverse all plans:**
```bash
php artisan roi:reverse --percentage=83.16 --plan=all --days=2 --include-profit-share
```

---

## Solution 2: Admin Web Interface

### Access the Interface

1. **Login as Admin**
2. **Navigate to:** `https://globalvisionersint.com/admin/roi-reversal`
   - Or go to: **Admin Panel → ROI Reversal**

### Features

#### 1. **Statistics Dashboard**
- View ROI distribution for last 1, 2, 7, and 30 days
- Separate breakdown for Standard and VIP plans
- Real-time totals

#### 2. **Preview Before Execution**
- Configure reversal parameters:
  - Reversal percentage (e.g., 83.16%)
  - Target plan (Standard/VIP/All)
  - Days to look back (1-30)
  - Include profit share adjustment (checkbox)
  - Reason for reversal (required)

- Click **"Preview Reversal"** to see:
  - Total entries affected
  - Total users affected
  - Original total amount
  - Amount that will be reversed
  - Amount that will remain
  - Top 20 affected users with individual breakdowns

#### 3. **Execute Reversal**
- After previewing, click **"Execute Reversal"**
- Two confirmation dialogs for safety
- Real-time execution with results

#### 4. **Recent Reversal History**
- View last 50 reversal actions
- See which users were affected
- Track reversal amounts
- Full audit trail

---

## Current Situation Analysis

Based on the last 2 days of ROI distribution:

### ROI Distribution (Standard Plan)
- **Total Entries:** 173
- **Total Users:** 173
- **Total ROI Distributed:** $27,898.91

### What Will Be Reversed (83.16%)
- **Amount to Reverse:** $23,200.73
- **Amount Remaining:** $4,698.18 (correct amount - 16.84%)

### Profit Share Impact
- **Entries Affected:** 50
- **Amount to Reverse:** $155.53

### Top Affected Users
1. **Nisar Ahmed:** $4,413.46 → Reversal: $3,670.71
2. **Abdul wahab:** $1,482.18 → Reversal: $1,232.54
3. **Nagina Hakim:** $1,465.80 → Reversal: $1,219.00
4. **Naveed Ahmed Khan:** $1,009.59 → Reversal: $839.57
5. (and 168 more users...)

---

## Step-by-Step Execution Guide

### Recommended Approach

#### Step 1: Preview First
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share --dry-run
```

**Review the output carefully:**
- Check the total entries (should be ~173)
- Verify the reversal amount (~$23,200)
- Confirm the affected users list

#### Step 2: Execute Reversal
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share
```

**The system will:**
1. Update all original ROI wallet entries (reduce by 83.16%)
2. Create reversal records for audit trail
3. Update profit share entries proportionally
4. Create profit share reversal records
5. Log all actions

#### Step 3: Verify Results
```bash
# Check recent reversals
mysql -u root -proot mlm-28-12-25 -e "SELECT COUNT(*) FROM wallets WHERE wallet_type = 'roi_reversal'"

# Check total reversed amount
mysql -u root -proot mlm-28-12-25 -e "SELECT SUM(balance) FROM wallets WHERE wallet_type = 'roi_reversal'"
```

---

## What Happens During Reversal

### For Each ROI Entry:

**Before:**
```
User: Nisar Ahmed
Wallet Type: roi
Balance: $4,413.46
```

**After:**
```
User: Nisar Ahmed
Wallet Type: roi
Balance: $742.75 (16.84% remaining)

+ NEW ENTRY:
Wallet Type: roi_reversal
Balance: -$3,670.71 (83.16% reversed)
Description: "ROI Reversal: 83.16% reversed from entry #79583..."
```

### Database Changes:

1. **Original ROI entry updated:**
   - `balance` reduced by 83.16%
   - `total_amount` reduced by 83.16%

2. **New reversal record created:**
   - `wallet_type = 'roi_reversal'`
   - `balance = negative amount (shows reversal)`
   - `description` includes full details
   - `transaction_type = 'reversal'`
   - `wallet_src = 'roi_reversal_command'` or `'admin_roi_reversal'`

3. **Same process for profit share entries** if `--include-profit-share` is used

---

## Safety Features

### 1. **Dry-Run Mode**
- Always preview before executing
- No database changes made
- Shows exact same output as live execution

### 2. **Transaction Safety**
- All changes wrapped in database transaction
- If anything fails, everything rolls back
- No partial reversals

### 3. **Audit Trail**
- Every reversal logged to Laravel logs
- Reversal records permanently stored in database
- Can track who executed, when, and why

### 4. **Confirmation Prompts**
- Command requires confirmation before execution
- Web interface has double confirmation dialogs

### 5. **Detailed Logging**
Every reversal action is logged with:
- Admin ID and name (if via web)
- Timestamp
- Percentage reversed
- Plan affected
- Total entries and amount
- Reason for reversal

---

## Troubleshooting

### Issue: "No ROI entries found"
**Solution:** Check the `--days` parameter. Try increasing it.

```bash
php artisan roi:reverse --days=7 --dry-run
```

### Issue: "Permission denied"
**Solution:** Ensure you're logged in as admin in web interface, or run command from correct directory.

### Issue: Results don't match expected
**Solution:**
1. Run dry-run first
2. Check the date range (`--days`)
3. Verify the plan (`--plan=standard`)
4. Check database directly:
```bash
mysql -u root -proot mlm-28-12-25 -e "SELECT COUNT(*), SUM(balance) FROM wallets WHERE wallet_type = 'roi' AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY) AND user_id IN (SELECT id FROM users WHERE user_plan = 'standard')"
```

---

## Post-Reversal Verification

### 1. Check Total Reversed
```bash
mysql -u root -proot mlm-28-12-25 -e "SELECT
    COUNT(*) as total_reversals,
    SUM(balance) as total_reversed
FROM wallets
WHERE wallet_type IN ('roi_reversal', 'profit_share_reversal')"
```

### 2. Check Specific User
```bash
mysql -u root -proot mlm-28-12-25 -e "SELECT
    wallet_type,
    balance,
    description,
    created_at
FROM wallets
WHERE user_id = 234
ORDER BY created_at DESC
LIMIT 10"
```

### 3. Verify User Balances
```bash
# Check if Standard users now have correct ROI amounts
mysql -u root -proot mlm-28-12-25 -e "SELECT
    u.id,
    u.name,
    u.user_plan,
    SUM(w.balance) as total_roi
FROM users u
JOIN wallets w ON u.id = w.user_id
WHERE w.wallet_type = 'roi'
    AND w.created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)
    AND u.user_plan = 'standard'
GROUP BY u.id
LIMIT 10"
```

---

## Prevention for Future

### 1. **Double-Check Settings Before Changing ROI Percentage**
   - Always verify: 0.42% (not 42%)
   - Test on single user first

### 2. **Use Admin Interface Validations**
   - ROI Settings page now has percentage validation
   - Shows preview before applying

### 3. **Set Up Alerts**
   - Monitor daily ROI distribution totals
   - Alert if distribution exceeds expected thresholds

### 4. **Regular Backups**
   - Always backup database before major ROI changes
   ```bash
   mysqldump -u root -proot mlm-28-12-25 > backup_before_roi_change.sql
   ```

---

## Quick Reference

### Execute Current Reversal (Standard Plan, 83.16%, 2 days)

**DRY RUN:**
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share --dry-run
```

**LIVE:**
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share
```

**Expected Results:**
- ROI Entries Affected: 173
- ROI Amount Reversed: ~$23,200
- Profit Share Entries: 50
- Profit Share Reversed: ~$155

---

## Support

If you encounter any issues:

1. **Check Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Check Log Viewer:**
   - Navigate to: `https://globalvisionersint.com/log-viewer`

3. **Contact Developer** with:
   - Exact command used
   - Error message
   - Screenshot of dry-run output

---

## Summary

✅ **Two tools available:** Command-line (faster) and Web Interface (visual)

✅ **Safe execution:** Dry-run mode, transaction safety, audit trail

✅ **Current task:** Reverse 83.16% of Standard plan ROI from last 2 days

✅ **Expected reversal:** $23,200.73 from ROI + $155.53 from profit share

✅ **Users affected:** 173 Standard plan users

**Ready to execute when you are!**
