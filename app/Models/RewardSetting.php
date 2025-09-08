<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class RewardSetting extends Model
{
    protected $fillable = [
        'level',
        'reward_amount',
        'users_required',
        'description',
        'is_active',
        'additional_settings'
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'additional_settings' => 'array'
    ];

    /**
     * Get all active reward levels as array (compatible with current system)
     */
    public static function getRewardLevelsArray(): array
    {
        $levels = [];
        $settings = self::where('is_active', true)->orderBy('level')->get();
        
        foreach ($settings as $setting) {
            $levels[$setting->level] = [
                'reward_amount' => (float) $setting->reward_amount,
                'users_required' => $setting->users_required,
                'description' => $setting->description,
            ];
        }

        return $levels;
    }

    /**
     * Get specific level settings
     */
    public static function getLevel(int $level): ?self
    {
        return self::where('level', $level)->where('is_active', true)->first();
    }

    /**
     * Get all levels with statistics
     */
    public static function getLevelsWithStats(): Collection
    {
        return self::orderBy('level')->get()->map(function ($setting) {
            // Add statistics for each level
            $setting->total_users_eligible = \DB::table('referral_trees')
                ->join('users', 'referral_trees.descendant_id', '=', 'users.id')
                ->where('referral_trees.level', $setting->level)
                ->where('users.blocked', false)
                ->where('users.can_login', 1)
                ->distinct('referral_trees.ancestor_id')
                ->count('referral_trees.ancestor_id');

            $setting->rewards_paid_count = \DB::table('wallets')
                ->where('wallet_type', 'reward')
                ->where('level', $setting->level)
                ->where('balance', '>', 0)
                ->count();

            $setting->total_amount_paid = \DB::table('wallets')
                ->where('wallet_type', 'reward')
                ->where('level', $setting->level)
                ->sum('balance');

            $setting->pending_count = \DB::table('pending_rewards')
                ->where('level', $setting->level)
                ->where('status', 'pending')
                ->count();

            return $setting;
        });
    }

    /**
     * Seed default reward levels
     */
    public static function seedDefaults(): void
    {
        $defaultLevels = [
            1 => ['reward_amount' => 130, 'users_required' => 10, 'description' => 'Bronze Level - First milestone reward'],
            2 => ['reward_amount' => 350, 'users_required' => 20, 'description' => 'Silver Level - Team building bonus'],
            3 => ['reward_amount' => 1050, 'users_required' => 30, 'description' => 'Gold Level - Leadership achievement'],
            4 => ['reward_amount' => 3450, 'users_required' => 40, 'description' => 'Platinum Level - Advanced leader'],
            5 => ['reward_amount' => 8650, 'users_required' => 50, 'description' => 'Diamond Level - Elite performer'],
            6 => ['reward_amount' => 26000, 'users_required' => 60, 'description' => 'Master Level - Exceptional leader'],
            7 => ['reward_amount' => 41500, 'users_required' => 70, 'description' => 'Champion Level - Ultimate achievement'],
        ];

        foreach ($defaultLevels as $level => $data) {
            self::updateOrCreate(
                ['level' => $level],
                [
                    'reward_amount' => $data['reward_amount'],
                    'users_required' => $data['users_required'],
                    'description' => $data['description'],
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Validation rules
     */
    public static function validationRules(): array
    {
        return [
            'level' => 'required|integer|min:1|max:10',
            'reward_amount' => 'required|numeric|min:0',
            'users_required' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
