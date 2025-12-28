<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ROIReversalController extends Controller
{
    /**
     * Display the ROI reversal interface
     */
    public function index()
    {
        // Get recent ROI statistics
        $stats = $this->getRecentROIStats();

        return view('admin.roi-reversal.index', [
            'stats' => $stats,
            'recentReversals' => $this->getRecentReversals(),
        ]);
    }

    /**
     * Preview reversal before executing
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
            'plan' => 'required|in:standard,vip,all',
            'days' => 'required|integer|min:1|max:30',
            'include_profit_share' => 'boolean',
        ]);

        try {
            $roiEntries = $this->getAffectedROIEntries(
                $validated['plan'],
                $validated['days']
            );

            $profitShareEntries = null;
            if ($validated['include_profit_share']) {
                $profitShareEntries = $this->getAffectedProfitShareEntries(
                    $validated['plan'],
                    $validated['days']
                );
            }

            $preview = $this->calculatePreview(
                $roiEntries,
                $profitShareEntries,
                $validated['percentage']
            );

            return response()->json([
                'success' => true,
                'preview' => $preview,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating preview: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute the reversal
     */
    public function execute(Request $request)
    {
        $validated = $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
            'plan' => 'required|in:standard,vip,all',
            'days' => 'required|integer|min:1|max:30',
            'include_profit_share' => 'boolean',
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Execute ROI reversal
            $roiResults = $this->executeROIReversal(
                $validated['plan'],
                $validated['days'],
                $validated['percentage']
            );

            // Execute profit share reversal if requested
            $profitShareResults = null;
            if ($validated['include_profit_share']) {
                $profitShareResults = $this->executeProfitShareReversal(
                    $validated['plan'],
                    $validated['days'],
                    $validated['percentage']
                );
            }

            // Log the reversal
            $this->logReversal($validated, $roiResults, $profitShareResults);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reversal executed successfully',
                'results' => [
                    'roi' => $roiResults,
                    'profit_share' => $profitShareResults,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ROI Reversal Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error executing reversal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent ROI statistics
     */
    private function getRecentROIStats()
    {
        $stats = [];

        foreach ([1, 2, 7, 30] as $days) {
            $standardROI = Wallet::where('wallet_type', 'roi')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereIn('user_id', function ($q) {
                    $q->select('id')->from('users')->where('user_plan', 'standard');
                })
                ->sum('balance');

            $vipROI = Wallet::where('wallet_type', 'roi')
                ->where('created_at', '>=', now()->subDays($days))
                ->whereIn('user_id', function ($q) {
                    $q->select('id')->from('users')->where('user_plan', 'vip');
                })
                ->sum('balance');

            $stats[$days] = [
                'standard' => $standardROI,
                'vip' => $vipROI,
                'total' => $standardROI + $vipROI,
            ];
        }

        return $stats;
    }

    /**
     * Get recent reversal history
     */
    private function getRecentReversals()
    {
        return Wallet::whereIn('wallet_type', ['roi_reversal', 'profit_share_reversal'])
            ->with('user:id,name,username,user_plan')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($wallet) {
                return [
                    'id' => $wallet->id,
                    'user_id' => $wallet->user_id,
                    'user_name' => $wallet->user->name ?? 'Unknown',
                    'username' => $wallet->user->username ?? 'N/A',
                    'user_plan' => $wallet->user->user_plan ?? 'N/A',
                    'type' => $wallet->wallet_type,
                    'amount' => $wallet->balance,
                    'description' => $wallet->description,
                    'created_at' => $wallet->created_at,
                ];
            });
    }

    /**
     * Get affected ROI entries
     */
    private function getAffectedROIEntries(string $plan, int $days)
    {
        $query = Wallet::where('wallet_type', 'roi')
            ->where('created_at', '>=', now()->subDays($days));

        if ($plan !== 'all') {
            $query->whereIn('user_id', function ($q) use ($plan) {
                $q->select('id')->from('users')->where('user_plan', $plan);
            });
        }

        return $query->get();
    }

    /**
     * Get affected profit share entries
     */
    private function getAffectedProfitShareEntries(string $plan, int $days)
    {
        $query = Wallet::where('wallet_type', 'profit_share')
            ->where('created_at', '>=', now()->subDays($days));

        if ($plan !== 'all') {
            $query->whereIn('user_id', function ($q) use ($plan) {
                $q->select('id')->from('users')->where('user_plan', $plan);
            });
        }

        return $query->get();
    }

    /**
     * Calculate preview
     */
    private function calculatePreview($roiEntries, $profitShareEntries, float $percentage)
    {
        $preview = [
            'roi' => [
                'total_entries' => $roiEntries->count(),
                'total_users' => $roiEntries->unique('user_id')->count(),
                'original_total' => $roiEntries->sum('balance'),
                'reversal_amount' => $roiEntries->sum('balance') * ($percentage / 100),
                'remaining_total' => $roiEntries->sum('balance') * ((100 - $percentage) / 100),
            ],
        ];

        if ($profitShareEntries) {
            $preview['profit_share'] = [
                'total_entries' => $profitShareEntries->count(),
                'total_users' => $profitShareEntries->unique('user_id')->count(),
                'original_total' => $profitShareEntries->sum('balance'),
                'reversal_amount' => $profitShareEntries->sum('balance') * ($percentage / 100),
                'remaining_total' => $profitShareEntries->sum('balance') * ((100 - $percentage) / 100),
            ];
        }

        // Get top affected users
        $topUsers = $roiEntries->groupBy('user_id')
            ->map(function ($entries) use ($percentage) {
                $userId = $entries->first()->user_id;
                $user = User::find($userId);
                $original = $entries->sum('balance');

                return [
                    'user_id' => $userId,
                    'name' => $user->name ?? 'Unknown',
                    'username' => $user->username ?? 'N/A',
                    'plan' => $user->user_plan ?? 'N/A',
                    'original' => $original,
                    'reversal' => $original * ($percentage / 100),
                    'remaining' => $original * ((100 - $percentage) / 100),
                ];
            })
            ->sortByDesc('reversal')
            ->take(20)
            ->values();

        $preview['top_affected_users'] = $topUsers;

        return $preview;
    }

    /**
     * Execute ROI reversal
     */
    private function executeROIReversal(string $plan, int $days, float $percentage)
    {
        $roiEntries = $this->getAffectedROIEntries($plan, $days);

        $totalAffected = 0;
        $totalReversed = 0;

        foreach ($roiEntries as $entry) {
            $originalAmount = $entry->balance;
            $reversalAmount = $originalAmount * ($percentage / 100);
            $newAmount = $originalAmount - $reversalAmount;

            // Update the original entry
            $entry->balance = $newAmount;
            $entry->total_amount = $newAmount;
            $entry->save();

            // Create reversal record
            Wallet::create([
                'user_id' => $entry->user_id,
                'wallet_type' => 'roi_reversal',
                'balance' => -$reversalAmount,
                'total_amount' => -$reversalAmount,
                'commission_type' => 'roi_reversal',
                'description' => "ROI Reversal: {$percentage}% reversed from entry #{$entry->id} (Admin Action)",
                'transaction_type' => 'reversal',
                'wallet_src' => 'admin_roi_reversal',
            ]);

            $totalAffected++;
            $totalReversed += $reversalAmount;
        }

        return [
            'total_affected' => $totalAffected,
            'total_reversed' => $totalReversed,
        ];
    }

    /**
     * Execute profit share reversal
     */
    private function executeProfitShareReversal(string $plan, int $days, float $percentage)
    {
        $profitShareEntries = $this->getAffectedProfitShareEntries($plan, $days);

        $totalAffected = 0;
        $totalReversed = 0;

        foreach ($profitShareEntries as $entry) {
            $originalAmount = $entry->balance;
            $reversalAmount = $originalAmount * ($percentage / 100);
            $newAmount = $originalAmount - $reversalAmount;

            // Update the original entry
            $entry->balance = $newAmount;
            $entry->total_amount = $newAmount;
            $entry->save();

            // Create reversal record
            Wallet::create([
                'user_id' => $entry->user_id,
                'wallet_type' => 'profit_share_reversal',
                'balance' => -$reversalAmount,
                'total_amount' => -$reversalAmount,
                'commission_type' => 'profit_share_reversal',
                'description' => "Profit Share Reversal: {$percentage}% reversed from entry #{$entry->id} (Admin Action)",
                'transaction_type' => 'reversal',
                'wallet_src' => 'admin_profit_share_reversal',
            ]);

            $totalAffected++;
            $totalReversed += $reversalAmount;
        }

        return [
            'total_affected' => $totalAffected,
            'total_reversed' => $totalReversed,
        ];
    }

    /**
     * Log reversal action
     */
    private function logReversal(array $validated, array $roiResults, ?array $profitShareResults)
    {
        $admin = Auth::user();

        Log::info('ROI Reversal Executed via Admin Panel', [
            'admin_id' => $admin ? $admin->id : null,
            'admin_name' => $admin ? $admin->name : 'Unknown',
            'percentage' => $validated['percentage'],
            'plan' => $validated['plan'],
            'days' => $validated['days'],
            'include_profit_share' => $validated['include_profit_share'],
            'reason' => $validated['reason'],
            'roi_affected' => $roiResults['total_affected'],
            'roi_reversed' => $roiResults['total_reversed'],
            'profit_share_affected' => $profitShareResults['total_affected'] ?? 0,
            'profit_share_reversed' => $profitShareResults['total_reversed'] ?? 0,
            'timestamp' => now(),
        ]);
    }
}
