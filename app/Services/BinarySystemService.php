<?php

namespace App\Services;

use App\Models\BinarySystem;
use App\Models\User;
use App\Models\UserRank;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BinarySystemService
{
    public function initializeBinarySystem($userId, $systemType, $investmentAmount = null)
    {
        try {
            DB::beginTransaction();

            $user = User::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }

            if (!$user->canAccessBinary($systemType)) {
                throw new \Exception("User is not eligible for {$systemType} binary system. Please upgrade your rank first.");
            }

            $existingSystem = BinarySystem::where('user_id', $userId)
                ->where('system_type', $systemType)
                ->where('is_active', true)
                ->first();

            if ($existingSystem) {
                throw new \Exception("User already has an active {$systemType} binary system");
            }

            $levels = BinarySystem::getLevels();
            $firstLevel = $levels[$systemType][1];

            $system = BinarySystem::create([
                'user_id' => $userId,
                'system_type' => $systemType,
                'current_level' => 1,
                'investment_amount' => $investmentAmount ?? $firstLevel['investment'],
                'current_limit' => $firstLevel['limit'],
                'total_earned' => 0,
                'auto_next_level' => true,
                'is_active' => true
            ]);

            // Record investment in wallet
            if ($investmentAmount) {
                $this->recordInvestment($userId, $investmentAmount, $systemType);
            }

            DB::commit();
            Log::info("Binary system {$systemType} initialized for user {$userId}");

            return $system;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to initialize binary system: " . $e->getMessage());
            throw $e;
        }
    }

    public function processEarnings($userId, $amount, $source = 'commission')
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            // Process 2x system earnings
            if ($user->binary2x && $user->binary2x->is_active) {
                $this->addEarnings($user->binary2x, $amount, $source);
            }

            // Process 7x system earnings
            if ($user->binary7x && $user->binary7x->is_active) {
                $this->addEarnings($user->binary7x, $amount, $source);
            }

            // Update user's total binary earnings
            $totalBinaryEarnings = $user->binarySystems()
                ->where('is_active', true)
                ->sum('total_earned');

            $user->update(['total_binary_earnings' => $totalBinaryEarnings]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to process binary earnings for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    private function addEarnings(BinarySystem $system, $amount, $source)
    {
        $newTotal = $system->total_earned + $amount;
        $system->update(['total_earned' => $newTotal]);

        // Record the earning in wallet
        $this->recordBinaryEarning(
            $system->user_id,
            $amount,
            $system->system_type,
            $system->current_level,
            $source
        );

        // Check if level completion and auto progression
        if ($system->canProgress() && $system->auto_next_level) {
            $this->progressToNextLevel($system);
        }

        Log::info("Added {$amount} earnings to {$system->system_type} system for user {$system->user_id}");
    }

    public function progressToNextLevel(BinarySystem $system)
    {
        try {
            DB::beginTransaction();

            if (!$system->canProgress()) {
                throw new \Exception('System not ready for progression');
            }

            $nextLevel = $system->getNextLevel();
            if (!$nextLevel) {
                // System completed all levels
                $system->update(['completed_at' => now()]);
                Log::info("Binary system {$system->system_type} completed for user {$system->user_id}");

                DB::commit();
                return true;
            }

            // Record completion bonus
            $completionBonus = $system->current_limit * 0.1; // 10% completion bonus
            $this->recordBinaryEarning(
                $system->user_id,
                $completionBonus,
                $system->system_type,
                $system->current_level,
                'level_completion_bonus'
            );

            // Progress to next level
            $system->progressToNextLevel();

            // Automatically purchase next level if user has enough balance
            if ($system->auto_next_level) {
                $this->autoInvestNextLevel($system);
            }

            DB::commit();
            Log::info("Binary system {$system->system_type} progressed to level {$system->current_level} for user {$system->user_id}");

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to progress binary system: " . $e->getMessage());
            throw $e;
        }
    }

    private function autoInvestNextLevel(BinarySystem $system)
    {
        $user = User::find($system->user_id);
        $availableBalance = $user->wallets()
            ->where('wallet_type', 'commission')
            ->sum('balance');

        if ($availableBalance >= $system->investment_amount) {
            // Deduct investment from commission wallet
            $commissionWallet = $user->wallets()
                ->where('wallet_type', 'commission')
                ->where('balance', '>', 0)
                ->first();

            if ($commissionWallet && $commissionWallet->balance >= $system->investment_amount) {
                $commissionWallet->decrement('balance', $system->investment_amount);

                // Record the investment
                $this->recordInvestment(
                    $system->user_id,
                    $system->investment_amount,
                    $system->system_type,
                    'auto_reinvestment'
                );

                Log::info("Auto-invested {$system->investment_amount} for {$system->system_type} level {$system->current_level} for user {$system->user_id}");
            }
        }
    }

    private function recordBinaryEarning($userId, $amount, $systemType, $level, $source)
    {
        Wallet::create([
            'user_id' => $userId,
            'wallet_type' => 'binary_earning',
            'commission_type' => $systemType,
            'level' => $level,
            'balance' => $amount,
            'total_amount' => $amount,
            'source' => $source,
            'description' => "Binary {$systemType} earning - Level {$level}"
        ]);
    }

    private function recordInvestment($userId, $amount, $systemType, $source = 'binary_investment')
    {
        Wallet::create([
            'user_id' => $userId,
            'wallet_type' => 'investment',
            'commission_type' => $systemType,
            'balance' => -$amount, // Negative for investment
            'total_amount' => $amount,
            'source' => $source,
            'description' => "Investment in {$systemType} binary system"
        ]);
    }

    public function getBinarySystemStatus($userId)
    {
        $user = User::with(['binary2x', 'binary7x', 'currentRank'])->find($userId);
        if (!$user) {
            return null;
        }

        return [
            'user_rank' => [
                'current_level' => $user->current_rank_level,
                'rank_name' => $user->currentRank->rank_name ?? 'No Rank',
                'eligible_2x' => $user->eligible_for_binary_2x,
                'eligible_7x' => $user->eligible_for_binary_7x,
            ],
            'binary_2x' => $user->binary2x ? [
                'system_id' => $user->binary2x->id,
                'active' => $user->binary2x->is_active,
                'current_level' => $user->binary2x->current_level,
                'total_earned' => $user->binary2x->total_earned,
                'current_limit' => $user->binary2x->current_limit,
                'progress_percentage' => ($user->binary2x->total_earned / $user->binary2x->current_limit) * 100,
                'can_progress' => $user->binary2x->canProgress(),
                'next_level' => $user->binary2x->getNextLevel()
            ] : null,
            'binary_7x' => $user->binary7x ? [
                'system_id' => $user->binary7x->id,
                'active' => $user->binary7x->is_active,
                'current_level' => $user->binary7x->current_level,
                'total_earned' => $user->binary7x->total_earned,
                'current_limit' => $user->binary7x->current_limit,
                'progress_percentage' => ($user->binary7x->total_earned / $user->binary7x->current_limit) * 100,
                'can_progress' => $user->binary7x->canProgress(),
                'next_level' => $user->binary7x->getNextLevel()
            ] : null,
            'total_binary_earnings' => $user->total_binary_earnings
        ];
    }

    public function processCompletedProductPurchase($userId, $amount)
    {
        try {
            // This method will be called when user completes a product purchase
            // and automatically set up next level binary systems if eligible

            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            // Update rank based on new purchase
            UserRank::updateUserRank($userId);

            // Refresh user model to get updated eligibility
            $user->refresh();

            // Auto-initialize binary systems if user becomes eligible
            if ($user->eligible_for_binary_2x && !$user->binary2x) {
                $this->initializeBinarySystem($userId, '2x', $amount);
            }

            if ($user->eligible_for_binary_7x && !$user->binary7x) {
                $this->initializeBinarySystem($userId, '7x', $amount);
            }

            // Process earnings for existing systems
            $this->processEarnings($userId, $amount * 0.1, 'product_purchase'); // 10% of purchase goes to binary

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to process completed product purchase for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    public function fixOnlineIncomeConnection($userId)
    {
        try {
            // Disconnect online transfer income from 2x/7x systems
            // This addresses the client requirement about online income issues

            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            // Remove any online transfer earnings from binary systems
            $onlineEarnings = Wallet::where('user_id', $userId)
                ->where('wallet_type', 'binary_earning')
                ->where('source', 'online_transfer')
                ->get();

            foreach ($onlineEarnings as $earning) {
                // Reverse the online transfer binary earning
                $binarySystem = BinarySystem::where('user_id', $userId)
                    ->where('system_type', $earning->commission_type)
                    ->first();

                if ($binarySystem) {
                    $binarySystem->decrement('total_earned', $earning->balance);
                }

                // Mark the earning as reversed
                $earning->update([
                    'balance' => 0,
                    'description' => $earning->description . ' - REVERSED (Online income disconnected from binary)'
                ]);
            }

            Log::info("Fixed online income connection for user {$userId}");
            return true;

        } catch (\Exception $e) {
            Log::error("Failed to fix online income connection for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    public function reverseRewardBasedBinaryEarnings($userId, $rewardLevel, $reversalReason = 'Reward reversed')
    {
        try {
            DB::beginTransaction();

            $user = User::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }

            // Find binary earnings that might be related to this reward level
            // We'll look for binary earnings created around the same time as reward assignments
            $rewardBasedEarnings = Wallet::where('user_id', $userId)
                ->where('wallet_type', 'binary_earning')
                ->whereIn('source', ['commission', 'reward_based', 'level_progression'])
                ->where('created_at', '>=', now()->subDays(30)) // Look within last 30 days
                ->get();

            $totalReversed2x = 0;
            $totalReversed7x = 0;

            foreach ($rewardBasedEarnings as $earning) {
                if ($earning->balance > 0) {
                    // Get the binary system this earning belongs to
                    $binarySystem = BinarySystem::where('user_id', $userId)
                        ->where('system_type', $earning->commission_type)
                        ->first();

                    if ($binarySystem) {
                        // Reverse the earning from binary system total
                        $binarySystem->decrement('total_earned', $earning->balance);

                        if ($earning->commission_type === '2x') {
                            $totalReversed2x += $earning->balance;
                        } else if ($earning->commission_type === '7x') {
                            $totalReversed7x += $earning->balance;
                        }

                        // Mark the earning as reversed
                        $earning->update([
                            'balance' => 0,
                            'description' => $earning->description . " - REVERSED ({$reversalReason} - Level {$rewardLevel})"
                        ]);

                        Log::info("Reversed binary earning", [
                            'user_id' => $userId,
                            'system_type' => $earning->commission_type,
                            'amount' => $earning->balance,
                            'reason' => $reversalReason
                        ]);
                    }
                }
            }

            // Recalculate binary system levels after reversal
            $this->recalculateBinarySystemLevels($userId);

            // Update user's total binary earnings
            $totalBinaryEarnings = $user->binarySystems()
                ->where('is_active', true)
                ->sum('total_earned');

            $user->update(['total_binary_earnings' => $totalBinaryEarnings]);

            DB::commit();

            Log::info("Reversed reward-based binary earnings", [
                'user_id' => $userId,
                'reward_level' => $rewardLevel,
                'total_2x_reversed' => $totalReversed2x,
                'total_7x_reversed' => $totalReversed7x,
                'reason' => $reversalReason
            ]);

            return [
                'success' => true,
                'total_2x_reversed' => $totalReversed2x,
                'total_7x_reversed' => $totalReversed7x
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reverse reward-based binary earnings for user {$userId}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function recalculateBinarySystemLevels($userId)
    {
        try {
            $binarySystems = BinarySystem::where('user_id', $userId)
                ->where('is_active', true)
                ->get();

            foreach ($binarySystems as $system) {
                $levels = BinarySystem::getLevels();
                $systemLevels = $levels[$system->system_type];

                // Find the correct level based on total_earned
                $correctLevel = 1;
                foreach ($systemLevels as $level => $config) {
                    if ($system->total_earned >= $config['limit']) {
                        $correctLevel = $level + 1; // Move to next level if limit reached
                    } else {
                        break;
                    }
                }

                // Ensure we don't exceed max levels
                $maxLevel = count($systemLevels);
                $correctLevel = min($correctLevel, $maxLevel);

                // Update system if level changed
                if ($system->current_level !== $correctLevel) {
                    $newLevelConfig = $systemLevels[$correctLevel] ?? $systemLevels[$maxLevel];

                    $system->update([
                        'current_level' => $correctLevel,
                        'current_limit' => $newLevelConfig['limit'],
                        'investment_amount' => $newLevelConfig['investment']
                    ]);

                    Log::info("Recalculated binary system level", [
                        'user_id' => $userId,
                        'system_type' => $system->system_type,
                        'old_level' => $system->current_level,
                        'new_level' => $correctLevel,
                        'total_earned' => $system->total_earned
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error("Failed to recalculate binary system levels for user {$userId}: " . $e->getMessage());
        }
    }
}