<?php

namespace App\Services;

use App\Models\Wallet;

class WalletService
{
    public function createOrUpdateWallet($userId, $walletType, $amount = 0.00)
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId, 'wallet_type' => $walletType],
            ['balance' => 0.00, 'direct_balance' => 0.00, 'indirect_balance' => 0.00]
        );

        // Update the wallet balance based on the wallet type
        if ($walletType === 'direct_indirect') {
            $wallet->direct_balance += $amount; // Update the direct balance
        } elseif ($walletType === 'bonus') {
            $wallet->balance += $amount; // Update the bonus balance
        } elseif ($walletType === 'withdrawal') {
            $wallet->balance += $amount; // Update the withdrawal balance
        }
        
        // Save the wallet
        $wallet->save();
    }
 

    public function wwassignCommission($userId, $amount, $type, $user)
    {
        if ($amount <= 0) {
            return; // Skip if no valid amount to assign
        }

        // Fetch or create the wallet for the user
        $wallet = Wallet::firstOrCreate(
            [
                'user_id' => $userId,
                'wallet_from'=>$user->id,
                'wallet_type' => 'direct_indirect',
            ],
            [
                'wallet_from'=>$user->id,
                'balance' => 0.00,
                'direct_balance' => 0.00,
                'indirect_balance' => 0.00,
            ]
        );

        // Update the relevant column based on commission type
        if ($type === 'direct') {
            $wallet->direct_balance += $amount;
        } elseif ($type === 'indirect') {
            $wallet->indirect_balance += $amount;
        }

        // Save the wallet
        $wallet->save(); 
    }

    public function assignCommission($userId, $amount, $type, $user,$level)
{
    if ($amount <= 0) {
        return; // Skip if no valid amount to assign
    } 
    // Fetch or create the wallet for the user
    $wallet = Wallet::Create(
        
        [
            'user_id' => $userId,
            'wallet_type' => 'direct_indirect',
            'balance' => 0.00,
            'direct_balance' => 0.00,
            'indirect_balance' => 0.00,
            'level' => $level,
            'wallet_from' => $user->id,
            'commission_type'=>$type
        ]
    );
    $wallet->balance += $amount;
    // Ensure only the relevant column is updated
    if ($type === 'direct') {
        $wallet->direct_balance += $amount; 
    } elseif ($type === 'indirect') {
        $wallet->indirect_balance += $amount;
       
    }

    // Save the wallet
    $wallet->save();

     
}



    
    



    

    // Optionally, handle withdrawal requests
    public function processWithdrawal($userId, $amount)
    {
        $wallet = Wallet::where('user_id', $userId)->where('wallet_type', 'withdrawal')->first();

        if ($wallet && $wallet->balance >= $amount) {
            $wallet->balance -= $amount; // Deduct from withdrawal balance
            $wallet->save();
            return true;
        }

        return false; // Insufficient balance
    }
}