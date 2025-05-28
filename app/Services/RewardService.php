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
        2 => ['reward_amount' => 350, 'users_required' => 50],
        3 => ['reward_amount' => 1050, 'users_required' => 150],
        4 => ['reward_amount' => 3450, 'users_required' => 400],
        5 => ['reward_amount' => 8650, 'users_required' => 1000],
        6 => ['reward_amount' => 26000, 'users_required' => 2000],
        7 => ['reward_amount' => 41500, 'users_required' => 4000],
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

        // Calculate team count based on level
        $teamCount = $this->calculateTeamCountForLevel($parentId, $level);
        
        Log::info("Level: {$level}, Team Count: {$teamCount}, Required: {$rewardLevel['users_required']}");

        if ($teamCount >= $rewardLevel['users_required']) {
            $this->assignReward($parentId, $rewardLevel['reward_amount'], $level);
        }

        // Continue processing for the parent's sponsor
        if ($parent->sponsor_id) {
            $this->processRewardsRecursively($parent->sponsor_id, $level + 1);
        }
    }

    private function calculateTeamCountForLevel(int $userId, int $level): int
    {
        if ($level === 1) {
            // Level 1: Direct referrals only
            return $this->calculateDirectReferrals($userId);
        } else {
            // Other levels: Total team size (direct + indirect)
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
        if ($level === 1) {
            return true; // No previous level for level 1
        }

        for ($i = 1; $i < $level; $i++) {
            if (!$this->hasRewardForLevel($userId, $i)) {
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
        ])->where('balance', '>', 0)->exists(); // Check balance > 0
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
        ];

        foreach ($rewards as $reward) {
            $summary['rewards_by_level'][$reward->level] = [
                'amount' => $reward->balance,
                'achieved_at' => $reward->created_at,
            ];
        }

        return $summary;
    }
}