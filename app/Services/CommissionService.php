<?php

namespace App\Services;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class CommissionService
{
    private WalletService $walletService;

    private const COMMISSION_RATES = [
        1 => 7.00,    // Level 1: 5%
        2 => 2.00,    // Level 2: 2%
        3 => 1.50,    // Level 3: 1.5%
        4 => 1,    // Level 4: 1.25%
        5 => 0.75,    // Level 5: 1%
        6 => 0.50,    // Level 6: 0.75%
        7 => 0.25,    // Level 7: 0.5%
    ];

    private const TEAM_SIZE_REQUIREMENTS = [
        1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7
    ];

    private const MAX_COMMISSION_LEVELS = 7;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function assignCommissions(User $user, $amountToTopUp, string $sourceType = 'investment'): void
    {
        try { 
            if (!$this->hasEligibleInvestment($user)) {
                Log::info("User {$user->id} | {$user->name} has no eligible investment for commission");
                return;
            } 
            $this->procesDirectCommission($user, $amountToTopUp, $sourceType);
            $this->processIndirectCommissions($user, $amountToTopUp, $sourceType);
            
            Log::info("Successfully processed commissions for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Failed to assign commissions for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function procesDirectCommission(User $user, $amountToTopUp, string $sourceType): void
    {
        $sponsor = $this->getSponsor($user);
        
        if (!$sponsor) {
            Log::info("No sponsor found for user {$user->id}");
            return;
        }

        if (!$this->qualifiesForLevel($sponsor, 1)) {
            Log::info("Sponsor {$sponsor->id} doesn't qualify for Level 1 commission");
            return;
        }

        $commission = $this->calculateCommission(1, $amountToTopUp, $sponsor);

        $this->walletService->assignCommission(
            userId: $sponsor->id,
            amount: $commission['amount'],
            type: 'direct',
            sourceUser: $user,
            level: 1,
            percentage: $commission['percentage'],
            sourceType: $sourceType
        );

        Log::info("Assigned direct commission to user {$sponsor->id}: {$commission['amount']} ({$commission['percentage']}%)");
    }

    private function processIndirectCommissions(User $user, $amountToTopUp, string $sourceType): void
    {
        $ancestors = $this->getQualifiedAncestors($user);

        foreach ($ancestors as $ancestor) {
            $ancestorUser = $this->getAncestorUser($ancestor);
            
            if (!$ancestorUser || !$this->qualifiesForLevel($ancestorUser, $ancestor->level)) {
                continue;
            }

            $commission = $this->calculateCommission($ancestor->level, $amountToTopUp, $ancestorUser);

            $this->walletService->assignCommission(
                userId: $ancestorUser->id,
                amount: $commission['amount'],
                type: 'indirect',
                sourceUser: $user,
                level: $ancestor->level,
                percentage: $commission['percentage'],
                sourceType: $sourceType
            );

            Log::info("Assigned Level {$ancestor->level} commission to user {$ancestorUser->id}: {$commission['amount']} ({$commission['percentage']}%)");
        }
    }

    private function getSponsor(User $user): ?User
    {
        return User::where('blocked', false)
            ->find($user->sponsor_id);
    }


    private function getQualifiedAncestors(User $user): Collection
    {
        return DB::table('referral_trees')
            ->select('ancestor_id', 'level')
            ->where('descendant_id', $user->id)
            ->where('level', '>', 1) // Exclude Level 1 (direct sponsor)
            ->where('level', '<=', self::MAX_COMMISSION_LEVELS)
            ->orderBy('level')
            ->get();
    }

    private function getAncestorUser(object $ancestor): ?User
    {
        return User::where('blocked', false)
            ->find($ancestor->ancestor_id);
    }

    private function qualifiesForLevel(User $user, int $level): bool
    {
        $activeDirectUsers = $this->getActiveDirectUsersCount($user->id);
        $requiredTeamSize = self::TEAM_SIZE_REQUIREMENTS[$level] ?? 0;
        
        return $activeDirectUsers >= $requiredTeamSize;
    }

    private function getActiveDirectUsersCount(int $userId): int
    {
        return User::where('blocked', false)
            ->where('sponsor_id', $userId)
            ->count();
    }

    private function calculateCommission(int $level, float $investmentAmount, User $receivingUser = null): array
    {
        // Get dynamic commission rate from settings
        // Commission is SAME for both VIP and Standard users (direct investment commission)
        $setting = \App\Models\Setting::first();
        $userPlan = $receivingUser?->user_plan ?? 'standard';

        // Use standard commission rates for BOTH plans (client requirement)
        $fieldName = "standard_commission_l{$level}";
        $percentage = $setting->$fieldName ?? self::COMMISSION_RATES[$level] ?? 0;

        $amount = ($investmentAmount * $percentage) / 100;

        return [
            'percentage' => $percentage,
            'amount' => round($amount, 2),
            'user_plan' => $userPlan
        ];
    }

    private function hasEligibleInvestment(User $user): bool
    {
        return isset($user->roi_eligible_investment_amount) 
            && $user->roi_eligible_investment_amount > 0;
    }

    public function getCommissionRates(): array
    {
        return self::COMMISSION_RATES;
    }

    public function getTeamSizeRequirements(): array
    {
        return self::TEAM_SIZE_REQUIREMENTS;
    } 

     
}
