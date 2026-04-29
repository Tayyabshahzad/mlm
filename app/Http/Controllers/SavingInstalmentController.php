<?php

namespace App\Http\Controllers;

use App\Exports\SavingDueInstalmentExport;
use App\Models\ReferralLink;
use App\Models\SavingInstalment;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\SavingAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SavingInstalmentController extends Controller
{
    public function __construct(private SavingAccountService $savingAccountService) {}

    // =========================================================================
    // USER — view their instalment dashboard
    // =========================================================================

    public function userIndex()
    {
        $user    = Auth::user();

        $hasInstalments = $user->savingInstalments()->exists();
        abort_unless($user->account_type === 'saving' || ($user->saving_enrolled && $user->saving_enrollment_activated && $hasInstalments), 403);

        $summary = $this->savingAccountService->getInstalmentSummary($user);
        $setting = Setting::first();

        $regFee     = (float) ($setting->saving_registration_fee ?? 5);
        $minDeposit = (float) ($setting->saving_min_deposit ?? 19);
        $fullTotal  = $regFee + $minDeposit;

        // Use saving_initial_payment / saving_initial_fee for both dedicated saving users
        // and enrolled standard users. converted_usdt_amount belongs to the standard plan
        // and must NOT be used here.
        $paidAtReg      = (float) ($user->saving_initial_payment ?? 0);
        $savingFeeAtReg = (float) ($user->saving_initial_fee ?? $regFee);

        $partialDeposit = max(0, $paidAtReg - $savingFeeAtReg);
        $inst1Remaining = max(0, $minDeposit - $partialDeposit);

        return view('saving.instalments', array_merge($summary, [
            'setting'        => $setting,
            'paidAtReg'      => $paidAtReg,
            'regFee'         => $savingFeeAtReg,
            'partialDeposit' => $partialDeposit,
            'inst1Remaining' => $inst1Remaining,
            'fullTotal'      => $fullTotal,
        ]));
    }

    /**
     * User submits proof for the next pending instalment.
     */
    public function userSubmit(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->account_type !== 'saving' && !($user->saving_enrolled && $user->saving_enrollment_activated && $user->savingInstalments()->exists())) {
            return back()->with('error', 'This action is only available for Savings Program members.');
        }

        $request->validate([
            'transaction_id'  => 'required|string|max:100',
            'payment_method'  => 'required|in:bank,usdt,cash_slip',
            'submitted_amount'=> 'required|numeric|min:1',
            'proof'           => 'required|image|mimes:jpg,jpeg,png|max:3072',
        ]);

        $instalment = $this->savingAccountService->nextDueInstalment($user);

        if (!$instalment) {
            return back()->with('error', 'No pending instalment found.');
        }

        if ($instalment->status === 'submitted') {
            return back()->with('error', 'You have already submitted proof for this instalment. Please wait for admin confirmation.');
        }

        // Early payment is allowed for any instalment.
        // ROI for early-paid instalments will only start from their original due_date
        // (handled by roi_eligible_from in confirmAndDeposit).

        DB::transaction(function () use ($request, $instalment) {
            $instalment->update([
                'status'           => 'submitted',
                'transaction_id'   => $request->transaction_id,
                'payment_method'   => $request->payment_method,
                'submitted_amount' => $request->submitted_amount,
                'submitted_at'     => now(),
            ]);

            if ($request->hasFile('proof')) {
                $instalment->addMedia($request->file('proof'))
                    ->toMediaCollection('instalment_proof');
            }
        });

        return back()->with('success', 'Payment proof submitted. Awaiting admin confirmation.');
    }

    // =========================================================================
    // ADMIN — list all saving account users
    // =========================================================================

    public function adminIndex(Request $request)
    {
        // Only actual saving plan participants:
        // — dedicated saving account users (account_type = 'saving')
        // — enrolled standard users who actually signed up (saving_initial_payment > 0)
        // Auto-enrolled sponsors (saving_enrolled = true but never paid) are excluded.
        $query = User::where(function ($q) {
                $q->where('account_type', 'saving')
                  ->orWhere(function ($q2) {
                      $q2->where('saving_enrolled', true)
                         ->where('saving_initial_payment', '>', 0);
                  });
            })
            ->with(['savingInstalments', 'savingSponsor'])
            ->orderBy('created_at', 'desc');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'completed') {
                $query->where('saving_registration_completed', true);
            } elseif ($status === 'pending') {
                $query->where('saving_registration_completed', false);
            }
        }

        $users   = $query->paginate(20)->withQueryString();
        $setting = Setting::first();

        // Counts for summary cards — same scope as the main query (exclude auto-enrolled sponsors)
        $savingParticipant = fn($q) => $q->where('account_type', 'saving')
            ->orWhere(fn($q2) => $q2->where('saving_enrolled', true)->where('saving_initial_payment', '>', 0));

        $totalSaving        = User::where($savingParticipant)->count();
        $registrationDone   = User::where($savingParticipant)->where('saving_registration_completed', true)->count();
        $pendingSubmissions = SavingInstalment::where('status', 'submitted')->count();

        // Lightweight list for the due-export modal dropdown
        $savingUsers = User::where($savingParticipant)
            ->orderBy('name')
            ->get(['id', 'name', 'username']);

        return view('admin.saving.index', compact(
            'users', 'setting', 'totalSaving', 'registrationDone', 'pendingSubmissions', 'savingUsers'
        ));
    }

    /**
     * Admin views detail for one saving account user.
     */
    public function adminShow(User $user)
    {
        // Allow dedicated saving users AND enrolled standard users
        abort_unless($user->account_type === 'saving' || $user->saving_enrolled, 404);

        $summary = $this->savingAccountService->getInstalmentSummary($user);
        $setting = Setting::first();

        return view('admin.saving.show', array_merge($summary, [
            'savingUser' => $user,
            'setting'    => $setting,
        ]));
    }

    /**
     * Admin confirms a submitted instalment and deposits the amount.
     */
    public function adminConfirm(Request $request, SavingInstalment $instalment): RedirectResponse
    {
        if ($instalment->status !== 'submitted') {
            return back()->with('error', 'This instalment is not in a submitted state.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->savingAccountService->confirmAndDeposit(
                $instalment,
                Auth::id(),
                $request->notes
            );

            return back()->with('success', "Instalment #{$instalment->instalment_number} confirmed and deposited.");
        } catch (\Exception $e) {
            Log::error("Failed to confirm saving instalment {$instalment->id}: " . $e->getMessage());
            return back()->with('error', 'Confirmation failed: ' . $e->getMessage());
        }
    }

    /**
     * Admin rejects a submitted instalment (sends it back to pending).
     */
    public function adminReject(Request $request, SavingInstalment $instalment): RedirectResponse
    {
        if ($instalment->status !== 'submitted') {
            return back()->with('error', 'This instalment is not in a submitted state.');
        }

        $request->validate([
            'notes' => 'required|string|max:500',
        ]);

        $instalment->update([
            'status'           => 'pending',
            'submitted_at'     => null,
            'submitted_amount' => null,
            'transaction_id'   => null,
            'payment_method'   => null,
            'notes'            => 'Rejected: ' . $request->notes,
        ]);

        // Remove proof media
        $instalment->clearMediaCollection('instalment_proof');

        return back()->with('success', "Instalment #{$instalment->instalment_number} rejected and reset to pending.");
    }

    // =========================================================================
    // ADMIN — create a saving account user (like creating an admin)
    // =========================================================================

    public function adminCreateUserForm()
    {
        $setting = Setting::first();
        return view('admin.saving.create-user', compact('setting'));
    }

    public function adminCreateUser(Request $request): RedirectResponse
    {
        $setting = Setting::first();

        $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => 'required|string|max:255|unique:users',
            'email'        => 'required|email|unique:users',
            'phone_number' => 'required|unique:users',
            'password'     => 'required|confirmed|min:8',
            'referral_link'=> 'nullable|string|exists:referral_links,link',
            'is_saving_parent' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request, $setting) {
            $username = Str::slug($request->username);
            $parentId = null;

            if ($request->referral_link) {
                $refLink  = ReferralLink::where('link', $request->referral_link)->firstOrFail();
                $parentId = $refLink->user_id;
            } elseif ($setting->saving_parent_user_id) {
                $parentId = $setting->saving_parent_user_id;
            }

            $user = User::create([
                'name'                          => $request->name,
                'email'                         => $request->email,
                'password'                      => Hash::make($request->password),
                'username'                      => $username,
                'is_active'                     => true,
                'phone_verified'                => true,
                'can_login'                     => false, // requires admin activation
                'phone_number'                  => $request->phone_number,
                'sponsor_id'                    => $parentId,
                'transaction_id'                => 'ADMIN-' . strtoupper(Str::random(8)),
                'account_type'                  => 'saving',
                'saving_plan_start_date'        => Carbon::today()->toDateString(),
                'saving_registration_completed' => false,
                'user_plan'                     => 'standard',
            ]);

            $user->assignRole('member');

            ReferralLink::create(['user_id' => $user->id, 'link' => $username]);

            if ($parentId) {
                // Build SAVING referral tree (separate from standard tree)
                DB::table('referral_trees')->insertOrIgnore([
                    'ancestor_id'   => $parentId,
                    'descendant_id' => $user->id,
                    'level'         => 1,
                    'tree_type'     => 'saving',
                ]);

                $ancestors = DB::table('referral_trees')
                    ->where('descendant_id', $parentId)
                    ->where('tree_type', 'saving')
                    ->get();
                foreach ($ancestors as $ancestor) {
                    DB::table('referral_trees')->insertOrIgnore([
                        'ancestor_id'   => $ancestor->ancestor_id,
                        'descendant_id' => $user->id,
                        'level'         => $ancestor->level + 1,
                        'tree_type'     => 'saving',
                    ]);
                }
                // If sponsor has no saving-tree ancestry, anchor through the saving root
                $savingRootId = $setting->saving_parent_user_id ?? null;
                if ($savingRootId && $ancestors->isEmpty() && $parentId !== $savingRootId) {
                    DB::table('referral_trees')->insertOrIgnore([
                        'ancestor_id'   => $savingRootId,
                        'descendant_id' => $parentId,
                        'level'         => 1,
                        'tree_type'     => 'saving',
                    ]);
                    DB::table('referral_trees')->insertOrIgnore([
                        'ancestor_id'   => $savingRootId,
                        'descendant_id' => $user->id,
                        'level'         => 2,
                        'tree_type'     => 'saving',
                    ]);
                }
            }

            // If this is being set as the saving parent user
            if ($request->boolean('is_saving_parent')) {
                $setting->update(['saving_parent_user_id' => $user->id]);
            }

            // Create 25-month instalment schedule (starting with $19 as first instalment)
            $monthly = $setting->saving_monthly_instalment ?? 19;
            $this->savingAccountService->createInstalmentSchedule($user, $monthly, $monthly);
        });

        return redirect()->route('admin.saving.index')->with('success', 'Saving account user created successfully.');
    }

    // =========================================================================
    // ADMIN — pending submissions list
    // =========================================================================

    public function adminPendingSubmissions()
    {
        $instalments = SavingInstalment::with('user')
            ->where('status', 'submitted')
            ->orderBy('submitted_at')
            ->paginate(20);

        return view('admin.saving.pending', compact('instalments'));
    }

    // =========================================================================
    // ADMIN — activate a saving account user
    // =========================================================================

    public function adminActivate(User $user): RedirectResponse
    {
        abort_unless($user->account_type === 'saving' || $user->saving_enrolled, 404);

        if ($user->account_type === 'saving') {
            // Dedicated saving account user — enable login
            $user->update([
                'saving_registration_completed' => true,
                'can_login'                     => true,
            ]);
        } else {
            // Enrolled standard user — activate the saving program membership only
            // (their existing can_login is untouched)
            $user->update([
                'saving_registration_completed'  => true,
                'saving_enrollment_activated'    => true,
                'saving_enrollment_activated_at' => now(),
                'saving_enrollment_activated_by' => Auth::id(),
            ]);
        }

        // Auto-enroll the sponsor so they can see their saving team / wallets
        $sponsor = $user->savingSponsor()->first() ?? $user->parent;
        if ($sponsor) {
            $this->savingAccountService->autoEnrollSponsor($sponsor);
        }

        // Check whether inst #1 has been fully deposited (confirmed + deposited_at set)
        $inst1Deposited = $user->savingInstalments()
            ->where('instalment_number', 1)
            ->whereNotNull('deposited_at')
            ->exists();

        // Only fire commissions if inst #1 is fully deposited — never on a partial/pending payment
        if ($inst1Deposited && $user->saving_total_deposited > 0) {
            $alreadyFired = \App\Models\Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'direct_indirect')
                ->where('source_type', 'saving_instalment')
                ->exists();

            if (!$alreadyFired) {
                $this->savingAccountService->assignSavingCommissions($user, $user->saving_total_deposited);
            }
        }

        // Build a success message that accurately reflects what was enabled
        if ($inst1Deposited) {
            $message = "'{$user->name}' activated — login enabled and ROI/commissions are now running.";
        } else {
            $setting    = Setting::first();
            $inst1Amount = $setting->saving_min_deposit ?? 19;
            $message = "'{$user->name}' activated — login access granted. ROI and commissions will start automatically once the first instalment (\${$inst1Amount}) is confirmed.";
        }

        return back()->with('success', $message);
    }

    // =========================================================================
    // ADMIN — list enrolled standard users pending saving activation
    // =========================================================================

    public function adminEnrollments(Request $request)
    {
        $query = User::where('saving_enrolled', true)
            ->where('account_type', '!=', 'saving') // exclude dedicated saving account users
            ->with(['savingInstalments' => fn($q) => $q->orderBy('instalment_number'), 'savingSponsor']);

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->status === 'activated') {
            $query->where('saving_enrollment_activated', true);
        } elseif ($request->status === 'pending') {
            $query->where('saving_enrollment_activated', false);
        }

        $users              = $query->latest()->paginate(20)->withQueryString();
        $totalEnrolled      = User::where('saving_enrolled', true)->where('account_type', '!=', 'saving')->count();
        $totalActivated     = User::where('saving_enrolled', true)->where('account_type', '!=', 'saving')->where('saving_enrollment_activated', true)->count();
        $pendingActivation  = $totalEnrolled - $totalActivated;

        return view('admin.saving.enrollments', compact(
            'users', 'totalEnrolled', 'totalActivated', 'pendingActivation'
        ));
    }

    /**
     * Admin activates a saving program enrollment for an existing standard user.
     * After activation: daily ROI and saving commissions become eligible.
     */
    public function activateEnrollment(User $user): RedirectResponse
    {
        abort_unless($user->saving_enrolled && $user->account_type !== 'saving', 404);

        if ($user->saving_enrollment_activated) {
            return back()->with('info', "'{$user->name}' is already activated.");
        }

        $user->update([
            'saving_enrollment_activated'    => true,
            'saving_enrollment_activated_at' => now(),
            'saving_enrollment_activated_by' => Auth::id(),
            'saving_registration_completed'  => true,
        ]);

        // Auto-enroll the sponsor so they can see their saving team, commissions and ROI
        $sponsor = $user->savingSponsor()->first() ?? $user->parent;
        if ($sponsor) {
            $this->savingAccountService->autoEnrollSponsor($sponsor);
        }

        // Fire commissions if instalment #1 has been deposited and commissions not already sent
        $inst1 = $user->savingInstalments()
            ->where('instalment_number', 1)
            ->whereNotNull('deposited_at')
            ->first();

        $alreadyFired = \App\Models\Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'direct_indirect')
            ->where('source_type', 'saving_instalment')
            ->exists();

        if ($inst1 && !$alreadyFired && $user->saving_total_deposited > 0) {
            $this->savingAccountService->assignSavingCommissions($user, $user->saving_total_deposited);
        }

        return back()->with('success', "'{$user->name}' Savings Program enrollment activated. ROI and commissions are now enabled.");
    }

    // =========================================================================
    // ADMIN — set saving parent user
    // =========================================================================

    // =========================================================================
    // ADMIN — download due / overdue instalment report
    // =========================================================================

    public function adminDueExport(Request $request)
    {
        $from   = $request->get('from');
        $to     = $request->get('to');
        $userId = $request->get('user_id') ?: null;

        $suffix   = $userId ? ('-user' . $userId) : '-all';
        $filename = 'due-instalments' . $suffix . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new SavingDueInstalmentExport($from, $to, $userId ? (int) $userId : null), $filename);
    }

    public function setSavingParent(Request $request): RedirectResponse
    {
        $request->validate([
            'saving_parent_user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->saving_parent_user_id);

        if ($user->account_type !== 'saving') {
            return back()->with('error', 'The selected user must have a Saving account type.');
        }

        Setting::first()->update(['saving_parent_user_id' => $user->id]);

        return back()->with('success', "'{$user->name}' set as the Saving Account parent user.");
    }
}
