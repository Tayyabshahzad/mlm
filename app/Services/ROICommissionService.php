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
     * FIXED: Now processes commission for ALL users at each level if minimum is met
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
     * FIXED: Now gives commission for ALL users at the level, not just minimum required
     */
    private function processLevelCommission(User $user, float $roiAmount, int $level, float $percentage): void
    {
        // Find the ancestor at this level (the person who will receive commission)
        $ancestor = $this->getAncestorByLevel($user, $level);
        
        if (!$ancestor || !$this->isAncestorEligibleForCommission($ancestor, $level)) {
            return;
        }

        // FIXED: Calculate commission for ALL users generating ROI at this level
        $this->processCommissionForAllUsersAtLevel($ancestor, $user, $roiAmount, $percentage, $level);
    }

    /**
     * NEW METHOD: Process commission for all users at a specific level under an ancestor
     * This ensures commission is given for ALL users, not just the minimum required
     */
    private function processCommissionForAllUsersAtLevel(
        User $ancestor, 
        User $currentUser, 
        float $roiAmount, 
        float $percentage, 
        int $level
    ): void {
        // Get all users at this exact level under the ancestor
        $allUsersAtLevel = $this->getAllUsersAtLevel($ancestor->id, $level);
        
        Log::info("Processing commission for ancestor {$ancestor->id} at level {$level}: Found {$allUsersAtLevel->count()} users");

        // Process commission for each user at this level who generated ROI
        foreach ($allUsersAtLevel as $userAtLevel) {
            // Only process if this is the user who just generated ROI
            if ($userAtLevel->id === $currentUser->id) {
                $commissionAmount = $this->calculateCommissionAmount($ancestor, $roiAmount, $percentage, $level);

                if ($commissionAmount > 0) {
                    $this->createCommissionRecords($ancestor, $currentUser, $commissionAmount, $percentage, $level);
                    Log::info("Commission processed: Ancestor {$ancestor->id} got {$commissionAmount} from user {$currentUser->id} at level {$level}");
                }
                break;
            }
        }

        $this->accountService->checkAndStopAccountAt2X($ancestor);
    }

    /**
     * ALTERNATIVE METHOD: Process commission for ALL users at level in bulk
     * Call this method if you want to process commission for ALL users at once
     */
    public function processCommissionForAllEligibleUsersAtLevel(
        User $ancestor, 
        int $level, 
        float $percentage
    ): void {
        // Check if ancestor is eligible for this level
        if (!$this->isAncestorEligibleForCommission($ancestor, $level)) {
            return;
        }

        // Get all users at this level
        $allUsersAtLevel = $this->getAllUsersAtLevel($ancestor->id, $level);
        
        Log::info("Bulk processing commission for ancestor {$ancestor->id} at level {$level}: Processing {$allUsersAtLevel->count()} users");

        foreach ($allUsersAtLevel as $userAtLevel) {
            // Get recent ROI amount for this user
            $roiAmount = $this->getRecentRoiAmount($userAtLevel);

            if ($roiAmount > 0) {
                $commissionAmount = $this->calculateCommissionAmount($ancestor, $roiAmount, $percentage, $level);

                if ($commissionAmount > 0) {
                    $this->createCommissionRecords($ancestor, $userAtLevel, $commissionAmount, $percentage, $level);
                }
            }
        }

        $this->accountService->checkAndStopAccountAt2X($ancestor);
    }

    /**
     * Get ALL users at exact level (not just count)
     */
    private function getAllUsersAtLevel(int $ancestorId, int $level): \Illuminate\Support\Collection
    {
        return User::whereIn('id', function ($query) use ($ancestorId, $level) {
            $query->select('descendant_id')
                ->from('referral_trees')
                ->where('ancestor_id', $ancestorId)
                ->where('level', $level);
        })->get();
    }

    /**
     * Check if ancestor is eligible for commission at this level
     * They must have enough users at this exact level
     */
    private function isAncestorEligibleForCommission(User $ancestor, int $level): bool
    {
        // FIXED: Profit sharing should be independent of ROI status
        // Basic account checks only - NOT tied to 2x/7x limits
        if ($ancestor->blocked || !$ancestor->can_login || $ancestor->freez_wallet) {
            return false;
        }

        // Must have investment to participate in profit sharing
        if (!$ancestor->roi_eligible_investment_amount || $ancestor->roi_eligible_investment_amount <= 0) {
            return false;
        }

        // Count users at this EXACT level under this ancestor
        $usersAtLevel = $this->countUsersAtExactLevel($ancestor->id, $level);
        $requiredUsers = self::REQUIRED_USERS_BY_LEVEL[$level] ?? 0;

        Log::info("Profit share eligibility check for ancestor {$ancestor->id} at level {$level}: {$usersAtLevel}/{$requiredUsers} users (Independent of ROI/2x status)");

        return $usersAtLevel >= $requiredUsers;
    }

    /**
     * Calculate commission amount for profit sharing
     * FIXED: Profit sharing should NOT be limited by 2X cap
     * Updated to use database settings for VIP/Standard differentiation
     */
    private function calculateCommissionAmount(User $ancestor, float $roiAmount, float $percentage, int $level): float
    {
        // Get dynamic profit share percentage from settings based on user plan
        $setting = \App\Models\Setting::first();
        $userPlan = $ancestor->user_plan ?? 'standard';

        // Get profit share percentage based on plan and level
        if ($userPlan === 'vip') {
            $fieldName = "vip_profit_l{$level}";
            $actualPercentage = $setting->$fieldName ?? ($percentage / 2); // VIP gets half
        } else {
            $fieldName = "standard_profit_l{$level}";
            $actualPercentage = $setting->$fieldName ?? $percentage; // Standard gets full
        }

        $baseCommission = ($roiAmount * $actualPercentage) / 100;

        // FIXED: Profit sharing is independent of 2X limits
        // Users should get full profit share regardless of their 2X status
        Log::info("Calculating profit share commission for ancestor {$ancestor->id} ({$userPlan}): {$baseCommission} at {$actualPercentage}% (Level {$level}, Full amount, not limited by 2X)");

        return $baseCommission;
    }

    /**
     * Create commission transaction and wallet records
     */
    private function createCommissionRecords(
        User $ancestor, 
        User $user, 
        float $amount, 
        float $percentage, 
        int $level
    ): void {
        ROITransaction::create([
            'user_id' => $ancestor->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'description' => "Level {$level} commission from user {$user->id} | {$user->name}",
        ]);

        Wallet::create([
            'user_id' => $ancestor->id,
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
     * Count users at exact level only (not cumulative)
     */
    private function countUsersAtExactLevel(int $ancestorId, int $level): int
    {
        return DB::table('referral_trees')
            ->where('ancestor_id', $ancestorId)
            ->where('level', $level)
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
     * ENHANCED METHOD: Process commissions for ALL users at ALL levels
     * This method processes commission for every eligible user at every level
     */
    public function processAllEligibleCommissionsEnhanced(): void
    {
        Log::info("Starting enhanced bulk commission processing for ALL eligible users");

        // Get all users who are eligible to receive commissions
        $eligibleAncestors = User::where('blocked', false)
            ->where('can_login', true)
            ->where('freez_wallet', false)
            ->where(function ($query) {
                $query->whereNull('roi_status')
                      ->orWhere('roi_status', 'active');
            })
            ->get();

        foreach ($eligibleAncestors as $ancestor) {
            foreach (self::COMMISSION_LEVELS as $level => $percentage) {
                $this->processCommissionForAllEligibleUsersAtLevel($ancestor, $level, $percentage);
            }
        }

        Log::info("Completed enhanced bulk commission processing");
    }

    /**
     * Get recent ROI amount for a user
     */
    private function getRecentRoiAmount(User $user): float
    {
        $recentRoi = ROITransaction::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->where('description', 'Weekly ROI Generated') // Only count actual ROI, not commissions
            ->sum('amount');

        return $recentRoi;
    }

    /**
     * Get commission statistics for a user - ENHANCED
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
            'downline_structure' => $this->getDownlineStructure($user),
        ];
    }

    /**
     * Get levels that user is eligible to earn commissions from - ENHANCED
     */
    private function getEligibleLevelsForUser(User $user): array
    {
        $eligibleLevels = [];
        
        foreach (self::COMMISSION_LEVELS as $level => $percentage) {
            $usersAtLevel = $this->countUsersAtExactLevel($user->id, $level);
            $required = self::REQUIRED_USERS_BY_LEVEL[$level] ?? 0;
            
            $eligibleLevels[$level] = [
                'percentage' => $percentage,
                'required_users' => $required,
                'current_users_at_level' => $usersAtLevel,
                'is_eligible' => $usersAtLevel >= $required,
                'commission_sources' => $usersAtLevel, // ALL users at this level will generate commission
                'excess_users' => max(0, $usersAtLevel - $required), // Additional users above requirement
                'potential_daily_commission' => $usersAtLevel >= $required ? $usersAtLevel : 0, // How many users can generate commission
            ];
        }
        
        return $eligibleLevels;
    }

    /**
     * Get detailed downline structure - ENHANCED
     */
    public function getDownlineStructure(User $user): array
    {
        $structure = [];
        
        for ($level = 1; $level <= 7; $level++) {
            $usersAtLevel = $this->countUsersAtExactLevel($user->id, $level);
            $required = self::REQUIRED_USERS_BY_LEVEL[$level] ?? 0;
            
            $structure[$level] = [
                'level' => $level,
                'users_count' => $usersAtLevel,
                'required_for_commission' => $required,
                'commission_percentage' => self::COMMISSION_LEVELS[$level] ?? 0,
                'is_eligible' => $usersAtLevel >= $required,
                'total_commission_sources' => $usersAtLevel >= $required ? $usersAtLevel : 0, // ALL users generate commission if eligible
                'users_above_requirement' => max(0, $usersAtLevel - $required),
                'commission_multiplier' => $usersAtLevel >= $required ? $usersAtLevel : 0, // How many times commission can be earned
            ];
        }
        
        return $structure;
    }

    /**
     * Debug method to check specific user's eligibility - ENHANCED
     */
    public function debugUserEligibility(User $user): array
    {
        $debug = [
            'user_id' => $user->id,
            'username' => $user->username ?? $user->name,
            'levels' => []
        ];

        foreach (self::COMMISSION_LEVELS as $level => $percentage) {
            $usersAtLevel = $this->countUsersAtExactLevel($user->id, $level);
            $required = self::REQUIRED_USERS_BY_LEVEL[$level] ?? 0;
            
            $debug['levels'][$level] = [
                'users_at_exact_level' => $usersAtLevel,
                'required_users' => $required,
                'is_eligible' => $usersAtLevel >= $required,
                'commission_percentage' => $percentage,
                'total_commission_sources' => $usersAtLevel >= $required ? $usersAtLevel : 0,
                'commission_earning_potential' => $usersAtLevel >= $required ? "Can earn from ALL {$usersAtLevel} users" : "Not eligible - need " . ($required - $usersAtLevel) . " more users",
            ];
        }

        return $debug;
    }
}