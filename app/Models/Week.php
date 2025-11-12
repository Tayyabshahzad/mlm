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
        return $plan === 'vip' ? $this->vip_percentage : $this->standard_percentage;
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
