<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReverseROI extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'roi:reverse
                            {--percentage=83.16 : Percentage of ROI to reverse}
                            {--plan=standard : User plan to reverse ROI for (standard/vip/all)}
                            {--days=2 : Number of days to look back}
                            {--dry-run : Preview changes without executing}
                            {--include-profit-share : Also reverse profit sharing based on reversed ROI}';

    /**
     * The console command description.
     */
    protected $description = 'Reverse ROI payments for specified users and optionally adjust profit sharing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $percentage = (float) $this->option('percentage');
        $plan = $this->option('plan');
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $includeProfitShare = $this->option('include-profit-share');

        $this->info("=== ROI Reversal Tool ===");
        $this->info("Reversal Percentage: {$percentage}%");
        $this->info("Target Plan: {$plan}");
        $this->info("Days to look back: {$days}");
        $this->info("Include Profit Share Adjustment: " . ($includeProfitShare ? 'Yes' : 'No'));
        $this->info("Mode: " . ($dryRun ? 'DRY RUN (No changes will be made)' : 'LIVE (Changes will be applied)'));
        $this->newLine();

        if (!$dryRun) {
            if (!$this->confirm('Are you sure you want to proceed with LIVE reversal?')) {
                $this->warn('Reversal cancelled.');
                return 0;
            }
        }

        try {
            DB::beginTransaction();

            // Step 1: Get affected ROI entries
            $roiEntries = $this->getAffectedROIEntries($plan, $days);

            if ($roiEntries->isEmpty()) {
                $this->warn('No ROI entries found for the specified criteria.');
                DB::rollBack();
                return 0;
            }

            $this->info("Found {$roiEntries->count()} ROI entries to reverse");
            $this->newLine();

            // Step 2: Calculate reversal statistics
            $stats = $this->calculateReversalStats($roiEntries, $percentage);
            $this->displayStats($stats);

            // Step 3: Process ROI reversal
            $roiReversalResults = $this->reverseROIEntries($roiEntries, $percentage, $dryRun);

            // Step 4: Process profit share reversal if requested
            $profitShareResults = null;
            if ($includeProfitShare) {
                $this->newLine();
                $this->info("Processing profit share reversals...");
                $profitShareResults = $this->reverseProfitSharing($plan, $days, $percentage, $dryRun);
            }

            // Step 5: Display results
            $this->displayResults($roiReversalResults, $profitShareResults);

            if ($dryRun) {
                $this->warn('DRY RUN COMPLETE - No changes were made to the database');
                DB::rollBack();
            } else {
                DB::commit();
                $this->info('✓ Reversal completed successfully!');

                // Log the reversal
                Log::info('ROI Reversal Completed', [
                    'percentage' => $percentage,
                    'plan' => $plan,
                    'days' => $days,
                    'roi_entries_affected' => $roiReversalResults['total_affected'],
                    'total_reversed' => $roiReversalResults['total_reversed'],
                    'profit_share_affected' => $profitShareResults['total_affected'] ?? 0,
                ]);
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during reversal: ' . $e->getMessage());
            Log::error('ROI Reversal Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Get affected ROI entries based on criteria
     */
    private function getAffectedROIEntries(string $plan, int $days)
    {
        $query = Wallet::where('wallet_type', 'roi')
            ->where('created_at', '>=', now()->subDays($days));

        if ($plan !== 'all') {
            $query->whereIn('user_id', function ($q) use ($plan) {
                $q->select('id')
                    ->from('users')
                    ->where('user_plan', $plan);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Calculate reversal statistics
     */
    private function calculateReversalStats($roiEntries, float $percentage)
    {
        $totalOriginal = $roiEntries->sum('balance');
        $totalToReverse = $totalOriginal * ($percentage / 100);
        $totalRemaining = $totalOriginal - $totalToReverse;

        $userBreakdown = $roiEntries->groupBy('user_id')->map(function ($entries) use ($percentage) {
            $original = $entries->sum('balance');
            return [
                'original' => $original,
                'to_reverse' => $original * ($percentage / 100),
                'remaining' => $original * ((100 - $percentage) / 100),
                'count' => $entries->count()
            ];
        });

        return [
            'total_entries' => $roiEntries->count(),
            'total_users' => $userBreakdown->count(),
            'total_original' => $totalOriginal,
            'total_to_reverse' => $totalToReverse,
            'total_remaining' => $totalRemaining,
            'user_breakdown' => $userBreakdown
        ];
    }

    /**
     * Display statistics
     */
    private function displayStats(array $stats)
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Entries', number_format($stats['total_entries'])],
                ['Total Users Affected', number_format($stats['total_users'])],
                ['Original Total ROI', '$' . number_format($stats['total_original'], 2)],
                ['Amount to Reverse', '$' . number_format($stats['total_to_reverse'], 2)],
                ['Amount Remaining', '$' . number_format($stats['total_remaining'], 2)],
            ]
        );

        if ($this->option('verbose')) {
            $this->newLine();
            $this->info('Per-User Breakdown:');
            $headers = ['User ID', 'Entries', 'Original', 'To Reverse', 'Remaining'];
            $rows = [];
            foreach ($stats['user_breakdown']->take(20) as $userId => $data) {
                $rows[] = [
                    $userId,
                    $data['count'],
                    '$' . number_format($data['original'], 2),
                    '$' . number_format($data['to_reverse'], 2),
                    '$' . number_format($data['remaining'], 2),
                ];
            }
            $this->table($headers, $rows);
            if ($stats['user_breakdown']->count() > 20) {
                $this->info('... and ' . ($stats['user_breakdown']->count() - 20) . ' more users');
            }
        }
    }

    /**
     * Reverse ROI entries
     */
    private function reverseROIEntries($roiEntries, float $percentage, bool $dryRun)
    {
        $totalAffected = 0;
        $totalReversed = 0;
        $reversalRecords = [];

        foreach ($roiEntries as $entry) {
            $originalAmount = $entry->balance;
            $reversalAmount = $originalAmount * ($percentage / 100);
            $newAmount = $originalAmount - $reversalAmount;

            if (!$dryRun) {
                // Update the original entry
                $entry->balance = $newAmount;
                $entry->total_amount = $newAmount;
                $entry->save();

                // Create reversal record
                Wallet::create([
                    'user_id' => $entry->user_id,
                    'wallet_type' => 'roi_reversal',
                    'balance' => -$reversalAmount,
                    'total_amount' => -$reversalAmount,
                    'wallet_from' => null,
                    'commission_type' => 'roi_reversal',
                    'description' => "ROI Reversal: {$percentage}% reversed from entry #{$entry->id} (Original: \${$originalAmount}, Reversed: \${$reversalAmount}, New: \${$newAmount})",
                    'transaction_type' => 'debit',
                    'wallet_src' => 'roi_reversal_command',
                ]);
            }

            $reversalRecords[] = [
                'entry_id' => $entry->id,
                'user_id' => $entry->user_id,
                'original' => $originalAmount,
                'reversed' => $reversalAmount,
                'new' => $newAmount,
            ];

            $totalAffected++;
            $totalReversed += $reversalAmount;
        }

        return [
            'total_affected' => $totalAffected,
            'total_reversed' => $totalReversed,
            'records' => $reversalRecords,
        ];
    }

    /**
     * Reverse profit sharing based on reversed ROI
     */
    private function reverseProfitSharing(string $plan, int $days, float $percentage, bool $dryRun)
    {
        $profitShareEntries = Wallet::where('wallet_type', 'profit_share')
            ->where('created_at', '>=', now()->subDays($days));

        if ($plan !== 'all') {
            $profitShareEntries->whereIn('user_id', function ($q) use ($plan) {
                $q->select('id')
                    ->from('users')
                    ->where('user_plan', $plan);
            });
        }

        $profitShareEntries = $profitShareEntries->get();

        $totalAffected = 0;
        $totalReversed = 0;

        foreach ($profitShareEntries as $entry) {
            $originalAmount = $entry->balance;
            $reversalAmount = $originalAmount * ($percentage / 100);
            $newAmount = $originalAmount - $reversalAmount;

            if (!$dryRun) {
                // Update the original entry
                $entry->balance = $newAmount;
                $entry->total_amount = $newAmount;
                $entry->save();

                // Create reversal record
                Wallet::create([
                    'user_id' => $entry->user_id,
                    'wallet_type' => 'profit_share_reversal',
                    'balance' => -$reversalAmount,
                    'total_amount' => -$reversalAmount,
                    'commission_type' => 'profit_share_reversal',
                    'description' => "Profit Share Reversal: {$percentage}% reversed from entry #{$entry->id} (Original: \${$originalAmount}, Reversed: \${$reversalAmount}, New: \${$newAmount})",
                    'transaction_type' => 'debit',
                    'wallet_src' => 'profit_share_reversal_command',
                ]);
            }

            $totalAffected++;
            $totalReversed += $reversalAmount;
        }

        return [
            'total_affected' => $totalAffected,
            'total_reversed' => $totalReversed,
        ];
    }

    /**
     * Display results
     */
    private function displayResults(array $roiResults, ?array $profitShareResults)
    {
        $this->newLine();
        $this->info('=== REVERSAL SUMMARY ===');

        $this->table(
            ['Category', 'Entries Affected', 'Total Reversed'],
            [
                [
                    'ROI Entries',
                    number_format($roiResults['total_affected']),
                    '$' . number_format($roiResults['total_reversed'], 2)
                ],
                [
                    'Profit Share Entries',
                    $profitShareResults ? number_format($profitShareResults['total_affected']) : 'N/A',
                    $profitShareResults ? '$' . number_format($profitShareResults['total_reversed'], 2) : 'N/A'
                ],
            ]
        );

        if ($this->option('verbose') && !empty($roiResults['records'])) {
            $this->newLine();
            $this->info('Sample Reversal Records (first 10):');
            $headers = ['Entry ID', 'User ID', 'Original', 'Reversed', 'New Amount'];
            $rows = [];
            foreach (array_slice($roiResults['records'], 0, 10) as $record) {
                $rows[] = [
                    $record['entry_id'],
                    $record['user_id'],
                    '$' . number_format($record['original'], 2),
                    '$' . number_format($record['reversed'], 2),
                    '$' . number_format($record['new'], 2),
                ];
            }
            $this->table($headers, $rows);
        }
    }
}
