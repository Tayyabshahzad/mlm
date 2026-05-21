<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Week;
use Illuminate\Http\Request;
use App\Services\PVService;
use App\Services\WalletService;
use App\Services\CommissionService;
use App\Services\InvestmentSlabService;
use App\Services\RewardService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivationCode;
use App\Models\Setting;
use App\Models\TransactionLog;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContactsExport;
use App\Models\UserInvestment;
use App\Models\InvestmentSlab;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $pvService;
    protected $walletService;
    protected $commissionService;
    protected $investmentSlabService;
    protected $rewardService;

    public function __construct(
        PVService $pvService,
        WalletService $walletService,
        CommissionService $commissionService,
        InvestmentSlabService $investmentSlabService,
        RewardService $rewardService
    ) {
        $this->pvService = $pvService;
        $this->walletService = $walletService;
        $this->commissionService = $commissionService;
        $this->investmentSlabService = $investmentSlabService;
        $this->rewardService = $rewardService;
    }

 
    public function updateStatus(Request $request)
    {
        $request->validate(['member_id' => ['required', 'integer', 'exists:users,id']]);
        $user = User::find($request->member_id);

        if ($user->can_login) {
            return redirect()->back()->with('error', 'This User is Already Activated');
        }

        // ── Saving account activation (separate flow) ──────────────────────
        if ($user->account_type === 'saving') {
            return $this->activateSavingUser($user);
        }

        // ── Standard investment activation (existing flow) ─────────────────
        try {
            DB::beginTransaction();

            if ($user->current_pv_balance <= 100) {
                $this->pvService->assignInitialPV($user);
            }

            $this->commissionService->assignCommissions($user, $user->roi_eligible_investment_amount);
            $this->investmentSlabService->createSlabsForUser($user);
            $user->can_login = true;
            $user->save();
            $this->rewardService->processRewardsForUserActivation($user);
            DB::commit();
            Log::info("Standard user {$user->id} activated successfully");
            return redirect()->back()->with('success', 'Member Status has been Updated');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to activate user {$user->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update member status');
        }
    }

    /**
     * Activate a saving account user.
     * Sets can_login = true, then distributes saving commissions for any already-deposited amount.
     * No PV, no reward, no profit-share, no investment slabs.
     */
    private function activateSavingUser(User $user)
    {
        try {
            DB::beginTransaction();

            $user->can_login = true;
            $user->saving_registration_completed = true;
            $user->save();

            // Fire saving commissions only if:
            // 1. Instalment #1 has already been deposited (deposited_at not null)
            // 2. Commission has NOT already been fired (no direct_indirect saving_instalment wallet entry)
            $inst1Deposited = $user->savingInstalments()
                ->where('instalment_number', 1)
                ->whereNotNull('deposited_at')
                ->exists();

            $alreadyFired = \App\Models\Wallet::where('user_id', $user->id)
                ->where('wallet_type', 'direct_indirect')
                ->where('source_type', 'saving_instalment')
                ->exists();

            if ($inst1Deposited && !$alreadyFired) {
                $savingService = app(\App\Services\SavingAccountService::class);
                $savingService->assignSavingCommissions($user, $user->saving_total_deposited);
            }

            DB::commit();
            Log::info("Saving account user {$user->id} activated successfully");
            return redirect()->back()->with('success', 'Saving Account Member Activated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to activate saving user {$user->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to activate saving account: ' . $e->getMessage());
        }
    }

 
    public function getChildCountAtLevel($userId, $level, $currentLevel = 1)
    {
        if ($currentLevel > $level) {
            return 0; // Stop recursion if the current level exceeds the desired level
        }

        // Get direct children at the current level
        $directChildren = User::where('blocked',false)->where('sponsor_id', $userId)->where('can_login', true)->pluck('id');

        if ($currentLevel == $level) {
            // If at the desired level, return the count of children
            return $directChildren->count();
        }

        $countAtSpecificLevel = 0;
        foreach ($directChildren as $childId) {
            // Recursively count children at the next level
            $countAtSpecificLevel += $this->getChildCountAtLevel($childId, $level, $currentLevel + 1);
        }

        return $countAtSpecificLevel;
    } 

    public function assignRewardToUser($parentID,  $level)
    { 
        if ($level > 7) {
            return;
        }
        $parent = User::where('blocked',false)->find($parentID);
        if (!$parent) {
            return;
        }
        $directChildCount = $this->getChildCountAtLevel($parentID, $level);
        $rewardLevels = collect([
            ['level' => 1, 'reward_amount' => 130, 'users_required' => 10],
            ['level' => 2, 'reward_amount' => 260, 'users_required' => 50],
            ['level' => 3, 'reward_amount' => 1050, 'users_required' => 150],
            ['level' => 4, 'reward_amount' => 3450, 'users_required' => 400],
            ['level' => 5, 'reward_amount' => 8650, 'users_required' => 1000],
            ['level' => 6, 'reward_amount' => 26000, 'users_required' => 2000],
            ['level' => 7, 'reward_amount' => 41500, 'users_required' => 4000],
        ]);
        $specificRewardLevel = $rewardLevels->firstWhere('level', $level); 
        for ($i = 1; $i < $level; $i++) {
            $previousReward = Wallet::where([
                ['user_id', '=', $parentID],
                ['wallet_type', '=', 'reward'],
                ['commission_type', '=', 'reward'],
                ['level', '=', $i],
            ])->first();
    
            if (!$previousReward || $previousReward->balance <= 0) {
                Log::info("Skipping reward for level {$level} because reward for level {$i} is not achieved.");
                return;
            }
        }

        Log::info("Level: " . $level . " directChildCount: " . $directChildCount . " specificRewardLevel: " . $specificRewardLevel['users_required']);
        // $usersRequired = $rewardLevels; 
        if ($directChildCount >= $specificRewardLevel['users_required']) {
          Log::info('Condition met for level ' . $level);
            $this->assignReward($parentID, $specificRewardLevel['reward_amount'], $specificRewardLevel['level']);
        }
        $parentExists  = User::where('blocked',false)->find($parent->sponsor_id);
        if ($parentExists) {
            Log::info('parentExists: ' . $parentExists->id);
            $this->assignRewardToUser($parentExists->id, $level+1);
        }
    }
    public function checkAndAssignRewards($userId, $user)
    {  
        $directChildren = User::where('blocked',false)->where('sponsor_id', $userId)
            ->where('can_login', 1) // Only active users
            ->get();
        Log::info("LOG 1 - { $user->name }  update ho raha ha  jis ka parent {$user->parent->name} ha , ham ny idr {$user->parent->name} k sary user ko get kr liya ha ");
        $rewardLevels = collect([
            ['level' => 1, 'reward_amount' => 130, 'users_required' => 10],
            ['level' => 2, 'reward_amount' => 260, 'users_required' => 50],
            ['level' => 3, 'reward_amount' => 1050, 'users_required' => 150],
            ['level' => 4, 'reward_amount' => 3450, 'users_required' => 400],
            ['level' => 5, 'reward_amount' => 8650, 'users_required' => 1000],
            ['level' => 6, 'reward_amount' => 26000, 'users_required' => 2000],
            ['level' => 7, 'reward_amount' => 41500, 'users_required' => 4000],
        ]);
        Log::info("Loop Start  ------------------  ");
        $sn = 0;
        foreach ($rewardLevels as $level) {
            $sn++;
            Log::info("Iteration  -----------  " . $sn);
            // Check if reward for this level has already been assigned
            //Wallet ke table min dakh rha ha k shahzad ke id k sat same level min entry to nhin ha agr ha to skip kro do process
            $existingReward = Wallet::where([
                ['user_id', '=', $userId],
                ['wallet_type', '=', 'reward'],
                ['commission_type', '=', 'reward'],
                ['level', '=', $level['level']],
            ])->exists();
            Log::info("LOG 2 -  Idr Chek kia ha {$user->parent->name} ko rward level {$level['level']} pr paly sy assign to  nhin ha ");
            if ($existingReward) {
                // Skip if reward for this level is already assigned
                continue;
            }
            //Loop k level 1 pr check k agr loop min level one ha to tm shahzad k wo sary bandy ly k eg. aqil , faisal , qasim aow phir un bandon ko count kro agr count k bad un ka number 7 k braber ha to assing revard ka functon call kro or shahazad ko level 1 dy do 
            Log::info("LOG 3 - {$user->parent->name} ko Rward level {$level['level']} pr koi be assing nhin huwa");

            if ($level['level'] === 1) {
                Log::info(" LOG 4 -Loop min jb level " . $level['level'] . "ho jy ga to ham check kryn gy k  {$user->parent->name} k bachon ga count kia ha gr wo count  7 ko match kr jata ha to level 1 rewad mil gya ga  ");
                // For Level 1, use direct referrals count
                $directReferralsCount = $this->calculateDirectReferrals($userId);
                if ($directReferralsCount >= $level['users_required']) {
                    Log::info($level['users_required'] . " user ke condation meet kr gy level 1 open ho gya ");
                    $this->assignReward($userId, $level['reward_amount'], $level['level']);
                    Log::info("Level 1 reward assigned to parent user id --  {$userId}");
                    break;
                }
            } else {
                Log::info(" LOG 4 -Loop min jb level " . $level['level'] . "ho jy ga ha or is bar ham indirect user ko check kr ahy hin  ");
                // For other levels, calculate combined team size of direct children
                $totalTeamSize = 0;
                //directChildren children min wo parson hin jo shahzad k user hin remember hm tayyab ko create kr rahy hin
                Log::info(" LOG 5 -  {$user->parent->name}  pr loop laga ha or  {$user->parent->name} k hr user ko loop min add kia ha idr hamry pas  {$user->parent->name} k total child count " . $directChildren->count() . " hain");
                foreach ($directChildren as $child) {
                    // Ab idr sb user ke list a gy ha jo shahzad k users k mtlb qasim asin etc or asim yeh qasim ke id ko pick kr k us ke team member ko check kia ja raha ha for exp is function ko asim ke id yeh aqil ke id pass ho gy ha
                    Log::info(" LOG 6 -  Ab idr sb ak ak child jis ka parent {$user->parent->name}  ha, us ke team k count check kia ja raha hs is check ke waja yeh ha k ham ny  {$user->parent->name}  ko next level pr move krna ha  currentally is child {$child->name} k tem ko check kia ja raha ha ");
                    $childTeamSize = $this->calculateTeamSize($child->id, $child);
                    $totalTeamSize += $childTeamSize;
                    Log::info("Sub Child {$child->name} has team size of: {$childTeamSize}");
                }

                Log::info("Total team size for Level {$level['level']} check: {$totalTeamSize}");

                if ($totalTeamSize >= $level['users_required']) {
                    $this->assignReward($userId, $level['reward_amount'], $level['level']);
                    Log::info("Level {$level['level']} reward assigned to user {$userId} with total team size {$totalTeamSize}");
                    break;
                } else {
                    Log::info("Next Level Open nin huwa");
                }
            }

            Log::info("End ------------------  ");
        }
        Log::info("-------------Loop End  ------------------  ");
    } 
    public function calculateDirectReferrals($userId)
    {
        return DB::table('users')->where('sponsor_id', $userId)->where('can_login', 1)->count();
    }
    public function calculateTeamSize($userId, $childUser)
    {

        $directReferrals = User::where('blocked',false)->where('sponsor_id', $userId)
            ->where('can_login', 1)
            ->get(); 
        $directReferralsCount = $directReferrals->count(); 
        Log::info("Idr ham ny {$childUser->name} k direct team ka cont kia ha =  {$directReferralsCount} ."); 
        Log::info("recursively query  .");
        $downlineTeamSize = $directReferrals->sum(function ($child) {
            return $this->calculateTeamSize($child->id);
        }); 
        $totalTeamSize = $directReferralsCount + $downlineTeamSize; 
        Log::info("Team size for user {$childUser->name}: Direct Referrals = {$directReferralsCount}, Downline = {$downlineTeamSize}, Total = {$totalTeamSize}");

        return $totalTeamSize;
    } 

    private function assignReward($userId, $amount, $level)
    { 
        $existingReward = Wallet::where([
            ['user_id', '=', $userId],
            ['wallet_type', '=', 'reward'],
            ['commission_type', '=', 'reward'],
            ['level', '=', $level],
        ])->first();
        Log::info("Checking if wallet exists for user {$userId} at level {$level}: " . ($existingReward ? 'Exists' : 'Does not exist'));
        if ($existingReward && $existingReward->balance > 0) { 
            Log::info("Reward already assigned", [
                'user_id' => $userId,
                'level' => $level,
            ]);
            return;
        } 
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId, 'wallet_type' => 'reward', 'commission_type' => 'reward', 'level' => $level],
            ['balance' => 0.00]
        );
        Log::info("Wallet created for user {$userId} at level {$level}"); 
        $wallet->balance += $amount;
        $wallet->total_amount += $amount;
        $wallet->save(); 
        Log::info("Reward assigned", [
            'user_id' => $userId,
            'amount' => $amount,
            'level' => $level,
        ]);
    } 
  
    private function logTransaction($userId, $toAddress, $fromAddress, $amount, $finalAmount, $description)
    {
        TransactionLog::create([
            'user_id' => $userId,
            'from_wallet_type' => $toAddress,
            'to_wallet_type' => $fromAddress,
            'charge' => 0,
            'amount' => $amount,
            'final_amount' => $finalAmount,
            'description' => $description,
        ]);
    }

    public function downloadContacts(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        return Excel::download(new ContactsExport($request->start_date, $request->end_date), "Contacts-info-from ".$request->start_date ."-".$request->end_date.".xlsx");
    }

     public function userDetails(Request $request)
    {
        $userId = $request->get('id');
        $user = User::with('activationCode')->find($userId);
        if ($user) {
            $data = [
                'name'                    => $user->name,
                'email'                   => $user->email,
                'phone_number'            => $user->phone_number,
                'username'                => $user->username,
                'created_at'              => $user->created_at->format('Y-m-d H:i:s'),
                'status'                  => $user->can_login ? 'Active' : 'Inactive',
                // For enrolled standard users the saving proof is in 'saving_enrollment_proof';
                // user_amount_source belongs to their standard plan and must not be shown here.
                'amount_proof' => ($user->saving_enrolled && $user->account_type !== 'saving')
                    ? $user->getFirstMediaUrl('saving_enrollment_proof')
                    : $user->getFirstMediaUrl('user_amount_source'),
                'transaction_id'          => $user->transaction_id,
                'payment_method'          => $user->payment_method,
                'transferred_amount'      => $user->transferred_amount,
                'converted_usdt_amount'   => $user->converted_usdt_amount,
                'fee_deducted'            => $user->fee_deducted,
                'net_invested_usdt_amount'=> $user->net_invested_usdt_amount,
                'usdt_rate'               => $user->usdt_rate,
                'account_type'            => $user->account_type,
                'saving_enrolled'         => (bool) $user->saving_enrolled,
                'referBy' => [
                    'username' => ucfirst($user->parent->username ?? '—'),
                    'id'       => $user->parent->id ?? null,
                ],
                'activationCode' => [
                    'code'         => $user->activationCode->code ?? 'NA',
                    'generated_by' => $user->activationCode->generatedBy->name ?? 'NA',
                ],
            ];

            if ($user->account_type === 'saving' || $user->saving_enrolled) {
                $data['saving_registration_completed'] = $user->saving_registration_completed;
                $data['saving_plan_start_date']        = $user->saving_plan_start_date;
                $data['saving_total_deposited']        = $user->saving_total_deposited;
                // Saving-specific payment fields — correct for both pure saving users AND enrolled users.
                // (Do NOT use converted_usdt_amount / fee_deducted for enrolled users — those belong
                //  to their standard investment, not their saving enrollment payment.)
                $data['saving_initial_payment'] = $user->saving_initial_payment;
                $data['saving_initial_fee']     = $user->saving_initial_fee;

                // Saving Plan sponsor: level-1 ancestor in the SAVING referral tree only.
                // No fallback to the standard-plan parent — they are different trees.
                $savingSponsor = $user->savingSponsor()->first();
                $data['saving_sponsor'] = $savingSponsor
                    ? ['username' => $savingSponsor->username, 'id' => $savingSponsor->id]
                    : ['username' => '—', 'id' => null];

                // Instalment #1 — carries the payment submitted at signup (full or partial).
                $inst1 = $user->savingInstalments()->where('instalment_number', 1)->first();
                $data['signup_instalment'] = $inst1 ? [
                    'amount'          => number_format((float) $inst1->amount, 2),
                    'submitted_amount'=> $inst1->submitted_amount
                                            ? number_format((float) $inst1->submitted_amount, 2)
                                            : null,
                    'transaction_id'  => $inst1->transaction_id,
                    'status'          => $inst1->status,
                    'due_date'        => $inst1->due_date->format('d M Y'),
                    'submitted_at'    => $inst1->submitted_at?->format('d M Y H:i'),
                    'proof_url'       => $inst1->proofScreenshot(),
                ] : null;

                // For pure saving accounts the saving transaction IS on the user record.
                // For enrolled users the enrollment transaction is on instalment #1 (set at enrollment).
                $data['saving_transaction_id']  = $user->account_type === 'saving'
                    ? $user->transaction_id
                    : ($inst1?->transaction_id ?? null);
                $data['saving_payment_method']  = $user->account_type === 'saving'
                    ? $user->payment_method
                    : null;
                $data['saving_transferred_amount'] = $user->account_type === 'saving'
                    ? $user->transferred_amount
                    : null;
            }

            return response()->json(['success' => true, 'data' => $data]);
        }
        return response()->json(['success' => false, 'message' => 'User not found.']);
    }

     public function roiPayments(Request $request)
    {
        $query = ROITransaction::query();
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                \Carbon\Carbon::parse($request->start_date)->startOfDay(),
                \Carbon\Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }
        $payments = $query->orderBy('created_at', 'desc')->paginate(100);
        $users = User::where('blocked', false)->where('can_login', true)->get();
        return view('users.roi-payments', compact('users', 'payments'));
    } 

    

    public function rentalPercentage(){
        $weeks = Week::all();
        return view('users.rental.index', compact('weeks'));
    }

    public function addRentalPercentage(Request $request){
        
        $request->validate([
            'week_name' => 'required|string',
            'percentage' => 'required|numeric|min:3|max:7',
        ]);  
        $currentMonthWeeksCount = Week::whereMonth('created_at', now()->month)->count();
        if ($currentMonthWeeksCount >= 4) {
            return redirect()->back()->with('error', 'You cannot add more than 4 weeks in a month.');
        }
        Week::create($request->all());
        return redirect()->back()->with('success', 'Week percentage added successfully.');
    }

    public function updateRentalPercentage(Request $request, $id){ 
        $request->validate([
            'percentage' => 'required|numeric|max:7',
        ]); 
        $week = Week::findOrFail($id);
        $week->percentage = $request->percentage;
        $week->updated_at = Carbon::now();
        $week->save();
        return redirect()->back()->with('success', 'Week percentage updated successfully.');
    }

    public function deleteRentalPercentage(Request $request, $id){
        $week = Week::find($id);
        if (!$week) {
            return redirect()->back()->with('error', 'Week not found.');
        }
        $week->delete();
        return redirect()->back()->with('success', 'Week deleted successfully.');
    }
     
    public function userInfoUpdate(Request $request){
       
        $user = User::find($request->id);
      
        $request->validate([
            'password' => 'nullable|confirmed|min:8', // Password is optional but must match confirmation
            'phone' => 'nullable',
            'freez_wallet' => 'required|boolean', 
            'blocked'=>'boolean',
            'reason' => 'required_if:blocked,1|max:255',
            'user_id' =>'required',
            'negative_pv' => 'numeric|min:0'
        ],[
            'reason.required_if' => 'The reason is required when the account is blocked.',
        ]); 
        $user = User::find($request->user_id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }
       
        if ($request->hasFile('profile_avatar')) { 
            if ($user->hasMedia('user_profile_images')) {
                $user->getMedia('user_profile_images')->each(function ($media) {
                    $media->delete();  
                });
            }
            $user->addMedia($request->file('profile_avatar'))
            ->toMediaCollection('user_profile_images');
        }   
        if ($request->hasFile('cnic_front')) { 
            if ($user->hasMedia('user_document_cnic_front')) {
                $user->getMedia('user_document_cnic_front')->each(function ($media) {
                    $media->delete();   
                });
            }
            $user->addMedia($request->file('cnic_front'))
            ->toMediaCollection('user_document_cnic_front');
        }    
        if ($request->hasFile('cnic_back')) { 
            if ($user->hasMedia('user_document_cnic_back')) {
                $user->getMedia('user_document_cnic_back')->each(function ($media) {
                    $media->delete(); 
                });
            }
            $user->addMedia($request->file('cnic_back'))
            ->toMediaCollection('user_document_cnic_back');
        }
        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password')); // Hash the password
        }

        if ($request->filled('username')) { 
            $user->username = $request->input('username'); // Hash the password
        }
        if ($request->filled('email')) {
            $user->email = $request->input('email'); // Hash the password
        }

        $requestData = $request->except(['password', 'password_confirmation', 'profile_avatar', 'cnic_front', 'cnic_back']);
        $profile = $user->profile ?: new Profile();  
        $profile->skills = null;  // Or handle as needed
        
        $profile->fill($requestData);
        $profile->user_id = $user->id;  
        if($request['phone']){
            $user->phone_number = $request['phone'];
        }
        //Settings 
        
        $user->freez_wallet = $request->freez_wallet; 
        $user->blocked = $request->blocked;
        $user->reason = $request->reason;
        $user->negative_pv = $request->negative_pv;
        $user->save(); 
        $profile->save();  
        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function userDelete(Request $request){
        $request->validate(['delete_id' => 'required']);
        $user = User::find($request->delete_id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        // Lock all operations before soft-deleting
        $user->update([
            'can_login'    => 0,
            'blocked'      => 1,
            'is_active'    => 0,
            'freez_wallet' => 1,
            'roi_status'   => 'stopped',
        ]);

        // Soft delete — sets deleted_at, keeps DB record intact, no cascade
        $user->delete();

        return redirect()->back()->with('success', 'User account has been deactivated.');
    }

    /**
     * Remove a user's saving plan data only — does NOT delete the user account.
     */
    public function removeSavingData(Request $request)
    {
        $request->validate(['delete_id' => 'required|exists:users,id']);
        $user = User::findOrFail($request->delete_id);

        DB::transaction(function () use ($user) {
            // Delete proof screenshots for their instalments
            $instalmentIds = $user->savingInstalments()->pluck('id');
            if ($instalmentIds->isNotEmpty()) {
                \DB::table('media')
                    ->where('model_type', 'App\\Models\\SavingInstalment')
                    ->whereIn('model_id', $instalmentIds)
                    ->delete();
            }

            // Delete saving instalments
            $user->savingInstalments()->delete();

            // Delete saving wallets and saving commissions
            Wallet::where('user_id', $user->id)
                ->whereIn('wallet_type', ['saving', 'saving_roi'])
                ->delete();
            Wallet::where('user_id', $user->id)
                ->where('source_type', 'saving_instalment')
                ->delete();

            // Remove from saving referral tree
            \DB::table('referral_trees')
                ->where('tree_type', 'saving')
                ->where(fn($q) => $q->where('ancestor_id', $user->id)->orWhere('descendant_id', $user->id))
                ->delete();

            // Reset saving fields — account stays intact
            $user->update([
                'account_type'                   => 'standard_investment',
                'saving_enrolled'                => 0,
                'saving_enrollment_activated'    => 0,
                'saving_enrollment_activated_at' => null,
                'saving_enrollment_activated_by' => null,
                'saving_registration_completed'  => 0,
                'saving_plan_start_date'         => null,
                'saving_total_deposited'         => 0.00,
                'saving_initial_payment'         => 0.00,
                'saving_initial_fee'             => 0.00,
            ]);
        });

        return redirect()->back()->with('success', 'Saving plan data removed. User account remains active.');
    }

    
    public function userInfo(Request $request , $id){

        $user = User::with('profile')->find($id);
        return view('users.information',compact('user'));
    }

    public function adminUserWallets($id)
    {
        $user = User::findOrFail($id);

        $wallets = \App\Models\Wallet::where('user_id', $id)->get();

        // Standard wallet summaries
        $summary = [
            'online'               => $wallets->where('wallet_type', 'online')->sum('balance'),
            'direct_indirect'      => $wallets->where('wallet_type', 'direct_indirect')->sum('balance'),
            'direct_balance'       => $wallets->where('wallet_type', 'direct_indirect')->sum('direct_balance'),
            'indirect_balance'     => $wallets->where('wallet_type', 'direct_indirect')->sum('indirect_balance'),
            'roi'                  => $wallets->where('wallet_type', 'roi')->sum('balance'),
            'reward'               => $wallets->where('wallet_type', 'reward')->sum('balance'),
            'profit_share'         => $wallets->where('wallet_type', 'profit_share')->sum('balance'),
            'designation_incentive'=> $wallets->where('wallet_type', 'designation_incentive')->sum('balance'),
        ];

        // Saving-specific wallet summaries
        $savingSummary = null;
        if ($user->account_type === 'saving') {
            $savingSummary = [
                'saving_deposit'   => $wallets->where('wallet_type', 'saving')->sum('balance'),
                'saving_roi'       => $wallets->where('wallet_type', 'saving_roi')->sum('balance'),
                'saving_direct'    => $wallets->where('wallet_type', 'direct_indirect')
                                              ->where('source_type', 'saving_instalment')->sum('direct_balance'),
                'saving_indirect'  => $wallets->where('wallet_type', 'direct_indirect')
                                              ->where('source_type', 'saving_instalment')->sum('indirect_balance'),
            ];
        }

        // Recent transactions per wallet type
        $recentRoi = \App\Models\Wallet::where('user_id', $id)
            ->whereIn('wallet_type', $user->account_type === 'saving' ? ['saving_roi'] : ['roi'])
            ->orderByDesc('created_at')->limit(20)->get();

        $recentCommissions = \App\Models\Wallet::where('user_id', $id)
            ->where('wallet_type', 'direct_indirect')
            ->when($user->account_type === 'saving', fn($q) => $q->where('source_type', 'saving_instalment'))
            ->orderByDesc('created_at')->limit(20)->get();

        return view('users.wallet-overview', compact(
            'user', 'summary', 'savingSummary', 'recentRoi', 'recentCommissions'
        ));
    }

    public function adminUserTeam($id){
        $user = User::with('profile')->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }

        $nodeDataArray = [];

        // Alternative approach: Get all team members using a single query with recursive CTE or multiple queries
        // This ensures we get all levels of the team hierarchy
        // Start from level 0 for the root user, children will be level 1-7
        $this->buildTeamHierarchy($user, $nodeDataArray, 0, 8);

        return view('admin.user-team', compact('user', 'nodeDataArray'));
    }

    private function buildTeamHierarchy($user, &$nodeDataArray, $level = 0, $maxLevel = 7, $parentId = null) {
        // Add current user to the array
        $nodeDataArray[] = [
            'id' => $user->id,
            'parent' => $parentId, // Use the passed parentId instead of sponsor_id for hierarchy
            'name' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone_number,
            'level' => $level,
            'image' => $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png'),
            'blocked' => $user->blocked,
            'can_login' => $user->can_login,
            'created_at' => $user->created_at->format('Y-m-d'),
            'sponsor_id' => $user->sponsor_id
        ];

        // If we haven't reached the maximum level, get children
        if ($level < $maxLevel) {
            // Get direct children (users who have this user as sponsor)
            $children = User::where('sponsor_id', $user->id)
                           ->where('can_login', true)
                           ->orderBy('created_at', 'asc')
                           ->get();

            foreach ($children as $child) {
                $this->buildTeamHierarchy($child, $nodeDataArray, $level + 1, $maxLevel, $user->id);
            }
        }
    }

     public function activationCode(){
        $user = Auth::user();
        $setting = Setting::first();
        $totalBalance =  Wallet::where('wallet_type', 'online')
        ->where('user_id', Auth::id())
        ->sum('balance'); 
        $activationCodes = ActivationCode::with('generatedBy','usedBy')->orderby('id','desc')->get();
        return view('users.activation-code',compact('user','setting','totalBalance','activationCodes'));
    
    }

    public function updateActivationCode(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:activation_codes,id',
            'admin_approval' => 'required|in:approved,rejected,pending'
        ]);  
        $code = ActivationCode::findOrFail($request->id);
        if ($code->admin_approval === 'approved' && $request->admin_approval !== 'approved') {
            return response()->json([
                'message' => 'Status cannot be reverted after approval.'
            ], 403);
        } 
        $code->admin_approval = $request->admin_approval;
        $code->save();  
        $this->logTransaction(Auth::id(),'activation_code', 'admin',  0, 0,0,
            "Admin updated activation code ID {$code->id} & code {$code->code} to status {$code->admin_approval}.",''
        ); 
        return response()->json(['message' => 'Status updated successfully']);
    }

    public function index(Request $request)
    {
        $search  = $request->input('search');
        $tab     = $request->input('tab', 'standard'); // 'standard' or 'saving'

        $baseQuery = function ($accountType) use ($request, $search) {
            $eagerLoads = $accountType === 'saving'
                ? ['team', 'activationCode', 'savingInstalments', 'parent', 'savingSponsor']
                : ['team', 'activationCode'];

            $query = User::with($eagerLoads);

            if ($accountType === 'saving') {
                // Only actual saving plan participants — exclude auto-enrolled sponsors
                // (who have saving_enrolled = true but saving_initial_payment = 0)
                $query->where(function ($q) {
                    $q->where('account_type', 'saving')
                      ->orWhere(function ($q2) {
                          $q2->where('saving_enrolled', true)
                             ->where('saving_initial_payment', '>', 0);
                      });
                });
            } else {
                $query->where('account_type', $accountType)
                      ->where('id', '!=', auth()->user()->id);
            }

            return $query->when($search, function ($q, $search) {
                    $q->where(function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('can_login', 'asc')
                ->orderBy('created_at', 'desc');
        };

        $teamMembers   = $baseQuery('standard_investment')->paginate(20)->withQueryString();
        $savingMembers = $baseQuery('saving')->paginate(20, ['*'], 'saving_page')->withQueryString();

        // Stats for standard users
        $totalMembers        = User::where('account_type', 'standard_investment')->count();
        $totalActiveMembers  = User::where('account_type', 'standard_investment')->where('can_login', true)->count();
        $totalInActiveMembers= User::where('account_type', 'standard_investment')->where('can_login', false)->count();
        $totalBlockedMembers = User::where('account_type', 'standard_investment')->where('blocked', true)->count();
        $totalfreezeMembers  = User::where('account_type', 'standard_investment')->where('freez_wallet', true)->count();

        // Stats for saving program members — exclude auto-enrolled sponsors (saving_initial_payment = 0)
        $savingScope = fn($q) => $q->where('account_type', 'saving')
            ->orWhere(fn($q2) => $q2->where('saving_enrolled', true)->where('saving_initial_payment', '>', 0));
        $totalSavingMembers   = User::where($savingScope)->count();
        $totalActiveSaving    = User::where($savingScope)->where(fn($q) => $q->where('can_login', true)->orWhere('saving_enrollment_activated', true))->count();
        $totalInactiveSaving  = User::where($savingScope)->where('saving_enrollment_activated', false)->where('can_login', false)->count();
        $totalActivatedSaving = User::where($savingScope)->where('saving_registration_completed', true)->count();
        $savingUsers          = User::where($savingScope)->orderBy('name')->get(['id', 'name', 'username']);

        return view('users.index', compact(
            'teamMembers', 'savingMembers', 'search', 'tab',
            'totalMembers', 'totalActiveMembers', 'totalInActiveMembers', 'totalBlockedMembers', 'totalfreezeMembers',
            'totalSavingMembers', 'totalActiveSaving', 'totalInactiveSaving', 'totalActivatedSaving', 'savingUsers'
        ));
    }
    public function deletedUser(Request $request)
    {

        $search = $request->input('search');
        $teamMembers = User::onlyTrashed()->with('team')
            ->where('id', '!=', auth()->user()->id)
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('username', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('can_login', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('users.deleted-users', compact('teamMembers', 'search'));
    }

    public function incentiveWallet(Request $request)
    {
        $users = User::where('can_login', true)->where('blocked', false)->orderBy('username')->get();

        $search = $request->input('search');

        $query = Wallet::where('wallet_type', 'designation_incentive')
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($search) {
            $query->whereHas('user', fn($q) => $q->where('username', 'like', "%{$search}%"));
        }

        $incentives = $query->paginate(20);

        return view('users.incentive-wallet', compact('users', 'incentives', 'search'));
    }

    public function sendIncentive(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'wallet_type' => 'required|in:designation_incentive',
            'amount'      => 'required|numeric|min:5',
            'description' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);
        $walletLabel = ucwords(str_replace('_', ' ', $request->wallet_type));

        Wallet::create([
            'user_id'         => $user->id,
            'wallet_type'     => $request->wallet_type,
            'balance'         => $request->amount,
            'transaction_type'=> 'credit',
            'direct_balance'  => 0,
            'indirect_balance'=> 0,
            'level'           => '-',
            'wallet_from'     => auth()->id(),
            'commission_type' => $walletLabel,
            'description'     => $request->description ?? "Incentive sent by admin",
            'total_earning'   => $request->amount,
            'total_amount'    => $request->amount,
        ]);

        TransactionLog::create([
            'user_id'          => $user->id,
            'from_wallet_type' => 'admin',
            'to_wallet_type'   => $request->wallet_type,
            'charge'           => 0,
            'amount'           => $request->amount,
            'final_amount'     => $request->amount,
            'description'      => "Admin sent \${$request->amount} to {$walletLabel} wallet. " . ($request->description ?? ''),
        ]);

        return redirect()->back()->with('success', "\${$request->amount} sent to {$user->username}'s {$walletLabel} wallet successfully.");
    }

    /**
     * Show the change parent form.
     */
    public function changeParent(Request $request)
    {
        $search = $request->input('search');
        $users  = User::where('can_login', true)
            ->where('blocked', false)
            ->when($search, fn($q) => $q->where('username', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"))
            ->orderBy('username')
            ->get();

        return view('users.change-parent', compact('users', 'search'));
    }

    /**
     * Process the parent change for a user.
     */
    public function processChangeParent(Request $request)
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'new_parent_id' => 'required|exists:users,id',
        ]);

        if ($request->user_id === $request->new_parent_id) {
            return redirect()->back()->with('error', 'A user cannot be their own parent.');
        }

        $movedUser = User::findOrFail($request->user_id);
        $newParent = User::findOrFail($request->new_parent_id);

        // Prevent circular reference: new parent cannot be a descendant of the moved user
        $isDescendant = DB::table('referral_trees')
            ->where('ancestor_id', $movedUser->id)
            ->where('descendant_id', $newParent->id)
            ->exists();

        if ($isDescendant) {
            return redirect()->back()->with('error', "Cannot move {$movedUser->username} under {$newParent->username} because {$newParent->username} is already a descendant of {$movedUser->username}. This would create a circular reference.");
        }

        $oldParentName = optional(User::find($movedUser->sponsor_id))->username ?? 'None';

        DB::beginTransaction();
        try {
            // 1. Collect entire standard-tree subtree: the moved user + all their descendants
            $subtreeIds = $this->getAllSubtreeIds($movedUser->id);

            // 2. Delete old upline connections for the whole subtree (standard tree only).
            //    Keep internal connections within the subtree.
            //    Saving-tree rows are intentionally untouched.
            DB::table('referral_trees')
                ->where('tree_type', 'standard')
                ->whereIn('descendant_id', $subtreeIds)
                ->whereNotIn('ancestor_id', $subtreeIds)
                ->delete();

            // 3. Update sponsor_id for the moved user
            $movedUser->update(['sponsor_id' => $newParent->id]);

            // 4. Rebuild referral_tree rows for each user in the subtree.
            //    referral_trees has no timestamp columns — do not include created_at/updated_at.
            $insertData = [];
            foreach ($subtreeIds as $descId) {
                $ancestors = $this->buildAncestorPath($descId);
                foreach ($ancestors as $level => $ancestorId) {
                    $insertData[] = [
                        'ancestor_id'   => $ancestorId,
                        'descendant_id' => $descId,
                        'level'         => $level,
                        'tree_type'     => 'standard',
                    ];
                }
            }

            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    DB::table('referral_trees')->insertOrIgnore($chunk);
                }
            }

            DB::commit();

            Log::info("Admin changed parent of user {$movedUser->id} ({$movedUser->username}) from {$oldParentName} to {$newParent->username}");

            return redirect()->back()->with('success', "Successfully moved {$movedUser->username} from under {$oldParentName} to under {$newParent->username}. Referral tree has been rebuilt.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to change parent for user {$movedUser->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to change parent: ' . $e->getMessage());
        }
    }

    /**
     * Get all IDs in a user's subtree (the user + all descendants recursively).
     */
    private function getAllSubtreeIds(int $userId): array
    {
        $descendants = DB::table('referral_trees')
            ->where('ancestor_id', $userId)
            ->where('tree_type', 'standard')
            ->pluck('descendant_id')
            ->toArray();

        return array_unique(array_merge([$userId], $descendants));
    }

    /**
     * Build the ancestor path for a user by walking up sponsor_id chain.
     * Returns [level => ancestor_id, ...]
     */
    private function buildAncestorPath(int $userId, int $maxDepth = 20): array
    {
        $ancestors  = [];
        $currentId  = $userId;
        $level      = 1;

        while ($level <= $maxDepth) {
            $row = DB::table('users')->select('sponsor_id')->where('id', $currentId)->first();
            if (!$row || !$row->sponsor_id) break;

            $ancestors[$level] = $row->sponsor_id;
            $currentId = $row->sponsor_id;
            $level++;
        }

        return $ancestors;
    }

}
