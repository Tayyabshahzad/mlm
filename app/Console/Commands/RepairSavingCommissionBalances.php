<?php

namespace App\Console\Commands;

use App\Models\Wallet;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repairs saving_instalment commission wallet balances that were incorrectly zeroed
 * by transferToOnline() before the source_type guard was added.
 *
 * Root cause: WalletController::transferToOnline() queried ALL direct_indirect wallets
 * without filtering source_type = 'saving_instalment', so saving commission balances
 * were consumed when users transferred their regular commissions to online.
 *
 * The command calculates the NET gap per user as:
 *   raw_gap - legitimate_saving_transfers_to_online
 * so that balances voluntarily moved via transferSavingCommissionToOnline are not
 * double-restored.
 *
 * Usage:
 *   php artisan wallet:repair-saving-commissions --dry-run
 *   php artisan wallet:repair-saving-commissions --force
 *   php artisan wallet:repair-saving-commissions --force --adjust-online
 *   php artisan wallet:repair-saving-commissions --force --user-id=151
 */
class RepairSavingCommissionBalances extends Command
{
    protected $signature = 'wallet:repair-saving-commissions
                            {--dry-run       : Preview all changes without writing to the database}
                            {--force         : Skip confirmation prompt}
                            {--adjust-online : Also deduct the restored amount from the online wallet to keep system balanced}
                            {--user-id=      : Repair a single user by ID}';

    protected $description = 'Restore saving_instalment commission balances that were incorrectly zeroed by regular direct/indirect transfers.';

