<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function assignCommission(int $userId, float $amount, string $type, User $sourceUser, int $level, float $percentage, string $sourceType = 'investment'): ?Wallet
    {
        if ($amount <= 0) {
            Log::warning("Attempted to assign non-positive commission amount: {$amount}");
            return null;
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
                'source_type' => $sourceType,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info("Commission assigned successfully: Wallet ID {$wallet->id}, Amount: {$amount}");

            return $wallet;

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

    public function createOrUpdateWallet($userId, $walletType, $amount = 0.00)
    {
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId, 'wallet_type' => $walletType],
            ['balance' => 0.00, 'direct_balance' => 0.00, 'indirect_balance' => 0.00]
        );

        // Update the wallet balance based on the wallet type
        if ($walletType === 'direct_indirect') {
            $wallet->direct_balance += $amount;
        } elseif ($walletType === 'bonus') {
            $wallet->balance += $amount;
        } elseif ($walletType === 'withdrawal') {
            $wallet->balance += $amount;
        }
        
        $wallet->total_amount += $amount;
        $wallet->save();
    }

    public function processWithdrawal($userId, $amount)
    {
        $wallet = Wallet::where('user_id', $userId)
            ->where('wallet_type', 'withdrawal')
            ->first();

        if ($wallet && $wallet->balance >= $amount) {
            $wallet->balance -= $amount;
            $wallet->save();
            return true;
        }

        return false;
    }

    public function getUserWalletSummary(int $userId): array
    {
        $wallets = Wallet::where('user_id', $userId)
            ->selectRaw('
                wallet_type,
                SUM(balance) as total_balance,
                SUM(direct_balance) as total_direct,
                SUM(indirect_balance) as total_indirect,
                COUNT(*) as transaction_count
            ')
            ->groupBy('wallet_type')
            ->get();

        $summary = [
            'total_balance' => 0,
            'wallets' => []
        ];

        foreach ($wallets as $wallet) {
            $summary['wallets'][$wallet->wallet_type] = [
                'balance' => $wallet->total_balance,
                'direct_balance' => $wallet->total_direct,
                'indirect_balance' => $wallet->total_indirect,
                'transaction_count' => $wallet->transaction_count
            ];
            $summary['total_balance'] += $wallet->total_balance;
        }

        return $summary;
    }

    public function transferBetweenWallets(int $userId, string $fromWalletType, string $toWalletType, float $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }

        try {
            $fromWallet = Wallet::where('user_id', $userId)
                ->where('wallet_type', $fromWalletType)
                ->first();

            if (!$fromWallet || $fromWallet->balance < $amount) {
                return false;
            }

            $toWallet = Wallet::firstOrCreate(
                ['user_id' => $userId, 'wallet_type' => $toWalletType],
                ['balance' => 0.00]
            );

            $fromWallet->balance -= $amount;
            $toWallet->balance += $amount;

            $fromWallet->save();
            $toWallet->save();

            Log::info("Wallet transfer completed", [
                'user_id' => $userId,
                'from' => $fromWalletType,
                'to' => $toWalletType,
                'amount' => $amount
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Wallet transfer failed: " . $e->getMessage());
            return false;
        }
    }
}