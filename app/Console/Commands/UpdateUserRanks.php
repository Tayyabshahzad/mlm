<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserRank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateUserRanks extends Command
{
    protected $signature = 'ranks:update {--user-id= : Update specific user ID} {--dry-run : Preview changes without applying}';

    protected $description = 'Update user ranks based on their performance and enable 2x/7x eligibility';

    public function handle()
    {
        $this->info('Starting user rank update process...');

        $userId = $this->option('user-id');
        $dryRun = $this->option('dry-run');

        if ($userId) {
            $this->updateUserRank($userId, $dryRun);
        } else {
            $this->updateAllUserRanks($dryRun);
        }
    }

    private function updateUserRank($userId, $dryRun = false)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return;
        }

        $this->info("Processing user: {$user->name} ({$user->username})");

        $currentRankLevel = $user->current_rank_level ?? 0;
        $newRankLevel = UserRank::calculateUserRank($userId);

        if (!$newRankLevel) {
            $this->warn("User {$userId} does not meet minimum rank requirements");
            return;
        }

        if ($newRankLevel <= $currentRankLevel) {
            $this->info("User {$userId} already has rank level {$currentRankLevel}, no upgrade needed");
            return;
        }

        $ranks = UserRank::getRankLevels();
        $rankData = $ranks[$newRankLevel];

        $this->info("Upgrading from level {$currentRankLevel} to level {$newRankLevel} ({$rankData['name']})");
        $this->info("Binary eligibility: 2x=" . ($rankData['eligible_2x'] ? 'YES' : 'NO') . ", 7x=" . ($rankData['eligible_7x'] ? 'YES' : 'NO'));

        if (!$dryRun) {
            $newRank = UserRank::updateUserRank($userId);
            if ($newRank) {
                $this->info("✅ Successfully upgraded user {$userId} to {$newRank->rank_name}");
            } else {
                $this->error("❌ Failed to upgrade user {$userId}");
            }
        } else {
            $this->info("DRY RUN: Would upgrade user to {$rankData['name']}");
        }
    }

    private function updateAllUserRanks($dryRun = false)
    {
        // Get all active users
        $users = User::where('blocked', false)
            ->where('can_login', 1)
            ->get();

        $this->info("Processing {$users->count()} users for rank updates");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $upgradedCount = 0;
        $errorCount = 0;
        $noChangeCount = 0;

        foreach ($users as $user) {
            try {
                $currentRankLevel = $user->current_rank_level ?? 0;
                $newRankLevel = UserRank::calculateUserRank($user->id);

                if (!$newRankLevel) {
                    $noChangeCount++;
                    $bar->advance();
                    continue;
                }

                if ($newRankLevel <= $currentRankLevel) {
                    $noChangeCount++;
                    $bar->advance();
                    continue;
                }

                if (!$dryRun) {
                    $newRank = UserRank::updateUserRank($user->id);
                    if ($newRank) {
                        $upgradedCount++;
                    } else {
                        $errorCount++;
                    }
                } else {
                    $upgradedCount++; // Count as would-be-upgraded for dry run
                }

            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error updating user {$user->id}: " . $e->getMessage());
                $errorCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("DRY RUN COMPLETE:");
            $this->info("Would upgrade: {$upgradedCount} users");
            $this->info("No changes needed: {$noChangeCount} users");
        } else {
            $this->info("PROCESS COMPLETE:");
            $this->info("Successfully upgraded: {$upgradedCount} users");
            $this->info("No changes needed: {$noChangeCount} users");
            if ($errorCount > 0) {
                $this->warn("Errors encountered: {$errorCount} users");
            }
        }

        // Show rank distribution
        $this->displayRankDistribution();
    }

    private function displayRankDistribution()
    {
        $rankDistribution = DB::table('users')
            ->leftJoin('user_ranks', function($join) {
                $join->on('users.id', '=', 'user_ranks.user_id')
                     ->where('user_ranks.is_active', true);
            })
            ->where('users.blocked', false)
            ->where('users.can_login', 1)
            ->select(
                DB::raw('COALESCE(user_ranks.rank_name, "No Rank") as rank_name'),
                DB::raw('COALESCE(user_ranks.rank_level, 0) as rank_level'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(CASE WHEN users.eligible_for_binary_2x = 1 THEN 1 ELSE 0 END) as eligible_2x'),
                DB::raw('SUM(CASE WHEN users.eligible_for_binary_7x = 1 THEN 1 ELSE 0 END) as eligible_7x')
            )
            ->groupBy('user_ranks.rank_name', 'user_ranks.rank_level')
            ->orderBy('rank_level')
            ->get();

        $this->info("\n=== Current Rank Distribution ===");
        $this->table(
            ['Rank', 'Users', '2x Eligible', '7x Eligible'],
            $rankDistribution->map(function ($rank) {
                return [
                    $rank->rank_name,
                    $rank->count,
                    $rank->eligible_2x,
                    $rank->eligible_7x
                ];
            })->toArray()
        );
    }
}