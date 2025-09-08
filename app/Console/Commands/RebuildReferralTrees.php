<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildReferralTrees extends Command
{
    protected $signature = 'fix:referral-trees {--dry-run : Show what would be fixed without making changes} {--user-id= : Fix specific user only}';
    protected $description = 'Rebuild referral_trees table with correct level calculations';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificUserId = $this->option('user-id');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
        } else {
            $this->warn('⚠️  This will rebuild the referral_trees table. Make sure you have a backup!');
            if (!$this->confirm('Do you want to continue?')) {
                return 0;
            }
        }

        $this->info('Starting referral trees rebuild...');

        if (!$dryRun) {
            // Clear existing data
            DB::table('referral_trees')->truncate();
            $this->info('✅ Cleared existing referral_trees data');
        }

        // Get all users with sponsors
        $users = DB::table('users')
            ->whereNotNull('sponsor_id')
            ->where('blocked', 0)
            ->where('can_login', 1)
            ->orderBy('id')
            ->get();

        $this->info("📊 Processing {$users->count()} users with sponsors");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $insertData = [];
        $errorCount = 0;

        foreach ($users as $user) {
            try {
                $ancestors = $this->getAncestorPath($user->id);
                
                foreach ($ancestors as $level => $ancestorId) {
                    if ($specificUserId && $ancestorId != $specificUserId) {
                        continue;
                    }
                    
                    $insertData[] = [
                        'ancestor_id' => $ancestorId,
                        'descendant_id' => $user->id,
                        'level' => $level,
                    ];
                }
                
            } catch (\Exception $e) {
                $this->error("\nError processing user {$user->id}: " . $e->getMessage());
                $errorCount++;
            }
            
            $bar->advance();
        }

        $bar->finish();

        if (!$dryRun && !empty($insertData)) {
            // Insert in batches to avoid memory issues
            $chunks = array_chunk($insertData, 1000);
            foreach ($chunks as $chunk) {
                DB::table('referral_trees')->insert($chunk);
            }
            
            $this->info("\n✅ Inserted " . count($insertData) . " referral tree relationships");
        } else {
            $this->info("\n📊 Would insert " . count($insertData) . " referral tree relationships");
        }

        if ($errorCount > 0) {
            $this->warn("⚠️  {$errorCount} errors occurred during processing");
        }

        // Show results for user 2 if it was processed
        if ($specificUserId == 2 || !$specificUserId) {
            $this->info("\n🔍 Results for User 2 after rebuild:");
            if (!$dryRun) {
                $this->call('debug:referral-tree', ['user_id' => 2]);
            } else {
                $this->info("Run without --dry-run to see actual results");
            }
        }

        return 0;
    }

    /**
     * Get ancestor path for a user (going up the sponsor chain)
     */
    private function getAncestorPath($userId, $maxDepth = 20)
    {
        $ancestors = [];
        $currentId = $userId;
        $level = 1;

        // Get sponsor chain going up
        while ($level <= $maxDepth) {
            $user = DB::table('users')->where('id', $currentId)->first();
            if (!$user || !$user->sponsor_id) {
                break;
            }
            
            $ancestors[$level] = $user->sponsor_id;
            $currentId = $user->sponsor_id;
            $level++;
        }

        return $ancestors;
    }
}
