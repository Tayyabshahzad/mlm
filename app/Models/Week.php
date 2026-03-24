<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Week extends Model
{
    protected $fillable = ['week_name', 'standard_percentage', 'vip_percentage'];

    /**
     * Get ROI percentage for a specific user plan
     */
    public function getPercentageForPlan(string $plan): float
    {
        return $plan === 'vip' ? (float)($this->vip_percentage ?? 0) : (float)($this->standard_percentage ?? 0);
    }

    /**
     * Get percentage for a user based on their plan
     */
    public function getPercentageForUser($user): float
    {
        $userPlan = $user->user_plan ?? 'standard';
        return $this->getPercentageForPlan($userPlan);
    }
}
