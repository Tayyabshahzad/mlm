<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\InvestmentSlab;
use App\Models\ReferralLink;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\UserInvestment;
use App\Services\SavingAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use App\Rules\ValidActivationCode;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    public function __construct(private SavingAccountService $savingAccountService) {}

    /**
     * Display the registration view.
     */
    public function create(Request $request, $ref = null)
    {
        $setting  = Setting::first();
        $isSaving = $request->query('type') === 'saving';
        return view('auth.register', compact('ref', 'setting', 'isSaving'));
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $setting     = Setting::first();
        $accountType = $request->input('account_type', 'standard_investment');

        if ($accountType === 'saving') {
            if ($request->input('user_type') === 'existing') {
                return $this->storeSavingExistingUser($request, $setting);
            }
            return $this->storeSavingAccount($request, $setting);
        }

        return $this->storeStandardAccount($request, $setting);
    }

    // -------------------------------------------------------------------------
    // Standard investment registration (existing logic)
    // -------------------------------------------------------------------------

    private function storeStandardAccount(Request $request, $setting): RedirectResponse
    {
        $minUsdt = ($setting->standard_package_min ?? 1) + ($setting->registration_fee ?? 0);

        $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => 'required|string|max:255|unique:' . User::class,
            'email'            => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
            'transaction_id'   => 'required|max:50|string|unique:' . User::class,
            'phone_number'     => 'required|unique:' . User::class,
            'cc'               => 'required',
            'payment_method'   => 'required|in:usdt,bank,cash_slip,activation_code',
            'referral_link'    => ['required', 'string', 'exists:referral_links,link'],
            'amount_src'       => $request->payment_method === 'activation_code' ? 'nullable|image|mimes:jpg,jpeg,png|max:2048' : 'required|image|mimes:jpg,jpeg,png|max:2048',
            'activation_code'  => ['nullable', new ValidActivationCode($request->payment_method)],
            'transferred_amount' => 'required|numeric',
            'usdt_amount'      => 'required|numeric|min:' . $minUsdt,
        ]);

        $netAmount = $request->usdt_amount - $setting->registration_fee;
        if ($netAmount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Insufficient USDT amount after fee deduction.');
        }

        DB::beginTransaction();
        try {
            $referralLink = ReferralLink::where('link', $request->referral_link)
                ->where('is_active', true)
                ->firstOrFail();

            $username = $this->generateUsername($request->username);
            $vipMin   = $setting->vip_package_min ?? 35;
            $userPlan = $netAmount >= $vipMin ? 'vip' : 'standard';

            $user = User::create([
                'name'                      => $request->name,
                'email'                     => $request->email,
                'password'                  => Hash::make($request->password),
                'username'                  => $username,
                'is_active'                 => true,
                'phone_verified'            => true,
                'sponsor_id'               => $referralLink->user->id,
                'transaction_id'            => $request->transaction_id,
                'phone_number'              => $request->cc . $request->phone_number,
                'payment_method'            => $request->payment_method,
                'usdt_rate'                 => $setting->usd,
                'transferred_amount'        => $request->transferred_amount,
                'converted_usdt_amount'     => $request->usdt_amount,
                'fee_deducted'              => $setting->registration_fee,
                'net_invested_usdt_amount'  => $netAmount,
                'roi_eligible_investment_amount' => $netAmount,
                'user_plan'                 => $userPlan,
                'account_type'              => 'standard_investment',
            ]);

            $this->handleActivationCode($request, $user);
            $this->createInitialInvestment($user);
            $this->checkAndCreateSlabs($user);
            $user->assignRole('member');

            ReferralLink::create(['user_id' => $user->id, 'link' => $username]);
            $this->registerUser($referralLink->user_id, $user->id);

            if ($request->hasFile('amount_src')) {
                $user->addMedia($request->file('amount_src'))->toMediaCollection('user_amount_source');
            }

            DB::commit();
            Auth::login($user);
            return redirect(route('dashboard', absolute: false));

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Standard registration failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Saving account registration
    // -------------------------------------------------------------------------

    private function storeSavingAccount(Request $request, $setting): RedirectResponse
    {
        $savingFee     = $setting->saving_registration_fee ?? 5;
        $savingDeposit = $setting->saving_min_deposit ?? 19;
        $minTotal      = $savingFee; // minimum = $5 only
        $fullTotal     = $savingFee + $savingDeposit; // $24

        // Determine if paying full $24 or $5 only
        $isFullPayment = $request->usdt_amount >= $fullTotal;

        $request->validate([
            'name'             => 'required|string|max:255',
            'username'         => 'required|string|max:255|unique:' . User::class,
            'email'            => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password'         => ['required', 'confirmed', Rules\Password::defaults()],
            'transaction_id'   => 'required|max:50|string|unique:' . User::class,
            'phone_number'     => 'required|unique:' . User::class,
            'cc'               => 'required',
            'payment_method'   => 'required|in:usdt,bank,cash_slip,activation_code',
            'referral_link'    => ['required', 'string', 'exists:referral_links,link'],
            'amount_src'       => $request->payment_method === 'activation_code' ? 'nullable|image|mimes:jpg,jpeg,png|max:2048' : 'required|image|mimes:jpg,jpeg,png|max:2048',
            'activation_code'  => ['nullable', new ValidActivationCode($request->payment_method)],
            'transferred_amount' => 'required|numeric',
            'usdt_amount'      => 'required|numeric|min:' . $minTotal . '|max:' . $fullTotal,
        ]);
        

        // Validate referral link belongs to the saving tree
        $referralLink = ReferralLink::where('link', $request->referral_link)
            ->where('is_active', true)
            ->first();

        if (!$referralLink) {
            return redirect()->back()->withInput()->with('error', 'Invalid referral code.');
        }

        // Referral link must belong to saving account user OR the saving parent user
        // $referralOwner = $referralLink->user;
        // if (
        //     $referralOwner->account_type !== 'saving'
        //     && $referralOwner->id !== ($setting->saving_parent_user_id ?? null)
        // ) {
        //     return redirect()->back()->withInput()
        //         ->with('error', 'Saving accounts must use a referral code from within the Saving Account network.');
        // }

        DB::beginTransaction();
        try {
            $username = $this->generateUsername($request->username);

            // Net deposit = everything above the fee, regardless of whether it covers inst#1 fully.
            $netDeposit           = max(0.0, (float) $request->usdt_amount - (float) $savingFee);
            // Registration is "complete" only when the net deposit covers the full saving amount.
            $registrationComplete = $netDeposit >= (float) $savingDeposit;

            $user = User::create([
                'name'                           => $request->name,
                'email'                          => $request->email,
                'password'                       => Hash::make($request->password),
                'username'                       => $username,
                'is_active'                      => true,
                'phone_verified'                 => true,
                'can_login'                      => false, // requires admin activation
                'sponsor_id'                     => $referralLink->user->id,
                'transaction_id'                 => $request->transaction_id,
                'phone_number'                   => $request->cc . $request->phone_number,
                'payment_method'                 => $request->payment_method,
                'usdt_rate'                      => $setting->usd,
                'transferred_amount'             => $request->transferred_amount,
                'converted_usdt_amount'          => $request->usdt_amount,
                'fee_deducted'                   => $savingFee,
                'net_invested_usdt_amount'       => $registrationComplete ? $netDeposit : 0,
                'roi_eligible_investment_amount' => $registrationComplete ? $netDeposit : 0,
                'user_plan'                      => 'standard',
                'account_type'                   => 'saving',
                'saving_plan_start_date'         => Carbon::today()->toDateString(),
                'saving_registration_completed'  => $registrationComplete,
                // saving_total_deposited is only updated via creditDepositToWallet on admin confirmation.
                // For full payment at registration we pre-set it; for partials it stays 0 and gets
                // incremented when the admin confirms the submitted instalment.
                'saving_total_deposited'         => $registrationComplete ? $netDeposit : 0,
                'saving_initial_payment'         => $request->usdt_amount,
                'saving_initial_fee'             => $savingFee,
            ]);

            $this->handleActivationCode($request, $user);
            $user->assignRole('member');

            ReferralLink::create(['user_id' => $user->id, 'link' => $username]);
            // Register into the SAVING tree — completely separate from standard tree
            $this->registerUser($referralLink->user_id, $user->id, 'saving', $setting->saving_parent_user_id);

            if ($request->hasFile('amount_src')) {
                $user->addMedia($request->file('amount_src'))->toMediaCollection('user_amount_source');
            }

            // Build 25-month instalment schedule
            $monthlyAmount = $setting->saving_monthly_instalment ?? 19;
            $this->savingAccountService->createInstalmentSchedule($user, $netDeposit, $monthlyAmount);

            if ($registrationComplete) {
                // Full payment: confirm instalment #1 immediately and credit saving wallet.
                $this->savingAccountService->markFirstInstalmentConfirmed($user, $request->transaction_id, 1);

                \App\Models\Wallet::create([
                    'user_id'         => $user->id,
                    'wallet_type'     => 'saving',
                    'balance'         => $netDeposit,
                    'commission_type' => 'saving_deposit',
                    'level'           => '-',
                    'total_amount'    => $netDeposit,
                    'wallet_src'      => 'saving_instalment',
                    'source_type'     => 'saving',
                    'description'     => 'Initial saving deposit on registration',
                    'transaction_type'=> 'credit',
                ]);
            }
            // Partial payment or fee-only: inst #1 stays 'pending'.
            // The signup amount is stored in saving_initial_payment / saving_initial_fee on the user.
            // Admin reviews proof and activates the account. The user then submits the
            // remaining amount for inst #1 via the normal instalment flow, at which point
            // creditDepositToWallet auto-credits the registration partial on top.

            DB::commit();
            Auth::login($user);
            return redirect(route('dashboard', absolute: false));

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Saving account registration failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Existing user Savings Program enrollment
    // -------------------------------------------------------------------------

    private function storeSavingExistingUser(Request $request, $setting): RedirectResponse
    {
        $savingFee     = $setting->saving_registration_fee ?? 5;
        $savingDeposit = $setting->saving_min_deposit ?? 19;
        $minTotal      = $savingFee;
        $fullTotal     = $savingFee + $savingDeposit;

        $isFullPayment = $request->usdt_amount >= $fullTotal;

        $request->validate([
            'identifier'         => 'required|string|max:255',
            'transaction_id'     => 'required|max:50|string',
            'payment_method'     => 'required|in:usdt,bank,cash_slip',
            'referral_link'      => ['required', 'string', 'exists:referral_links,link'],
            'amount_src'         => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'transferred_amount' => 'required|numeric',
            'usdt_amount'        => 'required|numeric|min:' . $minTotal . '|max:' . $fullTotal,
        ]);

        // Find the existing user by username or email
        $existingUser = User::where('username', $request->identifier)
            ->orWhere('email', $request->identifier)
            ->first();

        if (!$existingUser) {
            return redirect()->back()->withInput()
                ->with('error', 'No account found with that username or email.');
        }

        if ($existingUser->saving_enrolled) {
            return redirect()->back()->withInput()
                ->with('error', 'This account is already enrolled in the Savings Program.');
        }

        $referralLink = ReferralLink::where('link', $request->referral_link)
            ->where('is_active', true)
            ->first();

        if (!$referralLink) {
            return redirect()->back()->withInput()->with('error', 'Invalid referral code.');
        }

        DB::beginTransaction();
        try {
            $netDeposit           = max(0.0, (float) $request->usdt_amount - (float) $savingFee);
            $registrationComplete = $netDeposit >= (float) $savingDeposit;

            $existingUser->update([
                'saving_enrolled'               => true,
                'saving_plan_start_date'        => Carbon::today()->toDateString(),
                'saving_registration_completed' => $registrationComplete,
                'saving_total_deposited'        => $registrationComplete ? $netDeposit : 0,
                'saving_initial_payment'        => $request->usdt_amount,
                'saving_initial_fee'            => $savingFee,
            ]);

            // Place user in the saving tree under the referral link owner
            $this->registerUser($referralLink->user_id, $existingUser->id, 'saving', $setting->saving_parent_user_id);

            if ($request->hasFile('amount_src')) {
                // Use a dedicated collection so it never mixes with the standard-plan proof
                // stored in 'user_amount_source' from their original registration.
                $existingUser->addMedia($request->file('amount_src'))->toMediaCollection('saving_enrollment_proof');
            }

            $monthlyAmount = $setting->saving_monthly_instalment ?? 19;
            $this->savingAccountService->createInstalmentSchedule($existingUser, $netDeposit, $monthlyAmount);

            if ($registrationComplete) {
                $this->savingAccountService->markFirstInstalmentConfirmed($existingUser, $request->transaction_id, 1);

                \App\Models\Wallet::create([
                    'user_id'          => $existingUser->id,
                    'wallet_type'      => 'saving',
                    'balance'          => $netDeposit,
                    'commission_type'  => 'saving_deposit',
                    'level'            => '-',
                    'total_amount'     => $netDeposit,
                    'wallet_src'       => 'saving_instalment',
                    'source_type'      => 'saving',
                    'description'      => 'Initial saving deposit on enrollment',
                    'transaction_type' => 'credit',
                ]);
            }
            // Partial payment or fee-only: inst #1 stays 'pending'.
            // Admin reviews proof via saving_initial_payment and activates the enrollment.
            // User then submits the remaining amount for inst #1 via the normal instalment flow.

            DB::commit();

            return redirect(route('login'))
                ->with('status', 'You have been enrolled in the Savings Program. Please log in to view your saving dashboard.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Saving enrollment (existing user) failed: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Something went wrong! ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    private function generateUsername(string $rawUsername): string
    {
        $base  = Str::slug($rawUsername);
        if (!$base) {
            abort(422, 'Invalid Username format');
        }
        $name  = $base;
        $count = 1;
        while (User::where('username', $name)->exists()) {
            $name = $base . '-' . $count++;
        }
        return $name;
    }

    private function handleActivationCode(Request $request, User $user): void
    {
        if ($request->payment_method !== 'activation_code') {
            return;
        }

        $activationCode = ActivationCode::where('code', $request->activation_code)
            ->where('admin_approval', 'approved')
            ->where('status', 'unused')
            ->firstOrFail();

        $activationCode->update([
            'status'     => 'used',
            'used_by'    => $user->id,
            'updated_at' => now(),
        ]);

        $user->activation_code_id = $activationCode->id;
        $user->save();

        $this->logTransaction(
            $activationCode->generated_by,
            'activation_code',
            'activation_code',
            0, 0,
            "New user '{$user->name}' was created using an activation code.",
            'debit'
        );
    }

    public function registerUser($parentId, $newUserId, string $treeType = 'standard', ?int $savingRootId = null): void
    {
        DB::beginTransaction();
        try {
            DB::table('referral_trees')->insertOrIgnore([
                'ancestor_id'   => $parentId,
                'descendant_id' => $newUserId,
                'level'         => 1,
                'tree_type'     => $treeType,
            ]);

            // Walk ancestors of the same tree type
            $ancestors = DB::table('referral_trees')
                ->where('descendant_id', $parentId)
                ->where('tree_type', $treeType)
                ->get();

            foreach ($ancestors as $ancestor) {
                DB::table('referral_trees')->insertOrIgnore([
                    'ancestor_id'   => $ancestor->ancestor_id,
                    'descendant_id' => $newUserId,
                    'level'         => $ancestor->level + 1,
                    'tree_type'     => $treeType,
                ]);
            }

            // If this is a saving tree and the direct sponsor has no saving-tree ancestry,
            // anchor the sponsor and new user directly under the saving root so the
            // global saving tree stays connected even when a standard user is the sponsor.
            if ($treeType === 'saving' && $savingRootId && $ancestors->isEmpty() && $parentId !== $savingRootId) {
                DB::table('referral_trees')->insertOrIgnore([
                    'ancestor_id'   => $savingRootId,
                    'descendant_id' => $parentId,
                    'level'         => 1,
                    'tree_type'     => 'saving',
                ]);
                DB::table('referral_trees')->insertOrIgnore([
                    'ancestor_id'   => $savingRootId,
                    'descendant_id' => $newUserId,
                    'level'         => 2,
                    'tree_type'     => 'saving',
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(5) . rand(10000, 99999));
        } while (ReferralLink::where('link', $code)->exists());

        return $code;
    }

    private function logTransaction($userId, $toAddress, $fromAddress, $amount, $finalAmount, $description, $status): void
    {
        TransactionLog::create([
            'user_id'          => $userId,
            'from_wallet_type' => $toAddress,
            'to_wallet_type'   => $fromAddress,
            'charge'           => 0,
            'amount'           => $amount,
            'final_amount'     => $finalAmount,
            'description'      => $description,
            'status'           => $status,
        ]);
    }

    protected function createInitialInvestment(User $user): void
    {
        UserInvestment::create([
            'user_id'            => $user->id,
            'amount'             => $user->net_invested_usdt_amount,
            'type'               => 'join',
            'description'        => 'Initial payment during account creation',
            'committed_amount'   => $user->net_invested_usdt_amount * 2,
            'total_earnings'     => 0,
            'roi_status'         => 'active',
            'user_plan_at_time'  => $user->user_plan ?? 'standard',
        ]);
    }

    private function checkAndCreateSlabs(User $user): void
    {
        $totalInvestment = UserInvestment::where('user_id', $user->id)->sum('amount');
        $totalSlabs      = InvestmentSlab::where('user_id', $user->id)->count();
        $available       = $totalInvestment - ($totalSlabs * 100);

        while ($available >= 100) {
            $achievedAt = now();
            $willPayAt  = $achievedAt->copy()->addMonths(24)->startOfMonth()->addMonth();

            InvestmentSlab::create([
                'user_id'        => $user->id,
                'slab_count'     => ++$totalSlabs,
                'amount'         => 100,
                'achived_at'     => $achievedAt,
                'current_balance'=> $user->roi_eligible_investment_amount,
                'maturity_date'  => now()->addMonths(24),
                'will_pay_at'    => $willPayAt,
                'status'         => 'No',
            ]);

            $available -= 100;
        }
    }
}
