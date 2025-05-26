<?php

namespace App\Services;

use App\Models\InvestmentSlab;
use App\Models\User;
use App\Models\UserInvestment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class InvestmentSlabService
{
    private const SLAB_AMOUNT = 100;
    private const SLAB_MATURITY_MONTHS = 24;

    public function createSlabsForUser(User $user): int
    {
        $totalInvestment = $this->getTotalInvestment($user);
        $existingSlabCount = $this->getExistingSlabCount($user);
        $availableInvestment = $totalInvestment - ($existingSlabCount * self::SLAB_AMOUNT);
        
        if ($availableInvestment < self::SLAB_AMOUNT) {
            return 0;
        }
        
        $newSlabsCount = intval($availableInvestment / self::SLAB_AMOUNT);
        $newSlabsCreated = 0;
        
        for ($i = 1; $i <= $newSlabsCount; $i++) {
            $this->createSlab($user, $existingSlabCount + $i);
            $newSlabsCreated++;
        }

        if ($newSlabsCreated > 0) {
            Log::info("Created investment slabs", [
                'user_id' => $user->id,
                'slabs_created' => $newSlabsCreated,
                'total_slabs' => $existingSlabCount + $newSlabsCreated,
                'total_investment' => $totalInvestment
            ]);
        }

        return $newSlabsCreated;
    }

    private function getTotalInvestment(User $user): float
    {
        return UserInvestment::where('user_id', $user->id)->sum('amount');
    }

    private function getExistingSlabCount(User $user): int
    {
        return InvestmentSlab::where('user_id', $user->id)->count();
    }

    private function createSlab(User $user, int $slabCount): InvestmentSlab
    {
        $achievedAt = now();
        $maturityDate = $achievedAt->copy()->addMonths(self::SLAB_MATURITY_MONTHS);
        $willPayAt = $maturityDate->copy()->startOfMonth()->addMonth();

        return InvestmentSlab::create([
            'user_id' => $user->id,
            'slab_count' => $slabCount,
            'amount' => self::SLAB_AMOUNT,
            'achived_at' => $achievedAt,
            'current_balance' => $user->roi_eligible_investment_amount,
            'maturity_date' => $maturityDate,
            'will_pay_at' => $willPayAt,
            'status' => 'No',
        ]);
    }

    public function getSlabSummaryForUser(User $user): array
    {
        $totalInvestment = $this->getTotalInvestment($user);
        $totalSlabs = $this->getExistingSlabCount($user);
        $allocatedAmount = $totalSlabs * self::SLAB_AMOUNT;
        $availableForNewSlabs = $totalInvestment - $allocatedAmount;

        return [
            'total_investment' => $totalInvestment,
            'total_slabs' => $totalSlabs,
            'allocated_amount' => $allocatedAmount,
            'available_for_new_slabs' => $availableForNewSlabs,
            'potential_new_slabs' => intval($availableForNewSlabs / self::SLAB_AMOUNT),
            'slab_amount' => self::SLAB_AMOUNT,
            'maturity_months' => self::SLAB_MATURITY_MONTHS,
        ];
    }

    public function getActiveSlabsForUser(User $user): Collection
    {
        return InvestmentSlab::where('user_id', $user->id)
            ->where('status', '!=', 'paid')
            ->orderBy('achived_at')
            ->get();
    }

    public function getMaturedSlabsForUser(User $user): Collection
    {
        return InvestmentSlab::where('user_id', $user->id)
            ->where('maturity_date', '<=', now())
            ->where('status', '!=', 'paid')
            ->orderBy('maturity_date')
            ->get();
    }

    public function canCreateNewSlabs(User $user): bool
    {
        $summary = $this->getSlabSummaryForUser($user);
        return $summary['potential_new_slabs'] > 0;
    }

    public function getSlabConstants(): array
    {
        return [
            'slab_amount' => self::SLAB_AMOUNT,
            'maturity_months' => self::SLAB_MATURITY_MONTHS,
        ];
    }
}