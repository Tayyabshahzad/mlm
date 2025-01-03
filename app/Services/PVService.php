<?php

namespace App\Services;

use App\Models\User;
use App\Models\PVTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PVService
{
    /**
     * Assign initial PV to a newly registered user
     *
     * @param User $user
     * @param float $pvAmount
     * @return bool
     */
    public function assignInitialPV(User $user, float $pvAmount = 100.0): bool
    {
        try {
            DB::beginTransaction();
            // Update user's PV balance
            $previousBalance = $user->current_pv_balance;
           // $user->current_pv_balance = $previousBalance + $pvAmount;
            $user->current_pv_balance =  $pvAmount;
            $user->save();  
            // Create PV transaction record
            PVTransaction::create([
                'user_id' => $user->id,
                'transaction_type' => 'INVESTMENT',
                'user_id' => $user->id,
                'pv_amount' => $pvAmount,
                'previous_balance' => $previousBalance,
                'new_balance' => $user->current_pv_balance,
                'transaction_date' => Carbon::now(),
                'remarks' => 'Initial PV assignment after registration'
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) { 
            DB::rollBack();
            \Log::error('Failed to assign initial PV: ' . $e->getMessage());
            return false;
        }
    }
}