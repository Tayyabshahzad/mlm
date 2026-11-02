<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Read-only audit of every profit_share entry ever paid, checked against the
 * client-confirmed rules:
 *   - only the STANDARD referral tree (never saving/committee)
 *   - target = ACTIVE members (holding an investment) at that EXACT level:
 *     L1 10, L2 50, L3 150, L4 400, L5 1000, L6 2000, L7 4000
 *   - one payment per upline, per level, per ROI entry
 *
 * The rate is the one that was in force at the time (7%/6% old, 3.5%/3% new);
 * rate changes are not treated as overpayments.
 */
class ProfitShareAuditService
{
    public const REQ  = [1 => 10, 2 => 50, 3 => 150, 4 => 400, 5 => 1000, 6 => 2000, 7 => 4000];
    private const BASE = [1 => 3.5, 2 => 3.0, 3 => 2.5, 4 => 2.0, 5 => 1.5, 6 => 1.0, 7 => 0.5];
    private const HIGH = [1 => 7.0, 2 => 6.0, 3 => 5.0, 4 => 4.0, 5 => 3.0, 6 => 2.0, 7 => 1.0];
    private const SAVING_TREE_FROM = '2026-04-07';

    /** wallet_src used by the reversal command (also used to find what was already reversed) */
    public const REVERSAL_SRC = 'profit_share_audit';

    private array $users = [];
    private array $activeFrom = [];
    private array $anc = [];
    private array $desc = [];
    private array $savAnc = [];
    private array $systemCount = [];
    private array $eligCache = [];
    private array $obsRate = [];
    private array $lines = [];

