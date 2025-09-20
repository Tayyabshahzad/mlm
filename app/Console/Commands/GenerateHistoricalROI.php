<?php

namespace App\Console\Commands;

use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Week;
use App\Services\AccountManagementService;
use App\Services\ROICommissionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateHistoricalROI extends Command
{
    protected $signature = 'roi:generate-historical
                            {date : The date to generate ROI for (YYYY-MM-DD format)}
                            {--dry-run : Preview without making changes}
                            {--force : Skip safety checks and force generation}
                            {--user-id= : Generate ROI for specific user only}';

    protected $description = 'Generate ROI for a specific historical date with safety checks';

    private AccountManagementService $accountService;

    private ROICommissionService $ROICommissionService;

    private array $counters = ['processed' => 0, 'skipped' => 0, 'stopped' => 0];

    public function __construct(
        AccountManagementService $accountService,
        ROICommissionService $ROICommissionService
    ) {
        parent::__construct();
        $this->accountService = $accountService;
        $this->ROICommissionService = $ROICommissionService;
    }

    public function handle(): int
    {
        $dateString = $this->argument('date');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $specificUserId = $this->option('user-id');

        // Validate date format
        try {
            $targetDate = Carbon::createFromFormat('Y-m-d', $dateString)->startOfDay();
        } catch (\Exception $e) {
            $this->error('Invalid date format. Please use YYYY-MM-DD format.');

            return Command::FAILURE;
        }

        $this->info("Processing ROI for date: {$targetDate->format('Y-m-d')}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        if ($force) {
            $this->warn('FORCE MODE - Safety checks disabled');
        }

        // Safety checks
        if (! $force && ! $this->performSafetyChecks($targetDate)) {
            return Command::FAILURE;
        }

        $week = $this->getWeekConfiguration();
        if (! $week) {
            return Command::FAILURE;
        }

        $users = $this->getEligibleUsers($specificUserId);
        $this->info('Found '.count($users).' eligible users');

        if ($dryRun) {
            $this->info('DRY RUN: Would process ROI for '.count($users).' users');

            return Command::SUCCESS;
        }

        foreach ($users as $user) {
            $this->processUser($user, $week, $targetDate);
        }

        $this->displaySummary($targetDate);

        return Command::SUCCESS;
    }

    private function performSafetyChecks(Carbon $targetDate): bool
    {
        // Don't allow future dates
        if ($targetDate->isAfter(Carbon::today())) {
            $this->error('Cannot generate ROI for future dates');

            return false;
        }

        // Don't allow dates too far in the past (more than 30 days)
        if ($targetDate->isBefore(Carbon::today()->subDays(30))) {
            $this->error('Cannot generate ROI for dates older than 30 days');
            $this->info('Use --force flag to override this safety check');

            return false;
        }

        // Check if ROI was already generated for this date
        $existingCount = ROITransaction::whereDate('created_at', $targetDate->format('Y-m-d'))->count();
        if ($existingCount > 0) {
            $this->warn("Found {$existingCount} existing ROI transactions for this date");
            if (! $this->confirm('Do you want to continue? This may create duplicate payments.')) {
                return false;
            }
        }

        return true;
    }

    private function getWeekConfiguration(): ?Week
    {
        $week = Week::first();
        if (! $week) {
            $this->error('No week configuration found');
        }

        return $week;
    }

    private function getEligibleUsers(?int $specificUserId)
    {
        $query = User::where('blocked', false)
            ->where('can_login', true)
            ->where('freez_wallet', false)
            ->where(function ($query) {
                $query->whereNull('roi_status')
                    ->orWhere('roi_status', 'active');
            });

        if ($specificUserId) {
            $query->where('id', $specificUserId);
        }

        return $query->get();
    }

    private function processUser(User $user, Week $week, Carbon $targetDate): void
    {
        try {
            DB::beginTransaction();

            // Check if user already received ROI on this specific date
            if ($this->wasRoiPaidOnDate($user, $targetDate)) {
                $this->logUserAction($user, 'skipped', 'ROI already generated for this date');
                $this->counters['skipped']++;
                DB::commit();

                return;
            }

            // Early checks for account eligibility
            if ($this->shouldSkipUser($user, $targetDate)) {
                DB::commit();

                return;
            }

            // Calculate and validate ROI payment
            $roiPayment = $this->calculateRoiPayment($user, $week);

            if ($roiPayment <= 0) {
                $this->logUserAction($user, 'stopped', 'No ROI due - at 2X limit');
                $this->counters['stopped']++;
                DB::commit();

                return;
            }

            // Process the ROI payment with the target date
            $this->processRoiPayment($user, $roiPayment, $week->percentage, $targetDate);

            // Generate commissions for upline
            $this->ROICommissionService->generateCommissions($user, $roiPayment);

            // Final check for 2X limit
            $this->handleFinalAccountCheck($user);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->handleUserProcessingError($user, $e);
        }
    }

    private function wasRoiPaidOnDate(User $user, Carbon $targetDate): bool
    {
        return ROITransaction::where('user_id', $user->id)
            ->whereDate('created_at', $targetDate->format('Y-m-d'))
            ->where('description', 'NOT LIKE', '%commission%')
            ->exists();
    }

    private function shouldSkipUser(User $user, Carbon $targetDate): bool
    {
        // Check and stop if 2X limit reached
        if ($this->accountService->checkAndStopAccountAt2X($user)) {
            $this->logUserAction($user, 'stopped', 'Reached 2X limit');
            $this->counters['stopped']++;

            return true;
        }

        // Check if user can receive ROI
        if (! $this->accountService->canReceiveRoi($user)) {
            $this->logUserAction($user, 'skipped', 'ROI disabled');
            $this->counters['skipped']++;

            return true;
        }

        return false;
    }

    private function calculateRoiPayment(User $user, Week $week): float
    {
        $investedAmount = $user->roi_eligible_investment_amount;
        $proposedAmount = ($investedAmount * $week->percentage) / 100;

        return $this->accountService->calculateSafeRoiAmount($user, $proposedAmount);
    }

    private function processRoiPayment(User $user, float $amount, float $percentage, Carbon $targetDate): void
    {
        // Update user
        $user->increment('roi_wallet_balance', $amount);
        $user->update(['last_roi_payment_date' => $targetDate]);

        // Create wallet entry with target date
        $wallet = new Wallet([
            'user_id' => $user->id,
            'wallet_type' => 'roi',
            'balance' => $amount,
            'level' => '-',
            'commission_type' => 'Roi',
            'total_amount' => $amount,
            'percentage' => $percentage,
            'source_type' => 'historical_generation',
        ]);
        $wallet->created_at = $targetDate;
        $wallet->save();

        // Create transaction record with target date
        $transaction = new ROITransaction([
            'user_id' => $user->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'description' => 'Historical ROI Generated for '.$targetDate->format('Y-m-d'),
            'trigger_reason' => 'historical_command',
        ]);
        $transaction->created_at = $targetDate;
        $transaction->save();

        $this->logUserAction($user, 'processed', "Amount: {$amount}");
    }

    private function handleFinalAccountCheck(User $user): void
    {
        $stopped2X = $this->accountService->checkAndStopAccountAt2X($user);

        if ($stopped2X) {
            $this->logUserAction($user, 'stopped', 'Reached 2X limit after payment');
            $this->counters['stopped']++;
        } else {
            $this->counters['processed']++;
        }

        $this->accountService->checkAndStopAccountAt7X($user);
    }

    private function handleUserProcessingError(User $user, \Exception $e): void
    {
        $errorMessage = "Failed to process ROI for user {$user->id}: ".$e->getMessage();
        Log::error($errorMessage);
        $this->error($errorMessage);
    }

    private function logUserAction(User $user, string $action, string $message): void
    {
        $logMessage = ucfirst($action)." user {$user->id} | {$user->name} - {$message}";

        if ($action === 'processed') {
            $this->info('ROI generated for '.$logMessage);
        } else {
            $this->info($logMessage);
        }
    }

    private function displaySummary(Carbon $targetDate): void
    {
        $this->info("Historical ROI generation completed for {$targetDate->format('Y-m-d')}");
        $this->info('Processed: '.$this->counters['processed'].' users');
        $this->info('Skipped: '.$this->counters['skipped'].' users');
        $this->info('Stopped: '.$this->counters['stopped'].' users');
    }
}
