<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixInconsistentRewardReversals extends Command
{
    protected $signature = 'rewards:fix-inconsistent-reversals {--dry-run : Preview changes without applying them}';

    protected $description = 'Fix reward wallets that have balance = 0 but total_amount > 0 (incomplete reversals)';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        // Find inconsistent reward wallets
        $inconsistentWallets = Wallet::where('wallet_type', 'reward')
            ->where('commission_type', 'reward')
            ->where('balance', 0)
            ->where('total_amount', '>', 0)
            ->get();

        if ($inconsistentWallets->isEmpty()) {
            $this->info('✅ No inconsistent reward reversals found. All reward wallets are in a consistent state.');
            return 0;
        }

        $this->warn("Found {$inconsistentWallets->count()} wallets with inconsistent reward reversal states:");
        $this->newLine();

        $totalEarningsImpact = 0;

        // Display affected wallets
        $headers = ['User', 'User ID', 'Level', 'Balance', 'Total Amount', 'Impact'];
        $rows = [];

        foreach ($inconsistentWallets as $wallet) {
            $user = User::find($wallet->user_id);
            $totalEarningsImpact += $wallet->total_amount;

            $rows[] = [
                $user ? $user->name : 'Unknown',
                $wallet->user_id,
                $wallet->level,
                '$' . number_format($wallet->balance, 2),
                '$' . number_format($wallet->total_amount, 2),
                '$' . number_format($wallet->total_amount, 2)
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->warn("Total earnings incorrectly inflated by: $" . number_format($totalEarningsImpact, 2));

        if ($isDryRun) {
            $this->info('🔍 This was a dry run. Use the command without --dry-run to apply the fixes.');
            return 0;
        }

        if (!$this->confirm('Do you want to fix these inconsistencies by setting total_amount to 0 for these wallets?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Apply the fixes
        DB::beginTransaction();

        try {
            $fixed = 0;

            foreach ($inconsistentWallets as $wallet) {
                $user = User::find($wallet->user_id);

                $this->info("Fixing wallet for {$user->name} (Level {$wallet->level})...");

                $wallet->update([
                    'total_amount' => 0,
                    'updated_at' => now()
                ]);

                $fixed++;

                // Log the fix
                Log::info('Fixed inconsistent reward reversal', [
                    'wallet_id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'user_name' => $user->name,
                    'level' => $wallet->level,
                    'previous_total_amount' => $wallet->total_amount,
                    'fixed_by_command' => true,
                    'admin_user' => 'Console Command'
                ]);
            }

            DB::commit();

            $this->newLine();
            $this->info("✅ Successfully fixed {$fixed} inconsistent reward reversals.");
            $this->info("💰 Total earnings across all users reduced by: $" . number_format($totalEarningsImpact, 2));
            $this->info("📝 All changes have been logged for audit purposes.");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('❌ Error occurred while fixing inconsistencies: ' . $e->getMessage());
            Log::error('Error fixing inconsistent reward reversals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}