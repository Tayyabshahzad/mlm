<?php

namespace App\Console\Commands;

use App\Models\TransactionLog;
use App\Models\Wallet;
use App\Services\ProfitShareAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Finds profit share paid against the rules (see ProfitShareAuditService) and,
 * with --apply, takes the extra amount back:
 *   1. from the member's profit share wallet balance (reduces `balance` on their
 *      profit_share rows, like a transfer does — original amounts stay untouched)
 *   2. the rest from the online wallet (a negative online row)
 *   3. anything still left only with --allow-negative (online wallet goes below 0)
 * Every reversal is recorded as a `profit_share_reversal` row (wallet_src
 * profit_share_audit) plus a transaction log, so running again never takes the
 * same amount twice.
 */
class ReverseProfitShareOverpayments extends Command
{
    protected $signature = 'profit-share:reverse-overpayments
        {--apply : Take the money back (without this it is a dry run)}
        {--user=* : Only these usernames}
        {--exclude=* : Skip these usernames (e.g. pending client decision)}
        {--allow-negative : Let the online wallet go negative when balances are not enough}
        {--force : Do not ask for confirmation}';

    protected $description = 'Report (and with --apply reverse) profit share paid against the rules; writes an Excel report';

    public function handle(ProfitShareAuditService $audit): int
    {
        $apply = (bool) $this->option('apply');
        $allowNegative = (bool) $this->option('allow-negative');
        $only = array_map('strtolower', (array) $this->option('user'));
        $exclude = array_map('strtolower', (array) $this->option('exclude'));

        $this->info('Checking all profit share entries on DB "' . DB::connection()->getDatabaseName() . '" ...');
        $result = $audit->run();

        $already = DB::table('wallets')->where('wallet_type', 'profit_share_reversal')
            ->where('wallet_src', ProfitShareAuditService::REVERSAL_SRC)
            ->selectRaw('user_id, SUM(-total_amount) t')->groupBy('user_id')->pluck('t', 'user_id');

        $plan = [];
        foreach ($result['users'] as $id => $p) {
            if ($p['over'] <= 0.004) continue;
            $uname = strtolower($p['username']);
            if ($only && !in_array($uname, $only, true)) continue;
            if (in_array($uname, $exclude, true)) continue;

            $done = round((float) ($already[$id] ?? 0), 2);
            $todo = round($p['over'] - $done, 2);
            $balances = $this->balances($id);
            [$fromPs, $fromOnline, $short] = $this->split($todo, $balances, $allowNegative);

            $plan[$id] = $p + compact('done', 'todo', 'fromPs', 'fromOnline', 'short') + $balances;
        }
        uasort($plan, fn ($a, $b) => $b['todo'] <=> $a['todo']);

        $this->table(
            ['Username', 'Extra paid', 'Already reversed', 'To reverse', 'From profit share', 'From online', 'Not recoverable', 'PS bal', 'Online bal', 'Online after'],
            array_map(fn ($p) => [
                $p['username'], $this->m($p['over']), $this->m($p['done']), $this->m($p['todo']), $this->m($p['fromPs']), $this->m($p['fromOnline']),
                $p['short'] > 0 ? $this->m($p['short']) : '-', $this->m($p['ps']), $this->m($p['online']), $this->m($p['online'] - $p['fromOnline']),
            ], $plan)
        );

        $sum = fn ($k) => round(array_sum(array_column($plan, $k)), 2);
        $this->line(sprintf('Total to reverse: $%s  (profit share wallet $%s, online wallet $%s, not recoverable $%s)',
            number_format($sum('todo'), 2), number_format($sum('fromPs'), 2), number_format($sum('fromOnline'), 2), number_format($sum('short'), 2)));

        $negative = array_filter($plan, fn ($p) => $p['fromOnline'] > 0.004 && $p['online'] - $p['fromOnline'] < -0.004);
        if ($negative) {
            $this->warn('Online wallet will go NEGATIVE for: ' . implode(', ', array_map(fn ($p) => $p['username'] . ' (' . $this->m($p['online'] - $p['fromOnline']) . ')', $negative)));
        }
        $short = array_filter($plan, fn ($p) => $p['short'] > 0.004);
        if ($short) {
            $this->warn('Not enough balance (use --allow-negative to take it anyway): ' . implode(', ', array_map(fn ($p) => $p['username'] . ' ' . $this->m($p['short']), $short)));
        }

        $file = $this->writeReport($audit, $result, $plan);
        $this->info("Excel report: {$file}");

        if (!$apply) {
            $this->comment('Dry run — nothing was changed. Add --apply to reverse.');
            return self::SUCCESS;
        }

        $todoPlan = array_filter($plan, fn ($p) => $p['fromPs'] + $p['fromOnline'] > 0.004);
        if (!$todoPlan) {
            $this->info('Nothing to reverse.');
            return self::SUCCESS;
        }
        if (!$this->option('force') && !$this->confirm('Reverse $' . number_format($sum('fromPs') + $sum('fromOnline'), 2) . ' from ' . count($todoPlan) . ' member(s)?')) {
            return self::FAILURE;
        }

        foreach ($todoPlan as $id => $p) {
            DB::transaction(fn () => $this->reverse($id, $p, $allowNegative));
            $this->line("✔ {$p['username']}: reversed " . $this->m($p['fromPs'] + $p['fromOnline']));
        }
        $this->info('Done.');

        return self::SUCCESS;
    }

