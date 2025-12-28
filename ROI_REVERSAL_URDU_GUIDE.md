# ROI Reversal System - اردو گائیڈ

## مسئلہ کیا تھا؟

**کیا ہوا:**
- Standard Plan کی ROI Percentage غلطی سے **42%** set ہو گئی تھی **0.42%** کی بجائے
- یہ **2 دن** تک چلا
- نتیجہ: Users کو **100 گنا زیادہ ROI** مل گیا
- **حل:** Standard plan users کے **83.16%** ROI واپس لینا ہے

---

## حل کی تفصیل

### تیز طریقہ: Command Line استعمال کریں

**پہلے Preview دیکھیں (بغیر کوئی تبدیلی کیے):**
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share --dry-run
```

**اصل Reversal Execute کریں:**
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share
```

---

## موجودہ صورتحال

### ROI جو غلطی سے دیا گیا (آخری 2 دن):
- **کل Entries:** 173
- **کل Users:** 173
- **کل ROI دیا گیا:** $27,898.91

### کیا واپس لیا جائے گا (83.16%):
- **واپس لینے کی رقم:** $23,200.73
- **باقی رہنے والی رقم:** $4,698.18 (یہ صحیح amount ہے)

### Profit Share بھی Adjust ہو گا:
- **Entries متاثر:** 50
- **واپس لینے کی رقم:** $155.53

---

## زیادہ متاثر ہونے والے Users:

1. **نصیر احمد:** $4,413.46 → واپس: $3,670.71
2. **عبدالوہاب:** $1,482.18 → واپس: $1,232.54
3. **نگینہ حکیم:** $1,465.80 → واپس: $1,219.00
4. **نوید احمد خان:** $1,009.59 → واپس: $839.57
5. اور 168 Users...

---

## Admin Panel سے استعمال

### رسائی:
1. Admin کے طور پر login کریں
2. یہاں جائیں: `https://globalvisionersint.com/admin/roi-reversal`

### استعمال:
1. **Reversal Percentage** دیں: `83.16`
2. **Target Plan** منتخب کریں: `Standard`
3. **Days to Look Back** دیں: `2`
4. **Checkbox** check کریں: "Also reverse profit sharing"
5. **Reason** لکھیں: "42% غلطی سے set ہو گیا تھا، 0.42% ہونا چاہیے تھا"
6. **Preview Reversal** button دبائیں
7. تفصیل check کریں
8. **Execute Reversal** button دبائیں
9. دو confirmations دیں

---

## کیا ہوگا جب Reversal Execute ہوگا؟

### ہر User کے لیے:

**پہلے (Before):**
```
User: نصیر احمد
Wallet Type: roi
Balance: $4,413.46
```

**بعد میں (After):**
```
User: نصیر احمد
Wallet Type: roi
Balance: $742.75 (صرف 16.84% باقی - یہ صحیح amount ہے)

+ نیا Entry:
Wallet Type: roi_reversal
Balance: -$3,670.71 (83.16% واپس لیا گیا)
```

---

## Safety Features (حفاظتی خصوصیات)

### ✅ Dry-Run Mode
- پہلے preview دیکھ سکتے ہیں
- کوئی تبدیلی نہیں ہوگی
- صرف دیکھنے کے لیے

### ✅ Transaction Safety
- اگر کوئی غلطی ہو تو سب کچھ واپس ہو جائے گا
- نامکمل reversal نہیں ہوگا

### ✅ Complete Record
- ہر reversal کا مکمل ریکارڈ رہے گا
- کس نے کیا، کب کیا، کیوں کیا - سب محفوظ

### ✅ Confirmation Prompts
- دو مرتبہ confirmation مانگی جائے گی
- حادثاتی execution نہیں ہوگا

---

## Step-by-Step فوری گائیڈ

### مرحلہ 1: پہلے Preview دیکھیں
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share --dry-run
```

**چیک کریں:**
- کل entries تقریباً 173 ہیں
- Reversal amount تقریباً $23,200 ہے
- Users کی list صحیح ہے

### مرحلہ 2: Reversal Execute کریں
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share
```

**یہ کام ہوں گے:**
1. تمام ROI entries update ہوں گی (83.16% کم)
2. Reversal records بنیں گے
3. Profit share entries adjust ہوں گی
4. سب کچھ log ہوگا

### مرحلہ 3: Verify کریں
```bash
# Reversals کی تعداد check کریں
mysql -u root -proot mlm-28-12-25 -e "SELECT COUNT(*) FROM wallets WHERE wallet_type = 'roi_reversal'"

# کل reversed amount check کریں
mysql -u root -proot mlm-28-12-25 -e "SELECT SUM(balance) FROM wallets WHERE wallet_type = 'roi_reversal'"
```

---

## متوقع نتائج

جب آپ reversal execute کریں گے:

✅ **ROI Entries متاثر:** 173
✅ **ROI واپس لی گئی:** ~$23,200.73
✅ **Profit Share Entries:** 50
✅ **Profit Share واپس لیا گیا:** ~$155.53
✅ **Users متاثر:** 173 (Standard plan)

---

## مستقبل میں احتیاط

### 1. ROI Percentage تبدیل کرنے سے پہلے:
   - دو بار check کریں: 0.42% ہے یا 42%؟
   - پہلے ایک user پر test کریں

### 2. Database Backup:
   ```bash
   mysqldump -u root -proot mlm-28-12-25 > backup_before_changes.sql
   ```

### 3. Daily Monitoring:
   - روزانہ ROI distribution check کریں
   - اگر amount بہت زیادہ ہو تو فوراً دیکھیں

---

## فوری حوالہ

### موجودہ مسئلہ حل کرنے کے لیے:

**Preview (دیکھنے کے لیے):**
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share --dry-run
```

**Execute (اصل reversal):**
```bash
php artisan roi:reverse --percentage=83.16 --plan=standard --days=2 --include-profit-share
```

---

## خلاصہ

✅ **دو طریقے:** Command Line (تیز) یا Admin Panel (visual)

✅ **مکمل محفوظ:** Preview mode، Transaction safety، Complete record

✅ **موجودہ کام:** Standard plan کے 83.16% ROI واپس لینا (آخری 2 دن)

✅ **کل واپس لینا:** $23,200.73 (ROI) + $155.53 (Profit Share)

✅ **Users متاثر:** 173 Standard plan users

**جب آپ چاہیں execute کر سکتے ہیں!**

---

## اگر مسئلہ ہو تو:

### Logs دیکھیں:
```bash
tail -f storage/logs/laravel.log
```

### یا Log Viewer استعمال کریں:
- یہاں جائیں: `https://globalvisionersint.com/log-viewer`

### Admin Settings:
- ROI Reversal کی مکمل settings Admin Panel میں دستیاب ہیں
- Complete history دیکھ سکتے ہیں
- کسی بھی وقت reversal کر سکتے ہیں

**تیار ہو جائیں، جب چاہیں execute کریں! 🚀**
