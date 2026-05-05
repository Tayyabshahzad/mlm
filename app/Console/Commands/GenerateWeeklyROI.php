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

class GenerateWeeklyROI extends Command
{
    protected $signature = 'roi:generate-weekly
                            {--date=     : Process ROI for a specific date (Y-m-d), e.g. 2026-05-02}
                            {--dry-run   : Preview eligible users without processing}';
    protected $description = 'Generate weekly ROI and distribute commissions for all eligible users';

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
        $dryRun = $this->option('dry-run');

        $forDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::now();

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $this->info('Starting Weekly ROI generation for: ' . $forDate->toDateString());

        $week = $this->getWeekConfiguration();
        if (!$week) {
            return Command::FAILURE;
        }

        $users = $this->getEligibleUsers();

        if ($dryRun) {
            $this->table(
                ['ID', 'Username', 'Investment', 'ROI %', 'Est. ROI', 'Already Paid'],
                $users->map(function ($u) use ($week, $forDate) {
                    $percentage = $week->getPercentageForUser($u);
                    $investment = \App\Models\UserInvestment::where('user_id', $u->id)->where('roi_status', 'active')->sum('amount');
                    $estimated  = round(($investment * $percentage) / 100, 2);
                    $paid       = $this->wasRoiPaidForDate($u, $forDate) ? 'Yes' : 'No';
                    return [$u->id, $u->username, '$' . number_format($investment, 2), $percentage . '%', '$' . number_format($estimated, 2), $paid];
                })
            );
            return Command::SUCCESS;
        }

        foreach ($users as $user) {
            $this->processUser($user, $week, $forDate);
        }

