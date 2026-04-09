<?php

namespace App\Http\Controllers;

use App\Models\ReferralLink;
use App\Models\SavingInstalment;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
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
        $summary = $this->savingAccountService->getInstalmentSummary($user);
        $setting = Setting::first();

        // Registration payment breakdown
        $regFee        = (float) ($setting->saving_registration_fee ?? 5);
        $minDeposit    = (float) ($setting->saving_min_deposit ?? 19);
        $fullTotal     = $regFee + $minDeposit;
        $paidAtReg     = (float) ($user->converted_usdt_amount ?? 0);
        $partialDeposit = max(0, $paidAtReg - $regFee); // amount paid toward instalment #1 at registration
        $inst1Remaining = max(0, $minDeposit - $partialDeposit);

        return view('saving.instalments', array_merge($summary, [
            'setting'        => $setting,
            'paidAtReg'      => $paidAtReg,
            'regFee'         => $regFee,
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

        if ($user->account_type !== 'saving') {
            return back()->with('error', 'This action is only available for Saving accounts.');
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

        // Allow instalment #1 always (it may be overdue from registration partial payment).
        // For instalment #2+, enforce the due date — user cannot submit early.
        if ($instalment->instalment_number > 1 && \Carbon\Carbon::today()->lt($instalment->due_date)) {
            $daysLeft = \Carbon\Carbon::today()->diffInDays($instalment->due_date);
            return back()->with('error', "Instalment #{$instalment->instalment_number} is not due yet. You can submit it in {$daysLeft} day(s) (due: {$instalment->due_date->format('d M Y')}).");
        }

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
        $query = User::where('account_type', 'saving')
            ->with(['savingInstalments'])
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

        // Counts for summary cards
        $totalSaving      = User::where('account_type', 'saving')->count();
        $registrationDone = User::where('account_type', 'saving')->where('saving_registration_completed', true)->count();
        $pendingSubmissions = SavingInstalment::where('status', 'submitted')->count();

        return view('admin.saving.index', compact(
            'users', 'setting', 'totalSaving', 'registrationDone', 'pendingSubmissions'
        ));
    }

    /**
     * Admin views detail for one saving account user.
     */
    public function adminShow(User $user)
    {
        abort_unless($user->account_type === 'saving', 404);

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
        abort_unless($user->account_type === 'saving', 404);

        $user->update([
            'saving_registration_completed' => true,
            'can_login'                     => true,
        ]);

        // Fire commissions only if instalment #1 is deposited AND not already fired
        $inst1Deposited = $user->savingInstalments()
            ->where('instalment_number', 1)
            ->whereNotNull('deposited_at')
            ->exists();

        $alreadyFired = \App\Models\Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'direct_indirect')
            ->where('source_type', 'saving_instalment')
            ->exists();

        if ($inst1Deposited && !$alreadyFired) {
            $this->savingAccountService->assignSavingCommissions($user, $user->saving_total_deposited);
        }

        return back()->with('success', "'{$user->name}' has been activated. They can now log in and earn commissions.");
    }

    // =========================================================================
    // ADMIN — set saving parent user
    // =========================================================================

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