    public function handle(): int
    {
        $isDryRun     = $this->option('dry-run');
        $adjustOnline = $this->option('adjust-online');
        $targetUserId = $this->option('user-id');

        if ($isDryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }

        if ($adjustOnline && !$isDryRun) {
            $this->warn('--adjust-online is active: restored amounts will be deducted from each user\'s online wallet.');
        }

        // ── Legitimate saving commission transfers already made by each user ───
        // Subtract these so we don't restore amounts the user voluntarily moved
        // to their online wallet via transferSavingCommissionToOnline.
        $legitimateByUser = DB::table('transaction_logs')
            ->selectRaw('user_id, SUM(amount) as total')
            ->where('from_wallet_type', 'saving_commission')
            ->where('to_wallet_type', 'online')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        // ── Find wallet rows where balance < earned ────────────────────────────
        $query = DB::table('wallets')
            ->where('wallet_type', 'direct_indirect')
            ->where('source_type', 'saving_instalment')
            ->whereRaw('(direct_balance + indirect_balance) > balance');

        if ($targetUserId) {
            $query->where('user_id', $targetUserId);
        }

        $affected = $query->orderBy('user_id')->orderBy('created_at')->get();

        if ($affected->isEmpty()) {
            $this->info('No affected saving commission wallets found. Nothing to repair.');
            return 0;
        }

        $byUser = $affected->groupBy('user_id');

        $this->info("Found {$affected->count()} wallet rows across {$byUser->count()} users.");
        $this->newLine();

        // ── Build summary table ────────────────────────────────────────────────
        $tableRows     = [];
        $totalToRestore = 0;

        foreach ($byUser as $userId => $rows) {
            $user      = User::find($userId);
            $rawGap    = round($rows->sum(fn ($r) => ($r->direct_balance + $r->indirect_balance) - $r->balance), 4);
            $legitOut  = (float) ($legitimateByUser[$userId] ?? 0);
            $netGap    = max(0, round($rawGap - $legitOut, 4));
            $totalToRestore += $netGap;

            $tableRows[] = [
                $userId,
                $user->username ?? 'N/A',
                $user->name ?? 'N/A',
                $rows->count(),
                '$' . number_format($rawGap, 2),
                '$' . number_format($legitOut, 2),
                '$' . number_format($netGap, 2),
            ];
        }

        $this->table(
            ['User ID', 'Username', 'Name', 'Rows', 'Raw Gap', 'Legit Transferred', 'Net to Restore'],
            $tableRows
        );

        $this->newLine();
        $this->info('Total net to restore: $' . number_format($totalToRestore, 2));
        $this->newLine();

        if ($isDryRun) {
            $this->comment('Dry run complete. Run without --dry-run to apply.');
            return 0;
        }

        if ($totalToRestore <= 0) {
            $this->info('All gaps are explained by legitimate transfers. Nothing to restore.');
            return 0;
        }

        if (!$this->option('force') && !$this->confirm('Proceed with repairing these balances?')) {
            $this->info('Aborted.');
            return 0;
        }

        // ── Apply repairs ──────────────────────────────────────────────────────
        $repairedRows  = 0;
        $repairedUsers = 0;

        DB::beginTransaction();
        try {
            foreach ($byUser as $userId => $rows) {
                $legitOut  = (float) ($legitimateByUser[$userId] ?? 0);
                $rawGap    = round($rows->sum(fn ($r) => ($r->direct_balance + $r->indirect_balance) - $r->balance), 4);
                $netGap    = max(0, round($rawGap - $legitOut, 4));

                if ($netGap <= 0) {
                    continue; // Gap entirely from legitimate transfers — skip
                }

                // Distribute the net restoration across wallet rows (oldest first),
                // capping each row at its full earned amount.
                $remaining    = $netGap;
                $userRestored = 0;

                foreach ($rows->sortBy('created_at') as $row) {
                    if ($remaining <= 0) break;

                    $earned = round($row->direct_balance + $row->indirect_balance, 4);
                    $add    = min($earned - $row->balance, $remaining);
                    if ($add <= 0) continue;

                    DB::table('wallets')
                        ->where('id', $row->id)
                        ->update([
                            'balance'    => round($row->balance + $add, 4),
                            'updated_at' => now(),
                        ]);

                    $remaining    -= $add;
                    $userRestored += $add;
                    $repairedRows++;
                }

                if ($userRestored <= 0) continue;

                // Log the correction for traceability
                DB::table('transaction_logs')->insert([
                    'user_id'          => $userId,
                    'from_wallet_type' => 'admin_correction',
                    'to_wallet_type'   => 'saving_commission',
                    'charge'           => 0,
                    'amount'           => round($userRestored, 4),
                    'final_amount'     => round($userRestored, 4),
                    'description'      => "Admin correction: restored \${$userRestored} in saving commission balance "
                        . "— transferToOnline previously consumed saving_instalment wallets (bug fix " . now()->toDateString() . ")",
                    'status'           => 'credit',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                // Optionally offset online wallet to maintain system balance
                if ($adjustOnline) {
                    $onlineWallet = Wallet::where('user_id', $userId)
                        ->where('wallet_type', 'online')
                        ->orderBy('id')
                        ->first();

                    if ($onlineWallet) {
                        $deduct = min((float) $onlineWallet->balance, $userRestored);
                        $onlineWallet->decrement('balance', $deduct);

                        DB::table('transaction_logs')->insert([
                            'user_id'          => $userId,
                            'from_wallet_type' => 'online',
                            'to_wallet_type'   => 'admin_correction',
                            'charge'           => 0,
                            'amount'           => $deduct,
                            'final_amount'     => $deduct,
                            'description'      => "Admin correction: deducted \${$deduct} from Online Wallet "
                                . "(offset for saving commission restoration on " . now()->toDateString() . ")",
                            'status'           => 'debit',
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }

                $repairedUsers++;
                $user = User::find($userId);
                $this->line("  ✓ User {$userId} ({$user->username}): restored \$" . number_format($userRestored, 4));
            }

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error during repair: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info("Repair complete: {$repairedRows} wallet rows corrected across {$repairedUsers} users.");
        $this->info('Total restored: $' . number_format($totalToRestore, 2));

        if (!$adjustOnline) {
            $this->comment('Note: online wallet balances were NOT adjusted. Use --adjust-online to also deduct from online wallets.');
        }

        return 0;
    }
}
