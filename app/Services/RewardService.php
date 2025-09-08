<?php

namespace App\Services;

use App\Models\PendingReward;
use App\Models\RewardSetting;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardService
{
    // Fallback constants (used if database is not available)
    private const FALLBACK_REWARD_LEVELS = [
        1 => ['reward_amount' => 130, 'users_required' => 10],
        2 => ['reward_amount' => 350, 'users_required' => 20],
        3 => ['reward_amount' => 1050, 'users_required' => 30],
        4 => ['reward_amount' => 3450, 'users_required' => 40],
        5 => ['reward_amount' => 8650, 'users_required' => 50],
        6 => ['reward_amount' => 26000, 'users_required' => 60],
        7 => ['reward_amount' => 41500, 'users_required' => 70],
    ];

    private const MAX_REWARD_LEVELS = 10; // Increased to support more levels

    /**
     * Get reward levels from database with fallback to constants
     */
    public function getRewardLevels(): array
    {
        try {
            return RewardSetting::getRewardLevelsArray();
        } catch (\Exception $e) {
            Log::warning("Failed to load reward levels from database, using fallback: " . $e->getMessage());
            return self::FALLBACK_REWARD_LEVELS;
        }
    }

    /**
     * Get specific reward level from database
     */
    public function getRewardLevel(int $level): ?array
    {
        $levels = $this->getRewardLevels();
        return $levels[$level] ?? null;
    }

    /**
     * Get maximum configured reward level
     */
    public function getMaxRewardLevel(): int
    {
        try {
            $maxLevel = RewardSetting::where('is_active', true)->max('level');
            return $maxLevel ?: self::MAX_REWARD_LEVELS;
        } catch (\Exception $e) {
            return self::MAX_REWARD_LEVELS;
        }
    }

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
            Log::error("Failed to process rewards for user {$user->id}: ".$e->getMessage());
            throw $e;
        }
    }

    public function processRewardsRecursively(int $parentId, int $level): void
    {
        $maxLevel = $this->getMaxRewardLevel();
        if ($level > $maxLevel) {
            return;
        }

        $parent = User::where('blocked', false)->find($parentId);
        if (! $parent) {
            return;
        }

        // Debug: Log current processing state
        Log::debug("Processing rewards for user {$parentId} at level {$level}", [
            'current_counts' => $this->debugTeamCounts($parentId),
        ]);

        // Check if reward already exists (approved or pending) for this level - CRITICAL FIX
        if ($this->hasRewardForLevel($parentId, $level) || $this->hasPendingRewardForLevel($parentId, $level)) {
            Log::info("Reward already exists or is pending for user {$parentId} at level {$level}, skipping");
            // Continue processing upper levels
            if ($parent->sponsor_id) {
                $this->processRewardsRecursively($parent->sponsor_id, $level + 1);
            }

            return;
        }

        $rewardLevel = $this->getRewardLevel($level);
        if (! $rewardLevel) {
            Log::info("No reward level configuration found for level {$level}");
            return;
        }

        // Check if all previous level rewards are achieved
        if (! $this->hasPreviousLevelRewards($parentId, $level)) {
            Log::info("Skipping reward for level {$level} because previous level rewards are not achieved for user {$parentId}");

            return;
        }

        // FIXED: Calculate team count based on level using referral_trees table
        $teamCount = $this->calculateTeamCountForLevelFixed($parentId, $level);
        $requiredCount = $rewardLevel['users_required'];
        Log::info('Checking reward eligibility', [
            'user_id' => $parentId,
            'level' => $level,
            'team_count' => $teamCount,
            'required_count' => $requiredCount,
            'has_previous_rewards' => $this->hasPreviousLevelRewards($parentId, $level),
        ]);

        if ($teamCount >= $requiredCount) {
            Log::info("Attempting to assign level {$level} reward to user {$parentId}", [
                'team_count' => $teamCount,
                'required' => $requiredCount,
                'previous_levels_qualified' => $this->hasPreviousLevelRewards($parentId, $level),
            ]);

            try {
                $this->createPendingReward($parentId, $rewardLevel['reward_amount'], $level, $teamCount, $requiredCount);
                Log::info("Successfully created pending level {$level} reward for user {$parentId}");
            } catch (\Exception $e) {
                Log::error("Failed to create pending level {$level} reward for user {$parentId}: ".$e->getMessage());
            }
        }

        // Continue processing for the parent's sponsor
        if ($parent->sponsor_id) {
            $this->processRewardsRecursively($parent->sponsor_id, $level + 1);
        }
    }

    public function repairMissingRewards(int $userId): void
    {
        $levels = $this->getRewardLevels();

        foreach ($levels as $level => $data) {
            // Check if user is eligible and doesn't already have the reward or pending reward
            if ($this->hasRewardForLevel($userId, $level) || $this->hasPendingRewardForLevel($userId, $level)) {
                continue;
            }

            $validation = $this->validateRewardEligibility($userId, $level);

            if ($validation['eligible']) {
                Log::info("Creating pending reward for user {$userId} at level {$level} during repair");

                try {
                    $this->createPendingReward(
                        $userId,
                        $data['reward_amount'],
                        $level,
                        $validation['current_stats']['team_count'],
                        $data['users_required']
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to create pending reward during repair: '.$e->getMessage());
                }
            } else {
                Log::debug("User {$userId} not eligible for level {$level}: ".implode(', ', $validation['reasons']));
            }
        }
    }

    /**
     * FIXED METHOD: Calculate team count based on referral tree levels correctly
     */
    public function calculateTeamCountForLevelFixed(int $userId, int $rewardLevel): int
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
        Log::debug("Checking previous rewards for User ID {$userId} at level {$level}");

        if ($level === 1) {
            return true; // No previous level for level 1
        }

        // CRITICAL FIX: Check that user actually HAS the rewards for previous levels
        // Not just that they meet the team requirements
        for ($i = 1; $i < $level; $i++) {
            $hasReward = $this->hasRewardForLevel($userId, $i);
            $hasPending = $this->hasPendingRewardForLevel($userId, $i);

            Log::debug("Checking previous level {$i} for user {$userId}", [
                'has_reward' => $hasReward,
                'has_pending' => $hasPending,
                'level_being_checked' => $level,
            ]);

            // User must have either the actual reward or an approved pending reward for previous levels
            if (! $hasReward && ! $hasPending) {
                Log::info("User {$userId} doesn't qualify for level {$level} because they don't have level {$i} reward (neither actual nor pending)");

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

    private function hasPendingRewardForLevel(int $userId, int $level): bool
    {
        return PendingReward::where([
            ['user_id', '=', $userId],
            ['level', '=', $level],
        ])->whereIn('status', ['pending', 'approved'])->exists();
    }

    private function createPendingReward(int $userId, float $amount, int $level, int $teamCount, int $requiredCount): void
    {
        try {
            // Double check if pending reward already exists
            $existingPending = PendingReward::where([
                ['user_id', '=', $userId],
                ['level', '=', $level],
            ])->first();

            if ($existingPending) {
                Log::info("Pending reward already exists for user {$userId} at level {$level}");

                return;
            }

            // Gather detailed eligibility data for admin verification
            $eligibilityData = [
                'direct_referrals' => $this->calculateDirectReferrals($userId),
                'team_counts_by_level' => [],
                'previous_rewards_check' => [],
                'calculation_method' => 'specific_level',
                'verified_at' => now()->toISOString(),
            ];

            // Add team counts for all levels for verification
            $maxLevel = $this->getMaxRewardLevel();
            for ($i = 1; $i <= $maxLevel; $i++) {
                $eligibilityData['team_counts_by_level'][$i] = $this->countUsersAtSpecificLevel($userId, $i);
            }

            // Add previous rewards verification
            for ($i = 1; $i < $level; $i++) {
                $previousLevel = $this->getRewardLevel($i);
                if ($previousLevel) {
                    $eligibilityData['previous_rewards_check'][$i] = [
                        'required' => $previousLevel['users_required'],
                        'actual' => $this->countUsersAtSpecificLevel($userId, $i),
                        'has_reward' => $this->hasRewardForLevel($userId, $i),
                    ];
                }
            }

            PendingReward::create([
                'user_id' => $userId,
                'level' => $level,
                'reward_amount' => $amount,
                'team_count' => $teamCount,
                'users_required' => $requiredCount,
                'status' => 'pending',
                'eligibility_data' => $eligibilityData,
            ]);

            Log::info('Pending reward created successfully', [
                'user_id' => $userId,
                'amount' => $amount,
                'level' => $level,
                'team_count' => $teamCount,
                'required_count' => $requiredCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create pending reward: '.$e->getMessage());
            throw $e;
        }
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
                    'total_amount' => $amount,
                ]);
            }

            Log::info('Reward assigned successfully', [
                'user_id' => $userId,
                'amount' => $amount,
                'level' => $level,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to assign reward: '.$e->getMessage());
            throw $e;
        }
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
        $maxLevel = $this->getMaxRewardLevel();
        for ($level = 1; $level <= $maxLevel; $level++) {
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
        $maxLevel = $this->getMaxRewardLevel();
        for ($level = 1; $level <= $maxLevel; $level++) {
            $rewardLevel = $this->getRewardLevel($level);
            $counts["level_{$level}"] = [
                'cumulative_count' => $this->countCumulativeUsers($userId, $level),
                'specific_level_count' => $this->countUsersAtSpecificLevel($userId, $level),
                'required_for_reward' => $rewardLevel ? $rewardLevel['users_required'] : 0,
            ];
        }

        return $counts;
    }

    /**
     * Approve a pending reward and transfer it to user's wallet
     */
    public function approvePendingReward(int $pendingRewardId, int $adminUserId, string $notes = ''): bool
    {
        try {
            DB::beginTransaction();

            $pendingReward = PendingReward::where('id', $pendingRewardId)
                ->where('status', 'pending')
                ->first();

            if (! $pendingReward) {
                throw new \Exception('Pending reward not found or already processed');
            }

            // Re-verify eligibility before approval
            if (! $this->verifyCurrentEligibility($pendingReward)) {
                throw new \Exception('User no longer meets eligibility criteria');
            }

            // Mark as approved
            $pendingReward->update([
                'status' => 'approved',
                'approved_by' => $adminUserId,
                'approved_at' => now(),
                'admin_notes' => $notes,
            ]);

            // Create wallet entry
            $this->assignReward(
                $pendingReward->user_id,
                $pendingReward->reward_amount,
                $pendingReward->level
            );

            DB::commit();
            Log::info('Pending reward approved', [
                'pending_reward_id' => $pendingRewardId,
                'user_id' => $pendingReward->user_id,
                'level' => $pendingReward->level,
                'approved_by' => $adminUserId,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve pending reward: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Deny a pending reward
     */
    public function denyPendingReward(int $pendingRewardId, int $adminUserId, string $notes = ''): bool
    {
        try {
            $pendingReward = PendingReward::where('id', $pendingRewardId)
                ->where('status', 'pending')
                ->first();

            if (! $pendingReward) {
                throw new \Exception('Pending reward not found or already processed');
            }

            $pendingReward->update([
                'status' => 'denied',
                'approved_by' => $adminUserId,
                'approved_at' => now(),
                'admin_notes' => $notes,
            ]);

            Log::info('Pending reward denied', [
                'pending_reward_id' => $pendingRewardId,
                'user_id' => $pendingReward->user_id,
                'level' => $pendingReward->level,
                'denied_by' => $adminUserId,
                'reason' => $notes,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to deny pending reward: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify current eligibility for a pending reward
     */
    public function verifyCurrentEligibility(PendingReward $pendingReward): bool
    {
        $userId = $pendingReward->user_id;
        $level = $pendingReward->level;

        // Check if user still meets team count requirements
        $currentTeamCount = $this->calculateTeamCountForLevelFixed($userId, $level);
        $rewardLevel = $this->getRewardLevel($level);
        if (!$rewardLevel) {
            return false;
        }
        $requiredCount = $rewardLevel['users_required'];

        if ($currentTeamCount < $requiredCount) {
            Log::info("Current team count {$currentTeamCount} is less than required {$requiredCount} for level {$level}");

            return false;
        }

        // Check if user still has all previous level rewards
        if (! $this->hasPreviousLevelRewards($userId, $level)) {
            Log::info("User {$userId} no longer has required previous level rewards for level {$level}");

            return false;
        }

        // Check if user already has this reward (maybe approved elsewhere)
        if ($this->hasRewardForLevel($userId, $level)) {
            Log::info("User {$userId} already has level {$level} reward");

            return false;
        }

        return true;
    }

    /**
     * Get pending rewards for admin review
     */
    public function getPendingRewardsForAdmin(int $limit = 50)
    {
        return PendingReward::with(['user', 'approvedBy'])
            ->pending()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($pendingReward) {
                // Add current verification data
                $pendingReward->current_verification = [
                    'current_team_count' => $this->calculateTeamCountForLevelFixed($pendingReward->user_id, $pendingReward->level),
                    'still_eligible' => $this->verifyCurrentEligibility($pendingReward),
                    'verified_at' => now()->toISOString(),
                ];

                return $pendingReward;
            });
    }

    /**
     * Enhanced reward eligibility check with stricter validation
     */
    public function validateRewardEligibility(int $userId, int $level): array
    {
        $validation = [
            'eligible' => false,
            'reasons' => [],
            'current_stats' => [],
        ];

        // Check if reward level exists
        $rewardLevel = $this->getRewardLevel($level);
        if (! $rewardLevel) {
            $validation['reasons'][] = "Invalid reward level: {$level}";
            return $validation;
        }

        // Check current team count
        $teamCount = $this->calculateTeamCountForLevelFixed($userId, $level);
        $validation['current_stats']['team_count'] = $teamCount;
        $validation['current_stats']['required_count'] = $rewardLevel['users_required'];

        if ($teamCount < $rewardLevel['users_required']) {
            $validation['reasons'][] = "Insufficient team count: has {$teamCount}, needs {$rewardLevel['users_required']}";
        }

        // Check previous level rewards
        if (! $this->hasPreviousLevelRewards($userId, $level)) {
            $validation['reasons'][] = 'Missing required previous level rewards';
        }

        // Check if already has this reward
        if ($this->hasRewardForLevel($userId, $level)) {
            $validation['reasons'][] = "Already has level {$level} reward";
        }

        // Check if pending reward exists
        if ($this->hasPendingRewardForLevel($userId, $level)) {
            $validation['reasons'][] = "Pending reward already exists for level {$level}";
        }

        $validation['eligible'] = empty($validation['reasons']);

        return $validation;
    }
}
