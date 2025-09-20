<?php

namespace App\Services;

use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Week;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutomatedROIService
{
    private AccountManagementService $accountService;

    private ROICommissionService $roiCommissionService;

    public function __construct(
        AccountManagementService $accountService,
        ROICommissionService $roiCommissionService
    ) {
        $this->accountService = $accountService;
        $this->roiCommissionService = $roiCommissionService;
    }

    /**
     * Trigger automated ROI payment for a specific user
     */
    public function triggerAutomatedROI(User $user, string $triggerReason = 'automated'): array
    {
        try {
            Log::info('Automated ROI trigger initiated', [
                'user_id' => $user->id,
                'username' => $user->username,
                'trigger_reason' => $triggerReason,
            ]);

            // Check if user can receive ROI
            if (! $this->accountService->canReceiveRoi($user)) {
                return [
                    'success' => false,
                    'message' => 'User cannot receive ROI at this time',
                    'amount' => 0,
                ];
            }

            // Check if user has already received ROI today
            if ($this->hasReceivedROIToday($user)) {
                return [
                    'success' => false,
                    'message' => 'ROI already processed today',
                    'amount' => 0,
                ];
            }

            // Get week configuration
            $week = Week::first();
            if (! $week) {
                return [
                    'success' => false,
                    'message' => 'No week configuration found',
                    'amount' => 0,
                ];
            }

            DB::beginTransaction();

            // Calculate ROI payment
            $roiAmount = $this->calculateROIAmount($user, $week);

            if ($roiAmount <= 0) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'No ROI amount calculated or user at 2X limit',
                    'amount' => 0,
                ];
            }

            // Process the ROI payment
            $this->processROIPayment($user, $roiAmount, $week->percentage, $triggerReason);

            // Generate commissions for upline
            $this->roiCommissionService->generateCommissions($user, $roiAmount);

            // Check if user has reached 2X limit after this payment
            $this->accountService->checkAndStopAccountAt2X($user);

            DB::commit();

            Log::info('Automated ROI processed successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'amount' => $roiAmount,
                'trigger_reason' => $triggerReason,
            ]);

            return [
                'success' => true,
                'message' => 'ROI processed successfully',
                'amount' => $roiAmount,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Automated ROI processing failed', [
                'user_id' => $user->id,
                'username' => $user->username,
                'trigger_reason' => $triggerReason,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'ROI processing failed: '.$e->getMessage(),
                'amount' => 0,
            ];
        }
    }

    /**
     * Check if user has received ROI today
     */
    private function hasReceivedROIToday(User $user): bool
    {
        return $user->last_roi_payment_date &&
               \Carbon\Carbon::parse($user->last_roi_payment_date)->isToday();
    }

    /**
     * Calculate ROI amount for user
     */
    private function calculateROIAmount(User $user, Week $week): float
    {
        $investedAmount = $user->roi_eligible_investment_amount;
        $proposedAmount = ($investedAmount * $week->percentage) / 100;

        // Calculate safe amount that won't exceed 2X limit
        return $this->accountService->calculateSafeRoiAmount($user, $proposedAmount);
    }

    /**
     * Process ROI payment
     */
    private function processROIPayment(User $user, float $amount, float $percentage, string $triggerReason): void
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
            'source_type' => $triggerReason,
        ]);

        // Create transaction record
        ROITransaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'percentage' => $percentage,
            'description' => "Automated ROI Generated - Trigger: {$triggerReason}",
            'trigger_reason' => $triggerReason,
        ]);

        Log::info('ROI payment processed', [
            'user_id' => $user->id,
            'amount' => $amount,
            'trigger_reason' => $triggerReason,
        ]);
    }

    /**
     * Process automated ROI for multiple users
     */
    public function processAutomatedROIForUsers(array $userIds, string $triggerReason = 'bulk_automated'): array
    {
        $results = [
            'processed' => 0,
            'failed' => 0,
            'skipped' => 0,
            'total_amount' => 0,
        ];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (! $user) {
                $results['failed']++;

                continue;
            }

            $result = $this->triggerAutomatedROI($user, $triggerReason);

            if ($result['success']) {
                $results['processed']++;
                $results['total_amount'] += $result['amount'];
            } elseif (strpos($result['message'], 'already processed') !== false) {
                $results['skipped']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }
}
