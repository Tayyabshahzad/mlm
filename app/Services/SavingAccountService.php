<?php

namespace App\Services;

use App\Models\SavingInstalment;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SavingAccountService
{
    private const PLAN_MONTHS = 25;
    private const SAVING_COMMISSION_RATES = [
        1 => 7.00,
        2 => 2.00,
        3 => 1.00,
        4 => 1.00,
        5 => 1.00,
        6 => 1.00,
        7 => 1.00,
    ];

    // 7x7 rule: to receive commission at level N you must have N direct referrals
    // in the saving tree (same formula as the standard CommissionService).
    private const SAVING_TEAM_REQUIREMENTS = [
        1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7,
    ];

    public function __construct(private WalletService $walletService) {}

    // -------------------------------------------------------------------------
    // Instalment Schedule
    // -------------------------------------------------------------------------

    /**
     * Create the full 25-month instalment schedule for a saving account user.
     * Instalment 1 = registration date (due immediately).
     * Instalments 2–25 = monthly thereafter on the same day-of-month.
     */
    public function createInstalmentSchedule(User $user, float $firstAmount, float $monthlyAmount): void
    {
        $startDate = Carbon::parse($user->saving_plan_start_date);
        $setting   = Setting::first();
        $months    = $setting->saving_plan_months ?? self::PLAN_MONTHS;

        for ($i = 1; $i <= $months; $i++) {
            $dueDate = $i === 1
                ? $startDate->copy()
                : $startDate->copy()->addMonths($i - 1);

            $amount = $i === 1 ? $firstAmount : $monthlyAmount;

            // If $5-only registration, instalment 1 is the remaining $19
            if ($i === 1 && !$user->saving_registration_completed) {
                $amount = $setting->saving_min_deposit ?? 19.00;
            }

            SavingInstalment::create([
                'user_id'           => $user->id,
                'instalment_number' => $i,
                'amount'            => $amount,
                'due_date'          => $dueDate->toDateString(),
                'status'            => 'pending',
            ]);
        }
    }

    /**
     * Mark instalment 1 as confirmed immediately when the user registers with the full $24.
     */
    public function markFirstInstalmentConfirmed(User $user, string $transactionId, int $adminId): void
    {
        $instalment = SavingInstalment::where('user_id', $user->id)
            ->where('instalment_number', 1)
            ->first();

        if (!$instalment) {
            return;
        }

        $instalment->update([
            'status'           => 'confirmed',
            'transaction_id'   => $transactionId,
            'submitted_at'     => now(),
            'confirmed_at'     => now(),
            'confirmed_by'     => $adminId,
            'submitted_amount' => $instalment->amount,
            'deposited_at'     => now(),
            'is_late'          => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // Next Due Instalment
    // -------------------------------------------------------------------------

    public function nextDueInstalment(User $user): ?SavingInstalment
    {
        return SavingInstalment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('instalment_number')
            ->first();
    }

    /**
     * Auto-enroll a standard user as a saving-tree referrer when someone registers
     * under them in the saving system. They earn commissions and can see their saving
     * team / wallets — but they are NOT on the saving plan (no instalments).
     * No admin approval is needed for pure referrers.
     */
    public function autoEnrollSponsor(User $sponsor): void
    {
        // Only standard investment users who aren't already enrolled / in the plan
        if ($sponsor->account_type === 'saving' || $sponsor->saving_enrolled) {
            return;
        }

        $sponsor->update([
            'saving_enrolled'                => true,
            'saving_enrollment_activated'    => true,
            'saving_enrollment_activated_at' => now(),
            'saving_enrollment_activated_by' => null, // system-triggered, not by admin
        ]);
    }

    public function getInstalmentSummary(User $user): array
    {
        $instalments   = SavingInstalment::where('user_id', $user->id)->orderBy('instalment_number')->get();
        $totalAmount   = $instalments->sum('amount');
        $paidAmount    = $instalments->where('status', 'confirmed')->sum('amount');
        $remaining     = $totalAmount - $paidAmount;
        $paidCount     = $instalments->where('status', 'confirmed')->count();
        $totalCount    = $instalments->count();

        return [
            'instalments'   => $instalments,
            'total_amount'  => $totalAmount,
            'paid_amount'   => $paidAmount,
            'remaining'     => $remaining,
            'paid_count'    => $paidCount,
            'total_count'   => $totalCount,
            'next_due'      => $this->nextDueInstalment($user),
            'plan_complete' => $paidCount >= $totalCount && $totalCount > 0,
        ];
    }

    // -------------------------------------------------------------------------
    // Deposit on Admin Confirmation
    // -------------------------------------------------------------------------

    /**
     * Confirm a submitted instalment, apply late/deferred logic, then deposit.
     */
    public function confirmAndDeposit(SavingInstalment $instalment, int $adminId, ?string $notes = null): void
    {
        DB::transaction(function () use ($instalment, $adminId, $notes) {
            $user    = $instalment->user;
            $now     = Carbon::now();
            $isLate  = $now->gt(Carbon::parse($instalment->due_date)->endOfDay());

            // Determine next cycle date if late
            $nextCycleDate = null;
            $deferred      = false;
            if ($isLate) {
                // Next cycle = same day-of-month next month
                $nextCycleDate = Carbon::parse($instalment->due_date)->addMonth();
                $deferred      = true;
            }

            $instalment->update([
                'status'           => 'confirmed',
                'confirmed_at'     => $now,
                'confirmed_by'     => $adminId,
                'is_late'          => $isLate,
                'deposit_deferred' => $deferred,
                'next_cycle_date'  => $nextCycleDate,
                'notes'            => $notes,
            ]);

            // Always mark registration complete when instalment #1 is confirmed,
            // even if the wallet deposit is deferred due to late payment.
            if ($instalment->instalment_number === 1 && !$user->saving_registration_completed) {
                $user->update(['saving_registration_completed' => true]);
            }

            if (!$deferred) {
                $this->creditDepositToWallet($instalment);
            } else {
                Log::info("Saving instalment {$instalment->id} confirmed late — deposit deferred to {$nextCycleDate}");
            }
        });
    }

    /**
     * Credit the instalment amount to the user's saving wallet.
     * Commissions are only distributed if admin has activated the account (can_login = true).
     */
    public function creditDepositToWallet(SavingInstalment $instalment): void
    {
        $user   = $instalment->user;
        $amount = $instalment->submitted_amount ?? $instalment->amount;

        // For instalment #1: also credit the partial deposit the user paid at registration
        // (amount beyond the saving fee that was never yet credited to saving_total_deposited).
        // Use saving_initial_payment / saving_initial_fee — these are set for both dedicated
        // saving users and enrolled standard users. Do NOT use converted_usdt_amount or
        // fee_deducted, which belong to the standard investment plan.
        $registrationPartial = 0.0;
        if ($instalment->instalment_number === 1 && $user->saving_total_deposited == 0) {
            $savingFee   = (float) ($user->saving_initial_fee ?? 0);
            $savingPaid  = (float) ($user->saving_initial_payment ?? 0);
            $registrationPartial = max(0.0, $savingPaid - $savingFee);
        }

        $totalCredit = $amount + $registrationPartial;

        // Credit saving wallet for the confirmed instalment amount
        Wallet::create([
            'user_id'         => $user->id,
            'wallet_type'     => 'saving',
            'balance'         => $amount,
            'commission_type' => 'saving_deposit',
            'level'           => '-',
            'total_amount'    => $amount,
            'wallet_src'      => 'saving_instalment',
            'source_type'     => 'saving',
            'description'     => "Saving instalment #{$instalment->instalment_number} deposited",
            'transaction_type'=> 'credit',
        ]);

        // Credit the registration partial deposit as a separate wallet entry if applicable
        if ($registrationPartial > 0) {
            Wallet::create([
                'user_id'         => $user->id,
                'wallet_type'     => 'saving',
                'balance'         => $registrationPartial,
                'commission_type' => 'saving_deposit',
                'level'           => '-',
                'total_amount'    => $registrationPartial,
                'wallet_src'      => 'saving_instalment',
                'source_type'     => 'saving',
                'description'     => 'Partial deposit credited from registration payment',
                'transaction_type'=> 'credit',
            ]);

            TransactionLog::create([
                'user_id'          => $user->id,
                'from_wallet_type' => 'registration',
                'to_wallet_type'   => 'saving',
                'charge'           => 0,
                'amount'           => $registrationPartial,
                'final_amount'     => $registrationPartial,
                'description'      => 'Registration partial deposit credited to saving account',
                'status'           => 'credit',
            ]);
        }

        // Update user's saving total and roi_eligible_investment_amount (combined)
        $user->increment('saving_total_deposited', $totalCredit);
        $user->increment('roi_eligible_investment_amount', $totalCredit);

        // Mark registration complete when instalment #1 is deposited.
        // NOTE: can_login is intentionally NOT set here — that is admin's job via
        // adminActivate(). Commissions and ROI should only fire after admin approval.
        if ($instalment->instalment_number === 1 && !$user->saving_registration_completed) {
            $user->update(['saving_registration_completed' => true]);
            $user->refresh();
        }

        $instalment->update(['deposited_at' => now()]);

        // Log to transaction history
        TransactionLog::create([
            'user_id'          => $user->id,
            'from_wallet_type' => 'saving_instalment',
            'to_wallet_type'   => 'saving',
            'charge'           => 0,
            'amount'           => $amount,
            'final_amount'     => $amount,
            'description'      => "Saving instalment #{$instalment->instalment_number} confirmed and deposited",
            'status'           => 'credit',
        ]);

        // Commission fires ONLY on instalment #1, on the FULL deposit total
        // (submitted amount + any partial paid at registration), guarded against double-fire.
        // For enrolled standard users, also require saving_enrollment_activated.
        $isEligibleForCommission = $user->saving_registration_completed && (
            ($user->account_type === 'saving' && $user->can_login) ||
            ($user->saving_enrolled && $user->saving_enrollment_activated)
        );

        if ($instalment->instalment_number === 1 && $isEligibleForCommission) {
            $alreadyFired = DB::table('wallets')
                ->where('user_id', $user->id)
                ->where('wallet_type', 'direct_indirect')
                ->where('source_type', 'saving_instalment')
                ->exists();

            if (!$alreadyFired) {
                $this->assignSavingCommissions($user, $totalCredit);
            }
        }

        Log::info("Saving deposit credited: user={$user->id}, amount={$amount}, instalment={$instalment->instalment_number}");
    }

    /**
     * Process any deferred instalments that have reached their next_cycle_date.
     * Called by a scheduled command or manually.
     */
    public function processDeferredDeposits(): void
    {
        $deferred = SavingInstalment::where('status', 'confirmed')
            ->where('deposit_deferred', true)
            ->whereNull('deposited_at')
            ->where('next_cycle_date', '<=', Carbon::today())
            ->get();

        foreach ($deferred as $instalment) {
            $instalment->update(['deposit_deferred' => false]);
            $this->creditDepositToWallet($instalment);
        }
    }

    // -------------------------------------------------------------------------
    // Saving Account Commissions
    // -------------------------------------------------------------------------

    public function assignSavingCommissions(User $user, float $amount): void
    {
        $setting = Setting::first();

        $ancestors = DB::table('referral_trees')
            ->select('ancestor_id', 'level')
            ->where('descendant_id', $user->id)
            ->where('tree_type', 'saving')
            ->where('level', '>=', 1)
            ->where('level', '<=', 7)
            ->orderBy('level')
            ->get();

        foreach ($ancestors as $ancestor) {
            $ancestorUser = User::where('blocked', false)->find($ancestor->ancestor_id);
            if (!$ancestorUser) {
                continue;
            }

            // Saving account ancestors must have completed their own registration deposit
            if ($ancestorUser->account_type === 'saving' && !$ancestorUser->saving_registration_completed) {
                continue;
            }

            // 7x7 rule: ancestor must have at least N direct referrals in the saving tree
            // to receive commission at level N.
            // Example: need 1 direct for level 1, 2 directs for level 2, etc.
            if (!$this->qualifiesForSavingLevel($ancestorUser, $ancestor->level)) {
                Log::info("Saving commission skipped: user {$ancestorUser->id} has insufficient saving directs for level {$ancestor->level}");
                continue;
            }

            $fieldName  = "saving_commission_l{$ancestor->level}";
            $percentage = $setting && isset($setting->$fieldName)
                ? (float) $setting->$fieldName
                : (self::SAVING_COMMISSION_RATES[$ancestor->level] ?? 0);

            if ($percentage <= 0) {
                continue;
            }

            $commissionAmount = round(($amount * $percentage) / 100, 2);

            $this->walletService->assignCommission(
                userId: $ancestorUser->id,
                amount: $commissionAmount,
                type: $ancestor->level === 1 ? 'direct' : 'indirect',
                sourceUser: $user,
                level: $ancestor->level,
                percentage: $percentage,
                sourceType: 'saving_instalment'
            );
        }
    }

    /**
     * 7x7 check for saving commissions.
     * Returns true if the user has enough direct saving referrals to unlock commission at $level.
     * Direct saving referrals = level-1 entries in referral_trees where tree_type = 'saving'.
     */
    private function qualifiesForSavingLevel(User $user, int $level): bool
    {
        $required = self::SAVING_TEAM_REQUIREMENTS[$level] ?? 0;
        if ($required <= 0) {
            return false;
        }

        $directSavingReferrals = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('level', 1)
            ->where('tree_type', 'saving')
            ->count();

        return $directSavingReferrals >= $required;
    }

    // -------------------------------------------------------------------------
    // ROI for Saving Accounts
    // -------------------------------------------------------------------------

    /**
     * Process daily saving ROI for a single user.
     * Only fires if instalment #1 is deposited and account is within the 25-month plan.
     */
    public function processSavingRoi(User $user): array
    {
        if (!$this->canReceiveSavingRoi($user)) {
            return ['success' => false, 'message' => 'Not eligible for saving ROI'];
        }

        // Already received today?
        if ($user->last_roi_payment_date && Carbon::parse($user->last_roi_payment_date)->isToday()) {
            return ['success' => false, 'message' => 'ROI already processed today'];
        }

        $setting    = Setting::first();
        $dailyRate  = (float) ($setting->saving_roi_daily_rate ?? 0.1); // default 0.1% daily
        $base       = (float) $user->saving_total_deposited;

        if ($base <= 0 || $dailyRate <= 0) {
            return ['success' => false, 'message' => 'No deposit base or rate'];
        }

        $amount = round(($base * $dailyRate) / 100, 2);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Calculated ROI is zero'];
        }

        DB::transaction(function () use ($user, $amount) {
            Wallet::create([
                'user_id'          => $user->id,
                'wallet_type'      => 'saving_roi',
                'balance'          => $amount,
                'commission_type'  => 'saving_roi',
                'level'            => '-',
                'total_amount'     => $amount,
                'wallet_src'       => 'saving_roi',
                'source_type'      => 'saving',
                'description'      => 'Daily saving account ROI',
                'transaction_type' => 'credit',
            ]);

            $user->increment('roi_wallet_balance', $amount);
            $user->update(['last_roi_payment_date' => now()]);

            TransactionLog::create([
                'user_id'          => $user->id,
                'from_wallet_type' => 'saving_roi',
                'to_wallet_type'   => 'saving_roi',
                'charge'           => 0,
                'amount'           => $amount,
                'final_amount'     => $amount,
                'description'      => 'Daily saving ROI',
                'status'           => 'credit',
            ]);
        });

        return ['success' => true, 'amount' => $amount];
    }

    /**
     * Check if a saving account user is eligible for ROI.
     * ROI runs only after registration is complete and for the plan duration.
     */
    public function canReceiveSavingRoi(User $user): bool
    {
        // Dedicated saving account users: gated by can_login
        if ($user->account_type === 'saving') {
            if (!$user->can_login) {
                return false;
            }
        } elseif ($user->saving_enrolled) {
            // Enrolled standard users: gated by saving_enrollment_activated
            if (!$user->saving_enrollment_activated) {
                return false;
            }
        } else {
            return false;
        }

        if (!$user->saving_registration_completed) {
            return false;
        }

        if (!$user->saving_plan_start_date) {
            return false;
        }

        // Plan ends after 25 months
        $setting  = Setting::first();
        $months   = $setting->saving_plan_months ?? self::PLAN_MONTHS;
        $planEnd  = Carbon::parse($user->saving_plan_start_date)->addMonths($months);

        return Carbon::today()->lte($planEnd);
    }

    /**
     * Get the saving commission rates (for display).
     */
    public function getCommissionRates(): array
    {
        $setting = Setting::first();
        $rates   = [];
        for ($level = 1; $level <= 7; $level++) {
            $field          = "saving_commission_l{$level}";
            $rates[$level]  = $setting && isset($setting->$field)
                ? (float) $setting->$field
                : (self::SAVING_COMMISSION_RATES[$level] ?? 0);
        }
        return $rates;
    }
}
