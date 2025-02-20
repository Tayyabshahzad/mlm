<?php

namespace App\Http\Controllers;

use App\Mail\CompanyAgreement;
use App\Mail\InvoiceEmail;
use App\Mail\WelcomeEmail;
use App\Models\Profile;
use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransactions;
use App\Models\Week;
use Illuminate\Http\Request;
use App\Services\PVService;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    protected $pvService;
    protected $walletService;

    public function __construct(PVService $pvService, WalletService $walletService)
    {
        $this->pvService = $pvService;
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $search = $request->input('search'); 
        $teamMembers = User::with('team')
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
    public function updateStatus(Request $request)
    {

        $request->validate([
            'member_id' => [
                'required',
                'integer',  
                'exists:users,id', // Check if member_id exists in the 'id' column of 'users' table
            ],
        ]);
       $user = User::find($request->member_id);
        if ($user->can_login) {
            return redirect()->back()->with('error', 'This User is Already Activated');
        } 
        if($user->current_pv_balance <= 100){
            $this->pvService->assignInitialPV($user);   
        }
        // $user->can_login = true;
      
        // Sending mail to user
        // Mail::to($user->email)->send(new CompanyAgreement($user));
        // Mail::to($user->email)->send(new WelcomeEmail($user));
        // Mail::to($user->email)->send(new InvoiceEmail($user)); 

          //$users = User::where('blocked', false)->get(); // Fetch all unblocked users 
          //foreach ($users as $user) {
            $this->assignCommissionsUpdated($user);   
            $user->can_login = true;
            $user->save();  
        // }
       


        // $this->assignCommissionsUpdated($user);  
        
        $this->test($user->sponsor_id, 1);
      
        return redirect()->back()->with('success', 'Member Status has been Updated');
    } 
    private function getAncestors($user)
    {
        return \DB::table('referral_trees')
            ->select('ancestor_id', 'level') // Ensure level is retrieved
            ->where('descendant_id', $user->id)
            ->where('level', '<=', 7) // Include only ancestors up to Level 7
            ->get();
    }
    private function getCommissionForLevel($level)
    { 
        $commissionPercentages = [
            1 => 5,  // Level 1 gets 5%
            2 => 2,  // Level 2 gets 2%
            3 => 1.5, // Level 3 gets 1.5%
            4 => 1.25,   // Level 4 gets 1%
            5 => 1, // Level 5 gets 0.8%
            6 => 0.75, // Level 6 gets 0.5%
            7 => 0.50, // Level 7 gets 0.1%
        ]; 
        return $commissionPercentages[$level] ?? 0;
    } 
    public function userDetails(Request $request)
    {
        $userId = $request->get('id');
        $user = User::find($userId);
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
                ],
            ]);
        }
        return response()->json(['success' => false, 'message' => 'User not found.']);
    }
    public function old_getChildCountAtLevel($userId, $level)
    {
        if ($level == 1) { 
            return User::where('blocked',false)->where('sponsor_id', $userId)->where('can_login', true)->count();
        } 
        $directChildren = User::where('blocked',false)->where('sponsor_id', $userId)->where('can_login', true)->pluck('id'); 
        $countAtSpecificLevel = 0;
        
        foreach ($directChildren as $childId) {
            $countAtSpecificLevel += User::where('blocked',false)->where('sponsor_id', $childId)
                                         ->where('can_login', true)
                                         ->count();
        }

        return $countAtSpecificLevel;
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

    public function test($parentID,  $level)
    {
        if ($level > 7) {
            return;
        }
        $parent = User::where('blocked',false)->find($parentID);
        if (!$parent) {
            return;
        }

        // $requiredTeamSizes = [
        //     2 => 2, // Level 2 requires 2 team members
        //     3 => 3, // Level 3 requires 3 team members
        //     4 => 4, // Level 4 requires 4 team members
        //     5 => 5, // Level 5 requires 5 team members
        //     6 => 6, // Level 6 requires 6 team members
        //     7 => 7, // Level 7 requires 7 team members
        // ];


        // ['level' => 1, 'reward_amount' => 150, 'users_required' => 10],
        // ['level' => 2, 'reward_amount' => 300, 'users_required' => 50],
        // ['level' => 3, 'reward_amount' => 1200, 'users_required' => 150],
        // ['level' => 4, 'reward_amount' => 4000, 'users_required' => 400],
        // ['level' => 5, 'reward_amount' => 10000, 'users_required' => 1000],
        // ['level' => 6, 'reward_amount' => 30000, 'users_required' => 2000],
        // ['level' => 7, 'reward_amount' => 48000, 'users_required' => 4000],




        $directChildCount = $this->getChildCountAtLevel($parentID, $level);
        $rewardLevels = collect([
            ['level' => 1, 'reward_amount' => 150, 'users_required' => 10],
            ['level' => 2, 'reward_amount' => 300, 'users_required' => 50],
            ['level' => 3, 'reward_amount' => 1200, 'users_required' => 150],
            ['level' => 4, 'reward_amount' => 4000, 'users_required' => 400],
            ['level' => 5, 'reward_amount' => 10000, 'users_required' => 1000],
            ['level' => 6, 'reward_amount' => 30000, 'users_required' => 2000],
            ['level' => 7, 'reward_amount' => 48000, 'users_required' => 4000],
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
                \Log::info("Skipping reward for level {$level} because reward for level {$i} is not achieved.");
                return;
            }
        }
        
        \Log::info("Level: " . $level . " directChildCount: " . $directChildCount . " specificRewardLevel: " . $specificRewardLevel['users_required']);
        // $usersRequired = $rewardLevels; 
        if ($directChildCount >= $specificRewardLevel['users_required']) {
            \Log::info('condition meet');
            $this->assignReward($parentID, $specificRewardLevel['reward_amount'], $specificRewardLevel['level']);
        }
        $parentExists  = User::where('blocked',false)->find($parent->sponsor_id);
        if ($parentExists) {
            \Log::info('parentExists: ' . $parentExists->id);
            $this->test($parentExists->id, $level+1);
        }
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
            ['level' => 1, 'reward_amount' => 150, 'users_required' => 10],
            ['level' => 2, 'reward_amount' => 300, 'users_required' => 50],
            ['level' => 3, 'reward_amount' => 1200, 'users_required' => 150],
            ['level' => 4, 'reward_amount' => 4000, 'users_required' => 400],
            ['level' => 5, 'reward_amount' => 10000, 'users_required' => 1000],
            ['level' => 6, 'reward_amount' => 30000, 'users_required' => 2000],
            ['level' => 7, 'reward_amount' => 48000, 'users_required' => 4000],
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
                \Log::info("Skipping reward for level {$level} because reward for level {$i} is not achieved.");
                return;
            }
        }

        \Log::info("Level: " . $level . " directChildCount: " . $directChildCount . " specificRewardLevel: " . $specificRewardLevel['users_required']);
        // $usersRequired = $rewardLevels; 
        if ($directChildCount >= $specificRewardLevel['users_required']) {
          \Log::info('Condition met for level ' . $level);
            $this->assignReward($parentID, $specificRewardLevel['reward_amount'], $specificRewardLevel['level']);
        }
        $parentExists  = User::where('blocked',false)->find($parent->sponsor_id);
        if ($parentExists) {
            \Log::info('parentExists: ' . $parentExists->id);
            $this->assignRewardToUser($parentExists->id, $level+1);
        }
    }
    public function checkAndAssignRewards($userId, $user)
    {  
        $directChildren = User::where('blocked',false)->where('sponsor_id', $userId)
            ->where('can_login', 1) // Only active users
            ->get();
        \Log::info("LOG 1 - { $user->name }  update ho raha ha  jis ka parent {$user->parent->name} ha , ham ny idr {$user->parent->name} k sary user ko get kr liya ha ");
        $rewardLevels = collect([
            ['level' => 1, 'reward_amount' => 150, 'users_required' => 10],
            ['level' => 2, 'reward_amount' => 300, 'users_required' => 50],
            ['level' => 3, 'reward_amount' => 1200, 'users_required' => 150],
            ['level' => 4, 'reward_amount' => 4000, 'users_required' => 400],
            ['level' => 5, 'reward_amount' => 10000, 'users_required' => 1000],
            ['level' => 6, 'reward_amount' => 30000, 'users_required' => 2000],
            ['level' => 7, 'reward_amount' => 48000, 'users_required' => 4000],
        ]);
        \Log::info("Loop Start  ------------------  ");
        $sn = 0;
        foreach ($rewardLevels as $level) {
            $sn++;
            \Log::info("Iteration  -----------  " . $sn);
            // Check if reward for this level has already been assigned
            //Wallet ke table min dakh rha ha k shahzad ke id k sat same level min entry to nhin ha agr ha to skip kro do process
            $existingReward = Wallet::where([
                ['user_id', '=', $userId],
                ['wallet_type', '=', 'reward'],
                ['commission_type', '=', 'reward'],
                ['level', '=', $level['level']],
            ])->exists();
            \Log::info("LOG 2 -  Idr Chek kia ha {$user->parent->name} ko rward level {$level['level']} pr paly sy assign to  nhin ha ");
            if ($existingReward) {
                // Skip if reward for this level is already assigned
                continue;
            }
            //Loop k level 1 pr check k agr loop min level one ha to tm shahzad k wo sary bandy ly k eg. aqil , faisal , qasim aow phir un bandon ko count kro agr count k bad un ka number 7 k braber ha to assing revard ka functon call kro or shahazad ko level 1 dy do 
            \Log::info("LOG 3 - {$user->parent->name} ko Rward level {$level['level']} pr koi be assing nhin huwa");

            if ($level['level'] === 1) {
                \Log::info(" LOG 4 -Loop min jb level " . $level['level'] . "ho jy ga to ham check kryn gy k  {$user->parent->name} k bachon ga count kia ha gr wo count  7 ko match kr jata ha to level 1 rewad mil gya ga  ");
                // For Level 1, use direct referrals count
                $directReferralsCount = $this->calculateDirectReferrals($userId);
                if ($directReferralsCount >= $level['users_required']) {
                    \Log::info($level['users_required'] . " user ke condation meet kr gy level 1 open ho gya ");
                    $this->assignReward($userId, $level['reward_amount'], $level['level']);
                    \Log::info("Level 1 reward assigned to parent user id --  {$userId}");
                    break;
                }
            } else {
                \Log::info(" LOG 4 -Loop min jb level " . $level['level'] . "ho jy ga ha or is bar ham indirect user ko check kr ahy hin  ");
                // For other levels, calculate combined team size of direct children
                $totalTeamSize = 0;
                //directChildren children min wo parson hin jo shahzad k user hin remember hm tayyab ko create kr rahy hin
                \Log::info(" LOG 5 -  {$user->parent->name}  pr loop laga ha or  {$user->parent->name} k hr user ko loop min add kia ha idr hamry pas  {$user->parent->name} k total child count " . $directChildren->count() . " hain");
                foreach ($directChildren as $child) {
                    // Ab idr sb user ke list a gy ha jo shahzad k users k mtlb qasim asin etc or asim yeh qasim ke id ko pick kr k us ke team member ko check kia ja raha ha for exp is function ko asim ke id yeh aqil ke id pass ho gy ha
                    \Log::info(" LOG 6 -  Ab idr sb ak ak child jis ka parent {$user->parent->name}  ha, us ke team k count check kia ja raha hs is check ke waja yeh ha k ham ny  {$user->parent->name}  ko next level pr move krna ha  currentally is child {$child->name} k tem ko check kia ja raha ha ");
                    $childTeamSize = $this->calculateTeamSize($child->id, $child);
                    $totalTeamSize += $childTeamSize;
                    \Log::info("Sub Child {$child->name} has team size of: {$childTeamSize}");
                }

                \Log::info("Total team size for Level {$level['level']} check: {$totalTeamSize}");

                if ($totalTeamSize >= $level['users_required']) {
                    $this->assignReward($userId, $level['reward_amount'], $level['level']);
                    \Log::info("Level {$level['level']} reward assigned to user {$userId} with total team size {$totalTeamSize}");
                    break;
                } else {
                    \Log::info("Next Level Open nin huwa");
                }
            }

            \Log::info("End ------------------  ");
        }
        \Log::info("-------------Loop End  ------------------  ");
    } 
    public function calculateDirectReferrals($userId)
    {
        return \DB::table('users')->where('sponsor_id', $userId)->where('can_login', 1)->count();
    }
    public function calculateTeamSize($userId, $childUser)
    {

        $directReferrals = User::where('blocked',false)->where('sponsor_id', $userId)
            ->where('can_login', 1)
            ->get();
        // asim user ko count kro
        $directReferralsCount = $directReferrals->count();

        // Log direct referrals
        \Log::info("Idr ham ny {$childUser->name} k direct team ka cont kia ha =  {$directReferralsCount} .");

        // Downline team size calculation (recursively)
        \Log::info("recursively query  .");
        $downlineTeamSize = $directReferrals->sum(function ($child) {
            return $this->calculateTeamSize($child->id);
        });

        // Total team size
        $totalTeamSize = $directReferralsCount + $downlineTeamSize;

        // Log team size calculation
        \Log::info("Team size for user {$childUser->name}: Direct Referrals = {$directReferralsCount}, Downline = {$downlineTeamSize}, Total = {$totalTeamSize}");

        return $totalTeamSize;
    } 

    private function assignReward($userId, $amount, $level)
    {
        // Check if the reward for this level has already been assigned
        $existingReward = Wallet::where([
            ['user_id', '=', $userId],
            ['wallet_type', '=', 'reward'],
            ['commission_type', '=', 'reward'],
            ['level', '=', $level],
        ])->first();
        \Log::info("Checking if wallet exists for user {$userId} at level {$level}: " . ($existingReward ? 'Exists' : 'Does not exist'));
        if ($existingReward && $existingReward->balance > 0) {
            // Reward for this level already assigned, skip
            \Log::info("Reward already assigned", [
                'user_id' => $userId,
                'level' => $level,
            ]);
            return;
        }

        // Fetch or create reward wallet
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $userId, 'wallet_type' => 'reward', 'commission_type' => 'reward', 'level' => $level],
            ['balance' => 0.00]
        );
        \Log::info("Wallet created for user {$userId} at level {$level}");
        // Add reward to wallet
        $wallet->balance += $amount;
        $wallet->total_amount += $amount;
        $wallet->save();

        // Log reward assignment
        \Log::info("Reward assigned", [
            'user_id' => $userId,
            'amount' => $amount,
            'level' => $level,
        ]);
    }

    private function getActiveDirectUsersCount($userId)
    {
        return User::where('blocked', false)
            ->where('sponsor_id', $userId)
            // ->where('can_login', true) // Ensure user is active
            ->count();
    }




    private function assignCommissions($user)
    {
        // Fetch the immediate sponsor
        $parentUser = User::where('blocked',false)->find($user->sponsor_id);
       
        if ($parentUser) {
            $directCommissionPercentage = $this->getCommissionForLevel(1); // Level 1 for direct commission 
            $directCommissionAmount = ($directCommissionPercentage / 100) * $user->current_pv_balance; 
            // Assign only direct commission for immediate sponsor
            $this->walletService->assignCommission($parentUser->id, $directCommissionAmount, 'direct', $user, 1);
        }
    
        // Exclude the immediate sponsor from ancestors list
        $ancestors = $this->getAncestors($user)
            ->filter(function ($ancestor) use ($user) {
                return $ancestor->ancestor_id !== $user->sponsor_id && $ancestor->level <= 7;
            });
    
        foreach ($ancestors as $ancestor) {
            $level = $ancestor->level; // Get level of ancestor 
            // Check team size condition for each level
            $ancestorUser = User::where('blocked',false)->find($ancestor->ancestor_id);
            $teamSize = $this->getTeamSize($ancestorUser->id); // Fetch team size
            $requiredTeamSizes = [
                2 => 2, // Level 2 requires 2 team members
                3 => 3, // Level 3 requires 3 team members
                4 => 4, // Level 4 requires 4 team members
                5 => 5, // Level 5 requires 5 team members
                6 => 6, // Level 6 requires 6 team members
                7 => 7, // Level 7 requires 7 team members
            ];
    
            // Check team size condition for the current level (default to 0 if not defined)
            $requiredTeamSize = $requiredTeamSizes[$level] ?? 0;
            if ($teamSize >= $requiredTeamSize) {
                $indirectCommissionPercentage = $this->getCommissionForLevel($level); // Get commission for the ancestor's level
                $indirectCommissionAmount = ($indirectCommissionPercentage / 100) * $user->current_pv_balance;
    
                // Assign Indirect Commission for Ancestors
                $this->walletService->assignCommission($ancestor->ancestor_id, $indirectCommissionAmount, 'indirect', $user, $level);
            }
        }
    }
    
    /**
     * Get the team size of a user based on direct sponsorship.
     *
     * @param int $userId
     * @return int
     */
    private function getTeamSize($userId)
    {
        // Count the number of users directly sponsored by this user
        return User::where('blocked',false)->where('sponsor_id', $userId)->count();
    } 
    public function roiPayments()
    {
        $users = User::where('blocked',false)->where('can_login', true)->get();
        $payments = ROITransaction::orderby('id','desc')->get();
        return view('users.roi-payments', compact('users', 'payments'));
    }

    public function submitRoiPayments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id', // User selection
            'commission_percentage' => 'required|numeric|min:0|max:100', // Commission percentage
            'description' => 'required'
        ]);
        $user = User::
        where('can_login', true)->
        where('blocked',false)->find($request->user_id); 
        $walletTotal = Wallet::where('user_id',$user->id)->sum('total_amount');
        if($walletTotal >= 200){
            return redirect()->back()->with('error', '2x is completed for this user'); 
        }
        if ($user->roi_wallet_balance >= 200) {
            return redirect()->back()->with('error', 'ROI already completed for this user'); 
        }
        if (!$user->roi_start_date) {
            $user->roi_start_date = Carbon::now();
            $user->roi_end_date = Carbon::now()->addYears(2); // 2 years from start
            $user->save();
        }
        // $rois = Wallet::where('wallet_type','roi')->orderBy('id','asc')->get(); 
        // foreach($rois as $roi){
        //     $user = User::  where('can_login', true)-> where('blocked',false)->find($roi->user_id);  
        //     $this->generateParentCommissions($user, $roi->balance);
        // } 
        $monthsRemaining = Carbon::now()->diffInMonths($user->roi_end_date, false);
        $remainingPV = 200 - $user->roi_wallet_balance;
        $paymentPercentage = $request->commission_percentage;
        $maxMonthlyPayment = $remainingPV / $monthsRemaining;
        $roiPayment = ($remainingPV * $paymentPercentage) / 100;
        $user->roi_wallet_balance += $roiPayment;
        $user->last_roi_payment_date = Carbon::now();
        $user->save();
        $wallet = Wallet::Create(
            [
                'user_id' => $user->id,
                'wallet_type' => 'roi',
                'balance' => $roiPayment,
                'total_amount' => $roiPayment,
                'level' => '-',
                'commission_type' => 'Roi',
                'percentage' => $paymentPercentage,
            ]
        );

        ROITransaction::create([
            'user_id' => $user->id,
            'amount' => $roiPayment,
            'percentage' => $request->commission_percentage,
            'description' => 'ROI '. $request->description,
        ]);
        $this->generateParentCommissions($user, $roiPayment);
        return redirect()->back()->with('success', 'ROI Generated Successfully');
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

    private function old_generateParentCommissions($user, $roiAmount)
    {
        $commissionLevels = [
            1 => 3.5,
            2 => 3,
            3 => 2.5,
            4 => 2,
            5 => 1.5,
            6 => 1,
            7 => 0.5,
        ];
        
        foreach ($commissionLevels as $level => $percentage) { 
             
            $parent = $this->getAncestorByLevel($user, $level);  
           
            if ($parent) {
                $directChildrenCount = User::where('blocked',false)->where('sponsor_id', $parent->id)->where('can_login', true)->count();
              
                $requiredUsers = $this->getRequiredUsersForLevel($level);  
                if ($directChildrenCount >= $requiredUsers) { 
                    $commissionAmount = ($roiAmount * $percentage) / 100;
                    ROITransaction::create([
                        'user_id' => $parent->id,
                        'amount' => $commissionAmount,
                        'percentage' => $percentage,
                        'description' => "Profit Share for Level {$level} commission from user {$user->id} | {$user->username}",
                    ]);
                    Wallet::create([
                        'user_id' => $parent->id,
                        'wallet_type' => 'profit_share',
                        'balance' => $commissionAmount,
                        'level' => $level,
                        'commission_type' => 'profit_share',
                        'wallet_from' => $user->id,
                        'percentage' => $percentage,
                        'total_amount'=> $commissionAmount,
                    ]);
                }
                  
            }
        }
    }




    private function generateParentCommissions($user, $roiAmount)
    {
        $commissionLevels = [
            1 => 3.5,
            2 => 3,
            3 => 2.5,
            4 => 2,
            5 => 1.5,
            6 => 1,
            7 => 0.5,
        ];

        foreach ($commissionLevels as $level => $percentage) {
            $parent = $this->getAncestorByLevel($user, $level);

            if ($parent) {
                // Count total users (direct + indirect) up to this level
                $totalDownlineCount = $this->countDownlineUsers($parent->id, $level);
                $requiredUsers = $this->getRequiredUsersForLevel($level);

                if ($totalDownlineCount >= $requiredUsers) {
                    $commissionAmount = ($roiAmount * $percentage) / 100;

                    // Save commission transaction
                    ROITransaction::create([
                        'user_id' => $parent->id,
                        'amount' => $commissionAmount,
                        'percentage' => $percentage,
                        'description' => "Level {$level} commission from user {$user->id} | {$user->name}",
                    ]);

                    // Save to wallet
                    Wallet::create([
                        'user_id' => $parent->id,
                        'wallet_type' => 'profit_share',
                        'balance' => $commissionAmount,
                        'level' => $level,
                        'commission_type' => 'profit_share',
                        'wallet_from' => $user->id,
                        'percentage' => $percentage,
                        'total_amount' => $commissionAmount,
                    ]);

                    $this->info("Commission of $commissionAmount assigned to User {$parent->id} for Level {$level}");
                }
            }
        }
    }
    private function countDownlineUsers($parentId, $level)
    {
        return \DB::table('referral_trees')
            ->where('ancestor_id', $parentId)
            ->where('level', '<=', $level)  // Include all users up to this level
            ->count();
    }


    private function getRequiredUsersForLevel($level)
    {


        $requiredUsers = [
            1 => 10,  // Level 1 needs 2 users
            2 => 50,  // Level 2 needs 3 users
            3 => 150,  // Level 3 needs 4 users
            4 => 400,  // Level 4 needs 5 users
            5 => 1000,  // Level 5 needs 6 users
            6 => 2000,  // Level 6 needs 7 users
            7 => 4000,  // Level 7 needs 8 users
        ];

        return $requiredUsers[$level] ?? 0;  // Default to 0 if level is not defined
    }

    private function getAncestorByLevelOld($user, $level)
    {
        
        return \DB::table('referral_trees')
            ->join('users', 'referral_trees.ancestor_id', '=', 'users.id')
            ->where('referral_trees.descendant_id', $user->id)
            ->where('referral_trees.level', $level)
            ->select('users.*')
            ->first();
    }

    private function getAncestorByLevel($user, $level)
    {
        return User::whereIn('id', function ($query) use ($user, $level) {
            $query->select('ancestor_id')
                ->from('referral_trees')
                ->where('descendant_id', $user->id)
                ->where('level', $level);
        })->first(); // Get only one parent per level
    }

    public function userInfo(Request $request , $id){
        
        $user = User::with('profile')->find($id);
        return view('users.information',compact('user'));
    }

    public function userInfoUpdate(Request $request){
       
        $user = User::find($request->id);
       
        $request->validate([
            'password' => 'nullable|confirmed|min:8', // Password is optional but must match confirmation
            'phone' => 'nullable',
            'freez_wallet' => 'required|boolean', 
            'blocked'=>'boolean',
            'reason' => 'required_if:blocked,1|max:255',
            'user_id' =>'required'
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

    private function assignCommissionsUpdated($user)
    { 
        $parentUser = User::where('blocked', false)->find($user->sponsor_id); 
        if ($parentUser) {
            // Check if the parent user has enough active direct users
            $activeDirectUsers = $this->getActiveDirectUsersCount($parentUser->id);  
            if ($activeDirectUsers >= 1) { 
                $directCommissionPercentage = $this->getCommissionForLevel(1); // Level 1 for direct commission
                $directCommissionAmount = ($directCommissionPercentage / 100) * $user->current_pv_balance; 
                $this->walletService->assignCommission($parentUser->id, $directCommissionAmount, 'direct', $user, 1); 
            }

        }  
        // Exclude the immediate sponsor from ancestors list
        $ancestors = $this->getAncestors($user)
            ->filter(function ($ancestor) use ($user) {
                return $ancestor->ancestor_id !== $user->sponsor_id && $ancestor->level <= 7;
            });

        foreach ($ancestors as $ancestor) {
            $level = $ancestor->level; // Get level of ancestor

            // Check the ancestor's team size condition
            $ancestorUser = User::where('blocked', false)->find($ancestor->ancestor_id);
            $activeDirectUsers = $this->getActiveDirectUsersCount($ancestorUser->id);

            $teamSizeRequirement = $this->getTeamSizeRequirementForLevel($level); // Fetch team size requirement for the level

            if ($activeDirectUsers >= $teamSizeRequirement) {
                $indirectCommissionPercentage = $this->getCommissionForLevel($level); // Get commission for the ancestor's level
                $indirectCommissionAmount = ($indirectCommissionPercentage / 100) * $user->current_pv_balance;

                // Assign Indirect Commission for Ancestors
                $this->walletService->assignCommission($ancestor->ancestor_id, $indirectCommissionAmount, 'indirect', $user, $level);
            }
        }
    }

    private function getTeamSizeRequirementForLevel($level)
    {
        $requiredTeamSizes = [
            1 => 1, // Level 1 requires 2 active team members
            2 => 2, // Level 2 requires 2 active team members
            3 => 3, // Level 3 requires 3 active team members
            4 => 4, // Level 4 requires 4 active team members
            5 => 5, // Level 5 requires 5 active team members
            6 => 6, // Level 6 requires 6 active team members
            7 => 7, // Level 7 requires 7 active team members
        ];

        return $requiredTeamSizes[$level] ?? 0;
    }

    private function commissionExists($ancestorId, $fromUserId, $type, $level)
    {
        return Wallet::where('user_id', $ancestorId)
            ->where('wallet_from', $fromUserId)
            ->where('commission_type', $type)
            ->where('level', $level)
            ->exists();
    }
    private function manulAssignCommissionsUpdated($user)
    {
        

        $parentUser = User::where('blocked', false)->find($user->sponsor_id);
    
        if ($parentUser) {
            // Check if the parent user has enough active direct users
            $activeDirectUsers = $this->getActiveDirectUsersCount($parentUser->id);

            // Level 1 requires at least 1 active direct user for indirect commission
            if ($activeDirectUsers >= 1) {
                $directCommissionPercentage = $this->getCommissionForLevel(1); // Level 1 for direct commission
                $directCommissionAmount = ($directCommissionPercentage / 100) * $user->current_pv_balance;

                // Check if direct commission already exists
                if (!$this->commissionExists($parentUser->id, $user->id, 'direct', 1)) {
                    // Assign direct commission for the immediate sponsor
                    $this->walletService->assignCommission($parentUser->id, $directCommissionAmount, 'direct', $user, 1);
                }
            }
        }

        // Exclude the immediate sponsor from ancestors list
        $ancestors = $this->getAncestors($user)
            ->filter(function ($ancestor) use ($user) {
                return $ancestor->ancestor_id !== $user->sponsor_id && $ancestor->level <= 7;
            });

        foreach ($ancestors as $ancestor) {
            $level = $ancestor->level; // Get level of ancestor

            // Check the ancestor's team size condition
            $ancestorUser = User::where('blocked', false)->find($ancestor->ancestor_id);
            $activeDirectUsers = $this->getActiveDirectUsersCount($ancestorUser->id);

            $teamSizeRequirement = $this->getTeamSizeRequirementForLevel($level); // Fetch team size requirement for the level

            if ($activeDirectUsers >= $teamSizeRequirement) {
                $indirectCommissionPercentage = $this->getCommissionForLevel($level); // Get commission for the ancestor's level
                $indirectCommissionAmount = ($indirectCommissionPercentage / 100) * $user->current_pv_balance;

                // Check if indirect commission already exists
                if (!$this->commissionExists($ancestor->ancestor_id, $user->id, 'indirect', $level)) {
                    // Assign Indirect Commission for Ancestors
                    $this->walletService->assignCommission($ancestor->ancestor_id, $indirectCommissionAmount, 'indirect', $user, $level);
                }
            }
        }
    }

    public function recalculateCommissions()
    {
        $users = User::where('blocked', false)->get(); // Fetch all unblocked users 
        foreach ($users as $user) {
            $activeDirects = $this->manulAssignCommissionsUpdated($user); // Get active direct users 
        }
    }

    private function assignCommissionsUpdated1($user)
    {
        $parentUser = User::where('blocked', false)->find($user->sponsor_id); 
        if ($parentUser) {
            // Check the immediate sponsor's direct team size for Level 1
            $directTeamSize = $this->getActiveDirectUsersCount($parentUser->id);
            $requiredTeamSizeForLevel1 = $this->getTeamSizeRequirementForLevel(1);

            if ($directTeamSize >= $requiredTeamSizeForLevel1) {
                $directCommissionPercentage = $this->getCommissionForLevel(1); // Level 1 commission
                $directCommissionAmount = ($directCommissionPercentage / 100) * $user->current_pv_balance;

                // Assign direct commission for the immediate sponsor
                $this->walletService->assignCommission($parentUser->id, $directCommissionAmount, 'direct', $user, 1);
            }
        }

        // Fetch ancestors, excluding the immediate sponsor
        $ancestors = $this->getAncestors($user)
            ->filter(function ($ancestor) use ($user) {
                return $ancestor->ancestor_id !== $user->sponsor_id && $ancestor->level <= 7;
            });

        foreach ($ancestors as $ancestor) {
            $level = $ancestor->level; // Get ancestor level

            // Get the ancestor's direct sponsor (one level down from this ancestor)
            $directSponsor = User::where('id', $ancestor->ancestor_id)->first();

            if (!$directSponsor) {
                continue; // Skip if the ancestor doesn't exist
            }

            // Get the direct team size of the direct sponsor
            $directTeamSize = $this->getActiveDirectUsersCount($directSponsor->id);

            // Get required team size for this level
            $requiredTeamSize = $this->getTeamSizeRequirementForLevel($level);

            // Assign commission only if the direct team size meets the required criteria
            if ($directTeamSize >= $requiredTeamSize) {
                $indirectCommissionPercentage = $this->getCommissionForLevel($level); // Get level commission
                $indirectCommissionAmount = ($indirectCommissionPercentage / 100) * $user->current_pv_balance;

                // Assign indirect commission for this ancestor
                $this->walletService->assignCommission($ancestor->ancestor_id, $indirectCommissionAmount, 'indirect', $user, $level);
            }
        }
    }


    private function assignCommissionsUpdated3($user)
    {
        $parentUser = User::where('blocked', false)->find($user->sponsor_id);    
        if ($parentUser) {
            $activeDirectUsers = $this->getActiveDirectUsersCount($parentUser->id);    
            if ($activeDirectUsers >= 1) {  
                $directCommissionPercentage = $this->getCommissionForLevel(1); // Level 1 commission 
                $directCommissionAmount = ($directCommissionPercentage / 100) * $user->current_pv_balance;
                $this->walletService->assignCommission($parentUser->id, $directCommissionAmount, 'direct', $user, 1);
            }
            
           
        }

        // Step 2: Indirect Commission for Ancestors
        $ancestors = $this->getAncestors($user)
            ->filter(function ($ancestor) use ($user) {
                return $ancestor->ancestor_id !== $user->sponsor_id && $ancestor->level <= 7;
            });

        foreach ($ancestors as $ancestor) {
            $level = $ancestor->level;
            $ancestorUser = User::where('blocked', false)->find($ancestor->ancestor_id);
            $activeDirectUsers = $this->getActiveDirectUsersCount($ancestorUser->id);

            $teamSizeRequirement = $this->getTeamSizeRequirementForLevel($level);

            // Only assign commissions for new descendants if ancestor meets team size requirement
            if ($activeDirectUsers >= $teamSizeRequirement) {
                $isNewUser = $this->isNewDescendant($ancestorUser->id, $user->id); // Check if user is a new descendant
                if ($isNewUser) {
                    $indirectCommissionPercentage = $this->getCommissionForLevel($level);
                    $indirectCommissionAmount = ($indirectCommissionPercentage / 100) * $user->current_pv_balance;

                    // Assign Indirect Commission
                    $this->walletService->assignCommission($ancestor->ancestor_id, $indirectCommissionAmount, 'indirect', $user, $level);
                }
            }
        }
    }

private function isNewDescendant($ancestorId, $descendantId)
{
    $qualificationDate = $this->getAncestorQualificationDate($ancestorId);
    $descendant = \DB::table('users')
    ->where('id', $descendantId)
    ->first();

    return $descendant && $descendant->created_at > $qualificationDate;
}

private function getAncestorQualificationDate($ancestorId)
{
    // Fetch the date when ancestor qualified for their current level
    $ancestor = User::find($ancestorId);
    return $ancestor ? $ancestor->qualification_date : now(); // Example column `qualification_date`
}







}
