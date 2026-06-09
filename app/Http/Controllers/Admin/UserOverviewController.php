<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class UserOverviewController extends Controller
{
    public function show($id)
    {
        $user = User::with(['profile', 'parent', 'currentRank'])->findOrFail($id);

        // ── Network stats ─────────────────────────────────────────────────────
        $standardTree = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('tree_type', 'standard')
            ->whereBetween('level', [1, 7])
            ->selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->pluck('count', 'level');

        $savingTree = DB::table('referral_trees')
            ->where('ancestor_id', $user->id)
            ->where('tree_type', 'saving')
            ->whereBetween('level', [1, 7])
            ->selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->pluck('count', 'level');

        $standardTotalTeam = $standardTree->sum();
        $savingTotalTeam   = $savingTree->sum();

        // Active/inactive within standard team (can_login = active)
        $standardActiveCount = DB::table('referral_trees as rt')
            ->join('users', 'users.id', '=', 'rt.descendant_id')
            ->where('rt.ancestor_id', $user->id)
            ->where('rt.tree_type', 'standard')
            ->where('users.can_login', true)
            ->where('users.blocked', false)
            ->count();

        // ── Wallet summary ────────────────────────────────────────────────────
        $wallets = Wallet::where('user_id', $user->id)->get();

        $walletSummary = [
            'online'                => (float) $wallets->where('wallet_type', 'online')->sum('balance'),
            'direct_indirect'       => (float) $wallets->where('wallet_type', 'direct_indirect')->sum('balance'),
            'direct_balance'        => (float) $wallets->where('wallet_type', 'direct_indirect')->sum('direct_balance'),
            'indirect_balance'      => (float) $wallets->where('wallet_type', 'direct_indirect')->sum('indirect_balance'),
            'roi'                   => (float) $wallets->whereIn('wallet_type', ['roi', 'saving_roi'])->sum('balance'),
            'reward'                => (float) $wallets->where('wallet_type', 'reward')->sum('balance'),
            'profit_share'          => (float) $wallets->where('wallet_type', 'profit_share')->sum('balance'),
            'designation_incentive' => (float) $wallets->where('wallet_type', 'designation_incentive')->sum('balance'),
            'saving_deposit'        => (float) $wallets->where('wallet_type', 'saving')->sum('balance'),
            'saving_roi'            => (float) $wallets->where('wallet_type', 'saving_roi')->sum('balance'),
        ];

        $walletSummary['total_earnings'] = array_sum([
            $walletSummary['direct_indirect'],
            $walletSummary['roi'],
            $walletSummary['reward'],
            $walletSummary['profit_share'],
            $walletSummary['designation_incentive'],
            $walletSummary['saving_roi'],
        ]);

        // Saving-specific commission split
        $savingDirect   = (float) $wallets->where('wallet_type', 'direct_indirect')
                                          ->where('source_type', 'saving_instalment')
                                          ->sum('direct_balance');
        $savingIndirect = (float) $wallets->where('wallet_type', 'direct_indirect')
                                          ->where('source_type', 'saving_instalment')
                                          ->sum('indirect_balance');

        // ── Standard investment summary ───────────────────────────────────────
        $investments = DB::table('user_investments')
            ->where('user_id', $user->id)
            ->get();

        $investmentSummary = [
            'total'          => (float) $investments->sum('amount'),
            'active_count'   => $investments->where('roi_status', 'active')->count(),
            'completed_count'=> $investments->where('roi_status', 'completed')->count(),
            'total_earnings' => (float) $investments->sum('total_earnings'),
        ];

        // ROI transactions
        $roiTotal = (float) DB::table('r_o_i_transactions')
            ->where('user_id', $user->id)
            ->sum('amount');

        // ── Saving plan summary ───────────────────────────────────────────────
        $savingInstalments = [];
        $savingSummary     = null;

        if ($user->account_type === 'saving' || $user->saving_enrolled) {
            $savingInstalments = DB::table('saving_instalments')
                ->where('user_id', $user->id)
                ->orderBy('instalment_number')
                ->get();

            $paidCount    = $savingInstalments->where('status', 'confirmed')->count();
            $pendingCount = $savingInstalments->whereIn('status', ['pending', 'submitted'])->count();
            $missedCount  = $savingInstalments->where('status', 'missed')->count();
            $nextDue      = $savingInstalments->whereIn('status', ['pending', 'submitted'])->first();

            $savingSummary = [
                'total_deposited'  => (float) ($user->saving_total_deposited ?? 0),
                'paid_count'       => $paidCount,
                'pending_count'    => $pendingCount,
                'missed_count'     => $missedCount,
                'remaining'        => 25 - $paidCount,
                'next_due'         => $nextDue,
                'capital_amount'   => (float) ($user->roi_eligible_investment_amount ?? 0),
                'roi_earned'       => $walletSummary['saving_roi'],
            ];
        }

        // ── Commission history (last 50) ──────────────────────────────────────
        $commissionHistory = Wallet::with('form:id,name,username')
            ->where('user_id', $user->id)
            ->where('wallet_type', 'direct_indirect')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        // ── Withdrawals ───────────────────────────────────────────────────────
        $withdrawals = DB::table('with_drawalequests')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $withdrawalSummary = [
            'total'    => (float) $withdrawals->where('status', 'approved')->sum('amount'),
            'pending'  => (float) $withdrawals->where('status', 'pending')->sum('amount'),
            'rejected' => $withdrawals->where('status', 'rejected')->count(),
            'last'     => $withdrawals->where('status', 'approved')->sortByDesc('created_at')->first(),
        ];

        // ── Recent activity (transaction logs, last 30) ───────────────────────
        $recentActivity = DB::table('transaction_logs')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        // ── ROI transactions (last 20) ─────────────────────────────────────────
        $roiHistory = DB::table('r_o_i_transactions')
            ->where('user_id', $user->id)
            ->orderByDesc('transaction_date')
            ->limit(20)
            ->get();

        // ── Current rank ──────────────────────────────────────────────────────
        $currentRank = DB::table('user_ranks')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        return view('admin.users.overview', compact(
            'user',
            'standardTree',
            'savingTree',
            'standardTotalTeam',
            'savingTotalTeam',
            'standardActiveCount',
            'walletSummary',
            'savingDirect',
            'savingIndirect',
            'investmentSummary',
            'roiTotal',
            'savingInstalments',
            'savingSummary',
            'commissionHistory',
            'withdrawals',
            'withdrawalSummary',
            'recentActivity',
            'roiHistory',
            'currentRank',
        ));
    }
}
