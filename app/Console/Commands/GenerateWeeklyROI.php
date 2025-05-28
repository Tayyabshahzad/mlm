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
    protected $signature = 'roi:generate-weekly';
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
        $this->info('Starting Weekly ROI generation...');
        
        $week = $this->getWeekConfiguration(); 
        if (!$week) {
            return Command::FAILURE;
        }

        $users = $this->getEligibleUsers();
      
        foreach ($users as $user) {
            $this->processUser($user, $week);
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
                    $query->whereNull('roi_status')
                      ->orWhere('roi_status', 'active');
                })
                ->get();
    }

    private function processUser(User $user, Week $week): void
    {
        try {
            DB::beginTransaction(); 
            // Early checks for account eligibility
            if ($this->shouldSkipUser($user)) {
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
            $this->processRoiPayment($user, $roiPayment, $week->percentage);
               
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

    private function shouldSkipUser(User $user): bool
    {
        // Check and stop if 2X limit reached
        if ($this->accountService->checkAndStopAccountAt2X($user)) {
            $this->logUserAction($user, 'stopped', 'Reached 2X limit');
            $this->counters['stopped']++;
            return true;
        }

        // Check if user can receive ROI
        if (!$this->accountService->canReceiveRoi($user)) {
            $this->logUserAction($user, 'skipped', 'ROI disabled');
            $this->counters['skipped']++;
            return true;
        }

        // Skip if already paid today
        if ($this->wasRoiPaidToday($user)) {
            $this->logUserAction($user, 'skipped', 'ROI already generated today');
            $this->counters['skipped']++;
            return true;
        }

        return false;
    }

    private function wasRoiPaidToday(User $user): bool
    {
        return $user->last_roi_payment_date && 
               Carbon::parse($user->last_roi_payment_date)->isToday();
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
        $investedAmount = $user->roi_eligible_investment_amount;
        $proposedAmount = ($investedAmount * $week->percentage) / 100;
        
        return $this->accountService->calculateSafeRoiAmount($user, $proposedAmount);
    }

    private function processRoiPayment(User $user, float $amount, float $percentage): void
    {
        // Update user
        $user->increment('roi_wallet_balance', $amount);
        $user->update(['last_roi_payment_date' => now()]);

        // Create wallet entry
        Wallet::create([
            'user_id' => $user->id,
            'wallet_type' => 'roi',
            'balance' => $amount,
            'level' => '-',
            'commission_type' => 'Roi',
            'total_amount' => $amount,
            'percentage' => $percentage,
        ]);

        // Create transaction record
        ROITransaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'description' => 'Weekly ROI Generated',
        ]);

        $this->logUserAction($user, 'processed', "Amount: {$amount}");
    }

    private function handleFinalAccountCheck(User $user): void
    {
        if ($this->accountService->checkAndStopAccountAt2X($user)) {
            $this->logUserAction($user, 'stopped', 'Reached 2X limit after payment');
            $this->counters['stopped']++;
        } else {
            $this->counters['processed']++;
        }
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