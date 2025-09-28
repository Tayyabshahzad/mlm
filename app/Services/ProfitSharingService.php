<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRank;
use App\Models\Wallet;
use App\Models\BinarySystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfitSharingService
{
    public function distributeCompanyProfits($totalProfit, $distributionType = 'rank_based')
    {
        try {
            DB::beginTransaction();

            switch ($distributionType) {
                case 'rank_based':
                    $this->distributeBasedOnRanks($totalProfit);
                    break;
                case 'binary_performance':
                    $this->distributeBasedOnBinaryPerformance($totalProfit);
                    break;
                case 'investment_based':
                    $this->distributeBasedOnInvestment($totalProfit);
                    break;
                case 'team_size_based':
                    $this->distributeBasedOnTeamSize($totalProfit);
                    break;
                default:
                    throw new \Exception('Invalid distribution type');
            }

            DB::commit();
            Log::info("Profit sharing completed: {$totalProfit} distributed using {$distributionType}");

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Profit sharing failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function distributeBasedOnRanks($totalProfit)
    {
        // Get eligible users with active ranks
        $eligibleUsers = User::whereHas('currentRank', function($query) {
            $query->where('is_active', true);
        })
        ->where('blocked', false)
        ->where('can_login', 1)
        ->with('currentRank')
        ->get();

        if ($eligibleUsers->isEmpty()) {
            throw new \Exception('No eligible users found for profit sharing');
        }

        // Calculate total weight based on rank levels
        $totalWeight = $eligibleUsers->sum(function($user) {
            return $user->currentRank->rank_level * 10; // Higher ranks get more weight
        });

        // Distribute profits
        foreach ($eligibleUsers as $user) {
            $userWeight = $user->currentRank->rank_level * 10;
            $userShare = ($userWeight / $totalWeight) * $totalProfit;

            $this->creditProfitShare($user->id, $userShare, 'rank_based_profit_sharing', [
                'rank_name' => $user->currentRank->rank_name,
                'rank_level' => $user->currentRank->rank_level,
                'user_weight' => $userWeight,
                'total_weight' => $totalWeight
            ]);
        }
    }

    private function distributeBasedOnBinaryPerformance($totalProfit)
    {
        // Get users with active binary systems and their performance
        $eligibleUsers = User::whereHas('binarySystems', function($query) {
            $query->where('is_active', true)
                  ->where('total_earned', '>', 0);
        })
        ->where('blocked', false)
        ->where('can_login', 1)
        ->with('binarySystems')
        ->get();

        if ($eligibleUsers->isEmpty()) {
            throw new \Exception('No users with binary performance found');
        }

        // Calculate total performance score
        $totalPerformance = $eligibleUsers->sum(function($user) {
            return $user->binarySystems->sum('total_earned');
        });

        // Distribute based on binary performance
        foreach ($eligibleUsers as $user) {
            $userPerformance = $user->binarySystems->sum('total_earned');
            $userShare = ($userPerformance / $totalPerformance) * $totalProfit;

            $this->creditProfitShare($user->id, $userShare, 'binary_performance_profit_sharing', [
                'binary_2x_earnings' => $user->binary2x->total_earned ?? 0,
                'binary_7x_earnings' => $user->binary7x->total_earned ?? 0,
                'total_binary_earnings' => $userPerformance
            ]);
        }
    }

    private function distributeBasedOnInvestment($totalProfit)
    {
        // Get users based on their total investment
        $eligibleUsers = User::whereHas('wallets', function($query) {
            $query->where('wallet_type', 'investment')
                  ->where('total_amount', '>', 0);
        })
        ->where('blocked', false)
        ->where('can_login', 1)
        ->get();

        if ($eligibleUsers->isEmpty()) {
            throw new \Exception('No users with investments found');
        }

        // Calculate total investment
        $totalInvestment = 0;
        $userInvestments = [];

        foreach ($eligibleUsers as $user) {
            $userInvestment = $user->wallets()
                ->where('wallet_type', 'investment')
                ->sum('total_amount');

            $userInvestments[$user->id] = $userInvestment;
            $totalInvestment += $userInvestment;
        }

        // Distribute based on investment percentage
        foreach ($eligibleUsers as $user) {
            $userInvestment = $userInvestments[$user->id];
            $userShare = ($userInvestment / $totalInvestment) * $totalProfit;

            $this->creditProfitShare($user->id, $userShare, 'investment_based_profit_sharing', [
                'user_investment' => $userInvestment,
                'total_investment' => $totalInvestment,
                'investment_percentage' => ($userInvestment / $totalInvestment) * 100
            ]);
        }
    }

    private function distributeBasedOnTeamSize($totalProfit)
    {
        // Get users with team members
        $eligibleUsers = User::whereHas('descendants')
            ->where('blocked', false)
            ->where('can_login', 1)
            ->get();

        if ($eligibleUsers->isEmpty()) {
            throw new \Exception('No users with teams found');
        }

        // Calculate team sizes
        $userTeamSizes = [];
        $totalTeamSize = 0;

        foreach ($eligibleUsers as $user) {
            $teamSize = $user->descendants()
                ->where('blocked', false)
                ->count();

            $userTeamSizes[$user->id] = $teamSize;
            $totalTeamSize += $teamSize;
        }

        // Distribute based on team size
        foreach ($eligibleUsers as $user) {
            $teamSize = $userTeamSizes[$user->id];
            $userShare = ($teamSize / $totalTeamSize) * $totalProfit;

            $this->creditProfitShare($user->id, $userShare, 'team_size_profit_sharing', [
                'team_size' => $teamSize,
                'total_team_size' => $totalTeamSize,
                'team_percentage' => ($teamSize / $totalTeamSize) * 100
            ]);
        }
    }

    private function creditProfitShare($userId, $amount, $source, $metadata = [])
    {
        // Only credit if amount is significant
        if ($amount < 0.01) {
            return;
        }

        Wallet::create([
            'user_id' => $userId,
            'wallet_type' => 'profit_sharing',
            'commission_type' => 'profit_share',
            'balance' => $amount,
            'total_amount' => $amount,
            'wallet_src' => $source,
            'description' => "Company profit sharing: " . ucwords(str_replace('_', ' ', $source)),
            'metadata' => json_encode($metadata)
        ]);

        // Update user's total profit sharing earnings
        $user = User::find($userId);
        if ($user) {
            $totalProfitSharing = $user->wallets()
                ->where('wallet_type', 'profit_sharing')
                ->sum('balance');

            // You might want to add a field to track this in users table
            // $user->update(['total_profit_sharing' => $totalProfitSharing]);
        }

        Log::info("Credited ${amount} profit share to user {$userId} via {$source}");
    }

    public function getProfitSharingStats()
    {
        $stats = [
            'total_distributed' => Wallet::where('wallet_type', 'profit_sharing')
                ->sum('balance'),
            'total_recipients' => Wallet::where('wallet_type', 'profit_sharing')
                ->distinct('user_id')
                ->count('user_id'),
            'distribution_methods' => Wallet::where('wallet_type', 'profit_sharing')
                ->select('wallet_src as source', DB::raw('count(*) as count'), DB::raw('sum(balance) as total'))
                ->groupBy('wallet_src')
                ->get(),
            'recent_distributions' => Wallet::where('wallet_type', 'profit_sharing')
                ->with('user:id,name,username')
                ->latest()
                ->limit(10)
                ->get()
        ];

        return $stats;
    }

    public function getUserProfitSharingHistory($userId, $limit = 50)
    {
        return Wallet::where('user_id', $userId)
            ->where('wallet_type', 'profit_sharing')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($wallet) {
                return [
                    'amount' => $wallet->balance,
                    'source' => $wallet->wallet_src,
                    'description' => $wallet->description,
                    'date' => $wallet->created_at,
                    'metadata' => json_decode($wallet->metadata, true)
                ];
            });
    }

    public function calculateUserEligibilityScore($userId)
    {
        $user = User::with(['currentRank', 'binarySystems', 'descendants', 'wallets'])
            ->find($userId);

        if (!$user) {
            return null;
        }

        $score = [
            'rank_score' => $user->currentRank ? $user->currentRank->rank_level * 20 : 0,
            'binary_score' => $user->binarySystems->sum('total_earned') * 0.1,
            'team_score' => $user->descendants()->count() * 5,
            'investment_score' => $user->wallets()
                ->where('wallet_type', 'investment')
                ->sum('total_amount') * 0.01,
            'total_score' => 0
        ];

        $score['total_score'] = array_sum(array_slice($score, 0, -1));

        return $score;
    }
}