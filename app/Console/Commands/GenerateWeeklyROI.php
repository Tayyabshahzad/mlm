<?php

namespace App\Console\Commands;

use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Week;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateWeeklyROI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =  'roi:generate-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly ROI and distribute commissions for all eligible users';

    /**
     * Execute the console command.
     */
    
     public function handle()
    {
        $users = User::where('blocked',false)->where('can_login', true)->where('freez_wallet',false)->get(); // Fetch all users 
        foreach ($users as $user) {

            $totalRoiPaid = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'roi')
            ->sum('total_amount');
            $investedAmount = $user->roi_eligible_investment_amount;
            if ($totalRoiPaid >= ($investedAmount * 2)) {
                $this->info("Skipping user {$user->id} | {$user->name} - Already earned 2x ROI.");
                continue;
            }
            // ✅ Skip if already paid today
            if ($user->last_roi_payment_date && Carbon::parse($user->last_roi_payment_date)->isToday()) {
                $this->info("Skipping user {$user->id} | {$user->name} - ROI already generated today.");
                continue;
            }

            if (!$user->roi_start_date) {
                $user->roi_start_date = now();
                $user->roi_end_date = now()->addYears(2);
                $user->save();
            }

            $week = Week::first(); // or where('active', true) if you have multiple
            $percentage = $week->percentage; // Example: 0.10 means 10%
            $roiPayment = ($investedAmount * $percentage) / 100;
            $user->roi_wallet_balance += $roiPayment;
            $user->last_roi_payment_date = now();
            $user->save();

            Wallet::create([
                'user_id' => $user->id,
                'wallet_type' => 'roi',
                'balance' => $roiPayment,
                'level' => '-',
                'commission_type' => 'Roi',
                'total_amount' => $roiPayment,
                'percentage' => $percentage,
            ]);


            ROITransaction::create([
                'user_id' => $user->id,
                'amount' => $roiPayment,
                'percentage' => $percentage,
                'description' => 'Weekly ROI Generated',
            ]);

            // old Condation
            // $walletTotal = Wallet::where('user_id', $user->id)->sum('total_amount'); old condation
            // $walletTotal = $user->sum('roi_eligible_investment_amount');
            // if ($walletTotal < 100 ) {
            //     continue;
            // } 
            // // Skip if an ROI transaction has already been created today
            // if ($user->last_roi_payment_date && Carbon::parse($user->last_roi_payment_date)->isToday()) {
            //     $this->info("Skipping user {$user->id} | {$user->name} - ROI already generated today.");
            //     continue;
            // }

            // // Initialize ROI start and end dates if not set
            // if (!$user->roi_start_date) {
            //     $user->roi_start_date = Carbon::now();
            //     $user->roi_end_date = Carbon::now()->addYears(2);
            //     $user->save();
            // }

            // $monthsRemaining = Carbon::now()->diffInMonths($user->roi_end_date, false);
            // $remainingPV = (200 - $user->roi_wallet_balance);
        
            // $dailyPercentage = Week::first();
            // $paymentPercentage = $dailyPercentage->percentage; // Example: fixed percentage, adjust as needed
            // $maxMonthlyPayment = $remainingPV / $monthsRemaining;
            // $roiPayment = ($remainingPV * $paymentPercentage) / 100;

            // $user->roi_wallet_balance += $roiPayment;
            // $user->last_roi_payment_date = Carbon::now();
            // $user->save();

            // // Create wallet entry for ROI
            // Wallet::create([
            //     'user_id' => $user->id,
            //     'wallet_type' => 'roi',
            //     'balance' => $roiPayment,
            //     'level' => '-',
            //     'commission_type' => 'Roi',
            //     'total_amount'=> $roiPayment,
            //     'percentage' => $paymentPercentage,
            // ]);

            // // Record the ROI transaction
            // ROITransaction::create([
            //     'user_id' => $user->id,
            //     'amount' => $roiPayment,
            //     'percentage' => $paymentPercentage,
            //     'description' => 'Weekly ROI Generated',
            // ]);

            // Generate parent commissions
            $this->generateParentCommissions($user, $roiPayment);

            $this->info("ROI generated for user {$user->id} | {$user->name}");
        }

        $this->info('Weekly ROI generation completed.');
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
                $totalDownlineCount = $this->countDownlineUsers($parent->id, $level);
                $requiredUsers = $this->getRequiredUsersForLevel($level);

                if ($totalDownlineCount >= $requiredUsers) {
                    $commissionAmount = ($roiAmount * $percentage) / 100; 

                    ROITransaction::create([
                        'user_id' => $parent->id,
                        'amount' => $commissionAmount,
                        'percentage' => $percentage,
                        'description' => "Level {$level} commission from user {$user->id} | {$user->name}",
                    ]);

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


    private function getRequiredUsersForLevel($level)
    {
        $requiredUsers = [
            1 => 10,  // Level 1 needs 10 users
            2 => 50,  // Level 2 needs 50 users
            3 => 150,  // Level 3 needs 150 users
            4 => 400,  // Level 4 needs 400 users
            5 => 1000,  // Level 5 needs 1000 users
            6 => 2000,  // Level 6 needs 2000 users
            7 => 4000,  // Level 7 needs 4000 users
        ];

        return $requiredUsers[$level] ?? 0;  // Default to 0 if level is not defined
    }
    private function countDownlineUsers($parentId, $level)
    {
        return \DB::table('referral_trees')
            ->where('ancestor_id', $parentId)
            ->where('level', '<=', $level)  // Include all users up to this level
            ->count();
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

    
}
