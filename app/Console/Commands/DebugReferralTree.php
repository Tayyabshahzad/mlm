<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugReferralTree extends Command
{
    protected $signature = 'debug:referral-tree {user_id}';
    protected $description = 'Debug referral tree level calculations for a specific user';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        $this->info("🔍 Debugging Referral Tree for User: {$user->name} (ID: {$userId})");
        $this->info("==============================================================");

        // 1. Check direct referrals
        $this->info("\n1. Direct Referrals (sponsor_id = {$userId}):");
        $directRefs = DB::select("
            SELECT id, name, username, created_at
            FROM users 
            WHERE sponsor_id = ? AND blocked = 0 AND can_login = 1 
            ORDER BY id
        ", [$userId]);

        foreach ($directRefs as $ref) {
            $this->line("   - ID: {$ref->id}, Name: {$ref->name}, Username: {$ref->username}");
        }
        $this->info("   Total Direct: " . count($directRefs));

        // 2. Check referral_trees Level 1
        $this->info("\n2. Referral Trees Level 1 (should match direct referrals):");
        $level1Users = DB::select("
            SELECT rt.descendant_id, u.name, u.username
            FROM referral_trees rt
            JOIN users u ON rt.descendant_id = u.id
            WHERE rt.ancestor_id = ? AND rt.level = 1 AND u.blocked = 0 AND u.can_login = 1
            ORDER BY rt.descendant_id
        ", [$userId]);

        foreach ($level1Users as $user) {
            $this->line("   - ID: {$user->descendant_id}, Name: {$user->name}, Username: {$user->username}");
        }
        $this->info("   Total Level 1: " . count($level1Users));

        // 3. Compare discrepancies
        $directIds = collect($directRefs)->pluck('id')->toArray();
        $level1Ids = collect($level1Users)->pluck('descendant_id')->toArray();
        
        $missing = array_diff($directIds, $level1Ids);
        $extra = array_diff($level1Ids, $directIds);

        if (!empty($missing)) {
            $this->warn("   ⚠️  Missing from referral_trees Level 1: " . implode(', ', $missing));
        }
        if (!empty($extra)) {
            $this->warn("   ⚠️  Extra in referral_trees Level 1: " . implode(', ', $extra));
        }

        // 4. Check specific levels with high counts
        $this->info("\n3. All Referral Tree Levels:");
        $levels = DB::select("
            SELECT 
                level,
                COUNT(*) as count
            FROM referral_trees rt
            JOIN users u ON rt.descendant_id = u.id 
            WHERE rt.ancestor_id = ? AND u.blocked = 0 AND u.can_login = 1
            GROUP BY level 
            ORDER BY level
        ", [$userId]);

        foreach ($levels as $level) {
            $this->line("   Level {$level->level}: {$level->count} users");
        }

        // 5. Investigate a suspicious level (Level 7 in your case)
        if ($userId == 2) {
            $this->info("\n4. Investigating Level 7 Users (should they be Level 6?):");
            $level7Users = DB::select("
                SELECT 
                    rt.descendant_id,
                    u.name,
                    u.sponsor_id,
                    sponsor.name as sponsor_name
                FROM referral_trees rt
                JOIN users u ON rt.descendant_id = u.id
                LEFT JOIN users sponsor ON u.sponsor_id = sponsor.id
                WHERE rt.ancestor_id = 2 AND rt.level = 7
                ORDER BY rt.descendant_id
                LIMIT 5
            ");

            foreach ($level7Users as $user) {
                $this->line("   - ID: {$user->descendant_id}, Name: {$user->name}, Sponsor: {$user->sponsor_name} (ID: {$user->sponsor_id})");
                
                // Trace path back to user 2
                $path = $this->tracePath($user->descendant_id, $userId);
                $this->line("     Path length: " . count($path) . " levels");
                if (count($path) != 7) {
                    $this->warn("     ⚠️  Path length mismatch! Expected 7, got " . count($path));
                }
            }
        }

        return 0;
    }

    private function tracePath($fromUserId, $toUserId)
    {
        $path = [];
        $currentId = $fromUserId;
        
        while ($currentId && $currentId != $toUserId && count($path) < 20) {
            $user = DB::table('users')->where('id', $currentId)->first();
            if (!$user) break;
            
            $path[] = $currentId;
            $currentId = $user->sponsor_id;
        }
        
        if ($currentId == $toUserId) {
            $path[] = $toUserId;
        }
        
        return $path;
    }
}
