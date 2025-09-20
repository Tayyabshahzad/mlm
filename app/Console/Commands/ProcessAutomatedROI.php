<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AutomatedROIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutomatedROI extends Command
{
    protected $signature = 'roi:process-automated
                            {--user-id= : Process ROI for specific user ID}
                            {--trigger-reason=command : Reason for triggering ROI}
                            {--dry-run : Preview without processing}';

    protected $description = 'Process automated ROI payments for users who are eligible';

    private AutomatedROIService $automatedROIService;

    public function __construct(AutomatedROIService $automatedROIService)
    {
        parent::__construct();
        $this->automatedROIService = $automatedROIService;
    }

    public function handle(): int
    {
        $userId = $this->option('user-id');
        $triggerReason = $this->option('trigger-reason');
        $dryRun = $this->option('dry-run');

        $this->info('Starting automated ROI processing...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        if ($userId) {
            return $this->processSingleUser($userId, $triggerReason, $dryRun);
        } else {
            return $this->processAllEligibleUsers($triggerReason, $dryRun);
        }
    }

    private function processSingleUser(int $userId, string $triggerReason, bool $dryRun): int
    {
        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found");

            return Command::FAILURE;
        }

        $this->info("Processing ROI for user: {$user->id} | {$user->name}");

        if ($dryRun) {
            $this->info("DRY RUN: Would process ROI for user {$user->id}");

            return Command::SUCCESS;
        }

        $result = $this->automatedROIService->triggerAutomatedROI($user, $triggerReason);

        if ($result['success']) {
            $this->info('✓ ROI processed successfully: $'.number_format($result['amount'], 2));
        } else {
            $this->warn('✗ ROI processing failed: '.$result['message']);
        }

        return Command::SUCCESS;
    }

    private function processAllEligibleUsers(string $triggerReason, bool $dryRun): int
    {
        $users = $this->getEligibleUsers();
        $userCount = $users->count();

        $this->info("Found {$userCount} eligible users for ROI processing");

        if ($dryRun) {
            $this->info("DRY RUN: Would process ROI for {$userCount} users");

            return Command::SUCCESS;
        }

        $processed = 0;
        $failed = 0;
        $skipped = 0;
        $totalAmount = 0;

        $progressBar = $this->output->createProgressBar($userCount);
        $progressBar->start();

        foreach ($users as $user) {
            $result = $this->automatedROIService->triggerAutomatedROI($user, $triggerReason);

            if ($result['success']) {
                $processed++;
                $totalAmount += $result['amount'];
            } elseif (strpos($result['message'], 'already processed') !== false) {
                $skipped++;
            } else {
                $failed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // Display summary
        $this->info('Automated ROI processing completed:');
        $this->info("✓ Processed: {$processed} users");
        $this->info("⏭ Skipped: {$skipped} users");
        $this->info("✗ Failed: {$failed} users");
        $this->info('💰 Total Amount: $'.number_format($totalAmount, 2));

        Log::info('Automated ROI batch processing completed', [
            'processed' => $processed,
            'skipped' => $skipped,
            'failed' => $failed,
            'total_amount' => $totalAmount,
            'trigger_reason' => $triggerReason,
        ]);

        return Command::SUCCESS;
    }

    private function getEligibleUsers()
    {
        return User::where('blocked', false)
            ->where('can_login', true)
            ->where('freez_wallet', false)
            ->where(function ($query) {
                $query->whereNull('roi_status')
                    ->orWhere('roi_status', 'active');
            })
            ->whereNotNull('roi_eligible_investment_amount')
            ->where('roi_eligible_investment_amount', '>', 0)
            ->get();
    }
}
