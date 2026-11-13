<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Permanently removes accounts that were deleted (soft-deleted) earlier, when nobody
 * else depends on them (no team, no commissions to others, no withdrawals …).
 * Accounts that others depend on are listed with the reason and left as they are.
 */
class PurgeDeletedUsers extends Command
{
    protected $signature = 'users:purge-deleted
        {--apply : Remove them (without this it only shows the list)}
        {--user=* : Only these user IDs (e.g. --user=553)}';

    protected $description = 'Permanently remove deleted accounts that nobody else depends on';

    public function handle(): int
    {
        $only = (array) $this->option('user');

        $users = User::onlyTrashed()->orderBy('id')->get()
            ->filter(fn (User $u) => !$only || collect($only)->contains(fn ($o) => (string) $u->id === (string) $o || $u->username === (string) $o));

        if ($users->isEmpty()) {
            $this->info('No deleted accounts found.');
            return self::SUCCESS;
        }

        $rows = [];
        $removable = collect();
        foreach ($users as $u) {
            $blockers = $u->permanentDeleteBlockers();
            if (!$blockers) $removable->push($u);
            $rows[] = [$u->id, $u->name, $u->username, optional($u->deleted_at)->format('d M Y'), $blockers ? 'KEEP — ' . implode(', ', $blockers) : 'remove permanently'];
        }
        $this->table(['ID', 'Name', 'Username now', 'Deleted on', 'Action'], $rows);
        $this->line($removable->count() . ' of ' . $users->count() . ' deleted account(s) can be removed permanently.');

        if (!$this->option('apply')) {
            $this->comment('Nothing changed. Add --apply to remove them.');
            return self::SUCCESS;
        }
        if ($removable->isEmpty() || !$this->confirm('Remove ' . $removable->count() . ' account(s) permanently? This cannot be undone.', true)) {
            return self::SUCCESS;
        }

        foreach ($removable as $u) {
            $u->deletePermanently();
            $this->line("✔ removed #{$u->id} {$u->name}");
        }

        return self::SUCCESS;
    }
}
