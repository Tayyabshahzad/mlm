<?php

namespace App\Http\Controllers;

use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ROIController extends Controller
{
    public function submitRoiPayments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id', // User selection
            'commission_percentage' => 'required|numeric|min:0|max:100', // Commission percentage
            'description' => 'required'
        ]);
        $user = User::
        where('can_login', true)->
        where('blocked',false)->find($request->user_id); 
        $walletTotal = Wallet::where('user_id',$user->id)->sum('total_amount');
        if($walletTotal >= 200){
            return redirect()->back()->with('error', '2x is completed for this user'); 
        }
        if ($user->roi_wallet_balance >= 200) {
            return redirect()->back()->with('error', 'ROI already completed for this user'); 
        }
        if (!$user->roi_start_date) {
            $user->roi_start_date = Carbon::now();
            $user->roi_end_date = Carbon::now()->addYears(2); // 2 years from start
            $user->save();
        } 

        $monthsRemaining = Carbon::now()->diffInMonths($user->roi_end_date, false);
        $remainingPV = 200 - $user->roi_wallet_balance;
        $paymentPercentage = $request->commission_percentage;
        $maxMonthlyPayment = $remainingPV / $monthsRemaining;
        $roiPayment = ($remainingPV * $paymentPercentage) / 100;
        $user->roi_wallet_balance += $roiPayment;
        $user->last_roi_payment_date = Carbon::now();
        $user->save();
        $wallet = Wallet::Create(
            [
                'user_id' => $user->id,
                'wallet_type' => 'roi',
                'balance' => $roiPayment,
                'total_amount' => $roiPayment,
                'level' => '-',
                'commission_type' => 'Roi',
                'percentage' => $paymentPercentage,
            ]
        );

        ROITransaction::create([
            'user_id' => $user->id,
            'amount' => $roiPayment,
            'percentage' => $request->commission_percentage,
            'description' => 'ROI '. $request->description,
        ]);
        $this->generateParentCommissions($user, $roiPayment);
        return redirect()->back()->with('success', 'ROI Generated Successfully');
    }

      private function generateParentCommissions($user, $roiAmount)
    {
         
        $commissionLevels = [
            1 => 7.0,
            2 => 6.0,
            3 => 5.0,
            4 => 4.0,
            5 => 3.0,
            6 => 2.0,
            7 => 1.0,
        ]; 
        
        foreach ($commissionLevels as $level => $percentage) {
            $parent = $this->getAncestorByLevel($user, $level);

            if ($parent) {
                // Count total users (direct + indirect) up to this level
                $totalDownlineCount = $this->countDownlineUsers($parent->id, $level);
                $requiredUsers = $this->getRequiredUsersForLevel($level);

                if ($totalDownlineCount >= $requiredUsers) {
                    $commissionAmount = ($roiAmount * $percentage) / 100;

                    // Save commission transaction
                    ROITransaction::create([
                        'user_id' => $parent->id,
                        'amount' => $commissionAmount,
                        'percentage' => $percentage,
                        'description' => "Level {$level} commission from user {$user->id} | {$user->name}",
                    ]);

                    // Save to wallet
                    Wallet::create([
                        'user_id' => $parent->id,
                        'wallet_type' => 'profit_share',
                        'balance' => $commissionAmount,
                        'level' => $level,
                        'commission_type' => 'profit_share',
                        'wallet_from' => $user->id,
                        'percentage' => $percentage,
                        'total_amount' => $commissionAmount,
                    ]);

                    $this->info("Commission of $commissionAmount assigned to User {$parent->id} for Level {$level}");
                }
            }
        }
    }

    private function getAncestorByLevel($user, $level)
    {
        return User::whereIn('id', function ($query) use ($user, $level) {
            $query->select('ancestor_id')
                ->from('referral_trees')
                ->where('descendant_id', $user->id)
                ->where('level', $level);
        })->first(); // Get only one parent per level
    }

      private function countDownlineUsers($parentId, $level)
    {
        return DB::table('referral_trees')
            ->where('ancestor_id', $parentId)
            ->where('level', '<=', $level)  // Include all users up to this level
            ->count();
    }

      private function getRequiredUsersForLevel($level)
    {


        $requiredUsers = [
            1 => 10,  // Level 1 needs 2 users
            2 => 50,  // Level 2 needs 3 users
            3 => 150,  // Level 3 needs 4 users
            4 => 400,  // Level 4 needs 5 users
            5 => 1000,  // Level 5 needs 6 users
            6 => 2000,  // Level 6 needs 7 users
            7 => 4000,  // Level 7 needs 8 users
        ];

        return $requiredUsers[$level] ?? 0;  // Default to 0 if level is not defined
    } 

}
