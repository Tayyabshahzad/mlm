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
        // Prevent the same distribution type from running twice on the same calendar day.
        // Without this guard, running the command twice would credit every eligible user twice.
        $todaySource = $distributionType . '_profit_sharing';
        $alreadyRanToday = Wallet::where('wallet_type', 'profit_sharing')
            ->where('wallet_src', $todaySource)
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadyRanToday) {
            throw new \Exception(
                "Profit distribution '{$distributionType}' was already run today (" .
                now()->toDateString() . '). To re-run, reverse the existing entries first.'
            );
        }

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

        $setting = \App\Models\Setting::first();

        // Bake the plan multiplier INTO the weight so that
        // sum(all user shares) == $totalProfit exactly.
        // VIP users get a larger slice of the pool rather than a post-hoc bonus
        // that inflates the total payout beyond the budgeted amount.
        $totalWeight = $eligibleUsers->sum(function($user) use ($setting) {
            $multiplier = $user->user_plan === 'vip'
                ? (float)($setting->vip_profit_multiplier ?? 1.5)
                : (float)($setting->standard_profit_multiplier ?? 1.0);
            return $user->currentRank->rank_level * 10 * $multiplier;
        });

        foreach ($eligibleUsers as $user) {
            $multiplier = $user->user_plan === 'vip'
                ? (float)($setting->vip_profit_multiplier ?? 1.5)
                : (float)($setting->standard_profit_multiplier ?? 1.0);
            $userWeight = $user->currentRank->rank_level * 10 * $multiplier;
            $userShare  = ($userWeight / $totalWeight) * $totalProfit;

            $this->creditProfitShare($user->id, $userShare, 'rank_based_profit_sharing', [
                'rank_name'    => $user->currentRank->rank_name,
                'rank_level'   => $user->currentRank->rank_level,
                'user_weight'  => $userWeight,
                'total_weight' => $totalWeight,
                'user_plan'    => $user->user_plan ?? 'standard',
                'multiplier'   => $multiplier,
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

        $setting = \App\Models\Setting::first();

        // Multiplier baked into score so total payout == $totalProfit exactly.
        $totalScore = $eligibleUsers->sum(function($user) use ($setting) {
            $multiplier = $user->user_plan === 'vip'
                ? (float)($setting->vip_profit_multiplier ?? 1.5)
                : (float)($setting->standard_profit_multiplier ?? 1.0);
            return $user->binarySystems->sum('total_earned') * $multiplier;
        });

        foreach ($eligibleUsers as $user) {
            $multiplier    = $user->user_plan === 'vip'
                ? (float)($setting->vip_profit_multiplier ?? 1.5)
                : (float)($setting->standard_profit_multiplier ?? 1.0);
            $userScore = $user->binarySystems->sum('total_earned') * $multiplier;
            $userShare = ($userScore / $totalScore) * $totalProfit;

            $this->creditProfitShare($user->id, $userShare, 'binary_performance_profit_sharing', [
                'binary_2x_earnings'   => $user->binary2x->total_earned ?? 0,
                'binary_7x_earnings'   => $user->binary7x->total_earned ?? 0,
                'total_binary_earnings'=> $user->binarySystems->sum('total_earned'),
                'multiplier'           => $multiplier,
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

        $setting = \App\Models\Setting::first();

        $userScores    = [];
        $totalScore    = 0;

        foreach ($eligibleUsers as $user) {
            $investment = $user->wallets()
                ->where('wallet_type', 'investment')
                ->sum('total_amount');
            $multiplier = $user->user_plan === 'vip'
                ? (float)($setting->vip_profit_multiplier ?? 1.5)
                : (float)($setting->standard_profit_multiplier ?? 1.0);
            $score = $investment * $multiplier;
            $userScores[$user->id] = ['investment' => $investment, 'score' => $score, 'multiplier' => $multiplier];
            $totalScore += $score;
        }

        foreach ($eligibleUsers as $user) {
            $data      = $userScores[$user->id];
            $userShare = ($data['score'] / $totalScore) * $totalProfit;

            $this->creditProfitShare($user->id, $userShare, 'investment_based_profit_sharing', [
                'user_investment'      => $data['investment'],
                'multiplier'           => $data['multiplier'],
                'investment_percentage'=> ($data['score'] / $totalScore) * 100,
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

        $setting     = \App\Models\Setting::first();
        $userScores  = [];
        $totalScore  = 0;

        foreach ($eligibleUsers as $user) {
            $teamSize   = $user->descendants()->where('blocked', false)->count();
            $multiplier = $user->user_plan === 'vip'
                ? (float)($setting->vip_profit_multiplier ?? 1.5)
                : (float)($setting->standard_profit_multiplier ?? 1.0);
            $score = $teamSize * $multiplier;
            $userScores[$user->id] = ['team_size' => $teamSize, 'score' => $score, 'multiplier' => $multiplier];
            $totalScore += $score;
        }

        foreach ($eligibleUsers as $user) {
            $data      = $userScores[$user->id];
            $userShare = ($data['score'] / $totalScore) * $totalProfit;

            $this->creditProfitShare($user->id, $userShare, 'team_size_profit_sharing', [
                'team_size'      => $data['team_size'],
                'multiplier'     => $data['multiplier'],
                'team_percentage'=> ($data['score'] / $totalScore) * 100,
            ]);
        }
    }

    private function creditProfitShare($userId, $amount, $source, $metadata = [])
    {
        // Only credit if amount is significant
        if ($amount < 0.01) {
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            return;
        }

        // NOTE: The plan multiplier is already baked into $amount by the caller
        // (each distribution method folds the multiplier into the weight calculation
        // so that total payout == total pool). Do NOT apply it again here.
        $userPlan = $user->user_plan ?? 'standard';

        Wallet::create([
            'user_id'         => $userId,
            'wallet_type'     => 'profit_sharing',
            'commission_type' => 'profit_share',
            'balance'         => $amount,
            'total_amount'    => $amount,
            'wallet_src'      => $source,
            'description'     => 'Company profit sharing (' . strtoupper($userPlan) . '): ' . ucwords(str_replace('_', ' ', $source)),
            'metadata'        => json_encode($metadata),
        ]);

        Log::info("Credited {$amount} profit share to user {$userId} ({$userPlan}) via {$source}");
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