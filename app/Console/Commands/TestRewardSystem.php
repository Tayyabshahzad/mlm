<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Wallet;
use App\Models\PendingReward;
use App\Services\RewardService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestRewardSystem extends Command
{
    protected $signature = 'test:rewards {--user-id= : Test specific user} {--create-sample : Create sample test data}';
    protected $description = 'Test and verify reward system functionality';

    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        parent::__construct();
        $this->rewardService = $rewardService;
    }

    public function handle()
    {
        $this->info('🧪 Testing Reward System');
        $this->info('========================');

        if ($this->option('create-sample')) {
            $this->createSampleData();
            return;
        }

        $userId = $this->option('user-id');
        
        if ($userId) {
            $this->testSpecificUser($userId);
        } else {
            $this->runFullSystemTest();
        }
    }

    private function createSampleData()
    {
        $this->info('📝 Creating sample test data...');
        
        // Create a test user with 15 direct referrals
        $testUser = User::create([
            'name' => 'Test User Parent',
            'username' => 'test-parent-' . rand(1000, 9999),
            'email' => 'test-parent-' . rand(1000, 9999) . '@example.com',
            'password' => bcrypt('password'),
            'blocked' => false,
            'can_login' => 1,
        ]);

        $this->info("✅ Created test parent user: {$testUser->name} (ID: {$testUser->id})");

        // Create 15 referrals under this user
        for ($i = 1; $i <= 15; $i++) {
            $referral = User::create([
                'name' => "Test Referral {$i}",
                'username' => "test-ref-{$i}-" . rand(1000, 9999),
                'email' => "test-referral-{$i}-" . rand(1000, 9999) . "@example.com",
                'password' => bcrypt('password'),
                'sponsor_id' => $testUser->id,
                'blocked' => false,
                'can_login' => 1,
            ]);

            // Add to referral_trees table
            DB::table('referral_trees')->insert([
                'ancestor_id' => $testUser->id,
                'descendant_id' => $referral->id,
                'level' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info("✅ Created 15 direct referrals");
        $this->info("🎯 Test user should be eligible for Level 1 reward (needs 10, has 15)");
        $this->info("📋 Run: php artisan test:rewards --user-id={$testUser->id}");
    }

    private function testSpecificUser($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found");
            return;
        }

        $this->info("👤 Testing User: {$user->name} (ID: {$userId})");
        $this->info("======================================");

        // 1. Show team structure
        $this->showTeamStructure($userId);

        // 2. Show current rewards
        $this->showCurrentRewards($userId);

        // 3. Show pending rewards
        $this->showPendingRewards($userId);

        // 4. Test eligibility for each level
        $this->testEligibility($userId);

        // 5. Show what would happen if we process rewards
        $this->simulateRewardProcessing($userId);
    }

    private function showTeamStructure($userId)
    {
        $this->info("\n📊 Team Structure:");
        
        for ($level = 1; $level <= 7; $level++) {
            $count = DB::table('referral_trees')
                ->join('users', 'referral_trees.descendant_id', '=', 'users.id')
                ->where('referral_trees.ancestor_id', $userId)
                ->where('referral_trees.level', $level)
                ->where('users.blocked', false)
                ->where('users.can_login', 1)
                ->count();
            
            $required = $this->rewardService->getRewardLevels()[$level]['users_required'];
            $status = $count >= $required ? '✅' : '❌';
            
            $this->line("  Level {$level}: {$count} members (need {$required}) {$status}");
        }
    }

    private function showCurrentRewards($userId)
    {
        $this->info("\n💰 Current Rewards:");
        
        $rewards = Wallet::where('user_id', $userId)
            ->where('wallet_type', 'reward')
            ->where('commission_type', 'reward')
            ->orderBy('level')
            ->get();

        if ($rewards->isEmpty()) {
            $this->line("  No rewards assigned yet");
        } else {
            foreach ($rewards as $reward) {
                $this->line("  Level {$reward->level}: \${$reward->balance}");
            }
            $this->line("  Total: \$" . $rewards->sum('balance'));
        }
    }

    private function showPendingRewards($userId)
    {
        $this->info("\n⏳ Pending Rewards:");
        
        $pending = PendingReward::where('user_id', $userId)->get();

        if ($pending->isEmpty()) {
            $this->line("  No pending rewards");
        } else {
            foreach ($pending as $reward) {
                $statusEmoji = [
                    'pending' => '⏳',
                    'approved' => '✅',
                    'denied' => '❌'
                ][$reward->status] ?? '❓';
                
                $this->line("  Level {$reward->level}: \${$reward->reward_amount} {$statusEmoji} {$reward->status}");
            }
        }
    }

    private function testEligibility($userId)
    {
        $this->info("\n🔍 Eligibility Check:");
        
        for ($level = 1; $level <= 7; $level++) {
            $validation = $this->rewardService->validateRewardEligibility($userId, $level);
            
            if ($validation['eligible']) {
                $this->line("  Level {$level}: ✅ ELIGIBLE");
            } else {
                $this->line("  Level {$level}: ❌ Not eligible");
                foreach ($validation['reasons'] as $reason) {
                    $this->line("    - {$reason}");
                }
            }
        }
    }

    private function simulateRewardProcessing($userId)
    {
        $this->info("\n🎮 Simulation (DRY RUN):");
        
        $beforePending = PendingReward::where('user_id', $userId)->count();
        
        // Show what would happen
        for ($level = 1; $level <= 7; $level++) {
            $validation = $this->rewardService->validateRewardEligibility($userId, $level);
            
            if ($validation['eligible']) {
                $amount = $this->rewardService->getRewardLevels()[$level]['reward_amount'];
                $this->line("  ➡️ Would create pending Level {$level} reward: \${$amount}");
            }
        }
        
        $this->info("\n🚀 To actually process: php artisan rewards:process-existing --user-id={$userId}");
    }

    private function runFullSystemTest()
    {
        $this->info("\n📈 Full System Overview:");
        
        // Show system stats
        $totalUsers = User::where('blocked', false)->where('can_login', 1)->count();
        $totalRewards = Wallet::where('wallet_type', 'reward')->sum('balance');
        $pendingCount = PendingReward::pending()->count();
        $approvedCount = PendingReward::approved()->count();
        $deniedCount = PendingReward::denied()->count();
        
        $this->table(['Metric', 'Count'], [
            ['Active Users', $totalUsers],
            ['Total Rewards Paid', '$' . number_format($totalRewards, 2)],
            ['Pending Rewards', $pendingCount],
            ['Approved Rewards', $approvedCount],
            ['Denied Rewards', $deniedCount],
        ]);

        // Show users with most team members
        $this->info("\n👥 Top Users by Team Size:");
        
        $topUsers = DB::select("
            SELECT 
                u.id,
                u.name,
                u.email,
                COUNT(rt.descendant_id) as total_team_size
            FROM users u
            LEFT JOIN referral_trees rt ON u.id = rt.ancestor_id
            WHERE u.blocked = 0 AND u.can_login = 1
            GROUP BY u.id, u.name, u.email
            ORDER BY total_team_size DESC
            LIMIT 10
        ");

        $tableData = [];
        foreach ($topUsers as $user) {
            $tableData[] = [
                $user->id,
                $user->name,
                $user->email,
                $user->total_team_size ?? 0
            ];
        }

        $this->table(['ID', 'Name', 'Email', 'Team Size'], $tableData);

        $this->info("\n🔬 To test specific user: php artisan test:rewards --user-id=USER_ID");
    }
}
