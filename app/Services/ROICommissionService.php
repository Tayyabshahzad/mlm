<?php

namespace App\Services;

use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ROICommissionService
{
    private AccountManagementService $accountService;

    private const COMMISSION_LEVELS = [
        1 => 7.0,
        2 => 6.0,
        3 => 5.0,
        4 => 4.0,
        5 => 3.0,
        6 => 2.0,
        7 => 1.0,
    ];

    private const REQUIRED_USERS_BY_LEVEL = [
        1 => 10,
        2 => 50,
        3 => 150,
        4 => 400,
        5 => 1000,
        6 => 2000,
        7 => 4000,
    ];

    public function __construct(AccountManagementService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Generate commissions for all eligible levels
     */
    public function generateCommissions(User $user, float $roiAmount): void
    {
        foreach (self::COMMISSION_LEVELS as $level => $percentage) {
            try {
                $this->processLevelCommission($user, $roiAmount, $level, $percentage);
            } catch (\Exception $e) {
                Log::error("Failed to process commission for level {$level}: " . $e->getMessage());
            }
        }
    }

    /**
     * Process commission for a specific level
     */
    private function processLevelCommission(User $user, float $roiAmount, int $level, float $percentage): void
    {
        $parent = $this->getAncestorByLevel($user, $level);
        
        if (!$parent || !$this->isParentEligibleForCommission($parent, $level)) {
            return;
        }

        $commissionAmount = $this->calculateCommissionAmount($parent, $roiAmount, $percentage);
        
        if ($commissionAmount <= 0) {
            return;
        }

        $this->createCommissionRecords($parent, $user, $commissionAmount, $percentage, $level);
        $this->accountService->checkAndStopAccountAt2X($parent);
    }

    /**
     * Check if parent is eligible for commission
     */
    private function isParentEligibleForCommission(User $parent, int $level): bool
    {
        if (!$this->accountService->canReceiveRoi($parent)) {
            return false;
        }

        $totalDownlineCount = $this->countDownlineUsers($parent->id, $level);
        $requiredUsers = self::REQUIRED_USERS_BY_LEVEL[$level] ?? 0;

        return $totalDownlineCount >= $requiredUsers;
    }

    /**
     * Calculate safe commission amount that doesn't exceed 2X limit
     */
    private function calculateCommissionAmount(User $parent, float $roiAmount, float $percentage): float
    {
        $baseCommission = ($roiAmount * $percentage) / 100;
        return $this->accountService->calculateSafeRoiAmount($parent, $baseCommission);
    }

    /**
     * Create commission transaction and wallet records
     */
    private function createCommissionRecords(
        User $parent, 
        User $user, 
        float $amount, 
        float $percentage, 
        int $level
    ): void {
        ROITransaction::create([
            'user_id' => $parent->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'description' => "Level {$level} commission from user {$user->id} | {$user->name}",
        ]);

        Wallet::create([
            'user_id' => $parent->id,
            'wallet_type' => 'profit_share',
            'balance' => $amount,
            'level' => $level,
            'commission_type' => 'profit_share',
            'wallet_from' => $user->id,
            'percentage' => $percentage,
            'total_amount' => $amount,
        ]);
    }

    /**
     * Count downline users up to specified level
     */
    private function countDownlineUsers(int $parentId, int $level): int
    {
        return DB::table('referral_trees')
            ->where('ancestor_id', $parentId)
            ->where('level', '<=', $level)
            ->count();
    }

    /**
     * Get ancestor user by level
     */
    private function getAncestorByLevel(User $user, int $level): ?User
    {
        return User::whereIn('id', function ($query) use ($user, $level) {
            $query->select('ancestor_id')
                ->from('referral_trees')
                ->where('descendant_id', $user->id)
                ->where('level', $level);
        })->first();
    }

    /**
     * Get commission statistics for a user
     */
    public function getCommissionStats(User $user): array
    {
        $totalCommissions = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'profit_share')
            ->sum('total_amount');

        $commissionsByLevel = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'profit_share')
            ->selectRaw('level, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('level')
            ->get()
            ->keyBy('level');

        return [
            'total_commissions' => $totalCommissions,
            'commissions_by_level' => $commissionsByLevel,
            'eligible_levels' => $this->getEligibleLevelsForUser($user),
        ];
    }

    /**
     * Get levels that user is eligible to earn commissions from
     */
    private function getEligibleLevelsForUser(User $user): array
    {
        $eligibleLevels = [];
        
        foreach (self::COMMISSION_LEVELS as $level => $percentage) {
            $downlineCount = $this->countDownlineUsers($user->id, $level);
            $required = self::REQUIRED_USERS_BY_LEVEL[$level] ?? 0;
            
            $eligibleLevels[$level] = [
                'percentage' => $percentage,
                'required_users' => $required,
                'current_users' => $downlineCount,
                'is_eligible' => $downlineCount >= $required,
            ];
        }
        
        return $eligibleLevels;
    }
}