<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ROICommissionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessAllCommissions extends Command
{
    protected $signature = 'commissions:process-all 
                           {--user-id= : Process commissions for specific user only}
                           {--level= : Process commissions for specific level only}
                           {--dry-run : Show what would be processed without actually processing}';

    protected $description = 'Process commissions for all eligible users at all levels';

    private ROICommissionService $commissionService;

    public function __construct(ROICommissionService $commissionService)
    {
        parent::__construct();
        $this->commissionService = $commissionService;
    }

    public function handle()
    {
        $this->info('Starting commission processing...');
        
        $userId = $this->option('user-id');
        $level = $this->option('level');
        $isDryRun = $this->option('dry-run');

        if ($userId) {
            $this->processUserCommissions($userId, $isDryRun);
        } elseif ($level) {
            $this->processLevelCommissions($level, $isDryRun);
        } else {
            $this->processAllCommissions($isDryRun);
        }

        $this->info('Commission processing completed!');
    }

    private function processUserCommissions(int $userId, bool $isDryRun)
    {
        $user = User::findOrFail($userId);
        
        $this->info("Processing commissions for user: {$user->name} (ID: {$user->id})");
        
        // Get all users who should generate commissions for this user
        $commissionSources = $this->getCommissionSourcesForUser($user);
        
        $this->table(
            ['Level', 'Required Users', 'Current Users', 'Eligible', 'Commission Sources'],
            $commissionSources->map(function($source) {
                return [
                    $source['level'],
                    $source['required'],
                    $source['current'],
                    $source['eligible'] ? 'Yes' : 'No',
                    $source['sources_count']
                ];
            })
        );

        if (!$isDryRun) {
            // Process actual commissions
            foreach ($commissionSources as $source) {
                if ($source['eligible']) {
                    $this->processCommissionsFromSources($user, $source['level'], $source['sources']);
                }
            }
        }
    }

    private function processLevelCommissions(int $level, bool $isDryRun)
    {
        $this->info("Processing commissions for level: {$level}");
        
        // Find all users eligible to receive commissions at this level
        $eligibleUsers = $this->getEligibleUsersAtLevel($level);
        
        $this->info("Found {$eligibleUsers->count()} eligible users at level {$level}");
        
        foreach ($eligibleUsers as $user) {
            $this->info("Processing level {$level} commissions for: {$user->name}");
            
            if (!$isDryRun) {
                $this->processUserLevelCommissions($user, $level);
            }
        }
    }

    private function processAllCommissions(bool $isDryRun)
    {
        $this->info("Processing commissions for all eligible users at all levels");
        
        $summary = [];
        
        for ($level = 1; $level <= 7; $level++) {
            $eligibleUsers = $this->getEligibleUsersAtLevel($level);
            $summary[] = [
                'level' => $level,
                'eligible_users' => $eligibleUsers->count(),
                'required_users' => [10, 50, 150, 400, 1000, 2000, 4000][$level - 1],
                'commission_percentage' => [7.0, 6.0, 5.0, 4.0, 3.0, 2.0, 1.0][$level - 1]
            ];
            
            if (!$isDryRun && $eligibleUsers->count() > 0) {
                $this->info("Processing {$eligibleUsers->count()} users at level {$level}");
                
                foreach ($eligibleUsers as $user) {
                    $this->processUserLevelCommissions($user, $level);
                }
            }
        }
        
        $this->table(
            ['Level', 'Eligible Users', 'Required Users', 'Commission %'],
            collect($summary)->map(function($item) {
                return [
                    $item['level'],
                    $item['eligible_users'],
                    $item['required_users'],
                    $item['commission_percentage'] . '%'
                ];
            })
        );
    }

    private function getCommissionSourcesForUser(User $user): \Illuminate\Support\Collection
    {
        $sources = collect();
        
        for ($level = 1; $level <= 7; $level++) {
            $usersAtLevel = $this->getUsersAtLevel($user->id, $level);
            $required = [10, 50, 150, 400, 1000, 2000, 4000][$level - 1];
            
            $sources->push([
                'level' => $level,
                'required' => $required,
                'current' => $usersAtLevel->count(),
                'eligible' => $usersAtLevel->count() >= $required,
                'sources' => $usersAtLevel,
                'sources_count' => $usersAtLevel->count()
            ]);
        }
        
        return $sources;
    }

    private function getEligibleUsersAtLevel(int $level): \Illuminate\Support\Collection
    {
        $required = [10, 50, 150, 400, 1000, 2000, 4000][$level - 1];
        
        return User::whereHas('referralTrees', function($query) use ($level, $required) {
            $query->where('level', $level)
                  ->select('ancestor_id')
                  ->groupBy('ancestor_id')
                  ->havingRaw('COUNT(*) >= ?', [$required]);
        })->get();
    }

    private function getUsersAtLevel(int $ancestorId, int $level): \Illuminate\Support\Collection
    {
        return User::whereIn('id', function($query) use ($ancestorId, $level) {
            $query->select('descendant_id')
                  ->from('referral_trees')
                  ->where('ancestor_id', $ancestorId)
                  ->where('level', $level);
        })->get();
    }

    private function processCommissionsFromSources(User $ancestor, int $level, \Illuminate\Support\Collection $sources)
    {
        $percentage = [7.0, 6.0, 5.0, 4.0, 3.0, 2.0, 1.0][$level - 1];
        
        foreach ($sources as $source) {
            // Get recent ROI for this source user
            $recentRoi = $this->getRecentRoiAmount($source);
            
            if ($recentRoi > 0) {
                $this->info("Processing commission: {$ancestor->name} gets {$percentage}% from {$source->name}'s ROI of {$recentRoi}");
                
                // Call the commission service
                $this->commissionService->generateCommissions($source, $recentRoi);
            }
        }
    }

    private function processUserLevelCommissions(User $user, int $level)
    {
        $usersAtLevel = $this->getUsersAtLevel($user->id, $level);
        
        foreach ($usersAtLevel as $source) {
            $recentRoi = $this->getRecentRoiAmount($source);
            
            if ($recentRoi > 0) {
                $this->commissionService->generateCommissions($source, $recentRoi);
            }
        }
    }

    private function getRecentRoiAmount(User $user): float
    {
        // Get recent ROI transactions for this user
        return DB::table('r_o_i_transactions')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->sum('amount') ?? 0;
    }
}