<?php // app/Services/CommissionService.php
namespace App\Services;

use App\Models\User;
use App\Models\CommissionLog;
use Carbon\Carbon;

class CommissionService
{
    public function distributeCommissions($userId, $pvAdded)
    {
        $user = User::find($userId);
        $referrer = $user->referrer_user_id; // Parent User
        $level = 1;

        // Commission percentages for each level
        $commissionStructure = [
            1 => 7,
            2 => 6,
            3 => 5,
            4 => 4,
            5 => 3,
            6 => 2,
            7 => 1,
        ];

        // Loop through the referrer chain for 7 levels
        while ($referrer && $level <= 7) {
            $referrerUser = User::find($referrer);

            if ($referrerUser) {
                $percentage = $commissionStructure[$level];
                $commissionAmount = ($percentage / 100) * $pvAdded;

                // Log the commission
                CommissionLog::create([
                    'user_id' => $referrer,
                    'source_user_id' => $userId,
                    'level' => $level,
                    'percentage' => $percentage,
                    'pv_value' => $pvAdded,
                    'amount' => $commissionAmount,
                    'earned_date' => Carbon::now(),
                ]);

                // Update referrer's ROI wallet balance
                $referrerUser->roi_wallet_balance += $commissionAmount;
                $referrerUser->save();
            }

            // Move to the next level
            $referrer = $referrerUser->referrer_user_id;
            $level++;
        }
    }
}
