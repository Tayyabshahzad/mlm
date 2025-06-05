<?php
namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardService
{
    private const REWARD_LEVELS = [
        1 => ['reward_amount' => 130, 'users_required' => 10],
        2 => ['reward_amount' => 350, 'users_required' => 20],
        3 => ['reward_amount' => 1050, 'users_required' => 30],
        4 => ['reward_amount' => 3450, 'users_required' => 40],
        5 => ['reward_amount' => 8650, 'users_required' => 50],
        6 => ['reward_amount' => 26000, 'users_required' => 60],
        7 => ['reward_amount' => 41500, 'users_required' => 70],
    ];

    private const MAX_REWARD_LEVELS = 7;

    public function processRewardsForUserActivation(User $user): void
    {
        try {
            DB::beginTransaction(); 
            if ($user->sponsor_id) {
                $this->processRewardsRecursively($user->sponsor_id, 1);
            }

            DB::commit();
            Log::info("Rewards processing completed for user activation: {$user->id}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process rewards for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function processRewardsRecursively(int $parentId, int $level): void
    {
        if ($level > self::MAX_REWARD_LEVELS) {
            return;
        }

        $parent = User::where('blocked', false)->find($parentId);
        if (!$parent) {
            return;
        }

        // Debug: Log current processing state
        Log::debug("Processing rewards for user {$parentId} at level {$level}", [
            'current_counts' => $this->debugTeamCounts($parentId)
        ]);

        // Check if reward already exists for this level - CRITICAL FIX
        if ($this->hasRewardForLevel($parentId, $level)) {
            Log::info("Reward already exists for user {$parentId} at level {$level}, skipping");
            // Continue processing upper levels
            if ($parent->sponsor_id) {
                $this->processRewardsRecursively($parent->sponsor_id, $level + 1);
            }
            return;
        }

        $rewardLevel = self::REWARD_LEVELS[$level] ?? null;
        if (!$rewardLevel) {
            return;
        }

        // Check if all previous level rewards are achieved
        if (!$this->hasPreviousLevelRewards($parentId, $level)) {
            Log::info("Skipping reward for level {$level} because previous level rewards are not achieved for user {$parentId}");
            return;
        }

        // FIXED: Calculate team count based on level using referral_trees table
        $teamCount = $this->calculateTeamCountForLevelFixed($parentId, $level);
        $requiredCount = $rewardLevel['users_required'];
        Log::info("Checking reward eligibility", [
        'user_id' => $parentId,
        'level' => $level,
        'team_count' => $teamCount,
        'required_count' => $requiredCount,
        'has_previous_rewards' => $this->hasPreviousLevelRewards($parentId, $level)
        ]);

       if ($teamCount >= $requiredCount) {
        Log::info("Attempting to assign level {$level} reward to user {$parentId}", [
        'team_count' => $teamCount,
        'required' => $requiredCount,
        'previous_levels_qualified' => $this->hasPreviousLevelRewards($parentId, $level)
    ]);

             try {
        $this->assignReward($parentId, $rewardLevel['reward_amount'], $level);
        Log::info("Successfully assigned level {$level} reward to user {$parentId}");
    } catch (\Exception $e) {
        Log::error("Failed to assign level {$level} reward to user {$parentId}: " . $e->getMessage());
    }
        }

        // Continue processing for the parent's sponsor
        if ($parent->sponsor_id) {
            $this->processRewardsRecursively($parent->sponsor_id, $level + 1);
        }
    }


    public function repairMissingRewards(int $userId): void
{
    $levels = self::REWARD_LEVELS;
    
    foreach ($levels as $level => $data) {
        $count = $this->countUsersAtSpecificLevel($userId, $level);
        
        if ($count >= $data['users_required'] && !$this->hasRewardForLevel($userId, $level)) {
            Log::warning("Found missing level {$level} reward for user {$userId}");
            $this->assignReward($userId, $data['reward_amount'], $level);
        }
    }
}
    /**
     * FIXED METHOD: Calculate team count based on referral tree levels correctly
     */
    private function calculateTeamCountForLevelFixed(int $userId, int $rewardLevel): int
    {

         return $this->countUsersAtSpecificLevel($userId, $rewardLevel);

        // if ($rewardLevel === 1) {
        //     // Level 1: Count direct referrals (level = 1 in referral_trees)
        //     return $this->countUsersAtSpecificLevel($userId, 1);
        // } else {
        //     // For levels 2-7: Count cumulative users from level 1 to current level
        //     // This ensures that level 2 reward requires 50 users across levels 1-2
        //     // Level 3 reward requires 150 users across levels 1-3, etc.
        //     return $this->countCumulativeUsers($userId, $rewardLevel);
        // }
    }

    /**
     * Count users at a specific level in the referral tree
     */
    private function countUsersAtSpecificLevel(int $userId, int $level): int
    {
        return DB::table('referral_trees')
            ->join('users', 'referral_trees.descendant_id', '=', 'users.id')
            ->where('referral_trees.ancestor_id', $userId)
            ->where('referral_trees.level', $level)
            ->where('users.blocked', false)
            ->where('users.can_login', 1)
            ->count();
    }

    /**
     * Count cumulative users from level 1 to specified level
     */
    private function countCumulativeUsers(int $userId, int $maxLevel): int
    {
        return DB::table('referral_trees')
            ->join('users', 'referral_trees.descendant_id', '=', 'users.id')
            ->where('referral_trees.ancestor_id', $userId)
            ->where('referral_trees.level', '<=', $maxLevel)
            ->where('users.blocked', false)
            ->where('users.can_login', 1)
            ->count();
    }

    /**
     * Alternative method if you want each level to be independent
     * (Level 2 = exactly 50 users at level 2, Level 3 = exactly 150 users at level 3)
     */
    private function calculateIndependentLevelCount(int $userId, int $rewardLevel): int
    {
        // For independent counting, each reward level corresponds to users at that exact tree level
        return $this->countUsersAtSpecificLevel($userId, $rewardLevel);
    }

    // Keep all existing methods below unchanged
    private function calculateTeamCountForLevel(int $userId, int $level): int
    {
        // OLD METHOD - keeping for reference but not used
        if ($level === 1) {
            return $this->calculateDirectReferrals($userId);
        } else {
            $directChildren = $this->getDirectActiveChildren($userId);
            return $this->calculateTotalTeamSize($directChildren);
        }
    }

    private function getDirectActiveChildren(int $userId)
    {
        return User::where('blocked', false)
            ->where('sponsor_id', $userId)
            ->where('can_login', 1)
            ->get();
    }

    private function calculateDirectReferrals(int $userId): int
    {
        return User::where('sponsor_id', $userId)
            ->where('can_login', 1)
            ->where('blocked', false)
            ->count();
    }

    private function calculateTotalTeamSize($directChildren): int
    {
        $totalTeamSize = 0;
        foreach ($directChildren as $child) {
            $childTeamSize = $this->calculateTeamSize($child->id);
            $totalTeamSize += $childTeamSize;
        }
        return $totalTeamSize;
    }

    private function calculateTeamSize(int $userId): int
    {
        $directReferrals = User::where('blocked', false)
            ->where('sponsor_id', $userId)
            ->where('can_login', 1)
            ->get();

        $directReferralsCount = $directReferrals->count();
        $downlineTeamSize = $directReferrals->sum(function ($child) {
            return $this->calculateTeamSize($child->id);
        });

        return $directReferralsCount + $downlineTeamSize;
    }

    private function hasPreviousLevelRewards(int $userId, int $level): bool
    {

         Log::debug("User ID  {$userId}  " );


        if ($level === 1) {
            return true; // No previous level for level 1
        }

        for ($i = 1; $i < $level; $i++) {
            $requiredCount = self::REWARD_LEVELS[$i]['users_required'];
            $actualCount = $this->countUsersAtSpecificLevel($userId, $i);
            Log::debug("Checking previous level {$i} for user {$userId}", [
                'required' => $requiredCount,
                'actual' => $actualCount,
                'method' => 'cumulative' // or 'specific'
            ]);
            if ($actualCount < $requiredCount) {
                Log::info("User {$userId} doesn't qualify for level {$level} because level {$i} has {$actualCount} users but needs {$requiredCount}");
                return false;
            }
             
        }
        return true;
    }

    private function hasRewardForLevel(int $userId, int $level): bool
    {
        return Wallet::where([
            ['user_id', '=', $userId],
            ['wallet_type', '=', 'reward'],
            ['commission_type', '=', 'reward'],
            ['level', '=', $level],
        ])->where('balance', '>', 0)->exists();
    }

    private function assignReward(int $userId, float $amount, int $level): void
    {
        try {
            // Double check if reward already exists - CRITICAL
            $existingReward = Wallet::where([
                ['user_id', '=', $userId],
                ['wallet_type', '=', 'reward'],
                ['commission_type', '=', 'reward'],
                ['level', '=', $level],
            ])->first();

            if ($existingReward && $existingReward->balance > 0) {
                Log::info("Reward already assigned for user {$userId} at level {$level}");
                return;
            }

            if ($existingReward) {
                // Update existing record
                $existingReward->balance = $amount;
                $existingReward->total_amount = $amount;
                $existingReward->save();
            } else {
                // Create new record
                Wallet::create([
                    'user_id' => $userId,
                    'wallet_type' => 'reward',
                    'commission_type' => 'reward',
                    'level' => $level,
                    'balance' => $amount,
                    'total_amount' => $amount
                ]);
            }

            Log::info("Reward assigned successfully", [
                'user_id' => $userId,
                'amount' => $amount,
                'level' => $level,
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to assign reward: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRewardLevels(): array
    {
        return self::REWARD_LEVELS;
    }

    public function getUserRewardSummary(int $userId): array
    {
        $rewards = Wallet::where('user_id', $userId)
            ->where('wallet_type', 'reward')
            ->where('commission_type', 'reward')
            ->orderBy('level')
            ->get();

        $summary = [
            'total_rewards' => $rewards->sum('balance'),
            'levels_achieved' => $rewards->count(),
            'rewards_by_level' => [],
            'team_counts_by_level' => [], // Added for debugging
        ];

        foreach ($rewards as $reward) {
            $summary['rewards_by_level'][$reward->level] = [
                'amount' => $reward->balance,
                'achieved_at' => $reward->created_at,
            ];
        }

        // Add current team counts for debugging
        for ($level = 1; $level <= self::MAX_REWARD_LEVELS; $level++) {
            $summary['team_counts_by_level'][$level] = $this->calculateTeamCountForLevelFixed($userId, $level);
        }

        return $summary;
    }

    /**
     * Debug method to check team counts
     */
    public function debugTeamCounts(int $userId): array
    {
        $counts = [];
        for ($level = 1; $level <= 7; $level++) {
            $counts["level_{$level}"] = [
                'cumulative_count' => $this->countCumulativeUsers($userId, $level),
                'specific_level_count' => $this->countUsersAtSpecificLevel($userId, $level),
                'required_for_reward' => self::REWARD_LEVELS[$level]['users_required'],
            ];
        }
        return $counts;
    }
}