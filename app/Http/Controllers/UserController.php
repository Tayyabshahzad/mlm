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
        $request->validate(['member_id' => ['required',  'integer', 'exists:users,id']]);
        $user = User::find($request->member_id);
        if ($user->can_login) {
            return redirect()->back()->with('error', 'This User is Already Activated');
        }

        //  $info = $this->rewardService->repairMissingRewards(8);
        //  dd($info);

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
            Log::info("User {$user->id} activated successfully");
            return redirect()->back()->with('success', 'Member Status has been Updated');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to activate user {$user->id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update member status');
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
            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'status' => $user->can_login ? 'Active' : 'Inactive',
                    'amount_proof' => $user->getFirstMediaUrl('user_amount_source'),
                    'transaction_id' => $user->transaction_id,
                    'payment_method'=>$user->payment_method,
                    'transferred_amount'=>$user->transferred_amount,
                    'converted_usdt_amount'=>$user->converted_usdt_amount,
                    'fee_deducted'=>$user->fee_deducted,
                    'net_invested_usdt_amount'=>$user->net_invested_usdt_amount,
                    'usdt_rate'=>$user->usdt_rate,
                    'referBy'=> [
                        'username'=>ucfirst($user->parent->username),
                        'id'=>$user->parent->id,
                    ],
                    'activationCode' =>[
                        'code' => $user->activationCode->code ?? 'NA' ,
                        'generated_by' => $user->activationCode->generatedBy->name ?? 'NA'
                    ],
                    'created_at'=>$user->created_at,
                ],
            ]);
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
        
        $request->validate([
            'delete_id' => 'required',  
        ]);
        $user = User::find($request->delete_id);
        if (!$user) {
            return redirect()->back()->with('error', 'User not found.');
        }  
        $user->delete();   
        return redirect()->back()->with('success', 'User deleted successfully');
    }

    
    public function userInfo(Request $request , $id){
        
        $user = User::with('profile')->find($id);
        return view('users.information',compact('user'));
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
        $search = $request->input('search');
        $teamMembers = User::with('team','activationCode')
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
        $totalMembers = User::count();
        $totalActiveMembers = User::where('can_login',true)->count();
        $totalInActiveMembers = User::where('can_login',false)->count();
        $totalBlockedMembers = User::where('blocked',true)->count();
        $totalfreezeMembers = User::where('freez_wallet',true)->count();

        return view('users.index', compact('teamMembers', 'search' ,'totalMembers','totalActiveMembers','totalInActiveMembers','totalBlockedMembers','totalfreezeMembers'));
    }
    public function deletedUser(Request $request)
    {

        $search = $request->input('search');
        $teamMembers = User::where('deleted_at','!=',null)->with('team')
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

 
}
