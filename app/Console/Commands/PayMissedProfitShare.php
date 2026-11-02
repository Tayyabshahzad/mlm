<?php

namespace App\Console\Commands;

use App\Models\TransactionLog;
use App\Models\Wallet;
use App\Services\ProfitShareAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pays members the profit share they should have received under the rules
 * (standard tree, level target met) but did not — either nobody was paid or it
 * went to someone else. Amounts come from ProfitShareAuditService.
 *
 * The amount is credited to the member's profit share wallet as one
 * profit_share row (wallet_src profit_share_audit_missed), so running again never
 * pays the same amount twice.
 */
class PayMissedProfitShare extends Command
{
    protected $signature = 'profit-share:pay-missed
        {--apply : Pay the members (without this it is a dry run)}
        {--user=* : Only these usernames}
        {--exclude=* : Skip these usernames}
        {--force : Do not ask for confirmation}';

    protected $description = 'Report (and with --apply pay) profit share members should have received but did not';

    public function handle(ProfitShareAuditService $audit): int
    {
        $only = array_map('strtolower', (array) $this->option('user'));
        $exclude = array_map('strtolower', (array) $this->option('exclude'));

        $this->info('Checking all profit share entries on DB "' . DB::connection()->getDatabaseName() . '" ...');
        $result = $audit->run();

        $paidBefore = DB::table('wallets')->where('wallet_type', 'profit_share')
            ->where('wallet_src', ProfitShareAuditService::MISSED_SRC)
            ->selectRaw('user_id, SUM(total_amount) t')->groupBy('user_id')->pluck('t', 'user_id');

        $plan = [];
        foreach ($result['users'] as $id => $p) {
            if ($p['under'] <= 0.004) continue;
            $uname = strtolower($p['username']);
            if ($only && !in_array($uname, $only, true)) continue;
            if (in_array($uname, $exclude, true)) continue;

            $done = round((float) ($paidBefore[$id] ?? 0), 2);
            $plan[$id] = $p + ['done' => $done, 'todo' => round(max(0, $p['under'] - $done), 2)];
        }
        uasort($plan, fn ($a, $b) => $b['todo'] <=> $a['todo']);

        $why = function (array $r) {
            arsort($r);
            return implode(' / ', array_map(fn ($k, $v) => sprintf('%s $%.2f', $k, $v), array_keys($r), $r));
        };
        $this->table(['Username', 'Missed', 'Already paid', 'To pay', 'Why', 'Period'],
            array_map(fn ($p) => [$p['username'], '$' . number_format($p['under'], 2), '$' . number_format($p['done'], 2), '$' . number_format($p['todo'], 2), $why($p['reasons']['under']), implode(' → ', $p['period']['under'])], $plan));

        $total = round(array_sum(array_column($plan, 'todo')), 2);
        $this->line('Total to pay: $' . number_format($total, 2));

        if (!$this->option('apply')) {
            $this->comment('Dry run — nothing was changed. Add --apply to pay.');
            return self::SUCCESS;
        }

        $todo = array_filter($plan, fn ($p) => $p['todo'] > 0.004);
        if (!$todo) {
            $this->info('Nothing to pay.');
            return self::SUCCESS;
        }
        if (!$this->option('force') && !$this->confirm('Pay $' . number_format($total, 2) . ' to ' . count($todo) . ' member(s)?')) {
            return self::FAILURE;
        }

        foreach ($todo as $id => $p) {
            DB::transaction(function () use ($id, $p, $why) {
                Wallet::create([
                    'user_id'          => $id,
                    'wallet_type'      => 'profit_share',
                    'balance'          => $p['todo'],
                    'total_amount'     => $p['todo'],
                    'commission_type'  => 'profit_share',
                    'level'            => '0',
                    'percentage'       => 0,
                    'wallet_src'       => ProfitShareAuditService::MISSED_SRC,
                    'transaction_type' => 'credit',
                    'description'      => sprintf('Missed profit share paid (%s to %s): %s', $p['period']['under'][0], $p['period']['under'][1], $why($p['reasons']['under'])),
                ]);
                TransactionLog::create([
                    'user_id'          => $id,
                    'from_wallet_type' => 'profit_share_adjustment',
                    'to_wallet_type'   => 'profit_share',
                    'amount'           => $p['todo'],
                    'charge'           => 0,
                    'final_amount'     => $p['todo'],
                    'description'      => sprintf('Missed profit share paid to your Profit Share wallet: $%.2f', $p['todo']),
                    'status'           => 'credit',
                ]);
            });
            $this->line("✔ {$p['username']}: paid $" . number_format($p['todo'], 2));
        }
        $this->info('Done.');

        return self::SUCCESS;
    }
}
