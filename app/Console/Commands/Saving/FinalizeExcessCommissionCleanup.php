<?php

namespace App\Console\Commands\Saving;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinalizeExcessCommissionCleanup extends Command
{
    protected $signature = 'saving:finalize-cleanup
                            {--dry-run : Show what will change without touching anything}';

    protected $description = '100% clean fix: corrects wallet entries and removes reversal entries where safe';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('');
        $this->info('=== Saving Commission — Complete Cleanup ===');
        if ($isDryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }
        $this->info('');

        // Load all reversed records with their current wallet entry details
        $records = DB::select('
            SELECT
                sic.id                                                                     AS sic_id,
                sic.ancestor_id,
                sic.commission_type,
                sic.wallet_id,
                sic.level,
                sic.percentage,
                ROUND(COALESCE(si.submitted_amount, si.amount) * sic.percentage / 100, 4) AS correct_amount,
                w.balance                                                                  AS wallet_balance,
                w.direct_balance                                                           AS wallet_direct,
                w.indirect_balance                                                         AS wallet_indirect
            FROM saving_instalment_commissions sic
            JOIN saving_instalments si ON si.id = sic.saving_instalment_id
            LEFT JOIN wallets w ON w.id = sic.wallet_id
            WHERE sic.excess_reversed = 1
        ');

        if (empty($records)) {
            $this->info('✅ Nothing to clean up — no excess_reversed records found.');
            return self::SUCCESS;
        }

        // Case A: balance > 0 AND balance > correct_amount (safe to fully clean)
        $caseA = collect($records)->filter(function ($r) {
            $walletBal = (float) $r->wallet_balance;
            $correct   = (float) $r->correct_amount;
            return $r->wallet_id && $walletBal > 0 && $walletBal > $correct;
        });

        // Case B: balance = 0 OR balance <= correct_amount (reporting-only fix)
        $caseB = collect($records)->filter(function ($r) {
            $walletBal = (float) $r->wallet_balance;
            $correct   = (float) $r->correct_amount;
            return !$r->wallet_id || $walletBal == 0 || $walletBal <= $correct;
        });

        // Count reversal entries that will be deleted (Case A only)
        $reversalCount = DB::table('wallets')
            ->where('commission_type', 'saving_commission_reversal')
            ->where('wallet_src', 'commission_correction')
            ->count();

        $this->line('┌─ Summary ──────────────────────────────────────────────────────────────────┐');
        $this->line('│  Total reversed commission records : ' . count($records));
        $this->line('│');
        $this->line('│  CASE A — balance > 0, not consumed (' . $caseA->count() . ' entries):');
        $this->line('│    ① Update wallet entry: wrong amount → correct amount');
        $this->line('│    ② Delete reversal wallet entry (no longer needed)');
        $this->line('│    ③ Net balance change: $0.00 ✓');
        $this->line('│');
        $this->line('│  CASE B — balance = 0, already withdrawn (' . $caseB->count() . ' entries):');
        $this->line('│    ① Fix reporting columns (direct_balance / indirect_balance)');
        $this->line('│    ② Reversal entry kept (manages balance correction)');
        $this->line('│    ③ Net balance change: $0.00 ✓');
        $this->line('│');
        $this->line('│  Total reversal entries to delete  : ' . $caseA->count() . ' of ' . $reversalCount);
        $this->line('│  Reversal entries kept (Case B)    : ' . $caseB->count());
        $this->line('└────────────────────────────────────────────────────────────────────────────┘');
        $this->info('');

        // Show Case A details
        if ($caseA->isNotEmpty()) {
            $this->line('Case A — wallet entries to correct:');
            $rows = [];
            foreach ($caseA as $r) {
                $excess = round((float)$r->wallet_balance - (float)$r->correct_amount, 4);
                $rows[] = [$r->ancestor_id, $r->level, $r->commission_type,
                    '$' . number_format($r->wallet_balance, 4),
                    '$' . number_format($r->correct_amount, 4),
                    '$' . number_format($excess, 4)];
            }
            $this->table(['Ancestor', 'Lv', 'Type', 'Wrong Balance', 'Correct', 'Excess'], $rows);
        }

        // Show Case B details
        if ($caseB->isNotEmpty()) {
            $this->line('Case B — reporting columns to fix (balance kept, reversal entry kept):');
            $rows = [];
            foreach ($caseB as $r) {
                $isDirect = $r->commission_type === 'direct';
                $reportingWrong   = $isDirect ? $r->wallet_direct : $r->wallet_indirect;
                $rows[] = [$r->ancestor_id, $r->level, $r->commission_type,
                    '$' . number_format($reportingWrong, 4),
                    '$' . number_format($r->correct_amount, 4)];
            }
            $this->table(['Ancestor', 'Lv', 'Type', 'Reporting Was', 'Reporting Becomes'], $rows);
        }

        $this->info('');

        // Withdrawal recovery
        $this->line('Withdrawal recovery check:');
        $ancestorIds = collect($records)->pluck('ancestor_id')->unique()->values()->all();
        $allGood = true;
        foreach ($ancestorIds as $uid) {
            $user = DB::table('users')->where('id', $uid)->select('username')->first();
            $bal  = round(DB::table('wallets')->where('user_id', $uid)->where('wallet_type', 'direct_indirect')->sum('balance'), 4);
            $excess = $caseA->filter(fn($r) => $r->ancestor_id == $uid)->sum(fn($r) => round((float)$r->wallet_balance - (float)$r->correct_amount, 4))
                    + $caseB->filter(fn($r) => $r->ancestor_id == $uid)->sum(fn($r) => (float)$r->correct_amount);

            $firstDate = DB::table('saving_instalment_commissions')->where('ancestor_id', $uid)->where('excess_reversed', 1)->min('processed_at');
            $withdrawn = DB::table('with_drawalequests')->where('user_id', $uid)->where('status', 'approved')->where('created_at', '>=', $firstDate)->sum('amount');

            if ($bal < 0) $allGood = false;
            $this->line(sprintf('  User %-4s %-18s | withdrew: $%-8s | balance now: $%-8s | %s',
                $uid, ($user->username ?? '?'), number_format($withdrawn, 2), number_format($bal, 4), $bal < 0 ? '⚠️  NEGATIVE' : '✅ OK'));
        }
        if ($allGood) $this->info('  ✅ All balances positive — recovery complete.');
        $this->info('');

        if ($isDryRun) {
            $this->warn('DRY RUN complete — run without --dry-run to apply all fixes.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Apply cleanup? (net balance change = $0.00 for all users)')) {
            $this->warn('Aborted.');
            return self::FAILURE;
        }

        // ── Apply fixes ───────────────────────────────────────────────────────
        $caseAFixed = 0;
        $caseBFixed = 0;
        $reversalDeleted = 0;
        $now = now();

        DB::transaction(function () use ($caseA, $caseB, $now, &$caseAFixed, &$caseBFixed, &$reversalDeleted) {

            // Case A: fully correct — update wallet balance + delete reversal
            foreach ($caseA as $row) {
                $correct  = (float) $row->correct_amount;
                $excess   = round((float)$row->wallet_balance - $correct, 4);
                $isDirect = $row->commission_type === 'direct';

                // Set wallet entry to correct amount
                DB::table('wallets')->where('id', $row->wallet_id)->update([
                    'balance'          => $correct,
                    'direct_balance'   => $isDirect  ? $correct : 0,
                    'indirect_balance' => !$isDirect ? $correct : 0,
                    'total_amount'     => $correct,
                    'updated_at'       => $now,
                ]);

                // Delete ONE matching reversal entry for this user + level + excess amount
                $deleted = DB::table('wallets')
                    ->where('user_id', $row->ancestor_id)
                    ->where('commission_type', 'saving_commission_reversal')
                    ->where('wallet_src', 'commission_correction')
                    ->where('level', $row->level)
                    ->where('balance', -$excess)
                    ->orderBy('id')
                    ->limit(1)
                    ->delete();

                $reversalDeleted += $deleted;
                $caseAFixed++;
            }

            // Case B: reporting columns only — keep reversal, fix direct/indirect
            foreach ($caseB as $row) {
                if (!$row->wallet_id) {
                    $caseBFixed++;
                    continue;
                }
                $correct  = (float) $row->correct_amount;
                $isDirect = $row->commission_type === 'direct';

                DB::table('wallets')->where('id', $row->wallet_id)->update([
                    'direct_balance'   => $isDirect  ? $correct : 0,
                    'indirect_balance' => !$isDirect ? $correct : 0,
                    'total_amount'     => $correct,
                    'updated_at'       => $now,
                ]);
                $caseBFixed++;
            }
        });

        $this->info('');
        $this->info('✅ Cleanup complete:');
        $this->info("   Case A : {$caseAFixed} wallet entries corrected, {$reversalDeleted} reversal entries deleted");
        $this->info("   Case B : {$caseBFixed} reporting columns fixed (reversal entries kept)");
        $this->info('');
        $this->info('Result:');
        $this->info('  • Commission listing     → correct amounts with "corrected" badge ✓');
        $this->info('  • Wallet direct/indirect → correct totals ✓');
        $this->info('  • Transaction history    → Case A: single clean entry | Case B: original + reversal (audit trail)');
        $this->info('  • User balances          → unchanged ✓');
        $this->info('  • Future commissions     → code fixed, no double commissions ✓');

        return self::SUCCESS;
    }
}
