<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleRoiController extends Controller
{
    public function schedule(){
        $users = User::where('can_login', true)->get(); // Fetch all users  
        foreach ($users as $user) {
            $walletTotal = Wallet::where('user_id', $user->id)->sum('balance');
            if ($walletTotal >= 200 ) {
                continue;
            } 
            // Skip if an ROI transaction has already been created today
            if ($user->last_roi_payment_date && $user->last_roi_payment_date->isToday()) {
                $this->info("Skipping user {$user->id} | {$user->name} - ROI already generated today.");
                continue;
            }

            // Initialize ROI start and end dates if not set
            if (!$user->roi_start_date) {
                $user->roi_start_date = Carbon::now();
                $user->roi_end_date = Carbon::now()->addYears(2);
                $user->save();
            }

            $monthsRemaining = Carbon::now()->diffInMonths($user->roi_end_date, false);
            $remainingPV = (200 - $user->roi_wallet_balance);
        
            $dailyPercentage = Week::first();
            $paymentPercentage = $dailyPercentage->percentage; // Example: fixed percentage, adjust as needed
            $maxMonthlyPayment = $remainingPV / $monthsRemaining;
            $roiPayment = ($remainingPV * $paymentPercentage) / 100;

            $user->roi_wallet_balance += $roiPayment;
            $user->last_roi_payment_date = Carbon::now();
            $user->save();

            // Create wallet entry for ROI
            Wallet::create([
                'user_id' => $user->id,
                'wallet_type' => 'roi',
                'balance' => $roiPayment,
                'level' => '-',
                'commission_type' => 'Roi',
                'percentage' => $paymentPercentage,
            ]);

            // Record the ROI transaction
            ROITransaction::create([
                'user_id' => $user->id,
                'amount' => $roiPayment,
                'percentage' => $paymentPercentage,
                'description' => 'Weekly ROI Generated',
            ]);

            // Generate parent commissions
            $this->generateParentCommissions($user, $roiPayment);

            $this->info("ROI generated for user {$user->id} | {$user->name}");
        }
    }
}
