<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Fix2XAccounts extends Command
{
    protected $signature = 'roi:fix-2x-accounts
                            {--days=3 : Number of days to look back}
                            {--dry-run : Preview changes without executing}';

    protected $description = 'Fix accounts that were incorrectly stopped at 2X due to ROI error';

    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("=== 2X Account Fix Tool ===");
        $this->info("Days to look back: {$days}");
        $this->info("Mode: " . ($dryRun ? 'DRY RUN (No changes)' : 'LIVE (Changes will be applied)'));
        $this->newLine();

        try {
            DB::beginTransaction();

            // Step 1: Find affected users
            $affectedUsers = $this->getAffectedUsers($days);

            if ($affectedUsers->isEmpty()) {
                $this->warn('No affected users found.');
                DB::rollBack();
                return 0;
            }

            $this->info("Found {$affectedUsers->count()} affected users");
            $this->newLine();

            // Step 2: Categorize users
            $analysis = $this->analyzeUsers($affectedUsers);
            $this->displayAnalysis($analysis);

            // Step 3: Fix the accounts
            if (!$dryRun) {
                if (!$this->confirm('Do you want to proceed with fixing these accounts?')) {
                    $this->warn('Operation cancelled.');
                    DB::rollBack();
                    return 0;
                }
            }

            $results = $this->fixAccounts($analysis, $dryRun);
            $this->displayResults($results);

            if ($dryRun) {
                $this->warn('DRY RUN COMPLETE - No changes were made');
                DB::rollBack();
            } else {
                DB::commit();
                $this->info('✓ Fix completed successfully!');
                Log::info('2X Account Fix Completed', $results);
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            Log::error('2X Account Fix Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    private function getAffectedUsers(int $days)
    {
        return User::where('roi_stopped_at', '>=', now()->subDays($days))
            ->where('stop_reason', '2x_limit_reached')
            ->where('user_plan', 'standard')
            ->get();
    }

    private function analyzeUsers($users)
    {
        $analysis = [
            'received_after_stop' => collect(),
            'incorrectly_stopped' => collect(),
            'correctly_stopped' => collect(),
            'naturally_stopped' => collect(), // Users who legitimately reached 2X
        ];

        foreach ($users as $user) {
            $limit2x = $user->roi_eligible_investment_amount * 2;

            $roiBeforeStop = Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'roi')
                ->where('created_at', '<', $user->roi_stopped_at)
                ->sum('balance');

            $roiAfterStop = Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'roi')
                ->where('created_at', '>=', $user->roi_stopped_at)
                ->sum('balance');

            $totalRoi = $roiBeforeStop + $roiAfterStop;

            // Calculate what ROI SHOULD have been (without the error)
            // If they got 42% instead of 0.42%, the correct amount would be 1/100th
            $correctRoiEstimate = $roiBeforeStop * (0.42 / 42.0); // Approximate correction

            // Check if user would have reached 2X naturally (with correct ROI)
            $wouldBeNaturally2X = $correctRoiEstimate >= $limit2x;

            $userData = [
                'user' => $user,
                'limit_2x' => $limit2x,
                'roi_before_stop' => $roiBeforeStop,
                'roi_after_stop' => $roiAfterStop,
                'total_roi' => $totalRoi,
                'correct_roi_estimate' => $correctRoiEstimate,
                'should_be_stopped' => $totalRoi >= $limit2x,
                'naturally_2x' => $wouldBeNaturally2X,
            ];

            // Categorize
            if ($roiAfterStop > 0) {
                $analysis['received_after_stop']->push($userData);
            }

            // Only mark as incorrectly stopped if they wouldn't have reached 2X naturally
            if ($totalRoi < $limit2x || !$wouldBeNaturally2X) {
                $analysis['incorrectly_stopped']->push($userData);
            } else {
                // User legitimately reached 2X even with correct ROI
                $analysis['naturally_stopped']->push($userData);
            }
        }

        return $analysis;
    }

    private function displayAnalysis(array $analysis)
    {
        $this->info("=== ANALYSIS ===");
        $this->newLine();

        // Users who received ROI after stop
        if ($analysis['received_after_stop']->isNotEmpty()) {
            $this->error("Users who received ROI AFTER account was stopped:");
            $this->table(
                ['ID', 'Name', 'Username', '2X Limit', 'ROI Before Stop', 'ROI After Stop', 'Total'],
                $analysis['received_after_stop']->map(function ($data) {
                    return [
                        $data['user']->id,
                        $data['user']->name,
                        $data['user']->username,
                        '$' . number_format($data['limit_2x'], 2),
                        '$' . number_format($data['roi_before_stop'], 2),
                        '$' . number_format($data['roi_after_stop'], 2),
                        '$' . number_format($data['total_roi'], 2),
                    ];
                })->toArray()
            );
            $this->newLine();
        }

        // Users incorrectly stopped
        if ($analysis['incorrectly_stopped']->isNotEmpty()) {
            $this->warn("Users INCORRECTLY stopped (haven't reached 2X):");
            $this->table(
                ['ID', 'Name', 'Username', '2X Limit', 'Total ROI', 'Percentage', 'Should Reactivate'],
                $analysis['incorrectly_stopped']->map(function ($data) {
                    $percentage = ($data['total_roi'] / $data['limit_2x']) * 100;
                    return [
                        $data['user']->id,
                        $data['user']->name,
                        $data['user']->username,
                        '$' . number_format($data['limit_2x'], 2),
                        '$' . number_format($data['total_roi'], 2),
                        number_format($percentage, 2) . '%',
                        'YES',
                    ];
                })->toArray()
            );
            $this->newLine();
        }

        // Naturally stopped users (DON'T TOUCH)
        if ($analysis['naturally_stopped']->isNotEmpty()) {
            $this->info("Users who NATURALLY reached 2X (will NOT be modified):");
            $this->table(
                ['ID', 'Name', 'Username', '2X Limit', 'Total ROI', 'Correct ROI Est.', 'Status'],
                $analysis['naturally_stopped']->map(function ($data) {
                    return [
                        $data['user']->id,
                        $data['user']->name,
                        $data['user']->username,
                        '$' . number_format($data['limit_2x'], 2),
                        '$' . number_format($data['total_roi'], 2),
                        '$' . number_format($data['correct_roi_estimate'], 2),
                        'KEEP STOPPED',
                    ];
                })->toArray()
            );
            $this->newLine();
        }

        // Summary
        $totalAfterStop = $analysis['received_after_stop']->sum('roi_after_stop');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Users received ROI after stop', $analysis['received_after_stop']->count()],
                ['Users incorrectly stopped (will fix)', $analysis['incorrectly_stopped']->count()],
                ['Users naturally stopped (won\'t touch)', $analysis['naturally_stopped']->count()],
                ['Total ROI to reverse', '$' . number_format($totalAfterStop, 2)],
            ]
        );
    }

    private function fixAccounts(array $analysis, bool $dryRun)
    {
        $results = [
            'roi_reversed' => 0,
            'users_reactivated' => 0,
            'total_reversed_amount' => 0,
        ];

        // Fix 1: Reverse ROI given after stop
        foreach ($analysis['received_after_stop'] as $data) {
            $user = $data['user'];
            $roiAfterStop = $data['roi_after_stop'];

            if (!$dryRun) {
                // Get ROI entries after stop
                $roiEntries = Wallet::where('user_id', $user->id)
                    ->where('wallet_type', 'roi')
                    ->where('created_at', '>=', $user->roi_stopped_at)
                    ->get();

                foreach ($roiEntries as $entry) {
                    $originalAmount = $entry->balance;

                    // Delete the entry (it shouldn't exist)
                    $entry->balance = 0;
                    $entry->total_amount = 0;
                    $entry->save();

                    // Create reversal record
                    Wallet::create([
                        'user_id' => $user->id,
                        'wallet_type' => 'roi_reversal',
                        'balance' => -$originalAmount,
                        'total_amount' => -$originalAmount,
                        'description' => "2X Fix: ROI reversed - given after account was stopped (Entry #{$entry->id}, Amount: \${$originalAmount})",
                        'transaction_type' => 'debit',
                        'wallet_src' => '2x_account_fix',
                    ]);
                }
            }

            $results['roi_reversed'] += 1;
            $results['total_reversed_amount'] += $roiAfterStop;
        }

        // Fix 2: Reactivate incorrectly stopped accounts
        foreach ($analysis['incorrectly_stopped'] as $data) {
            $user = $data['user'];

            if (!$dryRun) {
                $user->roi_status = 'active';
                $user->roi_stopped_at = null;
                $user->stop_reason = null;
                $user->stop_reason_description = 'Reactivated - incorrectly stopped due to ROI calculation error';
                $user->save();

                Log::info("Reactivated user #{$user->id} - {$user->name}", [
                    'total_roi' => $data['total_roi'],
                    'limit_2x' => $data['limit_2x'],
                    'percentage' => ($data['total_roi'] / $data['limit_2x']) * 100,
                ]);
            }

            $results['users_reactivated'] += 1;
        }

        return $results;
    }

    private function displayResults(array $results)
    {
        $this->newLine();
        $this->info('=== FIX RESULTS ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Users with ROI reversed', $results['roi_reversed']],
                ['Users reactivated', $results['users_reactivated']],
                ['Total amount reversed', '$' . number_format($results['total_reversed_amount'], 2)],
            ]
        );
    }
}
