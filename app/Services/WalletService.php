<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class WalletService
{

    // New functions
    public function assignCommission(int $userId, float $amount, string $type, User $sourceUser,int $level,float $percentage): void 
    {
        if ($amount <= 0) {
            Log::warning("Attempted to assign non-positive commission amount: {$amount}");
            return;
        }

        try { 
            $wallet = Wallet::create([
                'user_id' => $userId,
                'wallet_type' => 'direct_indirect',
                'balance' => $amount,
                'direct_balance' => $type === 'direct' ? $amount : 0.00,
                'indirect_balance' => $type === 'indirect' ? $amount : 0.00,
                'total_amount' => $amount,
                'level' => $level,
                'wallet_from' => $sourceUser->id,
                'commission_type' => $type,
                'percentage' => $percentage, 
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("Commission assigned successfully: Wallet ID {$wallet->id}, Amount: {$amount}");
            
        } catch (\Exception $e) {
            Log::error("Failed to assign commission: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserCommissionBalance(int $userId): array
    {
        $balances = Wallet::where('user_id', $userId)
            ->where('wallet_type', 'direct_indirect')
            ->selectRaw('
                SUM(direct_balance) as total_direct,
                SUM(indirect_balance) as total_indirect,
                SUM(total_amount) as total_commission
            ')
            ->first();

        return [
            'direct_commission' => $balances->total_direct ?? 0,
            'indirect_commission' => $balances->total_indirect ?? 0,
            'total_commission' => $balances->total_commission ?? 0,
        ];
    }

    public function getCommissionHistory(int $userId, int $limit = 50): Collection
    {
        return Wallet::where('user_id', $userId)
            ->where('wallet_type', 'direct_indirect')
            ->with('sourceUser:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }


    // Old Functions
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
        $wallet->total_amount += $amount;
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
        $wallet->total_amount += $amount;
        // Save the wallet
        $wallet->save(); 
    }

    public function OldassignCommission($userId, $amount, $type, $user,$level,$percentage)
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
        $wallet->percentage += $percentage;
        $wallet->total_amount += $amount;
        // Save the wallet
        $wallet->save();

        
    }
 
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