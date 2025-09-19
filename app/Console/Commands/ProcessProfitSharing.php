<?php

namespace App\Console\Commands;

use App\Services\ProfitSharingService;
use Illuminate\Console\Command;

class ProcessProfitSharing extends Command
{
    protected $signature = 'profit:distribute {amount} {--type=rank_based : Distribution type (rank_based, binary_performance, investment_based, team_size_based)} {--dry-run : Preview distribution without applying}';

    protected $description = 'Distribute company profits among eligible users';

    protected ProfitSharingService $profitService;

    public function __construct(ProfitSharingService $profitService)
    {
        parent::__construct();
        $this->profitService = $profitService;
    }

    public function handle()
    {
        $amount = (float) $this->argument('amount');
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        if ($amount <= 0) {
            $this->error('Amount must be greater than 0');
            return 1;
        }

        $validTypes = ['rank_based', 'binary_performance', 'investment_based', 'team_size_based'];
        if (!in_array($type, $validTypes)) {
            $this->error('Invalid distribution type. Valid types: ' . implode(', ', $validTypes));
            return 1;
        }

        $this->info("Preparing to distribute ${amount} using {$type} method");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No actual distribution will occur');
        }

        try {
            if (!$dryRun) {
                $this->profitService->distributeCompanyProfits($amount, $type);
                $this->info("✅ Successfully distributed ${amount} to eligible users");
            } else {
                // For dry run, we'd need to implement a preview method
                $this->info("DRY RUN: Would distribute ${amount} using {$type} method");
            }

            // Show distribution stats
            $this->displayStats();

        } catch (\Exception $e) {
            $this->error("❌ Failed to distribute profits: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function displayStats()
    {
        $stats = $this->profitService->getProfitSharingStats();

        $this->info("\n=== Profit Sharing Statistics ===");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Distributed', '$' . number_format($stats['total_distributed'], 2)],
                ['Total Recipients', $stats['total_recipients']],
            ]
        );

        if ($stats['distribution_methods']->isNotEmpty()) {
            $this->info("\n=== Distribution Methods ===");
            $this->table(
                ['Method', 'Count', 'Total Amount'],
                $stats['distribution_methods']->map(function ($method) {
                    return [
                        ucwords(str_replace('_', ' ', $method->source)),
                        $method->count,
                        '$' . number_format($method->total, 2)
                    ];
                })->toArray()
            );
        }
    }
}