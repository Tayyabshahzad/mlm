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
            'submitted_amount'=> 'required|numeric|min:0.01',
            'proof'           => 'required|image|mimes:jpg,jpeg,png|max:3072',
        ]);

        $instalment = $this->savingAccountService->nextDueInstalment($user);

        if (!$instalment) {
            return back()->with('error', 'No pending instalment found.');
        }

        if ($instalment->status === 'submitted') {
            return back()->with('error', 'You have already submitted proof for this instalment. Please wait for admin confirmation.');
        }

        // Calculate total required = base + ADB + FISP charges
        $adbFee        = $user->adb_option  ? round($instalment->amount * 0.075 /* ADB: sum_assured/1000 * 3 */, 2) : 0;
        $fispFee       = $user->fisp_option ? round($instalment->amount * 0.1   /* FISP: sum_assured/1000 * 4 */, 2) : 0;
        $totalRequired = round($instalment->amount + $adbFee + $fispFee, 2);
        $submitted     = (float) $request->submitted_amount;

        if ($submitted < $totalRequired) {
            return back()->withInput()->with('error',
                "Insufficient amount. You submitted $" . number_format($submitted, 2) .
                " but the required amount is $" . number_format($totalRequired, 2) .
                " (base $" . number_format($instalment->amount, 2) .
                ($adbFee  > 0 ? " + ADB $" . number_format($adbFee, 2)  : '') .
                ($fispFee > 0 ? " + FISP $" . number_format($fispFee, 2) : '') . ")."
            );
        }

        if ($submitted > $totalRequired) {
            return back()->withInput()->with('error',
                "Amount too high. You entered $" . number_format($submitted, 2) .
                " but the exact required amount is $" . number_format($totalRequired, 2) . ". Please enter the correct amount."
            );
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

        // Pending instalment commissions for this user (as payer)
        $pendingCommissions = \App\Models\SavingInstalmentCommission::with('ancestor:id,name,username')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->orderBy('instalment_number')
            ->orderBy('level')
            ->get();

        return view('admin.saving.show', array_merge($summary, [
            'savingUser'         => $user,
            'setting'            => $setting,
            'pendingCommissions' => $pendingCommissions,
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

        // Validate submitted amount covers base + ADB + FISP
        $user          = $instalment->user;
        $adbFee        = $user->adb_option  ? round($instalment->amount * 0.075 /* ADB: sum_assured/1000 * 3 */, 2) : 0;
        $fispFee       = $user->fisp_option ? round($instalment->amount * 0.1   /* FISP: sum_assured/1000 * 4 */, 2) : 0;
        $totalRequired = round($instalment->amount + $adbFee + $fispFee, 2);
        $submitted     = (float) ($instalment->submitted_amount ?? 0);

        if ($submitted < $totalRequired) {
            return back()->with('error',
                "Cannot confirm — submitted amount $" . number_format($submitted, 2) .
                " is less than the required $" . number_format($totalRequired, 2) .
                " (base $" . number_format($instalment->amount, 2) .
                ($adbFee  > 0 ? " + ADB $"  . number_format($adbFee, 2)  : '') .
                ($fispFee > 0 ? " + FISP $" . number_format($fispFee, 2) : '') . "). " .
                "Please reject and ask the member to resubmit the correct amount."
            );
        }

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
     * Admin force-processes a deferred instalment (confirmed but wallet not yet credited).
     */
    public function adminForceDeposit(SavingInstalment $instalment): RedirectResponse
    {
        if ($instalment->status !== 'confirmed' || !$instalment->deposit_deferred || $instalment->deposited_at) {
            return back()->with('error', 'This instalment is not in a deferred-pending state.');
        }

        try {
            DB::transaction(function () use ($instalment) {
                $instalment->update(['deposit_deferred' => false, 'next_cycle_date' => null]);
                $this->savingAccountService->creditDepositToWallet($instalment);
            });

            return back()->with('success', "Instalment #{$instalment->instalment_number} deposit processed and wallet credited.");
        } catch (\Exception $e) {
            Log::error("Force deposit failed for instalment {$instalment->id}: " . $e->getMessage());
            return back()->with('error', 'Force deposit failed: ' . $e->getMessage());
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

        // Sync amount to current plan — if admin changed the plan while this instalment
        // was in 'submitted' state, it missed the bulk update. Align it now.
        $currentPlanAmount = SavingInstalment::where('user_id', $instalment->user_id)
            ->where('status', 'pending')
            ->where('id', '!=', $instalment->id)
            ->value('amount');

        if ($currentPlanAmount && $currentPlanAmount != $instalment->amount) {
            $instalment->update(['amount' => $currentPlanAmount]);
        }

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

    public function adminActivate(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->account_type === 'saving' || $user->saving_enrolled, 404);

        $request->validate([
            'monthly_amount' => 'nullable|numeric|min:0.01',
        ]);

        // Update all pending instalments to the admin-specified monthly amount
        $monthlyAmount = $request->filled('monthly_amount') ? (float) $request->monthly_amount : null;
        if ($monthlyAmount) {
            // Only update pending instalments — submitted ones keep their original amount
            // so the admin can still confirm/reject at what the user actually paid.
            $user->savingInstalments()
                ->where('status', 'pending')
                ->update(['amount' => $monthlyAmount]);
        }

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

        // Fire commissions for every deposited instalment that has not already been tracked.
        // assignSavingCommissions is idempotent per-instalment via the unique constraint.
        $depositedInstalments = $user->savingInstalments()
            ->where('status', 'confirmed')
            ->whereNotNull('deposited_at')
            ->orderBy('instalment_number')
            ->get();

        $inst1Deposited = false;
        foreach ($depositedInstalments as $inst) {
            $baseAmount = (float) ($inst->submitted_amount ?? $inst->amount);
            if ($inst->instalment_number === 1) {
                $inst1Deposited = true;
                // Registration partial is NOT added to the commission base here.
                // Commission is paid only on the instalment payment amount.
                // (The partial was already credited to the saving wallet at registration,
                //  and including it here caused double-commission for full-payment registrants.)
            }
            $this->savingAccountService->assignSavingCommissions($user, $baseAmount, $inst);
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
    // ADMIN — update instalment schedule for an already-activated user
    //         Only allowed while instalment #1 has not yet been confirmed.
    // =========================================================================

    public function adminUpdateSchedule(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->account_type === 'saving' || $user->saving_enrolled, 404);

        $request->validate([
            'monthly_amount' => 'required|numeric|min:0.01',
        ]);

        // Block if instalment #1 is already confirmed — plan is locked
        $inst1Confirmed = $user->savingInstalments()
            ->where('instalment_number', 1)
            ->where('status', 'confirmed')
            ->exists();

        if ($inst1Confirmed) {
            return back()->with('error', 'Instalment plan cannot be changed — instalment #1 has already been paid.');
        }

        $monthlyAmount = (float) $request->monthly_amount;
        $updated = $user->savingInstalments()
            ->where('status', 'pending')
            ->update(['amount' => $monthlyAmount]);

        return back()->with('success', "Instalment plan updated — {$updated} pending instalment(s) set to \${$monthlyAmount}.");
    }

    // =========================================================================
    // ADMIN — adjust instalment plan AFTER instalments 1 & 2 are confirmed
    // =========================================================================

    public function showAdjustPlan(Request $request)
    {
        $selectedUser    = null;
        $instalments     = collect();
        $confirmedCount  = 0;

        if ($request->filled('username')) {
            $selectedUser = User::where('username', $request->username)
                ->where(fn ($q) => $q->where('account_type', 'saving')->orWhere('saving_enrolled', true))
                ->first();

            if ($selectedUser) {
                $instalments    = $selectedUser->savingInstalments()->orderBy('instalment_number')->get();
                $confirmedCount = $instalments->whereIn('status', ['confirmed'])->count();
            }
        }

        return view('admin.saving.adjust-plan', compact('selectedUser', 'instalments', 'confirmedCount'));
    }

    public function applyAdjustPlan(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'add_to_inst1' => 'required|numeric|min:0',
            'add_to_inst2' => 'required|numeric|min:0',
            'admin_notes'  => 'nullable|string|max:500',
        ], [
            'add_to_inst1.min' => 'Instalment 1 addition must be 0 or greater.',
            'add_to_inst2.min' => 'Instalment 2 addition must be 0 or greater.',
        ]);

        if ((float)$request->add_to_inst1 <= 0 && (float)$request->add_to_inst2 <= 0) {
            return back()->with('error', 'At least one adjustment amount must be greater than zero.')->withInput();
        }

        $user = User::find($request->user_id);

        try {
            $result = $this->savingAccountService->adjustInstalmentPlan(
                user: $user,
                addToInst1: (float) $request->add_to_inst1,
                addToInst2: (float) $request->add_to_inst2,
                adminNotes: $request->admin_notes ?? ''
            );

            $msg = implode(' | ', [
                "Plan adjusted for {$user->username}.",
                "Additional credited: \${$result['total_additional']}",
                "New monthly rate: \${$result['new_monthly_amount']}",
                "Future instalments updated: {$result['future_rows_updated']}",
                "Commissions distributed to {$result['commissions_paid']} upline(s)",
            ]);

            return redirect()->route('admin.saving.show', $user)->with('success', $msg);

        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Throwable $e) {
            \Log::error("adjustInstalmentPlan failed for user {$user->id}: " . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred. Please check the logs.')->withInput();
        }
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

        // Fire commissions for every deposited instalment (idempotent per-instalment).
        $depositedInstalments = $user->savingInstalments()
            ->where('status', 'confirmed')
            ->whereNotNull('deposited_at')
            ->orderBy('instalment_number')
            ->get();

        foreach ($depositedInstalments as $inst) {
            $baseAmount = (float) ($inst->submitted_amount ?? $inst->amount);
            // Registration partial is NOT added — commission is on the instalment payment only.
            $this->savingAccountService->assignSavingCommissions($user, $baseAmount, $inst);
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

    public function adminDuePreview(Request $request)
    {
        $from   = $request->get('from')    ?: now()->startOfMonth()->toDateString();
        $to     = $request->get('to')      ?: now()->toDateString();
        $userId = $request->get('user_id') ?: null;

        // Include: instalments within the range AND overdue ones before the start date
        $query = \App\Models\SavingInstalment::where('status', 'pending')
            ->whereDate('due_date', '<=', $to);

        if ($userId) {
            $query->where('user_id', (int) $userId);
        }

        $userIds = $query->pluck('user_id')->unique();

        $rows = \App\Models\User::whereIn('id', $userIds)
            ->with([
                'savingInstalments' => fn($q) => $q
                    ->where('status', 'pending')
                    ->whereDate('due_date', '<=', $to)
                    ->orderBy('due_date'),
                'savingSponsor',
            ])
            ->orderBy('name')
            ->get()
            ->map(function ($user) use ($from) {
                $all      = $user->savingInstalments;
                $overdue  = $all->filter(fn($i) => $i->due_date->lt(\Carbon\Carbon::parse($from)));
                $inRange  = $all->filter(fn($i) => !$i->due_date->lt(\Carbon\Carbon::parse($from)));
                $sponsor  = $user->savingSponsor->first();

                return [
                    'name'             => $user->name,
                    'username'         => $user->username,
                    'phone'            => $user->phone_number ?? '—',
                    'sponsor'          => $sponsor ? $sponsor->name . ' (@' . $sponsor->username . ')' : '—',
                    'overdue_count'    => $overdue->count(),
                    'in_range_count'   => $inRange->count(),
                    'total_pending'    => $all->count(),
                    'overdue_amount'   => number_format($overdue->sum('amount'), 2),
                    'in_range_amount'  => number_format($inRange->sum('amount'), 2),
                    'total_due'        => number_format($all->sum('amount'), 2),
                    'oldest_due'       => $all->min('due_date') ? \Carbon\Carbon::parse($all->min('due_date'))->format('d M Y') : '—',
                    'latest_due'       => $all->max('due_date') ? \Carbon\Carbon::parse($all->max('due_date'))->format('d M Y') : '—',
                    'has_overdue'      => $overdue->count() > 0,
                ];
            });

        $totalDue     = $rows->sum(fn($r) => (float) str_replace(',', '', $r['total_due']));
        $overdueUsers = $rows->where('has_overdue', true)->count();

        return response()->json([
            'data'          => $rows->values(),
            'total'         => $rows->count(),
            'total_due'     => number_format($totalDue, 2),
            'overdue_users' => $overdueUsers,
        ]);
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

