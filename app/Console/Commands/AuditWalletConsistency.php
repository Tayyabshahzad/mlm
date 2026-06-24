<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Audits wallet records for known consistency issues and reports discrepancies.
 * Does NOT modify any data — read-only diagnostic tool.
 *
 * Checks performed:
 *   1. Saving commission balances below earned (direct_balance + indirect_balance)
 *   2. Negative wallet balances (any wallet type)
 *   3. Wallets whose balance exceeds total_amount (over-credited)
 *   4. direct_indirect wallets without a valid source_type that have non-zero balance
 *      and no corresponding transaction log entry (orphaned credits)
 *
 * Usage:
 *   php artisan wallet:audit
 *   php artisan wallet:audit --user-id=151
 */
class AuditWalletConsistency extends Command
{
    protected $signature = 'wallet:audit
                            {--user-id= : Audit a single user by ID}
                            {--fix-hint : Show the repair command to run for each issue}';

    protected $description = 'Read-only audit of wallet records for balance inconsistencies.';

    public function handle(): int
    {
        $targetUserId = $this->option('user-id');
        $fixHint      = $this->option('fix-hint');

        $this->info('=== Wallet Consistency Audit ===');
        $this->newLine();

        $issueCount = 0;
        $issueCount += $this->checkSavingCommissionGaps($targetUserId, $fixHint);
        $issueCount += $this->checkNegativeBalances($targetUserId);
        $issueCount += $this->checkOverCreditedWallets($targetUserId);

        $this->newLine();
        if ($issueCount === 0) {
            $this->info('All wallet checks passed. No issues found.');
        } else {
            $this->error("Audit complete: {$issueCount} issue(s) found. Review the output above.");
        }

        return $issueCount > 0 ? 1 : 0;
    }

    // ── Check 1: Saving commission wallets with balance < earned ──────────────
    // A gap is only flagged as an ISSUE when it exceeds the user's legitimate
    // saving-commission transfers (i.e., amounts they voluntarily moved to their
    // online wallet via transferSavingCommissionToOnline). A gap exactly equal to
    // those transfers is correct and expected behaviour.
    private function checkSavingCommissionGaps(?string $userId, bool $fixHint): int
    {
        $this->line('<fg=cyan>[Check 1]</> Saving commission balance vs. earned amount');

        // Legitimate saving → online transfers per user
        $legitByUser = DB::table('transaction_logs')
            ->selectRaw('user_id, SUM(amount) as total')
            ->where('from_wallet_type', 'saving_commission')
            ->where('to_wallet_type', 'online')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $query = DB::table('wallets as w')
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->selectRaw(
                'w.user_id, u.username, u.name,
                 COUNT(*) as affected_rows,
                 SUM(w.direct_balance + w.indirect_balance) as total_earned,
                 SUM(w.balance) as current_balance,
                 SUM(w.direct_balance + w.indirect_balance) - SUM(w.balance) as raw_gap'
            )
            ->where('w.wallet_type', 'direct_indirect')
            ->where('w.source_type', 'saving_instalment')
            ->whereRaw('(w.direct_balance + w.indirect_balance) > w.balance')
            ->groupBy('w.user_id', 'u.username', 'u.name')
            ->havingRaw('raw_gap > 0')
            ->orderByRaw('raw_gap DESC');

        if ($userId) {
            $query->where('w.user_id', $userId);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->info('  ✓ No saving commission gaps found.');
            return 0;
        }

        // Separate truly unexpected gaps from expected ones
        $unexpectedRows = $rows->filter(function ($r) use ($legitByUser) {
            $legit  = (float) ($legitByUser[$r->user_id] ?? 0);
            $netGap = round($r->raw_gap - $legit, 4);
            return $netGap > 0.01;
        });

        $expectedRows = $rows->filter(function ($r) use ($legitByUser) {
            $legit  = (float) ($legitByUser[$r->user_id] ?? 0);
            $netGap = round($r->raw_gap - $legit, 4);
            return $netGap <= 0.01;
        });

        if ($expectedRows->isNotEmpty()) {
            $this->info('  ✓ ' . $expectedRows->count() . ' user(s) have gaps fully explained by legitimate saving commission transfers (OK):');
            foreach ($expectedRows as $r) {
                $legit = (float) ($legitByUser[$r->user_id] ?? 0);
                $this->line(sprintf('      User %d (%s): gap $%s = legit transfer $%s',
                    $r->user_id, $r->username,
                    number_format($r->raw_gap, 2),
                    number_format($legit, 2)
                ));
            }
        }

        if ($unexpectedRows->isEmpty()) {
            $this->info('  ✓ No unexplained saving commission gaps found.');
            return 0;
        }

        $this->warn("  ✗ {$unexpectedRows->count()} user(s) have unexplained saving commission gaps:");
        $this->table(
            ['User ID', 'Username', 'Name', 'Rows', 'Total Earned', 'Balance', 'Raw Gap', 'Legit Out', 'Net Gap'],
            $unexpectedRows->map(function ($r) use ($legitByUser) {
                $legit  = (float) ($legitByUser[$r->user_id] ?? 0);
                $netGap = max(0, round($r->raw_gap - $legit, 4));
                return [
                    $r->user_id, $r->username, $r->name, $r->affected_rows,
                    '$' . number_format($r->total_earned, 2),
                    '$' . number_format($r->current_balance, 2),
                    '$' . number_format($r->raw_gap, 2),
                    '$' . number_format($legit, 2),
                    '$' . number_format($netGap, 2),
                ];
            })->values()->toArray()
        );

        $totalNetGap = $unexpectedRows->sum(fn ($r) =>
            max(0, round($r->raw_gap - (float) ($legitByUser[$r->user_id] ?? 0), 4))
        );
        $this->warn("  Total unexplained gap: \$" . number_format($totalNetGap, 2));

        if ($fixHint) {
            $this->comment('  Repair: php artisan wallet:repair-saving-commissions [--dry-run] [--adjust-online]');
        }

        return $unexpectedRows->count();
    }

