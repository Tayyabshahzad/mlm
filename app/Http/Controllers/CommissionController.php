<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionController extends Controller
{
    public function recalculateCommissions()
    {
        $users = User::where('blocked', false)->get(); // Fetch all unblocked users

        foreach ($users as $user) {
            $activeDirects = $this->getActiveDirects($user->id); // Get active direct users
            
            if (count($activeDirects) >= 1) { // Check if the user qualifies for commission
                $this->assignCommissions($user, true); // Pass true to indicate recalculation
            }
        }
    }

    private function assignCommissions(User $user, $isRecalculation = false)
    {
        // Immediate sponsor commission
        $parentUser = User::where('blocked', false)->find($user->sponsor_id);

        if ($parentUser) {
            $this->assignSingleCommission($parentUser, $user, 1, $isRecalculation); // Level 1 commission
        }

        // Indirect commissions for ancestors up to level 7
        $ancestors = $this->getAncestors($user)->filter(function ($ancestor) use ($user) {
            return $ancestor->ancestor_id !== $user->sponsor_id && $ancestor->level <= 7;
        });

        foreach ($ancestors as $ancestor) {
            $ancestorUser = User::where('blocked', false)->find($ancestor->ancestor_id);

            if ($ancestorUser) {
                $this->assignSingleCommission($ancestorUser, $user, $ancestor->level, $isRecalculation);
            }
        }
    }

    private function assignSingleCommission(User $recipient, User $payer, $level, $isRecalculation)
    {
        $existingCommission = $this->isCommissionAssigned($recipient->id, $payer->id, $level);

        if (!$existingCommission) {
            $commissionPercentage = $this->getCommissionForLevel($level);
            $commissionAmount = ($commissionPercentage / 100) * $payer->current_pv_balance;

            // Assign commission (store in database or process payment logic)
            $this->saveCommission($recipient->id, $commissionAmount, 'level-' . $level, $payer, $level);
        } elseif ($isRecalculation) {
            // Log skipped commission for monitoring
            Log::info("Commission already assigned: Recipient ID {$recipient->id}, Payer ID {$payer->id}, Level {$level}");
        }
    }

    private function getActiveDirects($userId)
    {
        return User::where('sponsor_id', $userId)
                   ->where('blocked', false)
                   ->get();
    }

    private function getCommissionForLevel($level)
    {
        $commissionRates = [
            1 => 5, // Level 1: 5%
            2 => 3, // Level 2: 3%
            3 => 2, // Level 3: 2%
            4 => 1, // Level 4: 1%
            5 => 1, // Level 5: 1%
            6 => 1, // Level 6: 1%
            7 => 1, // Level 7: 1%
        ];

        return $commissionRates[$level] ?? 0;
    }

    private function isCommissionAssigned($recipientId, $payerId, $level)
    {
        // Replace this with a database query to check for existing commissions
        return false;
    }

    private function saveCommission($recipientId, $amount, $description, $payer, $level)
    {
        // Implement your logic for saving the commission
        Log::info("Commission assigned: Recipient ID {$recipientId}, Amount {$amount}, Level {$level}");
    }

    private function getAncestors($user)
    {
        return DB::table('referral_trees')
            ->select('ancestor_id', 'level') // Ensure level is retrieved
            ->where('descendant_id', $user->id)
            ->where('level', '<=', 7) // Include only ancestors up to Level 7
            ->get();
    }

    

   

}
