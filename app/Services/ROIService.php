<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\ROITransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ROIService
{
    private const PROFIT_SHARE_RATES = [
        1 => 7.0,
        2 => 6.0,
        3 => 5.0,
        4 => 4.0,
        5 => 3.0,
        6 => 2.0,
        7 => 1.0,
    ];

    private const REQUIRED_USERS_FOR_LEVEL = [
        1 => 10,
        2 => 50,
        3 => 150,
        4 => 400,
        5 => 1000,
        6 => 2000,
        7 => 4000,
    ];

    private const MAX_PROFIT_SHARE_LEVELS = 7;

    public function generateProfitShareCommissions(User $user, float $roiAmount): void
    {
        try {
            foreach (self::PROFIT_SHARE_RATES as $level => $percentage) {
                $parent = $this->getAncestorByLevel($user, $level);
                
                if (!$parent) {
                    continue;
                }

                $totalDownlineCount = $this->countDownlineUsers($parent->id, $level);
                $requiredUsers = self::REQUIRED_USERS_FOR_LEVEL[$level] ?? 0;

                if ($totalDownlineCount >= $requiredUsers) {
                    $commissionAmount = ($roiAmount * $percentage) / 100;
                    
                    $this->createROITransaction($parent, $user, $commissionAmount, $percentage, $level);
                    $this->createProfitShareWallet($parent, $user, $commissionAmount, $percentage, $level);
                    
                    Log::info("Profit share commission assigned", [
                        'parent_id' => $parent->id,
                        'source_user_id' => $user->id,
                        'level' => $level,
                        'amount' => $commissionAmount,
                        'percentage' => $percentage
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to generate profit share commissions for user {$user->id}: " . $e->getMessage());
            throw $e;
        }
    }

    private function createROITransaction(User $parent, User $sourceUser, float $amount, float $percentage, int $level): void
    {
        ROITransaction::create([
            'user_id' => $parent->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'description' => "Level {$level} profit share commission from user {$sourceUser->id} | {$sourceUser->name}",
        ]);
    }

    private function createProfitShareWallet(User $parent, User $sourceUser, float $amount, float $percentage, int $level): void
    {
        Wallet::create([
            'user_id' => $parent->id,
            'wallet_type' => 'profit_share',
            'balance' => $amount,
            'level' => $level,
            'commission_type' => 'profit_share',
            'wallet_from' => $sourceUser->id,
            'percentage' => $percentage,
            'total_amount' => $amount,
        ]);
    }

    private function getAncestorByLevel(User $user, int $level): ?User
    {
        return User::whereIn('id', function ($query) use ($user, $level) {
            $query->select('ancestor_id')
                ->from('referral_trees')
                ->where('descendant_id', $user->id)
                ->where('level', $level);
        })->first();
    }

    private function countDownlineUsers(int $parentId, int $level): int
    {
        return DB::table('referral_trees')
            ->where('ancestor_id', $parentId)
            ->where('level', '<=', $level)
            ->count();
    }

    public function getUserProfitShareSummary(int $userId): array
    {
        $profitShares = Wallet::where('user_id', $userId)
            ->where('wallet_type', 'profit_share')
            ->where('commission_type', 'profit_share')
            ->selectRaw('
                SUM(balance) as total_profit_share,
                COUNT(*) as total_transactions,
                AVG(percentage) as avg_percentage
            ')
            ->first();

        $levelBreakdown = Wallet::where('user_id', $userId)
            ->where('wallet_type', 'profit_share')
            ->where('commission_type', 'profit_share')
            ->selectRaw('level, SUM(balance) as level_total, COUNT(*) as level_count')
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        return [
            'total_profit_share' => $profitShares->total_profit_share ?? 0,
            'total_transactions' => $profitShares->total_transactions ?? 0,
            'average_percentage' => $profitShares->avg_percentage ?? 0,
            'level_breakdown' => $levelBreakdown->mapWithKeys(function ($item) {
                return [$item->level => [
                    'total' => $item->level_total,
                    'count' => $item->level_count
                ]];
            })->toArray()
        ];
    }

    public function getProfitShareRates(): array
    {
        return self::PROFIT_SHARE_RATES;
    }

    public function getRequiredUsersForLevel(): array
    {
        return self::REQUIRED_USERS_FOR_LEVEL;
    }
}