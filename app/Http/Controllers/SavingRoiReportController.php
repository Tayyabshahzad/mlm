<?php

namespace App\Http\Controllers;

use App\Exports\SavingRoiExport;
use App\Models\User;
use App\Models\Wallet;
use App\Services\SavingAccountService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SavingRoiReportController extends Controller
{
    // ─── Admin: all users ────────────────────────────────────────────────────

    public function adminIndex(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : Carbon::now()->endOfDay();
        $userId    = $request->user_id;

        $query = Wallet::with('user')
            ->where('wallet_type', 'saving_roi')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Chart: daily totals
        $chartData = (clone $query)
            ->selectRaw('DATE(created_at) as date, SUM(balance) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartDates  = $chartData->pluck('date')->toArray();
        $chartTotals = $chartData->pluck('total')->map(fn($v) => round($v, 2))->toArray();

        // Per-user totals for summary table
        $userTotals = (clone $query)
            ->selectRaw('user_id, SUM(balance) as total, COUNT(*) as days_paid')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        // Detailed entries paginated
        $entries = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        // Users that have saving ROI entries (for report filter dropdown)
        $savingUsers = User::whereHas('wallets', fn($q) => $q->where('wallet_type', 'saving_roi'))
            ->orderBy('username')
            ->get(['id', 'username', 'name']);

        // All eligible saving users (for manual ROI run panel)
        $allSavingUsers = User::where(function ($q) {
                $q->where('account_type', 'saving')
                  ->orWhere(fn($q2) => $q2->where('saving_enrolled', true)->where('saving_initial_payment', '>', 0));
            })
            ->where('saving_registration_completed', true)
            ->where('blocked', false)
            ->where('freez_wallet', false)
            ->where('saving_total_deposited', '>', 0)
            ->where(function ($q) {
                $q->where(fn($q2) => $q2->where('account_type', 'saving')->where('can_login', true))
                  ->orWhere(fn($q2) => $q2->where('saving_enrolled', true)->where('saving_enrollment_activated', true));
            })
            ->orderBy('username')
            ->get(['id', 'username', 'name', 'last_saving_roi_payment_date']);

        $totalRoi     = array_sum($chartTotals);
        $totalEntries = $userTotals->sum('days_paid');

        return view('admin.saving-roi-report', compact(
            'entries', 'userTotals', 'savingUsers', 'allSavingUsers',
            'chartDates', 'chartTotals',
            'startDate', 'endDate', 'userId',
            'totalRoi', 'totalEntries'
        ));
    }

    public function adminExport(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;
        $userId    = $request->user_id;
        $filename  = 'saving_roi_report_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(new SavingRoiExport($startDate, $endDate, $userId), $filename);
    }

    // ─── User: own ROI only ───────────────────────────────────────────────────

    public function userIndex(Request $request)
    {
        $user = auth()->user();

        abort_unless(
            $user->account_type === 'saving' ||
            ($user->saving_enrolled && $user->saving_enrollment_activated),
            403
        );

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->subDays(29)->startOfDay();
        $endDate   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : Carbon::now()->endOfDay();

        $query = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'saving_roi')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Chart: daily totals
        $chartData = (clone $query)
            ->selectRaw('DATE(created_at) as date, SUM(balance) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $chartDates  = $chartData->pluck('date')->toArray();
        $chartTotals = $chartData->pluck('total')->map(fn($v) => round($v, 2))->toArray();

        // Entries paginated
        $entries = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $totalRoi      = array_sum($chartTotals);
        $totalAllTime  = Wallet::where('user_id', $user->id)->where('wallet_type', 'saving_roi')->sum('balance');

        return view('wallets.saving-roi-report', compact(
            'entries', 'chartDates', 'chartTotals',
            'startDate', 'endDate',
            'totalRoi', 'totalAllTime'
        ));
    }

    public function userExport(Request $request)
    {
        $user = auth()->user();

        abort_unless(
            $user->account_type === 'saving' ||
            ($user->saving_enrolled && $user->saving_enrollment_activated),
            403
        );

        $filename = 'my_saving_roi_' . date('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new SavingRoiExport($request->start_date, $request->end_date, $user->id),
            $filename
        );
    }

    // ─── Admin: manual ROI run ────────────────────────────────────────────────

    public function manualRun(Request $request, SavingAccountService $savingAccountService)
    {
        $request->validate([
            'user_ids'     => 'required|array|min:1',
            'user_ids.*'   => 'integer|exists:users,id',
            'roi_date'     => 'required|date|before_or_equal:today',
            'roi_type'     => 'required|in:saving_roi,roi',
            'custom_rate'  => 'nullable|numeric|min:0.001|max:100',
            'fixed_amount' => 'nullable|numeric|min:0.01',
            'description'  => 'nullable|string|max:300',
        ]);

        $forDate     = \Carbon\Carbon::parse($request->roi_date);
        $roiType     = $request->roi_type;
        $customRate  = $request->filled('custom_rate')  ? (float) $request->custom_rate  : null;
        $fixedAmount = $request->filled('fixed_amount') ? (float) $request->fixed_amount : null;
        $description = $request->filled('description')  ? trim($request->description)    : null;
        $useFixed    = $fixedAmount !== null;
        $results     = ['processed' => [], 'skipped' => [], 'failed' => []];
        $totalAmount = 0;

        foreach ($request->user_ids as $userId) {
            $user = User::find($userId);
            if (!$user) {
                $results['failed'][] = ['id' => $userId, 'reason' => 'User not found'];
                continue;
            }

            $typeLabel    = $roiType === 'saving_roi' ? 'Saving ROI' : 'Standard ROI';
            $fallbackDesc = "{$typeLabel} — Manual ({$forDate->format('d M Y')})";

            if ($useFixed) {
                // Exact dollar amount — any wallet type, no eligibility checks
                $result = $savingAccountService->manualCreditRoi(
                    $user, $fixedAmount, $forDate,
                    $description ?? $fallbackDesc,
                    $roiType
                );
            } elseif ($roiType === 'saving_roi') {
                // Saving ROI via % — forceManual bypasses eligibility & duplicate checks
                $result = $savingAccountService->processSavingRoi(
                    $user, $forDate, $customRate,
                    $description ?? $fallbackDesc,
                    forceManual: true
                );
            } else {
                // Standard ROI via % — use roi_eligible_investment_amount as base
                $base = max((float) $user->roi_eligible_investment_amount, (float) $user->saving_total_deposited);
                if ($base <= 0) {
                    $results['skipped'][] = ['username' => $user->username, 'reason' => 'No investment base for Standard ROI'];
                    continue;
                }
                $rate   = $customRate ?? 0.1;
                $amount = round(($base * $rate) / 100, 2);
                if ($amount <= 0) {
                    $results['skipped'][] = ['username' => $user->username, 'reason' => 'Calculated ROI is zero'];
                    continue;
                }
                $desc   = $description ?? "Standard ROI {$rate}% of \${$base} ({$forDate->format('d M Y')})";
                $result = $savingAccountService->manualCreditRoi($user, $amount, $forDate, $desc, 'roi');
            }

            if ($result['success']) {
                $results['processed'][] = ['username' => $user->username, 'amount' => $result['amount']];
                $totalAmount += $result['amount'];
            } else {
                $results['skipped'][] = ['username' => $user->username, 'reason' => $result['message']];
            }
        }

        $processedCount = count($results['processed']);
        $skippedCount   = count($results['skipped']);

        $typeLabel = $roiType === 'saving_roi' ? 'Saving ROI' : 'Standard ROI';
        if ($useFixed) {
            $modeStr = '$' . number_format($fixedAmount, 2) . ' fixed per user';
        } else {
            $modeStr = ($customRate ? "{$customRate}%" : 'default rate') . ' of base';
        }

        $message = "[{$typeLabel}] {$forDate->format('d M Y')} [{$modeStr}] — Processed: {$processedCount} | Skipped: {$skippedCount} | Total: $" . number_format($totalAmount, 2);

        if ($processedCount > 0) {
            return back()->with('roi_success', $message)->with('roi_results', $results);
        }

        return back()->with('roi_warning', $message)->with('roi_results', $results);
    }
}
