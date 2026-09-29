<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairReferralTrees extends Command
{
    protected $signature   = 'repair:referral-trees {--dry-run : Show what would be fixed without making changes}';
    protected $description = 'Find users missing from referral_trees and insert the correct ancestry rows';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Find every active user who has a sponsor but no entry in referral_trees
        $orphans = User::whereNotNull('sponsor_id')
            ->whereNotIn('id', fn ($q) => $q->select('descendant_id')->from('referral_trees'))
            ->orderBy('id')
            ->get(['id', 'name', 'username', 'sponsor_id', 'account_type']);

        if ($orphans->isEmpty()) {
            $this->info('No orphaned users found — referral_trees is consistent.');
            return self::SUCCESS;
        }

        $this->warn("Found {$orphans->count()} orphaned user(s):");
        foreach ($orphans as $u) {
            $sponsor = User::find($u->sponsor_id);
            $this->line("  [{$u->id}] {$u->username} ({$u->account_type}) → sponsor: " . ($sponsor?->username ?? 'MISSING') . " [{$u->sponsor_id}]");
        }

        if ($dryRun) {
            $this->info('Dry-run mode — no changes made.');
            return self::SUCCESS;
        }

        if (! $this->confirm('Repair these users now?', true)) {
            return self::FAILURE;
        }

        DB::beginTransaction();
        try {
            // Process in order so parents are fixed before their children
            foreach ($this->sortByDependency($orphans) as $user) {
                $this->repairUser($user->sponsor_id, $user->id, 'standard');

                // Also repair in saving tree if the user is in the saving tree context
                $sponsorInSaving = DB::table('referral_trees')
                    ->where('descendant_id', $user->sponsor_id)
                    ->where('tree_type', 'saving')
                    ->exists();

                if ($sponsorInSaving || $user->account_type === 'saving') {
                    $this->repairUser($user->sponsor_id, $user->id, 'saving');
                }

                $this->line("  ✓ Repaired [{$user->id}] {$user->username}");
            }

            DB::commit();
            $this->info('All orphaned users repaired successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Repair failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function repairUser(int $parentId, int $userId, string $treeType): void
    {
        DB::table('referral_trees')->insertOrIgnore([
            'ancestor_id'   => $parentId,
            'descendant_id' => $userId,
            'level'         => 1,
            'tree_type'     => $treeType,
        ]);

        $ancestors = DB::table('referral_trees')
            ->where('descendant_id', $parentId)
            ->where('tree_type', $treeType)
            ->get();

        foreach ($ancestors as $anc) {
            DB::table('referral_trees')->insertOrIgnore([
                'ancestor_id'   => $anc->ancestor_id,
                'descendant_id' => $userId,
                'level'         => $anc->level + 1,
                'tree_type'     => $treeType,
            ]);
        }
    }

    private function sortByDependency($orphans): array
    {
        $orphanIds = $orphans->pluck('id')->flip();
        $sorted    = [];
        $visited   = [];

        $visit = function (object $user) use (&$visit, $orphans, $orphanIds, &$sorted, &$visited): void {
            if (isset($visited[$user->id])) return;
            $visited[$user->id] = true;

            // If this user's sponsor is also an orphan, fix the sponsor first
            if (isset($orphanIds[$user->sponsor_id])) {
                $parent = $orphans->firstWhere('id', $user->sponsor_id);
                if ($parent) $visit($parent);
            }

            $sorted[] = $user;
        };

        foreach ($orphans as $user) {
            $visit($user);
        }

        return $sorted;
    }
}
