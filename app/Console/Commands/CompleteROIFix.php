<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompleteROIFix extends Command
{
    protected $signature = 'roi:complete-fix
                            {--percentage=83.16 : Percentage of ROI to reverse}
                            {--days=2 : Number of days to look back}
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
    ];

    public function handle()
    {
        $percentage = (float) $this->option('percentage');
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("╔════════════════════════════════════════════════════════════╗");
        $this->info("║        COMPLETE ROI FIX - All-in-One Solution            ║");
        $this->info("╚════════════════════════════════════════════════════════════╝");
        $this->newLine();
        $this->info("Reversal Percentage: {$percentage}%");
        $this->info("Days to look back: {$days}");
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

            $this->fix2XAccounts($days, $percentage, $dryRun);

            // PHASE 2: Reverse Excess ROI for Regular Users
            $this->newLine();
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("PHASE 2: Reversing Excess ROI for All Standard Users");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            $this->reverseExcessROI($percentage, $days, $dryRun);

            // PHASE 3: Reverse Excess Profit Share
            $this->newLine();
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("PHASE 3: Reversing Excess Profit Share");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->newLine();

            $this->reverseExcessProfitShare($percentage, $days, $dryRun);

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

    private function fix2XAccounts(int $days, float $percentage, bool $dryRun)
    {
        // Find users stopped in last N days
        $stoppedUsers = User::where('roi_stopped_at', '>=', now()->subDays($days))
            ->where('stop_reason', '2x_limit_reached')
            ->where('user_plan', 'standard')
            ->get();

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
                    $entry->balance = 0;
                    $entry->total_amount = 0;
                    $entry->save();

                    Wallet::create([
                        'user_id' => $user->id,
                        'wallet_type' => 'roi_reversal',
                        'balance' => -$entry->balance,
                        'total_amount' => -$entry->balance,
                        'commission_type' => 'roi_reversal',
                        'level' => 0,
                        'description' => "2X Fix: ROI reversed - given after account stopped (Entry #{$entry->id})",
                        'transaction_type' => 'debit',
                        'wallet_src' => '2x_complete_fix',
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

    private function reverseExcessROI(float $percentage, int $days, bool $dryRun)
    {
        $roiEntries = Wallet::where('wallet_type', 'roi')
            ->where('created_at', '>=', now()->subDays($days))
            ->whereIn('user_id', function ($q) {
                $q->select('id')->from('users')->where('user_plan', 'standard');
            })
            ->get();

        if ($roiEntries->isEmpty()) {
            $this->info('No ROI entries found.');
            return;
        }

        $this->info("Found {$roiEntries->count()} ROI entries");

        $totalOriginal = $roiEntries->sum('balance');
        $totalToReverse = $totalOriginal * ($percentage / 100);
        $totalRemaining = $totalOriginal - $totalToReverse;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total ROI Entries', number_format($roiEntries->count())],
                ['Original Total', '$' . number_format($totalOriginal, 2)],
                ['To Reverse (' . $percentage . '%)', '$' . number_format($totalToReverse, 2)],
                ['Will Remain', '$' . number_format($totalRemaining, 2)],
            ]
        );

        foreach ($roiEntries as $entry) {
            if ($entry->balance == 0) continue; // Skip already reversed

            $originalAmount = $entry->balance;
            $reversalAmount = $originalAmount * ($percentage / 100);
            $newAmount = $originalAmount - $reversalAmount;

            if (!$dryRun) {
                $entry->balance = $newAmount;
                $entry->total_amount = $newAmount;
                $entry->save();

                Wallet::create([
                    'user_id' => $entry->user_id,
                    'wallet_type' => 'roi_reversal',
                    'balance' => -$reversalAmount,
                    'total_amount' => -$reversalAmount,
                    'commission_type' => 'roi_reversal',
                    'level' => 0,
                    'description' => "ROI Reversal: {$percentage}% reversed - 42% error correction (Entry #{$entry->id})",
                    'transaction_type' => 'debit',
                    'wallet_src' => 'complete_roi_fix',
                ]);
            }

            $this->stats['regular_roi_reversed'] += $reversalAmount;
            $this->stats['total_amount_reversed'] += $reversalAmount;
        }
    }

    private function reverseExcessProfitShare(float $percentage, int $days, bool $dryRun)
    {
        $profitEntries = Wallet::where('wallet_type', 'profit_share')
            ->where('created_at', '>=', now()->subDays($days))
            ->whereIn('user_id', function ($q) {
                $q->select('id')->from('users')->where('user_plan', 'standard');
            })
            ->get();

        if ($profitEntries->isEmpty()) {
            $this->info('No profit share entries found.');
            return;
        }

        $this->info("Found {$profitEntries->count()} profit share entries");

        $totalOriginal = $profitEntries->sum('balance');
        $totalToReverse = $totalOriginal * ($percentage / 100);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Entries', number_format($profitEntries->count())],
                ['Original Total', '$' . number_format($totalOriginal, 2)],
                ['To Reverse (' . $percentage . '%)', '$' . number_format($totalToReverse, 2)],
            ]
        );

        foreach ($profitEntries as $entry) {
            if ($entry->balance == 0) continue; // Skip already reversed

            $originalAmount = $entry->balance;
            $reversalAmount = $originalAmount * ($percentage / 100);
            $newAmount = $originalAmount - $reversalAmount;

            if (!$dryRun) {
                $entry->balance = $newAmount;
                $entry->total_amount = $newAmount;
                $entry->save();

                Wallet::create([
                    'user_id' => $entry->user_id,
                    'wallet_type' => 'profit_share_reversal',
                    'balance' => -$reversalAmount,
                    'total_amount' => -$reversalAmount,
                    'commission_type' => 'profit_share_reversal',
                    'level' => 0,
                    'description' => "Profit Share Reversal: {$percentage}% reversed - 42% error correction",
                    'transaction_type' => 'debit',
                    'wallet_src' => 'complete_roi_fix',
                ]);
            }

            $this->stats['regular_profit_reversed'] += $reversalAmount;
            $this->stats['total_amount_reversed'] += $reversalAmount;
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
                ['━━━ GRAND TOTAL ━━━', ''],
                ['Total Amount Reversed', '$' . number_format($this->stats['total_amount_reversed'], 2)],
            ]
        );
    }
}
