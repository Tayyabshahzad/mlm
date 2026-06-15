<?php

namespace App\Services;

use App\Models\SavingInstalment;
use App\Models\SavingInstalmentCommission;
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
        // Prevent duplicate schedule if one already exists for this user.
        if (SavingInstalment::where('user_id', $user->id)->exists()) {
            Log::warning("createInstalmentSchedule called but user {$user->id} already has instalments — skipped.");
            return;
        }

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
            'status'            => 'confirmed',
            'transaction_id'    => $transactionId,
            'submitted_at'      => now(),
            'confirmed_at'      => now(),
            'confirmed_by'      => $adminId,
            'submitted_amount'  => $instalment->amount,
            'deposited_at'      => now(),
            'is_late'           => false,
            'roi_eligible_from' => $instalment->due_date,
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
     * Confirm a submitted instalment and immediately credit the wallet.
     *
     * Late payments are penalised via roi_eligible_from = today (not backdated),
     * but the wallet deposit always happens immediately on admin confirmation —
     * the previous "deposit_deferred" mechanic was never wired to a scheduler
     * and left confirmed instalments in limbo indefinitely.
     */
    public function confirmAndDeposit(SavingInstalment $instalment, int $adminId, ?string $notes = null): void
    {
        DB::transaction(function () use ($instalment, $adminId, $notes) {
            $user    = $instalment->user;
            $now     = Carbon::now();
            $dueDate = Carbon::parse($instalment->due_date);
            $isLate  = $now->gt($dueDate->copy()->endOfDay());

            // ROI base for this instalment starts:
            //   - Early or on-time payment: from the due_date itself.
            //   - Late payment: from today — no backdated ROI for the missed period.
            $roiEligibleFrom = $isLate
                ? $now->toDateString()
                : $dueDate->toDateString();

            $instalment->update([
                'status'           => 'confirmed',
                'confirmed_at'     => $now,
                'confirmed_by'     => $adminId,
                'is_late'          => $isLate,
                'deposit_deferred' => false,
                'next_cycle_date'  => null,
                'roi_eligible_from'=> $roiEligibleFrom,
                'notes'            => $notes,
            ]);

            // Mark registration complete when instalment #1 is confirmed.
            if ($instalment->instalment_number === 1 && !$user->saving_registration_completed) {
                $user->update(['saving_registration_completed' => true]);
            }

            // Auto-activate enrolled standard users on instalment #1 confirmation.
            if ($instalment->instalment_number === 1
                && $user->account_type !== 'saving'
                && $user->saving_enrolled
                && !$user->saving_enrollment_activated) {
                $user->update([
                    'saving_enrollment_activated'    => true,
                    'saving_enrollment_activated_at' => $now,
                    'saving_enrollment_activated_by' => $adminId,
                ]);
                $user->refresh();
            }

            $this->creditDepositToWallet($instalment);
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
        $registrationPartial = 0.0;
        if ($instalment->instalment_number === 1 && $user->saving_total_deposited == 0) {
            $savingFee           = (float) ($user->saving_initial_fee ?? 0);
            $savingPaid          = (float) ($user->saving_initial_payment ?? 0);
            $registrationPartial = max(0.0, $savingPaid - $savingFee);
        }

        $totalCredit = $amount + $registrationPartial;

        // ── ADB / FISP charges ──────────────────────────────────────────────
        // Calculated on the BASE instalment amount only (not registration partial).
        // ADB = 0.3% (Rs. 3 per Rs. 1000), FISP = 0.4% (Rs. 4 per Rs. 1000).
        // These are insurance premiums — deducted before crediting ROI-eligible amount.
        $baseForCharges = $instalment->amount; // always use scheduled base amount
        $adbCharge  = $user->adb_option  ? round($baseForCharges * 0.075 /* ADB: sum_assured/1000 * 3 */, 4) : 0.0;
        $fispCharge = $user->fisp_option ? round($baseForCharges * 0.1   /* FISP: sum_assured/1000 * 4 */, 4) : 0.0;
        $totalDeductions = $adbCharge + $fispCharge;
        $netCredit  = round($totalCredit - $totalDeductions, 4);

        // Store deduction details on the instalment record
        $instalment->update([
            'adb_charge'  => $adbCharge,
            'fisp_charge' => $fispCharge,
            'net_credited'=> $netCredit,
        ]);

        // Credit saving wallet for the gross instalment amount
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

        // Debit ADB/FISP charges from saving wallet if applicable
        if ($totalDeductions > 0) {
            $desc = implode(' + ', array_filter([
                $adbCharge  > 0 ? "ADB \${$adbCharge}"  : null,
                $fispCharge > 0 ? "FISP \${$fispCharge}" : null,
            ]));
            Wallet::create([
                'user_id'         => $user->id,
                'wallet_type'     => 'saving',
                'balance'         => -$totalDeductions,
                'commission_type' => 'saving_charge',
                'level'           => '-',
                'total_amount'    => $totalDeductions,
                'wallet_src'      => 'saving_instalment',
                'source_type'     => 'saving',
                'description'     => "Option charges (instalment #{$instalment->instalment_number}): {$desc}",
                'transaction_type'=> 'debit',
            ]);

            TransactionLog::create([
                'user_id'          => $user->id,
                'from_wallet_type' => 'saving',
                'to_wallet_type'   => 'saving_charge',
                'charge'           => $totalDeductions,
                'amount'           => $totalCredit,
                'final_amount'     => $netCredit,
                'description'      => "ADB/FISP charges — instalment #{$instalment->instalment_number}: {$desc}",
                'status'           => 'debit',
            ]);
        }

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

        // Update user's saving total (gross) and roi_eligible_investment_amount (net after ADB/FISP)
        $user->increment('saving_total_deposited', $totalCredit);
        $user->increment('roi_eligible_investment_amount', $netCredit);

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

        // Commissions fire for EVERY confirmed instalment once the account is active.
        // Deduplication is enforced by the unique constraint in saving_instalment_commissions.
        $isEligibleForCommission = $user->saving_registration_completed && (
            ($user->account_type === 'saving' && $user->can_login) ||
            ($user->saving_enrolled && $user->saving_enrollment_activated)
        );

        if ($isEligibleForCommission) {
            $this->assignSavingCommissions($user, $totalCredit, $instalment);
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

    /**
     * Distribute saving-tree commissions for a single instalment payment.
     *
     * When $instalment is provided every paid commission is recorded in
     * saving_instalment_commissions with a unique constraint on
     * (saving_instalment_id, ancestor_id, level) so the method is fully
     * idempotent — calling it twice for the same instalment is safe.
     *
     * When $instalment is null (legacy activation paths) the method falls back
     * to the previous wallet-existence check so older callers keep working.
     */
    public function assignSavingCommissions(User $user, float $amount, ?SavingInstalment $instalment = null): void
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

            if ($ancestorUser->account_type === 'saving' && !$ancestorUser->saving_registration_completed) {
                continue;
            }

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

            $commissionAmount = round(($amount * $percentage) / 100, 4);
            $type             = $ancestor->level === 1 ? 'direct' : 'indirect';

            if ($instalment) {
                // ── Idempotent path: track every commission in saving_instalment_commissions ──
                $alreadyExists = DB::table('saving_instalment_commissions')
                    ->where('saving_instalment_id', $instalment->id)
                    ->where('ancestor_id', $ancestorUser->id)
                    ->where('level', $ancestor->level)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $wallet = $this->walletService->assignCommission(
                    userId: $ancestorUser->id,
                    amount: $commissionAmount,
                    type: $type,
                    sourceUser: $user,
                    level: $ancestor->level,
                    percentage: $percentage,
                    sourceType: 'saving_instalment'
                );

                try {
                    DB::table('saving_instalment_commissions')->insert([
                        'saving_instalment_id' => $instalment->id,
                        'user_id'              => $user->id,
                        'ancestor_id'          => $ancestorUser->id,
                        'level'                => $ancestor->level,
                        'instalment_number'    => $instalment->instalment_number,
                        'commission_amount'    => $commissionAmount,
                        'percentage'           => $percentage,
                        'commission_type'      => $type,
                        'status'               => 'paid',
                        'wallet_id'            => $wallet?->id,
                        'processed_at'         => now(),
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Unique-constraint race — another process already inserted this record; safe to skip.
                    if ($e->errorInfo[1] == 1062) {
                        Log::info("Saving commission race-condition skip: instalment={$instalment->id} ancestor={$ancestorUser->id} level={$ancestor->level}");
                        continue;
                    }
                    throw $e;
                }
            } else {
                // ── Legacy path: no instalment object (old activation callers) ──────────────
                // Guard against re-firing using old wallet-existence check.
                $alreadyFired = DB::table('wallets')
                    ->where('user_id', $user->id)
                    ->where('wallet_type', 'direct_indirect')
                    ->where('source_type', 'saving_instalment')
                    ->exists();

                if ($alreadyFired) {
                    break; // all levels will also have been paid; stop looping
                }

                $this->walletService->assignCommission(
                    userId: $ancestorUser->id,
                    amount: $commissionAmount,
                    type: $type,
                    sourceUser: $user,
                    level: $ancestor->level,
                    percentage: $percentage,
                    sourceType: 'saving_instalment'
                );
            }
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
    public function processSavingRoi(
        User $user,
        ?Carbon $forDate = null,
        ?float $customRate = null,
        ?string $customDescription = null,
        bool $forceManual = false
    ): array {
        $forDate = $forDate ?? Carbon::today();

        if (!$forceManual && !$this->canReceiveSavingRoi($user)) {
            return ['success' => false, 'message' => 'Not eligible for saving ROI'];
        }

        if (!$forceManual) {
            // 'pending'   = user has not submitted anything yet.
            // 'submitted' = user uploaded a receipt but admin has NOT confirmed it yet.
            $hasOverdue = SavingInstalment::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'submitted'])
                ->whereDate('due_date', '<', $forDate->toDateString())
                ->exists();

            if ($hasOverdue) {
                return ['success' => false, 'message' => 'ROI suspended: overdue instalment awaiting admin confirmation'];
            }

            $alreadyPaid = Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'saving_roi')
                ->whereDate('created_at', $forDate->toDateString())
                ->exists();

            if ($alreadyPaid) {
                return ['success' => false, 'message' => 'ROI already processed for ' . $forDate->format('d M Y')];
            }
        }

        $setting   = Setting::first();
        $dailyRate = $customRate ?? (float) ($setting->saving_roi_daily_rate ?? 0.1);

        // ROI base = confirmed instalments whose roi_eligible_from has arrived by $forDate.
        // For legacy records without roi_eligible_from, fall back to due_date.
        $eligibleBase = SavingInstalment::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->where(function ($q) use ($forDate) {
                $q->whereDate('roi_eligible_from', '<=', $forDate->toDateString())
                  ->orWhere(function ($q2) use ($forDate) {
                      $q2->whereNull('roi_eligible_from')
                         ->whereDate('due_date', '<=', $forDate->toDateString());
                  });
            })
            ->sum('amount');

        $base = $eligibleBase > 0
            ? (float) $eligibleBase
            : max((float) $user->saving_total_deposited, (float) $user->roi_eligible_investment_amount);

        if ($base <= 0 || $dailyRate <= 0) {
            return ['success' => false, 'message' => 'No deposit base or rate'];
        }

        $amount = round(($base * $dailyRate) / 100, 2);
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Calculated ROI is zero'];
        }

        $desc = $customDescription ?? 'Daily saving account ROI (' . $forDate->format('d M Y') . ')';

        DB::transaction(function () use ($user, $amount, $forDate, $desc) {
            $entryDate = $forDate->copy()->setTime(23, 59, 0);

            $wallet = Wallet::create([
                'user_id'          => $user->id,
                'wallet_type'      => 'saving_roi',
                'balance'          => $amount,
                'commission_type'  => 'saving_roi',
                'level'            => '-',
                'total_amount'     => $amount,
                'wallet_src'       => 'saving_roi',
                'source_type'      => 'saving',
                'description'      => $desc,
                'transaction_type' => 'credit',
            ]);

            // Stamp the entry with the target date so reports show the correct day.
            $wallet->forceFill(['created_at' => $entryDate, 'updated_at' => $entryDate])->saveQuietly();

            $user->increment('roi_wallet_balance', $amount);

            // Only advance last_saving_roi_payment_date if this date is more recent.
            if (!$user->last_saving_roi_payment_date ||
                $forDate->gt(Carbon::parse($user->last_saving_roi_payment_date))) {
                $user->update(['last_saving_roi_payment_date' => $forDate->toDateString()]);
            }

            TransactionLog::create([
                'user_id'          => $user->id,
                'from_wallet_type' => 'saving_roi',
                'to_wallet_type'   => 'saving_roi',
                'charge'           => 0,
                'amount'           => $amount,
                'final_amount'     => $amount,
                'description'      => $desc,
                'status'           => 'credit',
            ]);
        });

        return ['success' => true, 'amount' => $amount];
    }

    /**
     * Directly credit ROI to any user — no eligibility, overdue, or duplicate checks.
     * Supports both saving_roi (saving plan wallet) and roi (standard plan wallet).
     */
    public function manualCreditRoi(
        User $user,
        float $amount,
        Carbon $forDate,
        string $description,
        string $walletType = 'saving_roi'
    ): array {
        $isSaving = ($walletType === 'saving_roi');

        DB::transaction(function () use ($user, $amount, $forDate, $description, $walletType, $isSaving) {
            $entryDate = $forDate->copy()->setTime(23, 59, 0);

            $wallet = Wallet::create([
                'user_id'          => $user->id,
                'wallet_type'      => $walletType,
                'balance'          => $amount,
                'commission_type'  => $isSaving ? 'saving_roi' : 'Roi',
                'level'            => '-',
                'total_amount'     => $amount,
                'wallet_src'       => $walletType,
                'source_type'      => $isSaving ? 'saving' : 'roi',
                'description'      => $description,
                'transaction_type' => 'credit',
            ]);

            $wallet->forceFill(['created_at' => $entryDate, 'updated_at' => $entryDate])->saveQuietly();

            $user->increment('roi_wallet_balance', $amount);

            if ($isSaving) {
                if (!$user->last_saving_roi_payment_date ||
                    $forDate->gt(Carbon::parse($user->last_saving_roi_payment_date))) {
                    $user->update(['last_saving_roi_payment_date' => $forDate->toDateString()]);
                }
            } else {
                $user->update(['last_roi_payment_date' => $forDate->toDateString()]);
            }

            TransactionLog::create([
                'user_id'          => $user->id,
                'from_wallet_type' => $walletType,
                'to_wallet_type'   => $walletType,
                'charge'           => 0,
                'amount'           => $amount,
                'final_amount'     => $amount,
                'description'      => $description,
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

