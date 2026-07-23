<?php

namespace App\Console\Commands;

use App\Models\SavingInstalment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RollbackSavingInstalment extends Command
{
    protected $signature = 'saving:rollback-instalment
                            {--user= : Username of the member}
                            {--instalment= : Instalment number to roll back (e.g. 2)}
                            {--dry-run : Preview what will be reversed without making any changes}
                            {--force : Skip the "are you sure?" confirmation prompt}';

    protected $description = 'Completely reverse a wrongly-confirmed saving instalment payment';

    public function handle(): int
    {
        $username   = $this->option('user');
        $instNum    = (int) $this->option('instalment');
        $dryRun     = (bool) $this->option('dry-run');

        if (!$username || !$instNum) {
            $this->error('Both --user and --instalment are required.');
            $this->line('  Example: php artisan saving:rollback-instalment --user=Haider512 --instalment=2 --dry-run');
            return 1;
        }

        // ── Find the user ──────────────────────────────────────────────────────
        $user = User::where('username', $username)->first();
        if (!$user) {
            $this->error("User '{$username}' not found.");
            return 1;
        }

        // ── Find the instalment ────────────────────────────────────────────────
        $instalment = SavingInstalment::where('user_id', $user->id)
            ->where('instalment_number', $instNum)
            ->first();

        if (!$instalment) {
            $this->error("Instalment #{$instNum} not found for user '{$username}'.");
            return 1;
        }

        if ($instalment->status !== 'confirmed') {
            $this->error("Instalment #{$instNum} status is '{$instalment->status}', not 'confirmed'. Nothing to roll back.");
            return 1;
        }

        $submittedAmount = (float) $instalment->submitted_amount;
        $netCredited     = (float) ($instalment->net_credited ?? $submittedAmount);

        // ── Gather commission records ──────────────────────────────────────────
        $commissions = DB::table('saving_instalment_commissions')
            ->where('saving_instalment_id', $instalment->id)
            ->get();

        $commissionWallets = [];
        $hasSpentCommission = false;

        foreach ($commissions as $comm) {
            if (!$comm->wallet_id) {
                continue;
            }
            $wallet = DB::table('wallets')->where('id', $comm->wallet_id)->first();
            if (!$wallet) {
                continue;
            }
            $original = round((float)$wallet->direct_balance + (float)$wallet->indirect_balance, 4);
            $remaining = round((float)$wallet->balance, 4);
            if ($remaining < $original - 0.001) {
                $hasSpentCommission = true;
                $this->warn("  ⚠  Commission wallet #{$wallet->id} (L{$comm->level}, \${$comm->commission_amount}) has been partially spent — original: \${$original}, remaining: \${$remaining}");
            }
            $commissionWallets[] = $wallet;
        }

        // ── Find deposit wallet ────────────────────────────────────────────────
        $depositWallet = DB::table('wallets')
            ->where('user_id', $user->id)
            ->where('commission_type', 'saving_deposit')
            ->where('source_type', 'saving')
            ->where('description', "Saving instalment #{$instNum} deposited")
            ->orderByDesc('id')
            ->first();

        // ── Find transaction log ───────────────────────────────────────────────
        $txLog = DB::table('transaction_logs')
            ->where('user_id', $user->id)
            ->where('from_wallet_type', 'saving_instalment')
            ->where('to_wallet_type', 'saving')
            ->where('amount', $submittedAmount)
            ->orderByDesc('id')
            ->first();

        // ── Print preview ──────────────────────────────────────────────────────
        $this->line('');
        $this->info("=== ROLLBACK PREVIEW ===");
        $this->line("  User       : {$user->name} (#{$user->id}, @{$user->username})");
        $this->line("  Instalment : #{$instNum} (ID: {$instalment->id})");
        $this->line("  Confirmed  : {$instalment->confirmed_at}");
        $this->line("  Wrong amt  : \${$submittedAmount}  (should have been \${$instalment->amount})");
        $this->line("  Net credit : \${$netCredited}");
        $this->line('');

        $this->line("  Actions that will be taken:");
        $this->line("    [1] Reset instalment #{$instNum} → status='pending', clear all submitted/confirmed fields");
        $this->line("    [2] Delete deposit wallet entry" . ($depositWallet ? " (ID: {$depositWallet->id}, \${$depositWallet->balance})" : " ⚠  NOT FOUND"));
        $this->line("    [3] Delete transaction log entry" . ($txLog ? " (ID: {$txLog->id})" : " ⚠  NOT FOUND"));
        $this->line("    [4] Delete " . count($commissionWallets) . " commission wallet entries:");
        foreach ($commissions as $comm) {
            $this->line("          L{$comm->level} → ancestor_id={$comm->ancestor_id}, \${$comm->commission_amount}, wallet_id={$comm->wallet_id}");
        }
        $this->line("    [5] Delete " . $commissions->count() . " saving_instalment_commissions records");
        $this->line("    [6] Decrement users.saving_total_deposited by \${$submittedAmount}");
        $this->line("    [7] Decrement users.roi_eligible_investment_amount by \${$netCredited}");
        $this->line("    [8] Clear instalment proof screenshot from media");
        $this->line('');
        $this->line("  Current user totals:");
        $this->line("    saving_total_deposited        : \${$user->saving_total_deposited}  → will become \$" . round($user->saving_total_deposited - $submittedAmount, 4));
        $this->line("    roi_eligible_investment_amount: \${$user->roi_eligible_investment_amount}  → will become \$" . round($user->roi_eligible_investment_amount - $netCredited, 4));
        $this->line('');

        if ($hasSpentCommission) {
            $this->error("BLOCKER: One or more commission wallets have already been partially spent.");
            $this->error("Manual review required before rollback can proceed safely.");
            return 1;
        }

        if ($dryRun) {
            $this->warn("--- DRY RUN: No changes were made. Remove --dry-run to execute. ---");
            return 0;
        }

        // ── Confirm before executing ───────────────────────────────────────────
        if (!$this->option('force') && !$this->confirm('Proceed with rollback? This cannot be undone.', false)) {
            $this->line('Aborted.');
            return 0;
        }

        // ── Execute inside a transaction ───────────────────────────────────────
        try {
            DB::transaction(function () use ($instalment, $user, $commissions, $commissionWallets, $depositWallet, $txLog, $submittedAmount, $netCredited, $instNum) {

                // 1. Delete commission wallets
                $walletIds = collect($commissionWallets)->pluck('id')->toArray();
                if (!empty($walletIds)) {
                    DB::table('wallets')->whereIn('id', $walletIds)->delete();
                    $this->line("  ✓ Deleted " . count($walletIds) . " commission wallet entries");
                }

                // 2. Delete saving_instalment_commissions
                DB::table('saving_instalment_commissions')
                    ->where('saving_instalment_id', $instalment->id)
                    ->delete();
                $this->line("  ✓ Deleted {$commissions->count()} saving_instalment_commissions records");

                // 3. Delete deposit wallet
                if ($depositWallet) {
                    DB::table('wallets')->where('id', $depositWallet->id)->delete();
                    $this->line("  ✓ Deleted deposit wallet entry (ID: {$depositWallet->id})");
                } else {
                    $this->warn("  ⚠  Deposit wallet entry not found — skipped");
                }

                // 4. Delete transaction log
                if ($txLog) {
                    DB::table('transaction_logs')->where('id', $txLog->id)->delete();
                    $this->line("  ✓ Deleted transaction log entry (ID: {$txLog->id})");
                } else {
                    $this->warn("  ⚠  Transaction log entry not found — skipped");
                }

                // 5. Decrement user amounts
                $user->decrement('saving_total_deposited', $submittedAmount);
                $user->decrement('roi_eligible_investment_amount', $netCredited);
                $this->line("  ✓ Decremented saving_total_deposited by \${$submittedAmount}");
                $this->line("  ✓ Decremented roi_eligible_investment_amount by \${$netCredited}");

                // 6. Reset instalment to pending
                $instalment->update([
                    'status'            => 'pending',
                    'submitted_amount'  => null,
                    'adb_charge'        => null,
                    'fisp_charge'       => null,
                    'net_credited'      => null,
                    'is_late'           => false,
                    'roi_eligible_from' => null,
                    'confirmed_at'      => null,
                    'confirmed_by'      => null,
                    'deposited_at'      => null,
                    'submitted_at'      => null,
                    'transaction_id'    => null,
                    'payment_method'    => null,
                    'deposit_deferred'  => false,
                    'next_cycle_date'   => null,
                    'notes'             => null,
                ]);
                $this->line("  ✓ Reset instalment #{$instNum} to 'pending'");

                // 7. Clear proof screenshot
                $instalment->clearMediaCollection('instalment_proof');
                $this->line("  ✓ Cleared payment proof screenshot");
            });

            $this->line('');
            $this->info("✅ ROLLBACK COMPLETE — Instalment #{$instNum} for {$user->username} has been fully reversed.");
            $this->line("   The user can now submit the correct payment amount again.");

            Log::info("Saving instalment rollback: user={$user->id} username={$user->username} instalment={$instNum} wrong_amount={$submittedAmount}");

        } catch (\Throwable $e) {
            $this->error("ROLLBACK FAILED: " . $e->getMessage());
            Log::error("Saving instalment rollback failed: user={$user->id} instalment={$instNum} error=" . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
