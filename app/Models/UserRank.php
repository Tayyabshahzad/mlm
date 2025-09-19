<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRank extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rank_name',
        'rank_level',
        'eligible_for_2x',
        'eligible_for_7x',
        'required_investment',
        'required_direct_referrals',
        'required_team_size',
        'rank_benefits',
        'achieved_at',
        'is_active'
    ];

    protected $casts = [
        'required_investment' => 'decimal:2',
        'rank_benefits' => 'array',
        'achieved_at' => 'datetime',
        'eligible_for_2x' => 'boolean',
        'eligible_for_7x' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEligibleFor2x($query)
    {
        return $query->where('eligible_for_2x', true);
    }

    public function scopeEligibleFor7x($query)
    {
        return $query->where('eligible_for_7x', true);
    }

    public static function getRankLevels()
    {
        return [
            1 => [
                'name' => 'Bronze',
                'investment' => 100,
                'direct_referrals' => 2,
                'team_size' => 5,
                'eligible_2x' => false,
                'eligible_7x' => false,
                'benefits' => ['basic_commission' => 5]
            ],
            2 => [
                'name' => 'Silver',
                'investment' => 500,
                'direct_referrals' => 5,
                'team_size' => 15,
                'eligible_2x' => true,
                'eligible_7x' => false,
                'benefits' => ['basic_commission' => 7, '2x_access' => true]
            ],
            3 => [
                'name' => 'Gold',
                'investment' => 1000,
                'direct_referrals' => 10,
                'team_size' => 30,
                'eligible_2x' => true,
                'eligible_7x' => true,
                'benefits' => ['basic_commission' => 10, '2x_access' => true, '7x_access' => true]
            ],
            4 => [
                'name' => 'Platinum',
                'investment' => 2500,
                'direct_referrals' => 20,
                'team_size' => 100,
                'eligible_2x' => true,
                'eligible_7x' => true,
                'benefits' => ['basic_commission' => 12, '2x_access' => true, '7x_access' => true, 'bonus_multiplier' => 1.2]
            ],
            5 => [
                'name' => 'Diamond',
                'investment' => 5000,
                'direct_referrals' => 50,
                'team_size' => 300,
                'eligible_2x' => true,
                'eligible_7x' => true,
                'benefits' => ['basic_commission' => 15, '2x_access' => true, '7x_access' => true, 'bonus_multiplier' => 1.5]
            ]
        ];
    }

    public static function calculateUserRank($userId)
    {
        $user = User::with(['team', 'wallets'])->find($userId);
        if (!$user) {
            return null;
        }

        $totalInvestment = $user->wallets()
            ->where('wallet_type', 'investment')
            ->sum('total_amount');

        $directReferrals = $user->team()->where('can_login', 1)->count();
        $teamSize = $user->descendants()->where('blocked', false)->count();

        $ranks = self::getRankLevels();
        $achievedRank = null;

        foreach ($ranks as $level => $rank) {
            if (
                $totalInvestment >= $rank['investment'] &&
                $directReferrals >= $rank['direct_referrals'] &&
                $teamSize >= $rank['team_size']
            ) {
                $achievedRank = $level;
            } else {
                break;
            }
        }

        return $achievedRank;
    }

    public static function updateUserRank($userId)
    {
        $newRankLevel = self::calculateUserRank($userId);
        if (!$newRankLevel) {
            return false;
        }

        $ranks = self::getRankLevels();
        $rankData = $ranks[$newRankLevel];

        $existingRank = self::where('user_id', $userId)->where('is_active', true)->first();

        if ($existingRank && $existingRank->rank_level >= $newRankLevel) {
            return false; // No upgrade needed
        }

        // Deactivate old rank
        if ($existingRank) {
            $existingRank->update(['is_active' => false]);
        }

        // Create new rank
        $userRank = self::create([
            'user_id' => $userId,
            'rank_name' => $rankData['name'],
            'rank_level' => $newRankLevel,
            'eligible_for_2x' => $rankData['eligible_2x'],
            'eligible_for_7x' => $rankData['eligible_7x'],
            'required_investment' => $rankData['investment'],
            'required_direct_referrals' => $rankData['direct_referrals'],
            'required_team_size' => $rankData['team_size'],
            'rank_benefits' => $rankData['benefits'],
            'achieved_at' => now(),
            'is_active' => true
        ]);

        // Update user binary eligibility
        User::where('id', $userId)->update([
            'current_rank_level' => $newRankLevel,
            'eligible_for_binary_2x' => $rankData['eligible_2x'],
            'eligible_for_binary_7x' => $rankData['eligible_7x']
        ]);

        return $userRank;
    }
}