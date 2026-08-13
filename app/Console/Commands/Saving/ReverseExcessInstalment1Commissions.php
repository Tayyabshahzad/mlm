<?php

namespace App\Console\Commands\Saving;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReverseExcessInstalment1Commissions extends Command
{
    protected $signature = 'saving:reverse-excess-commissions
                            {--dry-run : Preview what will be reversed without making changes}';

    protected $description = 'Reverse excess saving instalment #1 commissions caused by registration partial being double-counted in commission base';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('');
        $this->info('=== Saving Instalment #1 Excess Commission Reversal ===');
        if ($isDryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }
        $this->info('');

        // Step 1: Ensure excess_reversed column exists
        if (!Schema::hasColumn('saving_instalment_commissions', 'excess_reversed')) {
            if ($isDryRun) {
                $this->warn('Column excess_reversed does not exist yet — run migrations first.');
            } else {
                $this->line('Adding excess_reversed column to saving_instalment_commissions...');
                Schema::table('saving_instalment_commissions', function ($table) {
                    $table->boolean('excess_reversed')->default(false)->after('notes');
                });
                $this->info('Column added.');
            }
        }

        // Step 2: Find all affected commission records
        $affected = DB::select('
            SELECT
                sic.id            AS sic_id,
                sic.ancestor_id,
                sic.level,
                sic.commission_type,
                sic.percentage,
                sic.commission_amount                                                                AS paid,
                ROUND(COALESCE(si.submitted_amount, si.amount) * sic.percentage / 100, 4)          AS correct,
                ROUND(sic.commission_amount - (COALESCE(si.submitted_amount, si.amount) * sic.percentage / 100), 4) AS excess,
                sic.processed_at
            FROM saving_instalment_commissions sic
            JOIN saving_instalments si ON si.id = sic.saving_instalment_id
            WHERE sic.instalment_number = 1
              AND ROUND(sic.commission_amount * 100 / sic.percentage, 4) > COALESCE(si.submitted_amount, si.amount) + 0.001
              AND sic.excess_reversed = 0
        ');

        if (empty($affected)) {
            $this->info('✅ No excess commission records found. Nothing to reverse.');
            return self::SUCCESS;
        }

        $this->info("Found <comment>" . count($affected) . "</comment> excess commission records to reverse.");

        // Group by ancestor for display
        $byAncestor = collect($affected)->groupBy('ancestor_id');
        $grandTotal  = 0;

        $headers = ['Ancestor ID', 'Level', 'Type', 'Paid', 'Correct', 'Excess', 'Date'];
        $tableRows = [];
        foreach ($affected as $row) {
            $tableRows[] = [
                $row->ancestor_id,
                $row->level,
                $row->commission_type,
                '$' . number_format($row->paid, 4),
                '$' . number_format($row->correct, 4),
                '$' . number_format($row->excess, 4),
                substr($row->processed_at, 0, 10),
            ];
            $grandTotal += (float) $row->excess;
        }
        $this->table($headers, $tableRows);

        $this->info('');
        $this->info("Total excess to reverse: <comment>$" . number_format($grandTotal, 4) . "</comment>");
        $this->info('');

        // Show per-ancestor summary
        $this->line('Per-ancestor breakdown:');
        foreach ($byAncestor as $ancestorId => $records) {
            $user  = DB::table('users')->where('id', $ancestorId)->select('username', 'name')->first();
            $total = $records->sum('excess');
            $bal   = DB::table('wallets')->where('user_id', $ancestorId)->where('wallet_type', 'direct_indirect')->sum('balance');
            $after = round($bal - $total, 4);
            $this->line(sprintf(
                '  User %-4s %-20s | excess: $%-8s | balance now: $%-8s | after reversal: $%s%s',
                $ancestorId,
                ($user->username ?? 'unknown'),
                number_format($total, 4),
                number_format($bal, 4),
                number_format($after, 4),
                $after < 0 ? ' ⚠️  WILL GO NEGATIVE' : ''
            ));
        }

        if ($isDryRun) {
            $this->info('');
            $this->warn('DRY RUN complete — no changes made. Remove --dry-run to apply.');
            return self::SUCCESS;
        }

        if (!$this->confirm("\nProceed with reversal of $" . number_format($grandTotal, 4) . " across " . count($affected) . " records?")) {
            $this->warn('Aborted.');
            return self::FAILURE;
        }

        // Step 3: Apply reversals
        $reversed = 0;
        $now      = now();

        DB::beginTransaction();
        try {
            foreach ($affected as $row) {
                $excess = (float) $row->excess;
                if ($excess <= 0) {
                    continue;
                }

                \App\Models\Wallet::create([
                    'user_id'          => $row->ancestor_id,
                    'wallet_type'      => 'direct_indirect',
                    'balance'          => -$excess,
                    'direct_balance'   => $row->commission_type === 'direct'  ? -$excess : 0,
                    'indirect_balance' => $row->commission_type !== 'direct'  ? -$excess : 0,
                    'commission_type'  => 'saving_commission_reversal',
                    'level'            => $row->level,
                    'percentage'       => $row->percentage,
                    'total_amount'     => $excess,
                    'wallet_src'       => 'commission_correction',
                    'source_type'      => 'saving_instalment',
                    'description'      => 'Commission correction: instalment #1 registration partial excess reversed',
                    'transaction_type' => 'debit',
                ]);

                // Fix the displayed commission_amount to the correct value
                DB::table('saving_instalment_commissions')
                    ->where('id', $row->sic_id)
                    ->update([
                        'commission_amount' => $row->correct,
                        'excess_reversed'   => 1,
                        'updated_at'        => $now,
                    ]);

                $reversed++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('ERROR during reversal: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('');
        $this->info("✅ Done — <comment>{$reversed}</comment> records reversed. Total <comment>$" . number_format($grandTotal, 4) . "</comment> debited from ancestor wallets.");

        return self::SUCCESS;
    }
}
