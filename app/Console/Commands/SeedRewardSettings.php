<?php

namespace App\Console\Commands;

use App\Models\RewardSetting;
use Illuminate\Console\Command;

class SeedRewardSettings extends Command
{
    protected $signature = 'rewards:seed-settings';
    protected $description = 'Seed default reward settings into database';

    public function handle()
    {
        $this->info('Seeding default reward settings...');
        
        try {
            RewardSetting::seedDefaults();
            $this->info('✅ Default reward settings seeded successfully!');
            
            // Show what was created
            $settings = RewardSetting::orderBy('level')->get();
            $this->table(
                ['Level', 'Users Required', 'Reward Amount', 'Description', 'Status'],
                $settings->map(function ($setting) {
                    return [
                        $setting->level,
                        number_format($setting->users_required),
                        '$' . number_format($setting->reward_amount, 2),
                        $setting->description,
                        $setting->is_active ? 'Active' : 'Inactive'
                    ];
                })->toArray()
            );
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to seed reward settings: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
