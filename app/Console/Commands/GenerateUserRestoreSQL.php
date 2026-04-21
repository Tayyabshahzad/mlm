<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateUserRestoreSQL extends Command
{
    protected $signature   = 'users:generate-restore-sql';
    protected $description = 'Generate a SQL file to restore deleted users and all their data to the live DB';

    private array $userIds    = [17, 24, 25, 29, 276];
    private array $usernames  = ['sahibzada2727', 'munir512', 'mudassar512', 'raja512', 'admin-0786'];
    private string $outputFile = 'restore_deleted_users.sql';

    public function handle(): void
    {
        $lines = [];

        $lines[] = '-- ============================================================';
        $lines[] = '--  RESTORE DELETED USERS — generated ' . now()->toDateTimeString();
        $lines[] = '--  Users: ' . implode(', ', $this->usernames);
        $lines[] = '--';
        $lines[] = '--  HOW TO USE:';
        $lines[] = '--  1. Run the CHECK section first on LIVE and verify ALL rows show "OK"';
        $lines[] = '--  2. If IDs are safe, run the full RESTORE section';
        $lines[] = '--  Run on live: mysql -u root -p your_live_db < restore_deleted_users.sql';
        $lines[] = '-- ============================================================';
        $lines[] = '';

        // ── SECTION 1: Safety check ────────────────────────────────────────
        $lines[] = '-- ============================================================';
        $lines[] = '-- STEP 1: SAFETY CHECK — run this first, verify output manually';
        $lines[] = '-- Each row must show "ID is free" (deleted) or "OK - matches expected".';
        $lines[] = '-- If ANY row shows CONFLICT, STOP immediately — a different user owns that ID.';
        $lines[] = '-- ============================================================';
        $lines[] = "SELECT id, username, email, deleted_at,";
        $lines[] = "       CASE";
        foreach ($this->userIds as $i => $id) {
            $expected = $this->usernames[$i];
            $lines[] = "           WHEN id = {$id} AND username = '{$expected}' THEN 'OK - matches expected'";
            $lines[] = "           WHEN id = {$id} AND username != '{$expected}' THEN 'CONFLICT - different user has this ID'";
        }
        $lines[] = "           ELSE 'ID is free (deleted)'";
        $lines[] = "       END AS status";
        $lines[] = "FROM users WHERE id IN (" . implode(',', $this->userIds) . ");";
        $lines[] = '';

        // ── SECTION 2: Restore ─────────────────────────────────────────────
        $lines[] = '-- ============================================================';
        $lines[] = '-- STEP 2: RESTORE — only run after confirming Step 1 is safe';
        $lines[] = '-- INSERT IGNORE is used throughout: rows that already exist are skipped.';
        $lines[] = '-- ============================================================';
        $lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $lines[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
        $lines[] = '';

        // 1. Users
        $this->exportTable($lines, 'users', 'user_id', '1. Users',
            DB::table('users')->whereIn('id', $this->userIds)->get());

        // 2. Profiles
        $this->exportTable($lines, 'profiles', 'user_id', '2. Profiles',
            DB::table('profiles')->whereIn('user_id', $this->userIds)->get());

        // 3. Referral links
        $this->exportTable($lines, 'referral_links', 'user_id', '3. Referral links',
            DB::table('referral_links')->whereIn('user_id', $this->userIds)->get());

        // 4. Roles
        $this->exportTable($lines, 'model_has_roles', 'model_id', '4. Roles',
            DB::table('model_has_roles')->whereIn('model_id', $this->userIds)->get());

        // 5. Investment slabs
        $this->exportTable($lines, 'investment_slabs', 'user_id', '5. Investment slabs',
            DB::table('investment_slabs')->whereIn('user_id', $this->userIds)->get());

        // 6. User investments
        $this->exportTable($lines, 'user_investments', 'user_id', '6. User investments',
            DB::table('user_investments')->whereIn('user_id', $this->userIds)->get());

        // 7. Referral trees — both directions (standard and saving trees)
        $lines[] = '-- 7. Referral trees (restored users as ancestor OR descendant, both tree types)';
        $trees = DB::table('referral_trees')
            ->where(fn ($q) => $q->whereIn('ancestor_id', $this->userIds)->orWhereIn('descendant_id', $this->userIds))
            ->get();
        $this->info("Exporting {$trees->count()} referral_tree rows...");
        foreach ($trees as $row) {
            $lines[] = $this->buildInsertIgnore('referral_trees', (array) $row);
        }
        $lines[] = '';

        // 8. Wallets owned by restored users
        $lines[] = '-- 8. Wallets owned by restored users';
        $wallets = DB::table('wallets')->whereIn('user_id', $this->userIds)->get();
        $this->info("Exporting {$wallets->count()} wallet rows (owned by restored users)...");
        foreach ($wallets as $row) {
            $lines[] = $this->buildInsertIgnore('wallets', (array) $row);
        }
        $lines[] = '';

        // 9. Wallets belonging to OTHER users where wallet_from references a restored user
        //    CRITICAL: wallet_from has onDelete('cascade'), so these rows were DELETED on live
        //    when the users were deleted. We must INSERT them, not just UPDATE.
        $lines[] = '-- 9. Wallets of OTHER users sourced from restored users (cascade-deleted on live)';
        $lines[] = '--    These are commission/bonus wallets credited from the restored users.';
        $otherWallets = DB::table('wallets')
            ->whereIn('wallet_from', $this->userIds)
            ->whereNotIn('user_id', $this->userIds)
            ->get();
        $this->info("Exporting {$otherWallets->count()} wallet rows (other users, wallet_from restored users)...");
        foreach ($otherWallets as $row) {
            $lines[] = $this->buildInsertIgnore('wallets', (array) $row);
        }
        $lines[] = '';

        // 10. Transaction logs for restored users
        $lines[] = '-- 10. Transaction logs';
        $txLogs = DB::table('transaction_logs')->whereIn('user_id', $this->userIds)->get();
        $this->info("Exporting {$txLogs->count()} transaction_log rows...");
        foreach ($txLogs as $row) {
            $lines[] = $this->buildInsertIgnore('transaction_logs', (array) $row);
        }
        $lines[] = '';

        // 11. Commission logs earned BY restored users
        $lines[] = '-- 11a. Commission logs earned BY restored users';
        $commByUser = DB::table('commission_logs')->whereIn('user_id', $this->userIds)->get();
        $this->info("Exporting {$commByUser->count()} commission_log rows (earned by restored users)...");
        foreach ($commByUser as $row) {
            $lines[] = $this->buildInsertIgnore('commission_logs', (array) $row);
        }
        $lines[] = '';

        // 11b. Commission logs earned by OTHER users where source_user_id = restored user
        //      CRITICAL: source_user_id has onDelete('cascade'), these rows were also deleted on live
        $lines[] = '-- 11b. Commission logs for OTHER users sourced from restored users (cascade-deleted on live)';
        $commBySource = DB::table('commission_logs')
            ->whereIn('source_user_id', $this->userIds)
            ->whereNotIn('user_id', $this->userIds)
            ->get();
        $this->info("Exporting {$commBySource->count()} commission_log rows (other users, source = restored users)...");
        foreach ($commBySource as $row) {
            $lines[] = $this->buildInsertIgnore('commission_logs', (array) $row);
        }
        $lines[] = '';

        // 12. ROI transactions
        $this->exportTable($lines, 'r_o_i_transactions', 'user_id', '12. ROI transactions',
            DB::table('r_o_i_transactions')->whereIn('user_id', $this->userIds)->get());

        // 13. PV transactions
        $this->exportTable($lines, 'p_v_transactions', 'user_id', '13. PV transactions',
            DB::table('p_v_transactions')->whereIn('user_id', $this->userIds)->get());

        // 14. Binary system records
        $this->exportTable($lines, 'binary_system', 'user_id', '14. Binary system',
            DB::table('binary_system')->whereIn('user_id', $this->userIds)->get());

        // 15. User ranks
        $this->exportTable($lines, 'user_ranks', 'user_id', '15. User ranks',
            DB::table('user_ranks')->whereIn('user_id', $this->userIds)->get());

        // 16. Saving instalments
        $this->exportTable($lines, 'saving_instalments', 'user_id', '16. Saving instalments',
            DB::table('saving_instalments')->whereIn('user_id', $this->userIds)->get());

        // 17. Withdrawal requests
        $this->exportTable($lines, 'with_drawalequests', 'user_id', '17. Withdrawal requests',
            DB::table('with_drawalequests')->whereIn('user_id', $this->userIds)->get());

        // 18. Reward transactions
        $this->exportTable($lines, 'reward_transactions', 'user_id', '18. Reward transactions',
            DB::table('reward_transactions')->whereIn('user_id', $this->userIds)->get());

        // 19. Pending rewards
        $this->exportTable($lines, 'pending_rewards', 'user_id', '19. Pending rewards',
            DB::table('pending_rewards')->whereIn('user_id', $this->userIds)->get());

        // 20. Media (profile images, CNICs, proofs)
        $lines[] = '-- 20. Media files';
        $media = DB::table('media')
            ->where('model_type', 'App\\Models\\User')
            ->whereIn('model_id', $this->userIds)
            ->get();
        $this->info("Exporting {$media->count()} media rows...");
        foreach ($media as $row) {
            $lines[] = $this->buildInsertIgnore('media', (array) $row);
        }
        $lines[] = '';

        // 21. Fix sponsor_id for users whose sponsor was one of the deleted users
        $lines[] = '-- 21. Fix sponsor_id for users whose sponsor was deleted';
        $lines[] = '--     On live, their sponsor_id was set to NULL by cascade. Restore the correct value.';
        $sponsorUsers = DB::table('users')->whereIn('sponsor_id', $this->userIds)->get(['id', 'sponsor_id']);
        $this->info("Found {$sponsorUsers->count()} users with sponsor_id pointing to restored users...");
        foreach ($sponsorUsers as $row) {
            $lines[] = "UPDATE users SET sponsor_id = {$row->sponsor_id} WHERE id = {$row->id};";
        }
        $lines[] = '';

        $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
        $lines[] = '';
        $lines[] = '-- ============================================================';
        $lines[] = '-- DONE. Verify with:';
        $lines[] = "-- SELECT id, username, can_login FROM users WHERE id IN (" . implode(',', $this->userIds) . ");";
        $lines[] = "-- SELECT COUNT(*) FROM referral_trees WHERE ancestor_id IN (" . implode(',', $this->userIds) . ") OR descendant_id IN (" . implode(',', $this->userIds) . ");";
        $lines[] = "-- SELECT COUNT(*) FROM wallets WHERE user_id IN (" . implode(',', $this->userIds) . ");";
        $lines[] = "-- SELECT COUNT(*) FROM commission_logs WHERE user_id IN (" . implode(',', $this->userIds) . ") OR source_user_id IN (" . implode(',', $this->userIds) . ");";
        $lines[] = '-- ============================================================';
        $lines[] = '';
        $lines[] = '-- IMPORTANT: After restoring, also copy the media files:';
        $lines[] = '--   From local: storage/app/public/  →  Live: storage/app/public/';
        $lines[] = '--   Only copy files belonging to these user IDs to avoid overwriting live uploads.';

        $sql  = implode("\n", $lines);
        $path = storage_path('app/' . $this->outputFile);
        file_put_contents($path, $sql);

        $this->info('');
        $this->info('✅ SQL file generated: ' . $path);
        $this->info('   File size: ' . round(strlen($sql) / 1024, 1) . ' KB');
        $this->info('');
        $this->info('Next steps:');
        $this->info('  1. Run Step 1 (safety check) on the live DB and verify all rows show OK');
        $this->info('  2. If safe, import the full file: mysql -u root -p live_db < ' . $this->outputFile);
        $this->info('  3. Copy media files for these user IDs from local storage/app/public/ to live');
        $this->info('  4. Run verification queries at the bottom of the SQL file');
    }

    private function exportTable(array &$lines, string $table, string $label, string $comment, $rows): void
    {
        $count = $rows->count();
        $lines[] = "-- {$comment}";
        $this->info("Exporting {$count} rows from {$table}...");
        foreach ($rows as $row) {
            $lines[] = $this->buildInsertIgnore($table, (array) $row);
        }
        $lines[] = '';
    }

    private function buildInsertIgnore(string $table, array $row): string
    {
        $cols   = implode(', ', array_map(fn ($c) => "`{$c}`", array_keys($row)));
        $values = implode(', ', array_map(fn ($v) => $this->escapeValue($v), array_values($row)));
        return "INSERT IGNORE INTO `{$table}` ({$cols}) VALUES ({$values});";
    }

    private function escapeValue(mixed $value): string
    {
        if ($value === null) return 'NULL';
        if (is_bool($value)) return $value ? '1' : '0';
        if (is_int($value) || is_float($value)) return (string) $value;
        $value = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $value);
        return "'{$value}'";
    }
}
