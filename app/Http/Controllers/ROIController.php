<?php

namespace App\Http\Controllers;

use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AccountManagementService;
use App\Services\ROICommissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ROIController extends Controller
{
    public function __construct(
        private AccountManagementService $accountService,
        private ROICommissionService $roiCommissionService,
    ) {}

    public function submitRoiPayments(Request $request)
    {
        $request->validate([
            'user_id'               => 'required|exists:users,id',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'description'           => 'required',
        ]);

        $user = User::where('can_login', true)
            ->where('blocked', false)
            ->find($request->user_id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found or account is inactive.');
        }

        // Use the canonical 2X-limit check from AccountManagementService
        if ($this->accountService->checkAndStopAccountAt2X($user)) {
            return redirect()->back()->with('error', '2X limit already reached for this user.');
        }

        if (!$this->accountService->canReceiveRoi($user)) {
            return redirect()->back()->with('error', 'This user cannot receive ROI at this time.');
        }

        if (!$user->roi_start_date) {
            $user->update([
                'roi_start_date' => now(),
                'roi_end_date'   => now()->addYears(2),
            ]);
            $user->refresh();
        }

        $percentage  = (float) $request->commission_percentage;
        $roiBase     = (float) $user->roi_eligible_investment_amount;
        $roiPayment  = round(($roiBase * $percentage) / 100, 2);

        // Clamp to safe amount so the 2X cap is respected
        $roiPayment = $this->accountService->calculateSafeRoiAmount($user, $roiPayment);

        if ($roiPayment <= 0) {
            return redirect()->back()->with('error', 'Calculated ROI is zero — user may be at their 2X limit.');
        }

        $user->increment('roi_wallet_balance', $roiPayment);
        $user->update(['last_roi_payment_date' => now()->toDateString()]);

        Wallet::create([
            'user_id'         => $user->id,
            'wallet_type'     => 'roi',
            'balance'         => $roiPayment,
            'total_amount'    => $roiPayment,
            'level'           => '-',
            'commission_type' => 'Roi',
            'percentage'      => $percentage,
        ]);

        ROITransaction::create([
            'user_id'     => $user->id,
            'amount'      => $roiPayment,
            'percentage'  => $percentage,
            'description' => 'Manual ROI: ' . $request->description,
        ]);

        // Use the canonical ROICommissionService so upline ancestors receive
        // the correct plan-based (VIP vs standard) profit_share percentages
        // and the active-user eligibility check is enforced.
        $this->roiCommissionService->generateCommissions($user, $roiPayment);

        // Final 2X check after crediting
        $this->accountService->checkAndStopAccountAt2X($user);

        return redirect()->back()->with('success', 'ROI of ' . number_format($roiPayment, 2) . ' generated successfully.');
    }
}
