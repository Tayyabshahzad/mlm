<?php

namespace App\Console\Commands\Saving;

use App\Models\User;
use App\Notifications\ExcessCommissionCorrectedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyExcessCommissionUsers extends Command
{
    protected $signature = 'saving:notify-excess-users
                            {--dry-run : Preview without sending emails}';

    protected $description = 'Send notification emails to users who received excess saving commission and had approved withdrawals';

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('');
        $this->info('=== Excess Commission Notification — Affected Users ===');
        if ($isDryRun) {
            $this->warn('DRY RUN — emails will NOT be sent.');
        }
        $this->info('');

        // Find users who got excess commission AND had approved withdrawals
        $affectedUsers = DB::select('
            SELECT
                u.id,
                u.username,
                u.name,
                u.email,
                SUM(ROUND(COALESCE(si.submitted_amount, si.amount) * sic.percentage / 100, 4)) AS excess_total,
                MIN(sic.processed_at) AS first_excess_date,
                MAX(sic.processed_at) AS last_excess_date
            FROM saving_instalment_commissions sic
            JOIN users u ON u.id = sic.ancestor_id
            JOIN saving_instalments si ON si.id = sic.saving_instalment_id
            WHERE sic.excess_reversed = 1
            GROUP BY u.id, u.username, u.name, u.email
            HAVING (
                SELECT COUNT(*)
                FROM with_drawalequests wr
                WHERE wr.user_id = u.id
                  AND wr.status = \'approved\'
                  AND wr.created_at >= MIN(sic.processed_at)
            ) > 0
            ORDER BY excess_total DESC
        ');

        if (empty($affectedUsers)) {
            $this->info('No users found with both excess commissions and approved withdrawals.');
            return self::SUCCESS;
        }

        $this->info('Users who received excess commission AND had approved withdrawals:');
        $this->info('');

        $tableRows = [];
        foreach ($affectedUsers as $row) {
            $withdrawals = DB::table('with_drawalequests')
                ->where('user_id', $row->id)
                ->where('status', 'approved')
                ->where('created_at', '>=', $row->first_excess_date)
                ->selectRaw('COUNT(*) as cnt, SUM(amount) as total')
                ->first();

            $currentBalance = round(
                DB::table('wallets')->where('user_id', $row->id)->where('wallet_type', 'direct_indirect')->sum('balance'),
                2
            );

            $tableRows[] = [
                'user_id'         => $row->id,
                'username'        => $row->username,
                'name'            => $row->name,
                'email'           => $row->email,
                'excess'          => (float)$row->excess_total,
                'withdrawals'     => (int)($withdrawals->cnt ?? 0),
                'total_withdrawn' => (float)($withdrawals->total ?? 0),
                'period_from'     => substr($row->first_excess_date, 0, 10),
                'period_to'       => substr($row->last_excess_date, 0, 10),
                'balance_now'     => $currentBalance,
            ];
        }

        // Display table
        $this->table(
            ['ID', 'Username', 'Email', 'Excess($)', 'Withdrawals', 'Withdrawn($)', 'Balance Now($)'],
            array_map(fn($r) => [
                $r['user_id'], $r['username'], $r['email'],
                number_format($r['excess'], 2),
                $r['withdrawals'],
                number_format($r['total_withdrawn'], 2),
                number_format($r['balance_now'], 2),
            ], $tableRows)
        );

        $this->info('');

        if ($isDryRun) {
            $this->warn('DRY RUN — run without --dry-run to send emails.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Send notification emails to ' . count($tableRows) . ' users?')) {
            $this->warn('Aborted.');
            return self::FAILURE;
        }

        $sent = 0;
        $failed = 0;

        foreach ($tableRows as $userData) {
            $user = User::find($userData['user_id']);
            if (!$user || !$user->email) {
                $this->warn("  Skipped user {$userData['username']} — no email address.");
                $failed++;
                continue;
            }

            try {
                $user->notify(new ExcessCommissionCorrectedNotification(
                    excessAmount:    $userData['excess'],
                    currentBalance:  $userData['balance_now'],
                    periodFrom:      $userData['period_from'],
                    periodTo:        $userData['period_to'],
                    withdrawalCount: $userData['withdrawals'],
                    totalWithdrawn:  $userData['total_withdrawn'],
                ));

                $this->info("  ✅ Email sent → {$userData['username']} <{$userData['email']}>");
                $sent++;
            } catch (\Throwable $e) {
                $this->error("  ❌ Failed → {$userData['username']}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->info('');
        $this->info("Done — {$sent} email(s) sent." . ($failed > 0 ? " {$failed} failed." : ''));

        return self::SUCCESS;
    }
}
