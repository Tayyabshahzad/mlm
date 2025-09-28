<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AccountManagementService
{
    private const TWO_X_MULTIPLIER = 2;
    private const SEVEN_X_MULTIPLIER = 7;
    private const ROI_PERIOD_YEARS = 2;

    /**
     * Check if user can receive ROI payments
     */
    public function canReceiveRoi(User $user): bool
    {
        return $this->isAccountActive($user) &&
               $this->hasEligibleInvestment($user) &&
               !$this->isRoiPeriodExpired($user) &&
               !$this->isRoiDisabled($user);
    }

    /**
     * Check if user has reached 2X ROI limit and stop account if needed
     */
    public function checkAndStopAccountAt2X(User $user): bool
    {
        if ($this->hasReached2XLimit($user)) {
            $this->stopRoiAccount($user, '2x_limit_reached');
            return true;
        }
        return false;
    }

    /**
     * Check if user has reached 7X ROI limit and stop account if needed
     */
    public function checkAndStopAccountAt7X(User $user): bool
    {
        if ($this->hasReached7XLimit($user)) {
            $this->stopRoiAccount($user, '7x_limit_reached', 'ROI stopped - 7X earnings limit reached. Withdrawal disabled until topup.');
            return true;
        }
        return false;
    }

    /**
     * Calculate safe ROI payment amount (won't exceed 2X limit)
     */
    public function calculateSafeRoiAmount(User $user, float $proposedAmount): float
    {
        $remainingAmount = $this->getRemainingRoiAmount($user);
        return min($proposedAmount, $remainingAmount);
    }

    /**
     * Calculate safe ROI payment amount (won't exceed 7X limit)
     */
    public function calculateSafeRoi7XAmount(User $user, float $proposedAmount): float
    {
        $remaining7X = $this->getRemaining7XRoiAmount($user);
        return min($proposedAmount, $remaining7X);
    }

    /**
     * Get combined safe amount that respects both 2X and 7X limits
     */
    public function calculateCombinedSafeRoiAmount(User $user, float $proposedAmount): float
    {
        $safe2X = $this->calculateSafeRoiAmount($user, $proposedAmount);
        $safe7X = $this->calculateSafeRoi7XAmount($user, $proposedAmount);
        return min($safe2X, $safe7X);
    }

    /**
     * Get remaining ROI amount before reaching 2X limit
     */
    public function getRemainingRoiAmount(User $user): float
    {
        $totalPaid = $this->getTotalRoiPaid($user);
        $limit = $this->get2XLimit($user);
        return max(0, $limit - $totalPaid);
    }

    /**
     * Get remaining ROI amount before reaching 7X limit
     */
    public function getRemaining7XRoiAmount(User $user): float
    {
        $totalPaid = $this->getTotalRoiPaid($user);
        $limit = $this->get7XLimit($user);
        return max(0, $limit - $totalPaid);
    }

    /**
     * Get total ROI paid to user (including commissions)
     */
    public function getTotalRoiPaid(User $user): float
    {
        return Wallet::where('user_id', $user->id)->sum('total_amount');
    }

    /**
     * Get only direct ROI payments (excluding commissions)
     */
    public function getDirectRoiPaid(User $user): float
    {
        return Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'roi')
            ->sum('total_amount');
    }

    /**
     * Stop ROI account with reason
     */
    public function stopRoiAccount(User $user, string $reason = 'manual_stop', $description = 'Roi has been stoped'): void
    {
        $user->update([
            'roi_status' => 'stopped',
            'roi_stopped_at' => now(),
            'stop_reason' => $reason,
            'stop_reason_description'=>$description
        ]);

        $this->logAccountAction($user, 'stopped', $reason);
    }

    /**
     * Reactivate ROI account
     */
    public function reactivateRoiAccount(User $user): void
    {
        $user->update([
            'roi_status' => 'active',
            'roi_stopped_at' => null,
            'stop_reason' => null,
        ]);

        $this->logAccountAction($user, 'reactivated');
    }

    /**
     * Get comprehensive ROI account statistics
     */
    public function getRoiAccountStats(User $user): array
    {
        $totalPaid = $this->getTotalRoiPaid($user);
        $directPaid = $this->getDirectRoiPaid($user);
        $invested = $user->roi_eligible_investment_amount;
        $limit2X = $this->get2XLimit($user);
        $limit7X = $this->get7XLimit($user);

        return [
            'invested_amount' => $invested,
            'total_roi_paid' => $totalPaid,
            'direct_roi_paid' => $directPaid,
            'commission_earned' => $totalPaid - $directPaid,
            'two_x_limit' => $limit2X,
            'seven_x_limit' => $limit7X,
            'remaining_amount' => $this->getRemainingRoiAmount($user),
            'remaining_7x_amount' => $this->getRemaining7XRoiAmount($user),
            'completion_percentage' => $this->getCompletionPercentage($user),
            'completion_7x_percentage' => $this->get7XCompletionPercentage($user),
            'has_reached_2x' => $this->hasReached2XLimit($user),
            'has_reached_7x' => $this->hasReached7XLimit($user),
            'withdrawal_enabled' => $this->isWithdrawalEnabled($user),
            'roi_status' => $user->roi_status ?? 'active',
            'roi_start_date' => $user->roi_start_date,
            'roi_end_date' => $user->roi_end_date,
            'last_payment_date' => $user->last_roi_payment_date,
            'is_expired' => $this->isRoiPeriodExpired($user),
            'can_receive_roi' => $this->canReceiveRoi($user),
            'days_since_start' => $this->getDaysSinceRoiStart($user),
            'estimated_completion_date' => $this->getEstimatedCompletionDate($user),
        ];
    }

    /**
     * Bulk check and stop accounts that have reached 2X limit
     */
    public function bulkCheckAndStop2XAccounts(): int
    {
        $users = $this->getActiveRoiUsers();
        $stoppedCount = 0;

        foreach ($users as $user) {
            if ($this->checkAndStopAccountAt2X($user)) {
                $stoppedCount++;
            }
        }

        return $stoppedCount;
    }

    /**
     * Initialize ROI dates for a user
     */
    public function initializeRoiDates(User $user): void
    {
        if (!$user->roi_start_date) {
            $user->update([
                'roi_start_date' => now(),
                'roi_end_date' => now()->addYears(self::ROI_PERIOD_YEARS),
            ]);
        }
    }

    // Private helper methods

    private function isAccountActive(User $user): bool
    {
        return !$user->blocked && $user->can_login && !$user->freez_wallet;
    }

    private function hasEligibleInvestment(User $user): bool
    {
        return $user->roi_eligible_investment_amount && $user->roi_eligible_investment_amount > 0;
    }

    private function isRoiDisabled(User $user): bool
    {
        // FIXED: ROI should only be disabled if user has reached 2X limit
        // Not because of binary system completion or other reasons
        if (in_array($user->roi_status, ['disabled'])) {
            return true;
        }

        // Only stop ROI if specifically stopped for 2X limit reached
        if ($user->roi_status === 'stopped' && $user->stop_reason === '2x_limit_reached') {
            return true;
        }

        return false;
    }

    public function isRoiPeriodExpired(User $user): bool
    {
        return $user->roi_end_date && Carbon::parse($user->roi_end_date)->isPast();
    }

    private function get2XLimit(User $user): float
    {
        return $user->roi_eligible_investment_amount * self::TWO_X_MULTIPLIER;
    }

    private function get7XLimit(User $user): float
    {
        return $user->roi_eligible_investment_amount * self::SEVEN_X_MULTIPLIER;
    }

    public function hasReached7XLimit(User $user): bool
    {
        return $this->getTotalRoiPaid($user) >= $this->get7XLimit($user);
    }

    public function hasReached2XLimit(User $user): bool
    {
        return $this->getTotalRoiPaid($user) >= $this->get2XLimit($user);
    }

    private function getCompletionPercentage(User $user): float
    {
        $invested = $user->roi_eligible_investment_amount;
        if ($invested <= 0) return 0;

        $totalPaid = $this->getTotalRoiPaid($user);
        $limit = $this->get2XLimit($user);

        return ($totalPaid / $limit) * 100;
    }

    private function get7XCompletionPercentage(User $user): float
    {
        $invested = $user->roi_eligible_investment_amount;
        if ($invested <= 0) return 0;

        $totalPaid = $this->getTotalRoiPaid($user);
        $limit = $this->get7XLimit($user);

        return ($totalPaid / $limit) * 100;
    }

    /**
     * Check if withdrawal is enabled (not reached 7X limit)
     */
    public function isWithdrawalEnabled(User $user): bool
    {
        return !$this->hasReached7XLimit($user);
    }

    private function getDaysSinceRoiStart(User $user): ?int
    {
        if (!$user->roi_start_date) return null;

        return Carbon::parse($user->roi_start_date)->diffInDays(now());
    }

    private function getEstimatedCompletionDate(User $user): ?Carbon
    {
        // This is a basic estimation - you might want to make it more sophisticated
        if (!$user->roi_start_date || !$user->last_roi_payment_date) {
            return null;
        }

        $remaining = $this->getRemainingRoiAmount($user);
        if ($remaining <= 0) return null;

        // Estimate based on recent payment frequency
        $daysSinceStart = $this->getDaysSinceRoiStart($user);
        $totalPaid = $this->getTotalRoiPaid($user);

        if ($daysSinceStart <= 0 || $totalPaid <= 0) return null;

        $dailyRate = $totalPaid / $daysSinceStart;
        $daysToCompletion = $remaining / $dailyRate;

        return now()->addDays($daysToCompletion);
    }

    private function getActiveRoiUsers()
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

    private function logAccountAction(User $user, string $action, ?string $reason = null): void
    {
        $context = [
            'user_id' => $user->id,
            'action' => $action,
            'total_roi_paid' => $this->getTotalRoiPaid($user),
            'investment_amount' => $user->roi_eligible_investment_amount,
        ];

        if ($reason) {
            $context['reason'] = $reason;
        }

        Log::info("ROI account {$action} for user {$user->id}", $context);
    }
}