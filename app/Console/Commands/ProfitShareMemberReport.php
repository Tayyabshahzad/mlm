<?php

namespace App\Console\Commands;

use App\Services\ProfitShareAuditService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Very simple Excel report of the profit share correction, written for people
 * with no technical background. Read-only: it only reads the audit and the
 * take-back / missed-payment entries already made.
 */
class ProfitShareMemberReport extends Command
{
    protected $signature = 'profit-share:member-report {--user=* : Only these usernames}';

    protected $description = 'Very simple Excel report explaining each member\'s profit share correction';

    private const REQ = ProfitShareAuditService::REQ;

    public function handle(ProfitShareAuditService $audit): int
    {
        $this->info('Reading profit share history on DB "' . DB::connection()->getDatabaseName() . '" ...');
        $result = $audit->run();
        $only = array_map('strtolower', (array) $this->option('user'));

        // what was actually done
        $takenBack = DB::table('wallets')->where('wallet_type', 'profit_share_reversal')->where('wallet_src', ProfitShareAuditService::REVERSAL_SRC)
            ->selectRaw('user_id, SUM(-total_amount) t')->groupBy('user_id')->pluck('t', 'user_id');
        $fromOnline = DB::table('wallets')->where('wallet_type', 'online')->where('wallet_src', ProfitShareAuditService::REVERSAL_SRC)
            ->selectRaw('user_id, SUM(-total_amount) t')->groupBy('user_id')->pluck('t', 'user_id');
        $given = DB::table('wallets')->where('wallet_type', 'profit_share')->where('wallet_src', ProfitShareAuditService::MISSED_SRC)
            ->selectRaw('user_id, SUM(total_amount) t')->groupBy('user_id')->pluck('t', 'user_id');
        $online = DB::table('wallets')->where('wallet_type', 'online')->selectRaw('user_id, SUM(balance) b')->groupBy('user_id')->pluck('b', 'user_id');

        // simple groups per member: level + kind of mistake
        $groups = [];
        foreach ($result['lines'] as $l) {
            $kind = $this->kind($l['type'], $l['reason']);
            $g = &$groups[$l['user']][$l['type'] . '|' . $l['L'] . '|' . $kind];
            $g ??= ['type' => $l['type'], 'kind' => $kind, 'L' => $l['L'], 'amt' => 0.0, 'to' => $l['day'], 'max' => 0];
            $g['amt'] += $l['amt'];
            $g['to'] = max($g['to'], $l['day']);
            if (str_starts_with($l['note'], 'had ')) $g['max'] = max($g['max'], (int) substr($l['note'], 4));
            unset($g);
        }

        $members = array_filter($result['users'], fn ($p) => $p['over'] > 0.004 || $p['under'] > 0.004);
        if ($only) $members = array_filter($members, fn ($p) => in_array(strtolower($p['username']), $only, true));
        uasort($members, fn ($a, $b) => strcasecmp($a['username'], $b['username']));

        $book = new Spreadsheet();

        // ── Sheet 1: one line per member ──
        $sum = $book->getActiveSheet()->setTitle('All Members');
        $sum->fromArray([
            ['Profit Share Correction — ' . now()->format('d M Y')],
            ['We fixed profit share mistakes. Red = money you got by mistake (we took it back). Green = money we owed you (we gave it to you).'],
            [''],
            ['Member', 'Name', 'Got by mistake (taken back)', 'We owed you (given to you)', 'Online wallet now'],
        ], null, 'A1');
        $sum->getStyle('A1')->getFont()->setBold(true)->setSize(15);
        $sum->getStyle('A2')->getFont()->setItalic(true);
        $r = 5;
        foreach ($members as $id => $p) {
            $sum->fromArray([$p['username'], $p['name'], $p['over'] > 0.004 ? $p['over'] : null, $p['under'] > 0.004 ? $p['under'] : null, round((float) ($online[$id] ?? 0), 2)], null, "A{$r}", true);
            $r++;
        }
        $sum->getStyle('A4:E4')->getFont()->setBold(true);
        $sum->getStyle('A4:E4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEE5FF');
        $sum->getStyle("C5:C{$r}")->getFont()->getColor()->setRGB('C62828');
        $sum->getStyle("D5:D{$r}")->getFont()->getColor()->setRGB('2E7D32');
        $sum->getStyle("C5:E{$r}")->getNumberFormat()->setFormatCode('"$"#,##0.00;-"$"#,##0.00');
        foreach (['A' => 18, 'B' => 30, 'C' => 26, 'D' => 26, 'E' => 18] as $c => $w) $sum->getColumnDimension($c)->setWidth($w);
        $sum->mergeCells('A2:E2');
        $sum->getStyle('A2')->getAlignment()->setWrapText(true);
        $sum->getRowDimension(2)->setRowHeight(32);
        $sum->freezePane('A5');

        // ── Sheet 2: one simple box per member ──
        $det = $book->createSheet()->setTitle('Each Member');
        $det->getColumnDimension('A')->setWidth(95);
        $det->getColumnDimension('B')->setWidth(14);
        $r = 1;
        foreach ($members as $id => $p) {
            // name bar
            $det->setCellValue("A{$r}", $p['username'] . '  (' . $p['name'] . ')');
            $det->mergeCells("A{$r}:B{$r}");
            $det->getStyle("A{$r}")->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('FFFFFF');
            $det->getStyle("A{$r}:B{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6F3FE6');
            $r++;

            // your team today
            $det->setCellValue("A{$r}", 'Your team today:');
            $det->getStyle("A{$r}")->getFont()->setBold(true);
            $r++;
            foreach ($this->teamToday($p['levels']) as $line) {
                $det->setCellValue("A{$r}", '   ' . $line);
                $r++;
            }

            $rows = $groups[$id] ?? [];
            uasort($rows, fn ($a, $b) => [$a['type'], $a['L'], -$a['amt']] <=> [$b['type'], $b['L'], -$b['amt']]);

            if ($p['over'] > 0.004) {
                $r++;
                $det->setCellValue("A{$r}", 'Money you got by mistake (we took it back):');
                $det->getStyle("A{$r}")->getFont()->setBold(true)->getColor()->setRGB('C62828');
                $r++;
                foreach ($rows as $g) {
                    if ($g['type'] !== 'over' || $g['amt'] < 0.005) continue;
                    $det->fromArray(['   • ' . $this->sentence($g), round($g['amt'], 2)], null, "A{$r}", true);
                    $det->getStyle("B{$r}")->getFont()->getColor()->setRGB('C62828');
                    $r++;
                }
                $det->fromArray(['   Total taken back', $p['over']], null, "A{$r}", true);
                $det->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
                $r++;

                $onl = round((float) ($fromOnline[$id] ?? 0), 2);
                $ps = round((float) ($takenBack[$id] ?? 0) - $onl, 2);
                $det->setCellValue("A{$r}", '   We took $' . number_format($ps, 2) . ' from your Profit Share wallet'
                    . ($onl > 0 ? ' and $' . number_format($onl, 2) . ' from your Online wallet (Profit Share wallet did not have enough).' : '.'));
                $r++;
            }

            if ($p['under'] > 0.004) {
                $r++;
                $det->setCellValue("A{$r}", 'Money we owed you (we gave it to you):');
                $det->getStyle("A{$r}")->getFont()->setBold(true)->getColor()->setRGB('2E7D32');
                $r++;
                foreach ($rows as $g) {
                    if ($g['type'] !== 'under' || $g['amt'] < 0.005) continue;
                    $det->fromArray(['   • ' . $this->sentence($g), round($g['amt'], 2)], null, "A{$r}", true);
                    $det->getStyle("B{$r}")->getFont()->getColor()->setRGB('2E7D32');
                    $r++;
                }
                $det->fromArray(['   Total given to you (added to your Profit Share wallet)', $p['under']], null, "A{$r}", true);
                $det->getStyle("A{$r}:B{$r}")->getFont()->setBold(true);
                $r++;
            }

            $now = (float) ($online[$id] ?? 0);
            if ($now < -0.004) {
                $r++;
                $det->setCellValue("A{$r}", 'Your Online wallet is now -$' . number_format(abs($now), 2) . '. When you move money into your Online wallet, this will be cleared first.');
                $det->getStyle("A{$r}")->getFont()->setBold(true);
                $r++;
            }
            $r += 2;
        }
        $det->getStyle("B1:B{$r}")->getNumberFormat()->setFormatCode('"$"#,##0.00;-"$"#,##0.00');
        $det->getStyle("A1:B{$r}")->getAlignment()->setWrapText(true)->setVertical('top');

        // ── Sheet 3: the rule, in a few lines ──
        $rule = $book->createSheet()->setTitle('The Rule');
        $rule->fromArray([
            ['How profit share works'],
            [''],
            ['1. Your team members earn daily profit. You get a small part of it.'],
            ['2. Only your Standard plan team counts. The Saving / Committee plan team does not count.'],
            ['3. Only people with an investment count.'],
            ['4. You need enough people on each level:'],
            ['      Level 1 → 10 people'],
            ['      Level 2 → 50 people'],
            ['      Level 3 → 150 people'],
            ['      Level 4 → 400 people'],
            ['      Level 5 → 1,000 people'],
            ['      Level 6 → 2,000 people'],
            ['      Level 7 → 4,000 people'],
            ['5. Each level is counted on its own. People on Level 1 do not count for Level 2.'],
            ['6. You get it only once for each day.'],
        ], null, 'A1');
        $rule->getStyle('A1')->getFont()->setBold(true)->setSize(15);
        $rule->getColumnDimension('A')->setWidth(95);

        $book->setActiveSheetIndex(0);
        $dir = storage_path('app/private/reports');
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $file = $dir . '/profit-share-member-report-' . now()->format('Ymd-His') . '.xlsx';
        (new Xlsx($book))->save($file);

        $this->info(count($members) . ' member(s) in the report.');
        $this->info("Excel report: {$file}");

        return self::SUCCESS;
    }

    /** a few simple kinds instead of technical reasons */
    private function kind(string $type, string $reason): string
    {
        if ($type === 'under') return str_starts_with($reason, 'Paid to ') ? 'went_to_other' : 'not_paid';
        return match (true) {
            str_starts_with($reason, 'Target not met')           => 'not_enough_people',
            str_starts_with($reason, 'Receiver had no investment') => 'no_investment',
            str_starts_with($reason, 'Wrong person: saving-tree') => 'saving_team',
            str_starts_with($reason, 'Wrong person')              => 'not_your_team',
            str_starts_with($reason, 'Amount far above')          => 'too_much',
            default                                               => 'twice', // paid more than once / paid with no daily profit
        };
    }

    private function sentence(array $g): string
    {
        $L = $g['L'];
        $need = number_format(self::REQ[$L]);
        $until = Carbon::parse($g['to'])->format('d M Y');

        return match ($g['kind']) {
            'not_enough_people' => "Level {$L}: you need {$need} people. You had only " . ($g['max'] ?: 'a few') . " (until {$until}). So this was not your money.",
            'no_investment'     => "Level {$L}: you had no investment at that time. So this was not your money.",
            'saving_team'       => "Level {$L}: this came from your Saving / Committee team. Profit share comes only from your Standard team.",
            'not_your_team'     => "Level {$L}: this came from people who are not on your Level {$L}. So this was not your money.",
            'too_much'          => "Level {$L}: on one day the system paid you too much by mistake. We took back only the extra part.",
            'twice'             => "Level {$L}: the system paid you more than once for the same day. We took back the extra.",
            'went_to_other'     => "Level {$L}: you had enough people, but this money went to another member by mistake.",
            default             => "Level {$L}: you had enough people, but on some days you did not get your money.",
        };
    }

    /** "L1: 13/10 ✔ | L2: 22/50" → simple lines (first 3 levels only) */
    private function teamToday(string $levels): array
    {
        $out = [];
        foreach (explode(' | ', $levels) as $p) {
            if (!preg_match('/^L(\d): (\d+)\/(\d+)/', $p, $m) || (int) $m[1] > 3) continue;
            $ok = (int) $m[2] >= (int) $m[3];
            $out[] = "Level {$m[1]}: you have {$m[2]} people (need " . number_format((int) $m[3]) . ') → ' . ($ok ? 'YES, you get Level ' . $m[1] . ' profit share ✔' : 'NO, not yet ✘');
        }

        return $out ?: ['No team members with an investment yet.'];
    }
}