    private function balances(int $userId): array
    {
        return [
            'ps'     => round((float) Wallet::where('user_id', $userId)->where('wallet_type', 'profit_share')->sum('balance'), 2),
            'online' => round((float) Wallet::where('user_id', $userId)->where('wallet_type', 'online')->sum('balance'), 2),
        ];
    }

    /** @return array{0: float, 1: float, 2: float} [from profit share, from online, not recoverable] */
    private function split(float $todo, array $bal, bool $allowNegative): array
    {
        if ($todo <= 0) return [0.0, 0.0, 0.0];
        $fromPs = round(min($todo, max(0, $bal['ps'])), 2);
        $rest = round($todo - $fromPs, 2);
        $fromOnline = $allowNegative ? $rest : round(min($rest, max(0, $bal['online'])), 2);

        return [$fromPs, $fromOnline, round($rest - $fromOnline, 2)];
    }

    private function reverse(int $userId, array $p, bool $allowNegative): void
    {
        // re-read balances under lock so a transfer at the same moment cannot slip through
        $psRows = Wallet::where('user_id', $userId)->where('wallet_type', 'profit_share')->where('balance', '>', 0)->orderBy('id')->lockForUpdate()->get();
        Wallet::where('user_id', $userId)->where('wallet_type', 'online')->lockForUpdate()->get();
        [$fromPs, $fromOnline] = $this->split($p['todo'], $this->balances($userId), $allowNegative);

        $left = $fromPs;
        foreach ($psRows as $row) {
            if ($left <= 0) break;
            $take = round(min((float) $row->balance, $left), 2);
            $row->balance = round((float) $row->balance - $take, 2);
            $row->save();
            $left = round($left - $take, 2);
        }

        $reasons = $p['reasons']['over'];
        arsort($reasons);
        $why = implode('; ', array_map(fn ($k, $v) => sprintf('%s $%.2f', $k, $v), array_keys($reasons), $reasons));
        $total = round($fromPs + $fromOnline, 2);

        if ($fromOnline > 0) {
            Wallet::create([
                'user_id'          => $userId,
                'wallet_type'      => 'online',
                'balance'          => -$fromOnline,
                'total_amount'     => -$fromOnline,
                'commission_type'  => 'Profit Share Reversal',
                'level'            => '-',
                'wallet_src'       => ProfitShareAuditService::REVERSAL_SRC,
                'transaction_type' => 'debit',
                'description'      => 'Profit share paid by mistake taken back (profit share wallet balance was not enough)',
            ]);
        }

        // record row: balance 0 so no wallet total changes; total_amount = amount taken back
        Wallet::create([
            'user_id'          => $userId,
            'wallet_type'      => 'profit_share_reversal',
            'balance'          => 0,
            'total_amount'     => -$total,
            'commission_type'  => 'profit_share_reversal',
            'level'            => '0',
            'wallet_src'       => ProfitShareAuditService::REVERSAL_SRC,
            'transaction_type' => 'debit',
            'description'      => sprintf('Profit share reversal $%.2f (profit share wallet $%.2f, online wallet $%.2f). %s', $total, $fromPs, $fromOnline, $why),
        ]);

        foreach (['profit_share' => $fromPs, 'online' => $fromOnline] as $wallet => $amt) {
            if ($amt <= 0) continue;
            TransactionLog::create([
                'user_id'          => $userId,
                'from_wallet_type' => $wallet,
                'to_wallet_type'   => 'profit_share_reversal',
                'amount'           => $amt,
                'charge'           => 0,
                'final_amount'     => $amt,
                'description'      => sprintf('Profit share paid by mistake taken back from %s wallet: $%.2f', $wallet === 'online' ? 'Online' : 'Profit Share', $amt),
                'status'           => 'debit',
            ]);
        }
    }