    /**
     * @return array{users: array<int, array>, lines: array, totals: array}
     */
    public function run(): array
    {
        $this->load();

        $roi = [];
        foreach (DB::table('wallets')->where('wallet_type', 'roi')
            ->selectRaw('user_id, DATE(created_at) d, SUM(total_amount) amt, MAX(created_at) ts, COUNT(*) n')
            ->groupBy('user_id', DB::raw('DATE(created_at)'))->get() as $r) {
            $roi[$r->user_id][$r->d] = ['amt' => (float) $r->amt, 'ts' => $r->ts, 'n' => (int) $r->n];
        }

        $ps = [];
        $psDays = [];
        foreach (DB::table('wallets')->where('wallet_type', 'profit_share')->orderBy('id')
            ->get(['id', 'user_id', 'wallet_from', 'level', 'total_amount', 'percentage', 'created_at']) as $w) {
            $day = substr($w->created_at, 0, 10);
            $ps[$w->wallet_from][$day][$w->user_id][(int) $w->level][] = $w;
            $psDays[$day] = true;
            if ((float) $w->percentage != 0.42) {
                $this->obsRate[$w->user_id][$day][(int) $w->level] = (float) $w->percentage;
            }
        }

        $events = [];
        foreach ($ps as $d => $days) foreach ($days as $day => $_) $events["$d|$day"] = [$d, $day];
        foreach ($roi as $d => $days) foreach ($days as $day => $_) if (isset($psDays[$day])) $events["$d|$day"] = [$d, $day];

        $okAmount = 0.0;
        foreach ($events as [$d, $day]) {
            $roiAmt = $roi[$d][$day]['amt'] ?? 0.0;
            $nRoi = $roi[$d][$day]['n'] ?? 1;
            $ts = $roi[$d][$day]['ts'] ?? ($day . ' 23:59:59');
            $paid = $ps[$d][$day] ?? [];

            $expected = [];
            if ($roiAmt > 0) {
                for ($L = 1; $L <= 7; $L++) {
                    $a = $this->anc[$d][$L] ?? null;
                    if ($a && $this->eligible($a, $L, $ts)['ok']) $expected["$a|$L"] = true;
                }
            }

            $wrongAtLevel = [];
            foreach ($paid as $r => $byLevel) {
                foreach ($byLevel as $L => $rows) {
                    if ($roiAmt <= 0) {
                        foreach ($rows as $w) $this->add('over', $r, $day, $d, $L, (float) $w->total_amount, 'Paid without ROI that day (extra run)');
                        continue;
                    }
                    if (isset($expected["$r|$L"])) {
                        $allowed = array_slice($rows, 0, $nRoi);
                        $paidSum = array_sum(array_map(fn ($w) => (float) $w->total_amount, $allowed));
                        $should = round($roiAmt * (float) $allowed[0]->percentage / 100, 2);
                        // only clear anomalies: ROI rows were edited later, so small gaps are not reliable
                        if ($paidSum > $should * 1.5 + 0.011 * count($allowed)) {
                            $this->add('over', $r, $day, $d, $L, $paidSum - $should, 'Amount far above % of ROI');
                            $okAmount += $should;
                        } else {
                            $okAmount += $paidSum;
                        }
                        foreach (array_slice($rows, $nRoi) as $w) $this->add('over', $r, $day, $d, $L, (float) $w->total_amount, 'Paid more than once for same ROI');
                        continue;
                    }
                    foreach ($rows as $i => $w) {
                        $amt = (float) $w->total_amount;
                        if ($i > 0) { $this->add('over', $r, $day, $d, $L, $amt, 'Paid more than once for same ROI'); continue; }
                        $stdA = $this->anc[$d][$L] ?? null;
                        if ($stdA == $r) {
                            $e = $this->eligible($r, $L, $ts);
                            $why = $e['target'] ? "Target not met (L{$L} needs " . self::REQ[$L] . ')' : 'Receiver had no investment';
                            $this->add('over', $r, $day, $d, $L, $amt, $why, "had {$e['n']}");
                        } else {
                            if (($this->savAnc[$d][$L] ?? null) == $r) $why = 'Wrong person: saving-tree upline';
                            elseif ($day < self::SAVING_TREE_FROM) $why = 'Wrong person: old tree (before saving plan)';
                            else $why = 'Wrong person: not standard upline at this level';
                            $this->add('over', $r, $day, $d, $L, $amt, $why, $stdA ? 'Should have gone to ' . $this->name($stdA) : 'No standard upline at this level');
                            $wrongAtLevel[$L][$r] = true;
                        }
                    }
                }
            }

            foreach ($expected as $k => $_) {
                [$a, $L] = array_map('intval', explode('|', $k));
                if (!empty($paid[$a][$L])) continue;
                $amt = round($roiAmt * $this->rateFor($a, $L, $day) / 100, 2);
                if ($amt <= 0) continue;
                $instead = isset($wrongAtLevel[$L]) ? 'Paid to ' . implode(', ', array_map(fn ($x) => $this->name($x), array_keys($wrongAtLevel[$L]))) . ' instead' : 'Not paid';
                $this->add('under', $a, $day, $d, $L, $amt, $instead);
            }
        }

        return ['users' => $this->perUser(), 'lines' => $this->lines, 'totals' => ['correct' => round($okAmount, 2)]];
    }

    // ------------------------------------------------------------------

    private function load(): void
    {
        foreach (DB::table('users')->get(['id', 'username', 'name', 'account_type', 'user_plan', 'deleted_at', 'roi_eligible_investment_amount', 'can_login', 'blocked']) as $u) {
            $this->users[$u->id] = $u;
        }
        // active (has investment) from the first investment or first ROI, whichever is earlier
        foreach (DB::table('user_investments')->selectRaw('user_id, MIN(created_at) f')->groupBy('user_id')->get() as $r) {
            $this->activeFrom[$r->user_id] = $r->f;
        }
        foreach (DB::table('wallets')->where('wallet_type', 'roi')->selectRaw('user_id, MIN(created_at) f')->groupBy('user_id')->get() as $r) {
            if (!isset($this->activeFrom[$r->user_id]) || $r->f < $this->activeFrom[$r->user_id]) $this->activeFrom[$r->user_id] = $r->f;
        }
        foreach (DB::table('referral_trees')->get() as $r) {
            if ($r->tree_type === 'standard') {
                $this->anc[$r->descendant_id][$r->level] = $r->ancestor_id;
                $this->desc[$r->ancestor_id][$r->level][] = $r->descendant_id;
            } else {
                $this->savAnc[$r->descendant_id][$r->level] = $r->ancestor_id;
            }
            $u = $this->users[$r->descendant_id] ?? null;
            if ($u && $u->can_login && !$u->blocked) {
                $this->systemCount[$r->ancestor_id][$r->level] = ($this->systemCount[$r->ancestor_id][$r->level] ?? 0) + 1;
            }
        }
    }

