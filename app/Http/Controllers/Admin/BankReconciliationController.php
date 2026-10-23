<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ReconciliationException;
use App\Http\Controllers\Controller;
use App\Models\BankPaymentReconciliation;
use App\Models\InstalmentPaymentAudit;
use App\Models\SavingInstalment;
use App\Services\BankReconciliationService;
use App\Services\DigitalCardService;
use App\Services\SavingAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Bank Reconciliation — used by Office Admins (and admins) to match daily
 * bank collections to saving-plan instalments.
 */
class BankReconciliationController extends Controller
{
    public function __construct(
        private BankReconciliationService $reconciliation,
        private SavingAccountService $savingAccountService,
    ) {}

    public function index(Request $request)
    {
        $tab    = in_array($request->get('tab'), ['submitted', 'due', 'reconciled', 'audit'], true) ? $request->get('tab') : 'submitted';
        $search = trim((string) $request->get('search'));

        $memberSearch = function ($q) use ($search) {
            if ($search !== '') {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%"));
            }
        };

        $withRequired = fn ($inst) => tap($inst, fn ($i) => $i->setAttribute(
            'required_total', $this->savingAccountService->requiredInstalmentTotal($i)
        ));

        $rows = match ($tab) {
            // Member submitted a bank payment — check it against the statement
            'submitted' => SavingInstalment::with('user:id,name,username,phone_number,adb_option,fisp_option')
                ->where('status', 'submitted')
                ->where(fn ($q) => $q->whereNull('payment_method')->orWhere('payment_method', '!=', DigitalCardService::PAYMENT_METHOD))
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('transaction_id', 'like', "%{$search}%")->orWhere($memberSearch)))
                ->orderBy('submitted_at')
                ->paginate(25)->withQueryString()->through($withRequired),

            // Unpaid instalments due up to the chosen date — record money seen in the bank
            'due' => SavingInstalment::with('user:id,name,username,phone_number,adb_option,fisp_option')
                ->whereIn('status', ['pending', 'missed'])
                ->whereDate('due_date', '<=', $request->get('due_until') ?: now()->addDays(7)->toDateString())
                ->where($memberSearch)
                ->orderBy('due_date')
                ->paginate(25)->withQueryString()->through($withRequired),

            'reconciled' => BankPaymentReconciliation::with(['user:id,name,username', 'instalment:id,instalment_number', 'verifier:id,name,username'])
                ->when($request->get('from'), fn ($q, $d) => $q->whereDate('payment_date', '>=', $d))
                ->when($request->get('to'), fn ($q, $d) => $q->whereDate('payment_date', '<=', $d))
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('bank_reference', 'like', "%{$search}%")->orWhere($memberSearch)))
                ->latest('id')
                ->paginate(25)->withQueryString(),

            'audit' => InstalmentPaymentAudit::with(['actor:id,name,username', 'member:id,name,username', 'instalment:id,instalment_number'])
                ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('description', 'like', "%{$search}%")
                    ->orWhereHas('member', fn ($u) => $u->where('username', 'like', "%{$search}%"))
                    ->orWhereHas('actor', fn ($u) => $u->where('username', 'like', "%{$search}%"))))
                ->latest('id')
                ->paginate(30)->withQueryString(),
        };

        $today = now()->toDateString();
        $stats = [
            'awaiting'       => SavingInstalment::where('status', 'submitted')
                ->where(fn ($q) => $q->whereNull('payment_method')->orWhere('payment_method', '!=', DigitalCardService::PAYMENT_METHOD))->count(),
            'due_today'      => SavingInstalment::whereIn('status', ['pending', 'missed'])->whereDate('due_date', '<=', $today)->count(),
            'verified_today' => BankPaymentReconciliation::where('status', 'verified')->whereDate('verified_at', $today)->count(),
            'amount_today'   => (float) BankPaymentReconciliation::where('status', 'verified')->whereDate('verified_at', $today)->sum('amount'),
            'blocked_today'  => InstalmentPaymentAudit::where('action', InstalmentPaymentAudit::BLOCKED)->whereDate('created_at', $today)->count(),
        ];

        return view('admin.reconciliation.index', compact('tab', 'rows', 'stats', 'search'));
    }

    public function verify(Request $request, SavingInstalment $instalment): RedirectResponse
    {
        $data = $this->validatePayment($request);

        return $this->attempt(fn () => $this->reconciliation->verifySubmission($instalment, Auth::user(), $data),
            "Instalment #{$instalment->instalment_number} verified against the bank and confirmed.");
    }

    public function record(Request $request, SavingInstalment $instalment): RedirectResponse
    {
        $data = $this->validatePayment($request, withProof: true);

        return $this->attempt(fn () => $this->reconciliation->recordPayment($instalment, Auth::user(), $data, $request->file('proof')),
            "Payment recorded and instalment #{$instalment->instalment_number} confirmed.");
    }

    public function reject(Request $request, SavingInstalment $instalment): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        return $this->attempt(fn () => $this->reconciliation->rejectSubmission($instalment, Auth::user(), $request->reason),
            "Instalment #{$instalment->instalment_number} rejected and returned to the member as unpaid.");
    }

    private function validatePayment(Request $request, bool $withProof = false): array
    {
        return $request->validate(array_merge([
            'bank_reference'   => 'required|string|max:100',
            'amount'           => 'required|numeric|min:0.01',
            'payment_date'     => 'required|date|before_or_equal:today',
            'bank_name'        => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
            'confirm_verified' => 'accepted',
        ], $withProof ? ['proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'] : []), [
            'confirm_verified.accepted' => 'Please confirm that you have checked this payment in the bank statement.',
        ]);
    }

    private function attempt(callable $action, string $success): RedirectResponse
    {
        try {
            $action();
        } catch (ReconciliationException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Bank reconciliation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Could not update the instalment: ' . $e->getMessage());
        }

        return back()->with('success', $success);
    }
}
