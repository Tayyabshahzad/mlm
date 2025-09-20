# 🚀 ROI System Deployment Guide

## ✅ Current Laravel Scheduler Configuration

Your system already has the correct scheduler setup in `routes/console.php`:

```php
Schedule::command('roi:generate-weekly')
    ->dailyAt('23:40')
    ->timezone('Asia/Karachi')
    ->when(function () {
        return Carbon::now('Asia/Karachi')->dayOfWeek !== Carbon::FRIDAY;
    });
```

**This is PERFECT and ready to deploy!**

## 🛠 Server Deployment Steps

### 1. **Upload Code to Server**
```bash
# Upload all your code including the new files:
# - app/Services/AutomatedROIService.php
# - app/Console/Commands/ProcessAutomatedROI.php
# - app/Console/Commands/GenerateHistoricalROI.php
# - Updated app/Http/Controllers/TopupController.php
# - Database migrations (already exist)
```

### 2. **Run Database Migrations**
```bash
# On the server, run:
cd /path/to/your/mlm/project
php artisan migrate
```

### 3. **Set Up Server Cron Job**
Add this single line to your server's crontab:

```bash
# Edit crontab
crontab -e

# Add this line (replace /path/to/your/project with actual path):
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

**That's it!** Laravel's scheduler will handle the rest.

## 🕐 How the Schedule Works

### **Current Configuration:**
- ⏰ **Time**: 11:40 PM Pakistan Time (23:40)
- 📅 **Days**: Monday, Tuesday, Wednesday, Thursday, Saturday, Sunday
- ❌ **Excluded**: Friday (as requested)
- 🌍 **Timezone**: Asia/Karachi (Pakistan Time)

### **What Happens Each Day:**
1. At 11:40 PM Pakistan time, Laravel checks if it's Friday
2. If NOT Friday → Runs `php artisan roi:generate-weekly`
3. If Friday → Skips the command completely
4. The command processes all eligible users automatically

## 🔧 Server Requirements Check

### **Before Deployment, Verify:**

1. **PHP Timezone**
```bash
# Check server timezone
php -r "echo date_default_timezone_get();"
```

2. **Server Timezone**
```bash
# Check system timezone
timedatectl
# OR
date
```

3. **Laravel Configuration**
```bash
# Check app timezone in .env
grep TIMEZONE .env
# Should be: APP_TIMEZONE=Asia/Karachi
```

## 🚨 Important Configuration Check

### **If Server Timezone ≠ Pakistan Time:**

You have two options:

#### **Option A: Server-Level (Recommended)**
```bash
# Set server timezone to Pakistan
sudo timedatectl set-timezone Asia/Karachi
```

#### **Option B: Application-Level**
```bash
# In .env file, ensure:
APP_TIMEZONE=Asia/Karachi
```

## 📊 Testing After Deployment

### **1. Test Scheduler Setup**
```bash
# Check if scheduler recognizes the command
php artisan schedule:list
```

### **2. Test ROI Command**
```bash
# Dry run to verify everything works
php artisan roi:generate-weekly
```

### **3. Test Historical Command** (if needed)
```bash
# Test historical ROI for yesterday
php artisan roi:generate-historical $(date -d yesterday +%Y-%m-%d) --dry-run
```

### **4. Verify Cron Job**
```bash
# Check if cron is working
crontab -l
```

## 🎯 Expected Behavior After Deployment

### **Daily at 11:40 PM Pakistan Time:**
✅ Monday-Thursday, Saturday-Sunday: ROI distributed to all eligible users
❌ Friday: No ROI distribution
✅ Automatic commission generation for uplines
✅ 2X limit checking and account stopping
✅ Duplicate prevention (won't pay twice same day)

### **When Users Top Up:**
✅ Immediate ROI reactivation if below 2X limit
✅ Instant ROI payment after successful topup
✅ Smart duplicate prevention with daily ROI

## 🔍 Monitoring & Logs

### **Check ROI Processing:**
```bash
# View Laravel logs
tail -f storage/logs/laravel.log | grep ROI

# Check recent ROI transactions
php artisan tinker
>>> App\Models\ROITransaction::latest()->take(10)->get()
```

### **Scheduler Logs:**
```bash
# Check if scheduler runs
grep "schedule:run" /var/log/syslog
```

## 🆘 Troubleshooting

### **If ROI Doesn't Run:**
1. Check crontab is set correctly
2. Verify server timezone
3. Check Laravel logs for errors
4. Test manual command execution

### **If Users Don't Get ROI After Topup:**
1. Check if AutomatedROIService exists
2. Verify database migrations ran
3. Check user eligibility (not blocked, active status)

### **Command Reference:**
```bash
# Manual ROI for all users
php artisan roi:generate-weekly

# Manual ROI for specific user
php artisan roi:process-automated --user-id=123

# Historical ROI for specific date
php artisan roi:generate-historical 2025-09-20

# Dry run (no changes)
php artisan roi:generate-weekly --dry-run
```

## ✅ Final Checklist

- [ ] Code uploaded to server
- [ ] Database migrations executed
- [ ] Cron job added to crontab
- [ ] Server timezone verified (Pakistan)
- [ ] Laravel scheduler tested
- [ ] ROI command tested manually
- [ ] Logs monitored for first execution

**🎉 Your ROI system is ready for production!**