    private function isActiveAt(int $id, string $ts): bool
    {
        if (!isset($this->activeFrom[$id]) || $this->activeFrom[$id] > $ts) return false;
        $del = $this->users[$id]->deleted_at ?? null;

        return !$del || $del > $ts;
    }

    private function eligible(int $a, int $L, string $ts): array
    {
        $k = "$a|$L|" . substr($ts, 0, 10);
        if (!isset($this->eligCache[$k])) {
            if (!$this->isActiveAt($a, $ts)) {
                $this->eligCache[$k] = ['ok' => false, 'target' => false, 'n' => 0];
            } else {
                $n = 0;
                foreach ($this->desc[$a][$L] ?? [] as $d) if ($this->isActiveAt($d, $ts)) $n++;
                $this->eligCache[$k] = ['ok' => $n >= self::REQ[$L], 'target' => true, 'n' => $n];
            }
        }

        return $this->eligCache[$k];
    }

    private function rateFor(int $a, int $L, string $day): float
    {
        if (isset($this->obsRate[$a][$day][$L])) return $this->obsRate[$a][$day][$L];
        $highUntil = ($this->users[$a]->user_plan ?? 'standard') === 'vip' ? '2026-03-24' : '2026-09-10';

        return ($day >= '2025-04-26' && $day < $highUntil) ? self::HIGH[$L] : self::BASE[$L];
    }

    private function add(string $type, int $user, string $day, int $from, int $L, float $amt, string $reason, string $note = ''): void
    {
        $this->lines[] = compact('type', 'user', 'day', 'from', 'L', 'amt', 'reason', 'note');
    }

    public function name(int $id): string
    {
        $u = $this->users[$id] ?? null;

        return $u ? ($u->username ?: $u->name) : "#$id";
    }

    /** Current active members per level (correct rules) vs what the old code counted */
    public function levelText(int $a): string
    {
        $parts = [];
        for ($L = 1; $L <= 7; $L++) {
            $n = 0;
            foreach ($this->desc[$a][$L] ?? [] as $d) {
                $u = $this->users[$d] ?? null;
                if ($u && !$u->deleted_at && (float) $u->roi_eligible_investment_amount > 0 && in_array($u->account_type, ['standard_investment', null], true)) $n++;
            }
            $sys = $this->systemCount[$a][$L] ?? 0;
            if ($n || $sys) {
                $parts[] = "L{$L}: {$n}/" . self::REQ[$L] . ($sys != $n ? " (old code counted {$sys})" : '') . ($n >= self::REQ[$L] ? ' ✔' : '');
            }
        }

        return $parts ? implode(' | ', $parts) : 'No members';
    }

    private function perUser(): array
    {
        $per = [];
        foreach ($this->lines as $l) {
            $p = &$per[$l['user']];
            $p ??= ['over' => 0.0, 'under' => 0.0, 'reasons' => ['over' => [], 'under' => []], 'first' => $l['day'], 'last' => $l['day']];
            $p[$l['type']] += $l['amt'];
            $p['reasons'][$l['type']][$l['reason']] = ($p['reasons'][$l['type']][$l['reason']] ?? 0) + $l['amt'];
            $p['first'] = min($p['first'], $l['day']);
            $p['last'] = max($p['last'], $l['day']);
            unset($p);
        }
        foreach ($per as $id => &$p) {
            $p['over'] = round($p['over'], 2);
            $p['under'] = round($p['under'], 2);
            $p['username'] = $this->name($id);
            $p['name'] = $this->users[$id]->name ?? '';
            $p['plan'] = $this->users[$id]->user_plan ?? '';
            $p['levels'] = $this->levelText($id);
        }

        return $per;
    }
}
