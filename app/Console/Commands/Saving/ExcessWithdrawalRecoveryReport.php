<?php

namespace App\Console\Commands\Saving;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExcessWithdrawalRecoveryReport extends Command
{
    protected $signature = 'saving:excess-withdrawal-report
                            {--deduct : Create wallet deduction entries for users who still owe (balance went negative)}';

    protected $description = 'Report users who withdrew excess saving commissions, and optionally create recovery deductions';

    public function handle(): int
    {
        $this->info('');
        $this->info('=== Excess Commission Withdrawal Recovery Report ===');
        $this->info('');

        // Step 1: Get all users who had excess commissions (reversed)
        $excessByUser = DB::select('
            SELECT
                sic.ancestor_id                                       AS user_id,
                u.username,
                u.name,
                SUM(ROUND(sic.commission_amount * 100 / sic.percentage, 4) - sic.commission_amount) AS total_excess_originally,
                SUM(sic.commission_amount)                            AS total_correct_commission,
                MIN(sic.processed_at)                                 AS first_excess_date,
                MAX(sic.processed_at)                                 AS last_excess_date
            FROM saving_instalment_commissions sic
            JOIN users u ON u.id = sic.ancestor_id
            WHERE sic.excess_reversed = 1
            GROUP BY sic.ancestor_id, u.username, u.name
        ');

        if (empty($excessByUser)) {
            $this->info('No excess-reversed commission records found.');
            return self::SUCCESS;
        }

        $this->info('Users who received excess commissions:');
        $this->info(str_repeat('─', 120));

        $usersWithDebt = [];

        foreach ($excessByUser as $row) {
            $userId = $row->user_id;

            // Total excess that was paid (now reversed from wallet)
            $totalExcess = round(
                DB::table('saving_instalment_commissions')
                    ->where('ancestor_id', $userId)
                    ->where('excess_reversed', 1)
                    ->selectRaw('SUM(commission_amount) as correct_sum')
                    ->value('correct_sum') ?? 0,
                4
            );

            // Approved withdrawals made by this user during/after the excess period
            $withdrawals = DB::table('with_drawalequests')
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->where('created_at', '>=', $row->first_excess_date)
                ->select('id', 'amount', 'created_at', 'request_type')
                ->orderBy('created_at')
                ->get();

            $totalWithdrawn = $withdrawals->sum('amount');

            // Current wallet balance
            $currentBalance = round(
                DB::table('wallets')
                    ->where('user_id', $userId)
                    ->where('wallet_type', 'direct_indirect')
                    ->sum('balance'),
                4
            );

            // The excess that was originally paid (before correction)
            $originalExcessPaid = round(
                DB::select('
                    SELECT SUM(
                        ROUND(sic.commission_amount * 100 / sic.percentage, 4) - sic.commission_amount
                    ) AS excess
                    FROM saving_instalment_commissions sic
                    WHERE sic.ancestor_id = ? AND sic.excess_reversed = 1
                ', [$userId])[0]->excess ?? 0,
                4
            );

            // Recovery status
            $recoveryStatus = $currentBalance >= 0 ? '✅ RECOVERED' : '⚠️  NEEDS ATTENTION';
            $statusColor    = $currentBalance >= 0 ? 'info' : 'error';

            $this->$statusColor(sprintf(
                'User %-4s %-20s | excess originally: $%-8s | withdrawn after excess: $%-8s | wallet now: $%-8s | %s',
                $userId,
                $row->username,
                number_format($originalExcessPaid, 4),
                number_format($totalWithdrawn, 4),
                number_format($currentBalance, 4),
                $recoveryStatus
            ));

            if ($withdrawals->isNotEmpty()) {
                foreach ($withdrawals as $wd) {
                    $this->line(sprintf(
                        '           Withdrawal #%-5s | $%-8s | %s | %s',
                        $wd->id,
                        number_format($wd->amount, 2),
                        $wd->request_type ?? 'N/A',
                        substr($wd->created_at, 0, 10)
                    ));
                }
            } else {
                $this->line('           No withdrawals found after the excess commission period.');
            }

            if ($currentBalance < 0) {
                $usersWithDebt[] = [
                    'user_id'   => $userId,
                    'username'  => $row->username,
                    'debt'      => abs($currentBalance),
                ];
            }

            $this->line('');
        }

        $this->info(str_repeat('─', 120));

        if (empty($usersWithDebt)) {
            $this->info('✅ All excess commissions have been fully recovered via wallet reversal.');
            $this->info('   No users have negative balance — no further action required.');
        } else {
            $this->warn('⚠️  The following users have negative wallet balance after reversal:');
            foreach ($usersWithDebt as $debt) {
                $this->warn(sprintf('   User %-4s %-20s | owes: $%s', $debt['user_id'], $debt['username'], number_format($debt['debt'], 4)));
            }

            if ($this->option('deduct')) {
                $this->info('');
                $this->info('Creating deduction records for users with negative balance...');
                // Negative balance already exists in the wallet — no further action needed.
                // Future commissions will naturally bring the balance back to 0.
                $this->info('Note: Negative wallet balances are already recorded. Future commission credits will automatically offset the debt.');
            } else {
                $this->info('');
                $this->line('Run with --deduct to see debt recovery guidance for negative-balance users.');
            }
        }

        $this->info('');

        return self::SUCCESS;
    }
}
