<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use App\Models\BinarySystem;
use App\Models\TransactionLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CompleteROIFix extends Command
{
    protected $signature = 'roi:complete-fix
                            {--date= : Specific date to reverse (YYYY-MM-DD format). If not provided, uses --days}
                            {--set-percentage=0.42 : Target ROI percentage to set (e.g., 0.42 to change 42% to 0.42%)}
                            {--user-plan=standard : User plan to target (standard, premium, all)}
                            {--days=2 : Number of days to look back (ignored if --date is provided)}
                            {--dry-run : Preview changes without executing}';

    protected $description = 'Complete ROI fix - handles 2X accounts, ROI reversal, and profit share reversal all in one';

    private $stats = [
        '2x_accounts_fixed' => 0,
        '2x_roi_reversed' => 0,
        '2x_profit_reversed' => 0,
        'users_reactivated' => 0,
        'regular_roi_reversed' => 0,
        'regular_profit_reversed' => 0,
        'total_amount_reversed' => 0,
        'binary_2x_reversed' => 0,
        'binary_earnings_reversed' => 0,
    ];

    public function handle()
    {
        $setPercentage = (float) $this->option('set-percentage');
        $days = (int) $this->option('days');
        $dateOption = $this->option('date');
        $userPlan = $this->option('user-plan');
        $dryRun = $this->option('dry-run');

        // Parse date if provided
        $targetDate = null;
        $dateStart = null;
        $dateEnd = null;

        if ($dateOption) {
            try {
                $targetDate = Carbon::parse($dateOption);
                $dateStart = $targetDate->copy()->startOfDay();
                $dateEnd = $targetDate->copy()->endOfDay();
            } catch (\Exception $e) {
                $this->error("Invalid date format. Please use YYYY-MM-DD format.");
                return 1;
            }
        }

        $this->info("╔════════════════════════════════════════════════════════════╗");
        $this->info("║        COMPLETE ROI FIX - All-in-One Solution            ║");
        $this->info("╚════════════════════════════════════════════════════════════╝");
        $this->newLine();
        $this->info("Target ROI Percentage: {$setPercentage}%");
        $this->info("Target User Plan: " . strtoupper($userPlan));

        if ($targetDate) {
            $this->info("Target Date: {$targetDate->format('Y-m-d')}");
            $this->warn("Will reverse only transactions from this specific date");
        } else {
            $this->info("Days to look back: {$days}");
        }

        $this->info("Mode: " . ($dryRun ? '🔍 DRY RUN (Preview only)' : '⚡ LIVE (Will make changes)'));
        $this->newLine();

        if (!$dryRun) {
            $this->warn("⚠️  WARNING: This will modify user accounts and wallet balances!");
            if (!$this->confirm('Do you want to proceed?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            DB::beginTransaction();

            // PHASE 1: Fix 2X Accounts
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("PHASE 1: Fixing 2X Accounts");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            $this->fix2XAccounts($days, $setPercentage, $dryRun, $dateStart, $dateEnd, $userPlan);

            // PHASE 2: Set ROI to Target Percentage for Regular Users
            $this->newLine();
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("PHASE 2: Setting ROI to {$setPercentage}% for " . strtoupper($userPlan) . " Users");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            $this->setROIToPercentage($setPercentage, $days, $dryRun, $dateStart, $dateEnd, $userPlan);

            // PHASE 3: Set Profit Share to Target Percentage
            $this->newLine();
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("PHASE 3: Setting Profit Share to {$setPercentage}%");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            $this->setProfitShareToPercentage($setPercentage, $days, $dryRun, $dateStart, $dateEnd, $userPlan);

            // PHASE 4: Check and Reverse Binary 2X Completions
            $this->newLine();
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("PHASE 4: Checking Binary 2X Completions Impact");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            $this->checkAndReverseBinary2X($setPercentage, $dryRun, $dateStart, $dateEnd);

            // Display Final Summary
            $this->displayFinalSummary();

            if ($dryRun) {
                $this->newLine();
                $this->warn('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->warn('🔍 DRY RUN COMPLETE - No changes were made to the database');
                $this->warn('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                DB::rollBack();
            } else {
                DB::commit();
                $this->newLine();
                $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                $this->info('✅ COMPLETE FIX SUCCESSFUL!');
                $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

                Log::info('Complete ROI Fix Executed', $this->stats);
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('Complete ROI Fix Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function fix2XAccounts(int $days, float $percentage, bool $dryRun, $dateStart = null, $dateEnd = null, string $userPlan = 'standard')
    {
        // Find users stopped in last N days or on specific date
        $query = User::where('stop_reason', '2x_limit_reached');

        if ($userPlan !== 'all') {
            $query->where('user_plan', $userPlan);
        }

        if ($dateStart && $dateEnd) {
            $query->whereBetween('roi_stopped_at', [$dateStart, $dateEnd]);
        } else {
            $query->where('roi_stopped_at', '>=', now()->subDays($days));
        }

        $stoppedUsers = $query->get();

        if ($stoppedUsers->isEmpty()) {
            $this->info('No 2X stopped accounts found in last ' . $days . ' days.');
            return;
        }

        $this->info("Found {$stoppedUsers->count()} accounts stopped at 2X in last {$days} days");
        $this->newLine();

        $accountsToFix = collect();
        $accountsNaturallyReached = collect();

        foreach ($stoppedUsers as $user) {
            $analysis = $this->analyze2XAccount($user, $percentage);

            if ($analysis['naturally_2x']) {
                $accountsNaturallyReached->push($analysis);
            } else {
                $accountsToFix->push($analysis);
            }
        }

        // Display accounts to fix
        if ($accountsToFix->isNotEmpty()) {
            $this->warn("Accounts to FIX (incorrectly stopped due to error):");
            $this->table(
                ['ID', 'Name', '2X Limit', 'ROI Got', '% of 2X', 'ROI After Stop', 'Profit After Stop'],
                $accountsToFix->map(function ($data) {
                    return [
                        $data['user']->id,
                        $data['user']->name,
                        '$' . number_format($data['limit_2x'], 2),
                        '$' . number_format($data['total_roi'], 2),
                        number_format(($data['total_roi'] / $data['limit_2x']) * 100, 1) . '%',
                        '$' . number_format($data['roi_after_stop'], 2),
                        '$' . number_format($data['profit_after_stop'], 2),
                    ];
                })->toArray()
            );

            // Process fixes
            foreach ($accountsToFix as $data) {
                $this->process2XFix($data, $dryRun);
            }
        }

        // Display naturally reached accounts
        if ($accountsNaturallyReached->isNotEmpty()) {
            $this->info("Accounts NATURALLY reached 2X (will NOT touch):");
            $this->table(
                ['ID', 'Name', '2X Limit', 'Correct ROI', 'Status'],
                $accountsNaturallyReached->map(function ($data) {
                    return [
                        $data['user']->id,
                        $data['user']->name,
                        '$' . number_format($data['limit_2x'], 2),
                        '$' . number_format($data['correct_roi_estimate'], 2),
                        '✓ Keep Stopped',
                    ];
                })->toArray()
            );
        }
    }

    private function analyze2XAccount($user, $percentage)
    {
        $limit2x = $user->roi_eligible_investment_amount * 2;

        // ROI before stop
        $roiBeforeStop = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'roi')
            ->where('created_at', '<', $user->roi_stopped_at)
            ->sum('balance');

        // ROI after stop (shouldn't exist!)
        $roiAfterStop = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'roi')
            ->where('created_at', '>=', $user->roi_stopped_at)
            ->sum('balance');

        // Profit share after stop
        $profitAfterStop = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'profit_share')
            ->where('created_at', '>=', $user->roi_stopped_at)
            ->sum('balance');

        $totalRoi = $roiBeforeStop + $roiAfterStop;

        // Calculate what ROI SHOULD have been (correct percentage)
        $correctRoiEstimate = $roiBeforeStop * (0.42 / 42.0);

        // Would they have reached 2X naturally?
        $wouldBeNaturally2X = $correctRoiEstimate >= $limit2x;

        return [
            'user' => $user,
            'limit_2x' => $limit2x,
            'roi_before_stop' => $roiBeforeStop,
            'roi_after_stop' => $roiAfterStop,
            'profit_after_stop' => $profitAfterStop,
            'total_roi' => $totalRoi,
            'correct_roi_estimate' => $correctRoiEstimate,
            'naturally_2x' => $wouldBeNaturally2X,
        ];
    }

    private function process2XFix($data, bool $dryRun)
    {
        $user = $data['user'];

        // 1. Reverse ROI given after stop
        if ($data['roi_after_stop'] > 0) {
            $roiEntries = Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'roi')
                ->where('created_at', '>=', $user->roi_stopped_at)
                ->get();

            foreach ($roiEntries as $entry) {
                if (!$dryRun) {
                    $originalAmount = $entry->balance;
                    $entry->balance = 0;
                    $entry->total_amount = 0;
                    $entry->save();

                    Wallet::create([
                        'user_id' => $user->id,
                        'wallet_type' => 'roi_reversal',
                        'balance' => -$originalAmount,
                        'total_amount' => -$originalAmount,
                        'commission_type' => 'roi_reversal',
                        'level' => 0,
                        'description' => "2X Fix: ROI reversed - given after account stopped (Entry #{$entry->id})",
                        'transaction_type' => 'debit',
                        'wallet_src' => '2x_complete_fix',
                    ]);

                    // Log transaction history
                    TransactionLog::create([
                        'user_id' => $user->id,
                        'from_wallet_type' => 'roi',
                        'to_wallet_type' => 'roi_reversal',
                        'amount' => $originalAmount,
                        'charge' => 0,
                        'final_amount' => $originalAmount,
                        'description' => "2X Fix: ROI reversed - incorrectly given after account stopped at 2X",
                        'status' => 'debit',
                    ]);
                }
            }

            $this->stats['2x_roi_reversed'] += $data['roi_after_stop'];
        }

        // 2. Reverse profit share given after stop
        if ($data['profit_after_stop'] > 0) {
            $profitEntries = Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'profit_share')
                ->where('created_at', '>=', $user->roi_stopped_at)
                ->get();

            foreach ($profitEntries as $entry) {
                if (!$dryRun) {
                    $originalAmount = $entry->balance;
                    $entry->balance = 0;
                    $entry->total_amount = 0;
                    $entry->save();

                    Wallet::create([
                        'user_id' => $user->id,
                        'wallet_type' => 'profit_share_reversal',
                        'balance' => -$originalAmount,
                        'total_amount' => -$originalAmount,
                        'commission_type' => 'profit_share_reversal',
                        'level' => 0,
                        'description' => "2X Fix: Profit share reversed - given after account stopped",
                        'transaction_type' => 'debit',
                        'wallet_src' => '2x_complete_fix',
                    ]);

                    // Log transaction history
                    TransactionLog::create([
                        'user_id' => $user->id,
                        'from_wallet_type' => 'profit_share',
                        'to_wallet_type' => 'profit_share_reversal',
                        'amount' => $originalAmount,
                        'charge' => 0,
                        'final_amount' => $originalAmount,
                        'description' => "2X Fix: Profit share reversed - incorrectly given after account stopped at 2X",
                        'status' => 'debit',
                    ]);
                }
            }

            $this->stats['2x_profit_reversed'] += $data['profit_after_stop'];
        }

        // 3. Reactivate account
        if (!$dryRun) {
            $user->roi_status = 'active';
            $user->roi_stopped_at = null;
            $user->stop_reason = null;
            $user->stop_reason_description = 'Reactivated - incorrectly stopped due to ROI error (42% instead of 0.42%)';
            $user->save();
        }

        $this->stats['2x_accounts_fixed']++;
        $this->stats['users_reactivated']++;
        $this->stats['total_amount_reversed'] += ($data['roi_after_stop'] + $data['profit_after_stop']);
    }

    private function setROIToPercentage(float $targetPercentage, int $days, bool $dryRun, $dateStart = null, $dateEnd = null, string $userPlan = 'standard')
    {
        $query = Wallet::where('wallet_type', 'roi')
            ->whereIn('user_id', function ($q) use ($userPlan) {
                $q->select('id')->from('users');
                if ($userPlan !== 'all') {
                    $q->where('user_plan', $userPlan);
                }
            });

        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        } else {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $roiEntries = $query->get();

        if ($roiEntries->isEmpty()) {
            $this->info('No ROI entries found.');
            return;
        }

        $this->info("Found {$roiEntries->count()} ROI entries");

        $totalOriginal = $roiEntries->sum('balance');

        // Calculate what the new total should be based on target percentage
        // Assume original was given at 42%, we want it at targetPercentage
        $totalNew = $totalOriginal * ($targetPercentage / 42.0);
        $totalToReverse = $totalOriginal - $totalNew;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total ROI Entries', number_format($roiEntries->count())],
                ['Original Total (42%)', '$' . number_format($totalOriginal, 2)],
                ['Target Total (' . $targetPercentage . '%)', '$' . number_format($totalNew, 2)],
                ['To Reverse', '$' . number_format($totalToReverse, 2)],
            ]
        );

        foreach ($roiEntries as $entry) {
            if ($entry->balance == 0) continue; // Skip already reversed

            $originalAmount = $entry->balance;

            // Calculate new amount: original * (targetPercentage / 42)
            $newAmount = $originalAmount * ($targetPercentage / 42.0);
            $reversalAmount = $originalAmount - $newAmount;

            if (!$dryRun) {
                // Simply update the ROI entry - DON'T create reversal entries
                // This ensures total_earning stays correct for 2X calculations
                $entry->balance = $newAmount;
                $entry->total_amount = $newAmount;
                $entry->percentage = $targetPercentage;
                $entry->save();

                // Log transaction history for audit trail only
                TransactionLog::create([
                    'user_id' => $entry->user_id,
                    'from_wallet_type' => 'roi',
                    'to_wallet_type' => 'roi_adjusted',
                    'amount' => $reversalAmount,
                    'charge' => 0,
                    'final_amount' => $reversalAmount,
                    'description' => "ROI Adjusted: 42% → {$targetPercentage}% (Amount reduced by $" . number_format($reversalAmount, 2) . ")",
                    'status' => 'debit',
                ]);
            }

            $this->stats['regular_roi_reversed'] += $reversalAmount;
            $this->stats['total_amount_reversed'] += $reversalAmount;
        }
    }

    private function setProfitShareToPercentage(float $targetPercentage, int $days, bool $dryRun, $dateStart = null, $dateEnd = null, string $userPlan = 'standard')
    {
        $query = Wallet::where('wallet_type', 'profit_share')
            ->whereIn('user_id', function ($q) use ($userPlan) {
                $q->select('id')->from('users');
                if ($userPlan !== 'all') {
                    $q->where('user_plan', $userPlan);
                }
            });

        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        } else {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        $profitEntries = $query->get();

        if ($profitEntries->isEmpty()) {
            $this->info('No profit share entries found.');
            return;
        }

        $this->info("Found {$profitEntries->count()} profit share entries");

        $totalOriginal = $profitEntries->sum('balance');

        // Calculate what the new total should be based on target percentage
        $totalNew = $totalOriginal * ($targetPercentage / 42.0);
        $totalToReverse = $totalOriginal - $totalNew;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Entries', number_format($profitEntries->count())],
                ['Original Total (42%)', '$' . number_format($totalOriginal, 2)],
                ['Target Total (' . $targetPercentage . '%)', '$' . number_format($totalNew, 2)],
                ['To Reverse', '$' . number_format($totalToReverse, 2)],
            ]
        );

        foreach ($profitEntries as $entry) {
            if ($entry->balance == 0) continue; // Skip already reversed

            $originalAmount = $entry->balance;

            // Calculate new amount: original * (targetPercentage / 42)
            $newAmount = $originalAmount * ($targetPercentage / 42.0);
            $reversalAmount = $originalAmount - $newAmount;

            if (!$dryRun) {
                // Simply update the profit share entry - DON'T create reversal entries
                // This ensures total_earning stays correct for 2X calculations
                $entry->balance = $newAmount;
                $entry->total_amount = $newAmount;
                $entry->percentage = $targetPercentage;
                $entry->save();

                // Log transaction history for audit trail only
                TransactionLog::create([
                    'user_id' => $entry->user_id,
                    'from_wallet_type' => 'profit_share',
                    'to_wallet_type' => 'profit_share_adjusted',
                    'amount' => $reversalAmount,
                    'charge' => 0,
                    'final_amount' => $reversalAmount,
                    'description' => "Profit Share Adjusted: 42% → {$targetPercentage}% (Amount reduced by $" . number_format($reversalAmount, 2) . ")",
                    'status' => 'debit',
                ]);
            }

            $this->stats['regular_profit_reversed'] += $reversalAmount;
            $this->stats['total_amount_reversed'] += $reversalAmount;
        }
    }

    private function checkAndReverseBinary2X(float $percentage, bool $dryRun, $dateStart = null, $dateEnd = null)
    {
        // Get all users with active 2x binary systems
        $binarySystems = BinarySystem::where('system_type', '2x')
            ->where('is_active', true)
            ->with('user')
            ->get();

        if ($binarySystems->isEmpty()) {
            $this->info('No active 2X binary systems found.');
            return;
        }

        $this->info("Checking {$binarySystems->count()} active 2X binary systems...");
        $this->newLine();

        $affectedUsers = collect();

        foreach ($binarySystems as $binarySystem) {
            $user = $binarySystem->user;

            // Get ROI + Profit Share from the target date
            $dateQuery = function($query) use ($dateStart, $dateEnd) {
                if ($dateStart && $dateEnd) {
                    $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                }
            };

            $roiFromDate = Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'roi')
                ->when($dateStart && $dateEnd, $dateQuery)
                ->sum('balance');

            $profitFromDate = Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'profit_share')
                ->when($dateStart && $dateEnd, $dateQuery)
                ->sum('balance');

            $totalFromDate = $roiFromDate + $profitFromDate;

            if ($totalFromDate == 0) {
                continue; // No transactions on this date for this user
            }

            // Calculate the amount that will be reversed
            $reversalAmount = $totalFromDate * ($percentage / 100);

            // Current earnings in binary system
            $currentEarnings = $binarySystem->total_earned;

            // What earnings would be after reversal
            $earningsAfterReversal = $currentEarnings - $reversalAmount;

            // Get the 2X limit for current level
            $currentLimit = $binarySystem->current_limit;

            // Check if user crossed the limit due to this date's transactions
            $crossedLimitDueToDate = ($earningsAfterReversal < $currentLimit) && ($currentEarnings >= $currentLimit);

            if ($crossedLimitDueToDate) {
                $affectedUsers->push([
                    'user' => $user,
                    'binary_system' => $binarySystem,
                    'current_earnings' => $currentEarnings,
                    'earnings_after_reversal' => $earningsAfterReversal,
                    'current_limit' => $currentLimit,
                    'reversal_amount' => $reversalAmount,
                    'roi_from_date' => $roiFromDate,
                    'profit_from_date' => $profitFromDate,
                ]);
            }
        }

        if ($affectedUsers->isEmpty()) {
            $this->info('✓ No binary 2X systems were incorrectly completed due to reversed transactions.');
            return;
        }

        $this->warn("Found {$affectedUsers->count()} binary 2X completions that need to be reversed:");
        $this->newLine();

        $this->table(
            ['User ID', 'Name', 'Current Level', 'Current Earnings', 'After Reversal', '2X Limit', 'Reversal Amount'],
            $affectedUsers->map(function ($data) {
                return [
                    $data['user']->id,
                    $data['user']->name,
                    $data['binary_system']->current_level,
                    '$' . number_format($data['current_earnings'], 2),
                    '$' . number_format($data['earnings_after_reversal'], 2),
                    '$' . number_format($data['current_limit'], 2),
                    '$' . number_format($data['reversal_amount'], 2),
                ];
            })->toArray()
        );

        // Process reversals
        foreach ($affectedUsers as $data) {
            if (!$dryRun) {
                $binarySystem = $data['binary_system'];

                // Reverse the earnings
                $binarySystem->total_earned = $data['earnings_after_reversal'];
                $binarySystem->save();

                // Create a reversal entry in wallet for tracking
                Wallet::create([
                    'user_id' => $data['user']->id,
                    'wallet_type' => 'binary_2x_reversal',
                    'balance' => -$data['reversal_amount'],
                    'total_amount' => -$data['reversal_amount'],
                    'commission_type' => 'binary_2x_reversal',
                    'level' => 0,
                    'description' => "Binary 2X Reversal: Earnings reduced from {$data['current_earnings']} to {$data['earnings_after_reversal']} due to ROI/Profit reversal",
                    'transaction_type' => 'debit',
                    'wallet_src' => 'complete_roi_fix_binary',
                ]);

                // Log transaction history
                TransactionLog::create([
                    'user_id' => $data['user']->id,
                    'from_wallet_type' => 'binary_2x',
                    'to_wallet_type' => 'binary_2x_reversal',
                    'amount' => $data['reversal_amount'],
                    'charge' => 0,
                    'final_amount' => $data['reversal_amount'],
                    'description' => "Binary 2X Reversal: Level {$data['binary_system']->current_level} earnings reduced due to ROI/Profit reversal",
                    'status' => 'debit',
                ]);

                $this->stats['binary_2x_reversed']++;
                $this->stats['binary_earnings_reversed'] += $data['reversal_amount'];
                $this->stats['total_amount_reversed'] += $data['reversal_amount'];
            }
        }

        if (!$dryRun) {
            $this->info("✓ Reversed {$affectedUsers->count()} binary 2X completions");
        }
    }

    private function displayFinalSummary()
    {
        $this->newLine();
        $this->info("╔════════════════════════════════════════════════════════════╗");
        $this->info("║                    FINAL SUMMARY                         ║");
        $this->info("╚════════════════════════════════════════════════════════════╝");
        $this->newLine();

        $this->table(
            ['Category', 'Count/Amount'],
            [
                ['━━━ 2X ACCOUNTS ━━━', ''],
                ['Accounts Fixed', $this->stats['2x_accounts_fixed']],
                ['Users Reactivated', $this->stats['users_reactivated']],
                ['2X ROI Reversed', '$' . number_format($this->stats['2x_roi_reversed'], 2)],
                ['2X Profit Reversed', '$' . number_format($this->stats['2x_profit_reversed'], 2)],
                ['', ''],
                ['━━━ REGULAR USERS ━━━', ''],
                ['ROI Reversed', '$' . number_format($this->stats['regular_roi_reversed'], 2)],
                ['Profit Share Reversed', '$' . number_format($this->stats['regular_profit_reversed'], 2)],
                ['', ''],
                ['━━━ BINARY 2X SYSTEMS ━━━', ''],
                ['Binary 2X Reversed', $this->stats['binary_2x_reversed']],
                ['Binary Earnings Reversed', '$' . number_format($this->stats['binary_earnings_reversed'], 2)],
                ['', ''],
                ['━━━ GRAND TOTAL ━━━', ''],
                ['Total Amount Reversed', '$' . number_format($this->stats['total_amount_reversed'], 2)],
            ]
        );
    }
}