    private function m(float $v): string
    {
        return ($v < 0 ? '-$' : '$') . number_format(abs($v), 2);
    }

    private function writeReport(ProfitShareAuditService $audit, array $result, array $plan): string
    {
        $book = new Spreadsheet();
        $first = true;
        $sheet = function (string $title, array $head, array $rows) use ($book, &$first) {
            $s = $first ? $book->getActiveSheet() : $book->createSheet();
            $first = false;
            $s->setTitle($title);
            $s->fromArray($head, null, 'A1');
            if ($rows) $s->fromArray($rows, null, 'A2', true);
            $last = $s->getHighestColumn();
            $s->getStyle("A1:{$last}1")->getFont()->setBold(true);
            $s->freezePane('A2');
            foreach (range('A', $last) as $col) $s->getColumnDimension($col)->setAutoSize(true);
            $s->getStyle("A1:{$last}" . $s->getHighestRow())->getAlignment()->setWrapText(true)->setVertical('top');
        };
        $reasons = function (array $r) {
            arsort($r);
            return implode("\n", array_map(fn ($k, $v) => sprintf('%s: $%.2f', $k, $v), array_keys($r), $r));
        };

        $sheet('Overpaid (reverse)', ['Username', 'Name', 'Plan', 'Extra paid', 'Already reversed', 'To reverse', 'From profit share wallet', 'From online wallet', 'Not recoverable', 'Profit share balance', 'Online balance', 'Online after', 'Why', 'Active members now', 'Period'],
            array_values(array_map(fn ($p) => [$p['username'], $p['name'], $p['plan'], $p['over'], $p['done'], $p['todo'], $p['fromPs'], $p['fromOnline'], $p['short'], $p['ps'], $p['online'], round($p['online'] - $p['fromOnline'], 2), $reasons($p['reasons']['over']), $p['levels'], implode(' → ', $p['period']['over'] ?? [])], $plan)));

        $under = array_filter($result['users'], fn ($p) => $p['under'] > 0.004);
        uasort($under, fn ($a, $b) => $b['under'] <=> $a['under']);
        $sheet('Underpaid (decide)', ['Username', 'Name', 'Plan', 'Missed amount', 'Why', 'Active members now', 'Period'],
            array_values(array_map(fn ($p) => [$p['username'], $p['name'], $p['plan'], $p['under'], $reasons($p['reasons']['under']), $p['levels'], implode(' → ', $p['period']['under'] ?? [])], $under)));

        $agg = [];
        foreach ($result['lines'] as $l) {
            $k = "{$l['type']}|{$l['user']}|" . substr($l['day'], 0, 7) . "|{$l['L']}|{$l['reason']}";
            $agg[$k] ??= ['type' => $l['type'] === 'over' ? 'Extra' : 'Missed', 'user' => $audit->name($l['user']), 'month' => substr($l['day'], 0, 7), 'L' => $l['L'], 'reason' => $l['reason'], 'amt' => 0, 'n' => 0, 'had' => [], 'notes' => []];
            $agg[$k]['amt'] += $l['amt'];
            $agg[$k]['n']++;
            if (str_starts_with($l['note'], 'had ')) $agg[$k]['had'][] = (int) substr($l['note'], 4);
            elseif ($l['note']) $agg[$k]['notes'][$l['note']] = true;
        }
        usort($agg, fn ($a, $b) => [$a['type'], $a['user'], $a['month']] <=> [$b['type'], $b['user'], $b['month']]);
        $sheet('Detail by month', ['Type', 'Username', 'Month', 'Level', 'Reason', 'Amount', 'Entries', 'Note'],
            array_map(fn ($r) => [$r['type'], $r['user'], $r['month'], $r['L'], $r['reason'], round($r['amt'], 2), $r['n'],
                ($r['had'] ? 'Active members at that level then: ' . (min($r['had']) === max($r['had']) ? min($r['had']) : min($r['had']) . '–' . max($r['had'])) . '. ' : '') . implode('; ', array_slice(array_keys($r['notes']), 0, 5))], $agg));

        $dir = storage_path('app/private/reports');
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $file = $dir . '/profit-share-reverse-' . ($this->option('apply') ? 'apply' : 'dryrun') . '-' . now()->format('Ymd-His') . '.xlsx';
        (new Xlsx($book))->save($file);

        return $file;
    }
}
