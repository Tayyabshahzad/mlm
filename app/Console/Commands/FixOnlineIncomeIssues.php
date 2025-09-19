<?php

namespace App\Console\Commands;

use App\Services\BinarySystemService;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixOnlineIncomeIssues extends Command
{
    protected $signature = 'binary:fix-online-income {--user-id= : Fix for specific user ID} {--dry-run : Preview changes without applying}';

    protected $description = 'Fix online income connection issues and disconnect from 2x/7x binary systems';

    protected BinarySystemService $binaryService;

    public function __construct(BinarySystemService $binaryService)
    {
        parent::__construct();
        $this->binaryService = $binaryService;
    }

    public function handle()
    {
        $this->info('Starting online income fix process...');

        $userId = $this->option('user-id');
        $dryRun = $this->option('dry-run');

        if ($userId) {
            $this->fixUserOnlineIncome($userId, $dryRun);
        } else {
            $this->fixAllUsersOnlineIncome($dryRun);
        }
    }

    private function fixUserOnlineIncome($userId, $dryRun = false)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return;
        }

        $this->info("Processing user: {$user->name} ({$user->username})");

        // Find online transfer earnings connected to binary systems
        $onlineEarnings = Wallet::where('user_id', $userId)
            ->where('wallet_type', 'binary_earning')
            ->where('source', 'online_transfer')
            ->get();

        if ($onlineEarnings->isEmpty()) {
            $this->info("No online income issues found for user {$userId}");
            return;
        }

        $this->warn("Found {$onlineEarnings->count()} online income entries connected to binary systems");

        if (!$dryRun) {
            $result = $this->binaryService->fixOnlineIncomeConnection($userId);
            if ($result) {
                $this->info("✅ Fixed online income issues for user {$userId}");
            } else {
                $this->error("❌ Failed to fix online income issues for user {$userId}");
            }
        } else {
            $this->info("DRY RUN: Would fix {$onlineEarnings->count()} online income entries");
        }
    }

    private function fixAllUsersOnlineIncome($dryRun = false)
    {
        // Find all users with online income connected to binary systems
        $usersWithIssues = DB::table('wallets')
            ->where('wallet_type', 'binary_earning')
            ->where('source', 'online_transfer')
            ->distinct('user_id')
            ->pluck('user_id');

        $this->info("Found {$usersWithIssues->count()} users with online income issues");

        if ($usersWithIssues->isEmpty()) {
            $this->info("No online income issues found in the system");
            return;
        }

        $bar = $this->output->createProgressBar($usersWithIssues->count());
        $bar->start();

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($usersWithIssues as $userId) {
            try {
                if (!$dryRun) {
                    $result = $this->binaryService->fixOnlineIncomeConnection($userId);
                    if ($result) {
                        $fixedCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $fixedCount++; // Count as would-be-fixed for dry run
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error fixing user {$userId}: " . $e->getMessage());
                $errorCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("DRY RUN COMPLETE:");
            $this->info("Would fix: {$fixedCount} users");
        } else {
            $this->info("PROCESS COMPLETE:");
            $this->info("Successfully fixed: {$fixedCount} users");
            if ($errorCount > 0) {
                $this->warn("Errors encountered: {$errorCount} users");
            }
        }
    }
}