        $this->displaySummary();
        return Command::SUCCESS;
    }

    private function getWeekConfiguration(): ?Week
    {
        $week = Week::first();
        if (!$week) {
            $this->error('No week configuration found');
        }
        return $week;
    }

    private function getEligibleUsers()
    {
        return User::where('blocked', false)
                ->where('can_login', true)
                ->where('freez_wallet', false)
                ->where(function ($query) {
                    // Standard investment users only — saving users have separate ROI
                    $query->where('account_type', 'standard_investment')
                          ->orWhereNull('account_type');
                })
                ->where(function ($query) {
                    $query->whereNull('roi_status')
                      ->orWhere('roi_status', 'active');
                })
                ->get();
    }

    private function processUser(User $user, Week $week, Carbon $forDate): void
    {
        try {
            DB::beginTransaction();
            if ($this->shouldSkipUser($user, $forDate)) {
                DB::commit();
                return;
            }
           
            // Initialize ROI dates if needed
            $this->initializeRoiDates($user);

            // Calculate and validate ROI payment
            $roiPayment = $this->calculateRoiPayment($user, $week);
         
            if ($roiPayment <= 0) {
                $this->logUserAction($user, 'stopped', 'No ROI due - at 2X limit');
                $this->counters['stopped']++;
                DB::commit();
                return;
            }
         
            // Process the ROI payment
            $percentage = $week->getPercentageForUser($user);
            $this->processRoiPayment($user, $roiPayment, $percentage, $forDate);
               
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

    private function shouldSkipUser(User $user, Carbon $forDate): bool
    {
        if ($this->accountService->checkAndStopAccountAt2X($user)) {
            $this->logUserAction($user, 'stopped', 'Reached 2X limit');
            $this->counters['stopped']++;
            return true;
        }

        if (!$this->accountService->canReceiveRoi($user)) {
            $this->logUserAction($user, 'skipped', 'ROI disabled');
            $this->counters['skipped']++;
            return true;
        }

        if ($this->wasRoiPaidForDate($user, $forDate)) {
            $this->logUserAction($user, 'skipped', 'ROI already generated for ' . $forDate->toDateString());
            $this->counters['skipped']++;
            return true;
        }

        return false;
    }

    private function wasRoiPaidToday(User $user): bool
    {
        return $this->wasRoiPaidForDate($user, Carbon::today());
    }

    private function wasRoiPaidForDate(User $user, Carbon $date): bool
    {
        return ROITransaction::where('user_id', $user->id)
            ->whereDate('created_at', $date->toDateString())
            ->exists();
    }

    private function initializeRoiDates(User $user): void
    {
        if (!$user->roi_start_date) {
            $user->update([
                'roi_start_date' => now(),
                'roi_end_date' => now()->addYears(2)
            ]);
        }
    }

    private function calculateRoiPayment(User $user, Week $week): float
    {
        // Get user's plan-specific percentage (VIP or Standard)
        $percentage = $week->getPercentageForUser($user);

        // Calculate ROI for each active investment separately (Case 4 support)
        $totalRoi = 0;
        $activeInvestments = \App\Models\UserInvestment::where('user_id', $user->id)
            ->where('roi_status', 'active')
            ->get();

        foreach ($activeInvestments as $investment) {
            // Calculate ROI for this specific investment
            $investmentRoi = ($investment->amount * $percentage) / 100;

            // Check if adding this ROI would exceed this investment's 2X limit
            $remainingTo2X = $investment->getRemainingTo2X();

            if ($remainingTo2X > 0) {
                // Add only what's allowed for this investment
                $allowedRoi = min($investmentRoi, $remainingTo2X);
                $totalRoi += $allowedRoi;

                // Update investment's total_earnings
                $investment->increment('total_earnings', $allowedRoi);

                // Mark as completed if reached 2X
                if ($investment->hasReached2X()) {
                    $investment->update([
                        'roi_status' => 'completed',
                        'completed_at' => now()
                    ]);
                    Log::info("Investment {$investment->id} completed 2X for user {$user->id}");
                }
            }
        }

        return $totalRoi;
    }

    private function processRoiPayment(User $user, float $amount, float $percentage, Carbon $forDate): void
    {
        $entryDate = $forDate->copy()->setTime(23, 40, 0);

        $user->increment('roi_wallet_balance', $amount);
        if (!$user->last_roi_payment_date || $forDate->gt(Carbon::parse($user->last_roi_payment_date))) {
            $user->update(['last_roi_payment_date' => $forDate->toDateString()]);
        }

        $wallet = Wallet::create([
            'user_id'         => $user->id,
            'wallet_type'     => 'roi',
            'balance'         => $amount,
            'level'           => '-',
            'commission_type' => 'Roi',
            'total_amount'    => $amount,
            'percentage'      => $percentage,
        ]);
        $wallet->forceFill(['created_at' => $entryDate, 'updated_at' => $entryDate])->saveQuietly();

        $transaction = ROITransaction::create([
            'user_id'     => $user->id,
            'amount'      => $amount,
            'percentage'  => $percentage,
            'description' => 'Weekly ROI Generated',
        ]);
        $transaction->forceFill(['created_at' => $entryDate, 'updated_at' => $entryDate])->saveQuietly();

        $this->logUserAction($user, 'processed', "Amount: {$amount}");
    }

    private function handleFinalAccountCheck(User $user): void
    {
        // Only check 2X limit for ROI stopping. 7X is handled separately for withdrawals.
        $stopped2X = $this->accountService->checkAndStopAccountAt2X($user);

        if ($stopped2X) {
            $this->logUserAction($user, 'stopped', 'Reached 2X limit after payment - ROI permanently stopped');
            $this->counters['stopped']++;
        } else {
            $this->counters['processed']++;
        }

        // Separately check 7X for withdrawal control (doesn't stop ROI)
        $this->accountService->checkAndStopAccountAt7X($user);
    }

    private function handleUserProcessingError(User $user, \Exception $e): void
    {
        $errorMessage = "Failed to process ROI for user {$user->id}: " . $e->getMessage();
        Log::error($errorMessage);
        $this->error($errorMessage);
    }

    private function logUserAction(User $user, string $action, string $message): void
    {
        $logMessage = ucfirst($action) . " user {$user->id} | {$user->name} - {$message}";
        
        if ($action === 'processed') {
            $this->info("ROI generated for " . $logMessage);
        } else {
            $this->info($logMessage);
        }
    }

    private function displaySummary(): void
    {
        $this->info("Weekly ROI generation completed.");
        $this->info("Processed: {$this->counters['processed']} users");
        $this->info("Skipped: {$this->counters['skipped']} users");
        $this->info("Stopped: {$this->counters['stopped']} users");
    }
}