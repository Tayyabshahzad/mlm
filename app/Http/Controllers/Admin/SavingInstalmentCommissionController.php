<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingInstalment;
use App\Models\SavingInstalmentCommission;
use App\Models\User;
use App\Services\SavingAccountService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SavingInstalmentCommissionController extends Controller
{
    public function __construct(
        private SavingAccountService $savingService,
        private WalletService $walletService,
    ) {}

    // =========================================================================
    // Index — paginated list with filters
    // =========================================================================

    public function index(Request $request)
    {
        $query = SavingInstalmentCommission::query()
            ->with([
                'user:id,name,username',
                'ancestor:id,name,username',
                'instalment:id,instalment_number,amount,confirmed_at,transaction_id',
            ])
            ->join('saving_instalments', 'saving_instalments.id', '=', 'saving_instalment_commissions.saving_instalment_id')
            ->select('saving_instalment_commissions.*');

        // ── Filters ──────────────────────────────────────────────────────────

        if ($status = $request->get('status')) {
            $query->where('saving_instalment_commissions.status', $status);
        }

        if ($userId = $request->get('user_id')) {
            $query->where('saving_instalment_commissions.user_id', $userId);
        }

        if ($ancestorId = $request->get('ancestor_id')) {
            $query->where('saving_instalment_commissions.ancestor_id', $ancestorId);
        }

        if ($instalmentNumber = $request->get('instalment_number')) {
            $query->where('saving_instalment_commissions.instalment_number', $instalmentNumber);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('saving_instalments.confirmed_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('saving_instalments.confirmed_at', '<=', $to);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                                                   ->orWhere('username', 'like', "%{$search}%"))
                  ->orWhereHas('ancestor', fn($a) => $a->where('name', 'like', "%{$search}%")
                                                        ->orWhere('username', 'like', "%{$search}%"));
            });
        }

        $commissions = $query
            ->orderByDesc('saving_instalment_commissions.created_at')
            ->paginate(25)
            ->withQueryString();

        // ── Summary stats ─────────────────────────────────────────────────────
        $totalPending     = SavingInstalmentCommission::pending()->count();
        $totalPaid        = SavingInstalmentCommission::paid()->count();
        $pendingAmount    = SavingInstalmentCommission::pending()->sum('commission_amount');
        $paidAmount       = SavingInstalmentCommission::paid()->sum('commission_amount');

        // Dropdown options for filter selects
        $savingUsers = User::where(function ($q) {
                $q->where('account_type', 'saving')
                  ->orWhere(fn($q2) => $q2->where('saving_enrolled', true)->where('saving_initial_payment', '>', 0));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('admin.saving.instalment-commissions', compact(
            'commissions',
            'totalPending',
            'totalPaid',
            'pendingAmount',
            'paidAmount',
            'savingUsers',
        ));
    }

    // =========================================================================
    // Send a single pending commission
    // =========================================================================

    public function sendSingle(SavingInstalmentCommission $commission): RedirectResponse
    {
        if ($commission->status !== 'pending') {
            return back()->with('error', "Commission #{$commission->id} is already {$commission->status}.");
        }

        try {
            $this->payCommission($commission);
            return back()->with('success', "Commission #{$commission->id} sent — \${$commission->commission_amount} to {$commission->ancestor->name}.");
        } catch (\Exception $e) {
            Log::error("Failed to send saving commission #{$commission->id}: " . $e->getMessage());
            return back()->with('error', 'Failed to send commission: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // Send all pending commissions for a specific payer (member)
    // =========================================================================

    public function sendForUser(User $user): RedirectResponse
    {
        $pending = SavingInstalmentCommission::where('user_id', $user->id)
            ->where('status', 'pending')
            ->get();

        if ($pending->isEmpty()) {
            return back()->with('info', "No pending commissions found for {$user->name}.");
        }

        $sent  = 0;
        $total = 0;

        DB::transaction(function () use ($pending, &$sent, &$total) {
            foreach ($pending as $commission) {
                $this->payCommission($commission);
                $sent++;
                $total += $commission->commission_amount;
            }
        });

        return back()->with('success', "Sent {$sent} commission(s) totalling \$" . number_format($total, 2) . " for {$user->name}.");
    }

    // =========================================================================
    // Send all pending commissions globally
    // =========================================================================

    public function sendAll(Request $request): RedirectResponse
    {
        $query = SavingInstalmentCommission::where('status', 'pending');

        // Optional scoping by date range
        if ($from = $request->get('from')) {
            $query->whereHas('instalment', fn($q) => $q->whereDate('confirmed_at', '>=', $from));
        }
        if ($to = $request->get('to')) {
            $query->whereHas('instalment', fn($q) => $q->whereDate('confirmed_at', '<=', $to));
        }

        $pending = $query->get();

        if ($pending->isEmpty()) {
            return back()->with('info', 'No pending commissions to send.');
        }

        $sent  = 0;
        $total = 0.0;
        $errors = 0;

        foreach ($pending->chunk(50) as $chunk) {
            DB::transaction(function () use ($chunk, &$sent, &$total, &$errors) {
                foreach ($chunk as $commission) {
                    try {
                        $this->payCommission($commission);
                        $sent++;
                        $total += $commission->commission_amount;
                    } catch (\Exception $e) {
                        Log::error("Bulk commission send failed for #{$commission->id}: " . $e->getMessage());
                        $errors++;
                    }
                }
            });
        }

        $msg = "Sent {$sent} commission(s) totalling \$" . number_format($total, 2) . ".";
        if ($errors > 0) {
            $msg .= " {$errors} failed — check the logs.";
            return back()->with('warning', $msg);
        }

        return back()->with('success', $msg);
    }

    // =========================================================================
    // Internal: pay one pending commission record
    // =========================================================================

    private function payCommission(SavingInstalmentCommission $commission): void
    {
        $sourceUser = $commission->user;
        $recipient  = $commission->ancestor;

        if (!$sourceUser || !$recipient) {
            throw new \RuntimeException("Commission #{$commission->id} references missing user(s).");
        }

        $wallet = $this->walletService->assignCommission(
            userId: $recipient->id,
            amount: (float) $commission->commission_amount,
            type: $commission->commission_type,
            sourceUser: $sourceUser,
            level: $commission->level,
            percentage: (float) $commission->percentage,
            sourceType: 'saving_instalment'
        );

        $commission->update([
            'status'       => 'paid',
            'wallet_id'    => $wallet?->id,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);
    }
}
