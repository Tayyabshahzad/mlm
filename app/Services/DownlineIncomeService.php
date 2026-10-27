<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Income a member earned from their downline in a date range.
 *
 * "Income from downline" = commission wallet entries that name the downline
 * member they came from (wallets.wallet_from):
 *   - direct_indirect : level commissions (investment, top-up, saving instalments)
 *   - profit_share    : share of the downline's ROI
 * minus adjustments booked in the same range:
 *   - direct_indirect 'balance_correction' (excess commission recovered)
 *   - profit_share_reversal
 *
 * Entries are dated by when the commission was EARNED (wallets.created_at), so
 * both new downline (joined in the range) and existing downline (joined earlier)
 * are included.
 */
class DownlineIncomeService
{
    public const TYPES = ['direct_indirect', 'profit_share'];

    /**
     * @param string[]    $types      wallet types to count (default: direct_indirect + profit_share)
     * @param string|null $sourceType limit to one source, e.g. 'saving_instalment' for the Savings wallet
     */
    public function summary(User $member, Carbon $from, Carbon $to, array $types = self::TYPES, ?string $sourceType = null): array
    {
        $from = $from->copy()->startOfDay();
        $to   = $to->copy()->endOfDay();
        $withProfitShare = in_array('profit_share', $types, true);

        // Downline level of every member under $member (shallowest level across trees)
        $levels = DB::table('referral_trees')
            ->select('descendant_id', DB::raw('MIN(level) AS lvl'))
            ->where('ancestor_id', $member->id)
            ->groupBy('descendant_id');

        $earned = DB::table('wallets as w')
            ->leftJoinSub($levels, 'rt', 'rt.descendant_id', '=', 'w.wallet_from')
            ->leftJoin('users as d', 'd.id', '=', 'w.wallet_from')
            ->where('w.user_id', $member->id)
            ->whereIn('w.wallet_type', $types)
            ->when($sourceType, fn ($q) => $q->where('w.source_type', $sourceType))
            ->whereNotNull('w.wallet_from')
            ->where(fn ($q) => $q->whereNull('w.commission_type')->orWhere('w.commission_type', '!=', 'balance_correction'))
            ->where(fn ($q) => $q->whereNull('w.transaction_type')->orWhere('w.transaction_type', '!=', 'debit'))
            ->whereBetween('w.created_at', [$from, $to]);

        $totals = (clone $earned)->selectRaw("
                COALESCE(SUM(w.total_amount), 0) AS total,
                COALESCE(SUM(CASE WHEN w.wallet_type = 'direct_indirect' THEN w.total_amount END), 0) AS direct_indirect,
                COALESCE(SUM(CASE WHEN w.wallet_type = 'profit_share' THEN w.total_amount END), 0) AS profit_share,
                COALESCE(SUM(CASE WHEN w.wallet_type = 'direct_indirect' AND w.commission_type = 'direct' THEN w.total_amount END), 0) AS direct,
                COALESCE(SUM(CASE WHEN w.wallet_type = 'direct_indirect' AND w.commission_type = 'indirect' THEN w.total_amount END), 0) AS indirect,
                COALESCE(SUM(CASE WHEN d.created_at >= ? THEN w.total_amount END), 0) AS from_new,
                COUNT(DISTINCT w.wallet_from) AS members,
                COUNT(DISTINCT CASE WHEN d.created_at >= ? THEN w.wallet_from END) AS new_members
            ", [$from, $from])
            ->first();

        $adjustments = (float) DB::table('wallets')
            ->where('user_id', $member->id)
            ->whereBetween('created_at', [$from, $to])
            ->where(fn ($q) => $q
                ->where(fn ($c) => $c->where('wallet_type', 'direct_indirect')->where('commission_type', 'balance_correction')
                    ->when($sourceType, fn ($s) => $s->where('source_type', $sourceType)))
                ->when($withProfitShare && !$sourceType, fn ($r) => $r->orWhere('wallet_type', 'profit_share_reversal')))
            ->sum(DB::raw('ABS(total_amount)'));

        $byLevel = (clone $earned)
            ->selectRaw('rt.lvl AS level, COALESCE(SUM(w.total_amount), 0) AS amount, COUNT(DISTINCT w.wallet_from) AS members')
            ->groupBy('rt.lvl')
            ->orderByRaw('rt.lvl IS NULL, rt.lvl')
            ->get()
            ->map(fn ($r) => ['level' => $r->level !== null ? (int) $r->level : null, 'amount' => round((float) $r->amount, 2), 'members' => (int) $r->members]);

        $topMembers = (clone $earned)
            ->selectRaw("w.wallet_from AS user_id, d.name, d.username, d.created_at AS joined_at, rt.lvl AS level,
                COALESCE(SUM(w.total_amount), 0) AS amount,
                COALESCE(SUM(CASE WHEN w.wallet_type = 'direct_indirect' THEN w.total_amount END), 0) AS direct_indirect,
                COALESCE(SUM(CASE WHEN w.wallet_type = 'profit_share' THEN w.total_amount END), 0) AS profit_share")
            ->groupBy('w.wallet_from', 'd.name', 'd.username', 'd.created_at', 'rt.lvl')
            ->orderByDesc('amount')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'user_id'         => (int) $r->user_id,
                'name'            => $r->name,
                'username'        => $r->username,
                'level'           => $r->level !== null ? (int) $r->level : null,
                'joined_at'       => $r->joined_at ? Carbon::parse($r->joined_at) : null,
                'is_new'          => $r->joined_at && Carbon::parse($r->joined_at)->gte($from),
                'amount'          => round((float) $r->amount, 2),
                'direct_indirect' => round((float) $r->direct_indirect, 2),
                'profit_share'    => round((float) $r->profit_share, 2),
            ]);

        $gross = round((float) $totals->total, 2);

        return [
            'from'            => $from,
            'to'              => $to,
            'gross'           => $gross,
            'adjustments'     => round($adjustments, 2),
            'net'             => round($gross - $adjustments, 2),
            'direct_indirect' => round((float) $totals->direct_indirect, 2),
            'profit_share'    => round((float) $totals->profit_share, 2),
            'direct'          => round((float) $totals->direct, 2),
            'indirect'        => round((float) $totals->indirect, 2),
            'from_new'        => round((float) $totals->from_new, 2),
            'from_existing'   => round($gross - (float) $totals->from_new, 2),
            'members'         => (int) $totals->members,
            'new_members'     => (int) $totals->new_members,
            'by_level'        => $byLevel,
            'top_members'     => $topMembers,
        ];
    }
}