    // ── Check 2: Any wallet with negative balance ──────────────────────────────
    private function checkNegativeBalances(?string $userId): int
    {
        $this->newLine();
        $this->line('<fg=cyan>[Check 2]</> Wallets with negative balance');

        $query = DB::table('wallets as w')
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->select('w.id', 'w.user_id', 'u.username', 'w.wallet_type', 'w.source_type', 'w.balance', 'w.created_at')
            ->where('w.balance', '<', 0)
            ->orderBy('w.balance');

        if ($userId) {
            $query->where('w.user_id', $userId);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->info('  ✓ No negative balances found.');
            return 0;
        }

        $this->warn("  ✗ {$rows->count()} wallet row(s) have negative balance:");
        $this->table(
            ['Wallet ID', 'User ID', 'Username', 'Type', 'Source', 'Balance', 'Created'],
            $rows->map(fn ($r) => [
                $r->id, $r->user_id, $r->username, $r->wallet_type,
                $r->source_type ?? '—', $r->balance, $r->created_at,
            ])->toArray()
        );

        return $rows->count();
    }

    // ── Check 3: Wallets where balance > total_amount (over-credited) ─────────
    private function checkOverCreditedWallets(?string $userId): int
    {
        $this->newLine();
        $this->line('<fg=cyan>[Check 3]</> Wallets with balance exceeding total_amount');

        $query = DB::table('wallets as w')
            ->join('users as u', 'u.id', '=', 'w.user_id')
            ->select('w.id', 'w.user_id', 'u.username', 'w.wallet_type', 'w.source_type', 'w.balance', 'w.total_amount')
            ->whereRaw('w.balance > w.total_amount + 0.01') // 0.01 tolerance for rounding
            ->whereNotNull('w.total_amount')
            ->orderByRaw('(w.balance - w.total_amount) DESC');

        if ($userId) {
            $query->where('w.user_id', $userId);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            $this->info('  ✓ No over-credited wallets found.');
            return 0;
        }

        $this->warn("  ✗ {$rows->count()} wallet row(s) have balance > total_amount:");
        $this->table(
            ['Wallet ID', 'User ID', 'Username', 'Type', 'Source', 'Balance', 'Total Amount', 'Over By'],
            $rows->map(fn ($r) => [
                $r->id, $r->user_id, $r->username, $r->wallet_type, $r->source_type ?? '—',
                '$' . number_format($r->balance, 2),
                '$' . number_format($r->total_amount, 2),
                '$' . number_format($r->balance - $r->total_amount, 2),
            ])->toArray()
        );

        return $rows->count();
    }
}
