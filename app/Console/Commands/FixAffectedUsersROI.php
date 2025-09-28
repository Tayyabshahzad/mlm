<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AccountManagementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixAffectedUsersROI extends Command
{
    protected $signature = 'roi:fix-affected-users {--dry-run : Preview changes without applying them} {--user-id= : Fix specific user ID}';

    protected $description = 'Fix users whose ROI was incorrectly stopped due to binary system completion or invalid reward reversals';

    private AccountManagementService $accountService;

    public function __construct(AccountManagementService $accountService)
    {
        parent::__construct();
        $this->accountService = $accountService;
    }

    public function handle()
    {
        $this->info('🔍 Analyzing users with ROI issues...');

        $dryRun = $this->option('dry-run');
        $specificUserId = $this->option('user-id');

        if ($dryRun) {
            $this->warn('🚀 DRY RUN MODE - No changes will be made');
        }

        $query = User::where('roi_eligible_investment_amount', '>', 0)
                    ->where('roi_status', 'stopped');

        if ($specificUserId) {
            $query->where('id', $specificUserId);
        }

        $affectedUsers = $query->get();

        if ($affectedUsers->isEmpty()) {
            $this->info('✅ No affected users found');
            return;
        }

        $this->info("Found {$affectedUsers->count()} users with stopped ROI");

        $reactivatedCount = 0;
        $skippedCount = 0;

        foreach ($affectedUsers as $user) {
            $this->info("\n👤 Checking user: {$user->username} (ID: {$user->id})");

            // Check if user actually reached 2X limit
            $totalPaid = $this->accountService->getTotalRoiPaid($user);
            $twoXLimit = $user->roi_eligible_investment_amount * 2;
            $hasReached2X = $totalPaid >= $twoXLimit;

            $this->info("   💰 Total ROI Paid: $" . number_format($totalPaid, 2));
            $this->info("   🎯 2X Limit: $" . number_format($twoXLimit, 2));
            $this->info("   📊 Stop Reason: " . ($user->stop_reason ?? 'None'));

            if ($hasReached2X) {
                $this->warn("   ⚠️  User legitimately reached 2X limit - keeping stopped");
                $skippedCount++;
                continue;
            }

            if ($user->stop_reason !== '2x_limit_reached') {
                $this->info("   🔄 User stopped for reason: '{$user->stop_reason}' but hasn't reached 2X - REACTIVATING");

                if (!$dryRun) {
                    DB::beginTransaction();
                    try {
                        $user->update([
                            'roi_status' => 'active',
                            'stop_reason' => null,
                            'stop_reason_description' => null,
                            'roi_stopped_at' => null
                        ]);

                        Log::info("ROI reactivated for user {$user->id} - was incorrectly stopped", [
                            'user_id' => $user->id,
                            'username' => $user->username,
                            'previous_stop_reason' => $user->stop_reason,
                            'total_paid' => $totalPaid,
                            'two_x_limit' => $twoXLimit
                        ]);

                        DB::commit();
                        $this->info("   ✅ ROI REACTIVATED");
                        $reactivatedCount++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error("   ❌ Failed to reactivate: " . $e->getMessage());
                    }
                } else {
                    $this->info("   ✅ WOULD REACTIVATE ROI (dry run)");
                    $reactivatedCount++;
                }
            } else {
                $this->warn("   ⚠️  User marked as reached 2X but calculations show otherwise - check manually");
                $skippedCount++;
            }
        }

        $this->info("\n📊 SUMMARY:");
        $this->info("   🔄 Users reactivated: {$reactivatedCount}");
        $this->info("   ⏭️  Users skipped: {$skippedCount}");

        if ($dryRun && $reactivatedCount > 0) {
            $this->warn("\n🚀 Run without --dry-run to apply changes");
        }

        if ($reactivatedCount > 0 && !$dryRun) {
            $this->info("\n✅ All eligible users have been reactivated for ROI!");
        }
    }
}
