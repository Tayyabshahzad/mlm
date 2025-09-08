<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RewardService;
use Illuminate\Console\Command;

class ProcessExistingRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rewards:process-existing {--user-id= : Process specific user ID} {--dry-run : Show what would be processed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process existing users to create pending rewards for those who meet criteria';

    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        parent::__construct();
        $this->rewardService = $rewardService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        if ($userId) {
            $this->processUser($userId, $dryRun);
        } else {
            $this->processAllUsers($dryRun);
        }
    }

    private function processUser(int $userId, bool $dryRun)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return;
        }

        $this->info("Processing user: {$user->name} (ID: {$userId})");
        
        if (!$dryRun) {
            $this->rewardService->repairMissingRewards($userId);
        }
        
        $this->showUserRewardStatus($user);
    }

    private function processAllUsers(bool $dryRun)
    {
        $users = User::where('blocked', false)
            ->where('can_login', 1)
            ->orderBy('created_at')
            ->get();

        $this->info("Processing {$users->count()} eligible users");
        
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $processed = 0;
        $pendingCreated = 0;

        foreach ($users as $user) {
            if (!$dryRun) {
                try {
                    $beforeCount = \App\Models\PendingReward::where('user_id', $user->id)->count();
                    $this->rewardService->repairMissingRewards($user->id);
                    $afterCount = \App\Models\PendingReward::where('user_id', $user->id)->count();
                    
                    if ($afterCount > $beforeCount) {
                        $pendingCreated += ($afterCount - $beforeCount);
                    }
                } catch (\Exception $e) {
                    $this->error("\nError processing user {$user->id}: " . $e->getMessage());
                    continue;
                }
            }
            
            $processed++;
            $bar->advance();
        }

        $bar->finish();
        
        $this->info("\n\nProcessing completed!");
        $this->info("Users processed: {$processed}");
        
        if (!$dryRun) {
            $this->info("Pending rewards created: {$pendingCreated}");
        }
    }

    private function showUserRewardStatus(User $user)
    {
        $summary = $this->rewardService->getUserRewardSummary($user->id);
        
        $this->info("  Current rewards: {$summary['levels_achieved']} levels achieved");
        $this->info("  Total reward amount: $" . number_format($summary['total_rewards'], 2));
        
        foreach ($summary['team_counts_by_level'] as $level => $count) {
            $required = $this->rewardService->getRewardLevels()[$level]['users_required'];
            $status = $count >= $required ? '✓' : '✗';
            $this->info("  Level {$level}: {$count}/{$required} {$status}");
        }
        
        $pendingCount = \App\Models\PendingReward::where('user_id', $user->id)->pending()->count();
        if ($pendingCount > 0) {
            $this->info("  Pending rewards: {$pendingCount}");
        }
    }
}
