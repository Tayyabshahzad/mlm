<?php

namespace App\Http\Controllers;

use App\Mail\CompanyAgreement;
use App\Mail\InvoiceEmail;
use App\Mail\WelcomeEmail;
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

    public function index()
    {

        $teamMembers = User::with('team')->where('id', '!=', auth()->user()->id)->orderBy('can_login', 'asc')->paginate(20);;
        return view('users.index', compact('teamMembers'));
    }

    public function updateStatus(Request $request)
    {
        $user = User::find($request->member_id);
        $user->can_login = true;
        $user->save();


        // Sending mail to user
        // Mail::to($user->email)->send(new CompanyAgreement($user));
        // Mail::to($user->email)->send(new WelcomeEmail($user));
        // Mail::to($user->email)->send(new InvoiceEmail($user));
        $this->pvService->assignInitialPV($user);
        $this->assignCommissions($user);
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
                ],
            ]);
        }
        return response()->json(['success' => false, 'message' => 'User not found.']);
    }
    public function old_getChildCountAtLevel($userId, $level)
    {
        if ($level == 1) { 
            return User::where('sponsor_id', $userId)->where('can_login', true)->count();
        } 
        $directChildren = User::where('sponsor_id', $userId)->where('can_login', true)->pluck('id'); 
        $countAtSpecificLevel = 0;
        
        foreach ($directChildren as $childId) {
            $countAtSpecificLevel += User::where('sponsor_id', $childId)
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
    $directChildren = User::where('sponsor_id', $userId)->where('can_login', true)->pluck('id');

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
        $parent = User::find($parentID);
        if (!$parent) {
            return;
        }
        $directChildCount = $this->getChildCountAtLevel($parentID, $level);
        $rewardLevels = collect([
            ['level' => 1, 'reward_amount' => 150, 'users_required' => 2],
            ['level' => 2, 'reward_amount' => 300, 'users_required' => 3],
            ['level' => 3, 'reward_amount' => 1200, 'users_required' => 4],
            ['level' => 4, 'reward_amount' => 4000, 'users_required' => 5],
            ['level' => 5, 'reward_amount' => 10000, 'users_required' => 6],
            ['level' => 6, 'reward_amount' => 30000, 'users_required' => 7],
            ['level' => 7, 'reward_amount' => 48000, 'users_required' => 8],
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
        $parentExists  = User::find($parent->sponsor_id);
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
        $parent = User::find($parentID);
        if (!$parent) {
            return;
        }
        $directChildCount = $this->getChildCountAtLevel($parentID, $level);
        $rewardLevels = collect([
            ['level' => 1, 'reward_amount' => 150, 'users_required' => 2],
            ['level' => 2, 'reward_amount' => 300, 'users_required' => 3],
            ['level' => 3, 'reward_amount' => 1000, 'users_required' => 4],
            ['level' => 4, 'reward_amount' => 4000, 'users_required' => 5],
            ['level' => 5, 'reward_amount' => 10000, 'users_required' => 6],
            ['level' => 6, 'reward_amount' => 30000, 'users_required' => 7],
            ['level' => 7, 'reward_amount' => 48000, 'users_required' => 8],    //////////
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
        $parentExists  = User::find($parent->sponsor_id);
        if ($parentExists) {
            \Log::info('parentExists: ' . $parentExists->id);
            $this->assignRewardToUser($parentExists->id, $level+1);
        }
    }
    public function checkAndAssignRewards($userId, $user)
    {  
        $directChildren = User::where('sponsor_id', $userId)
            ->where('can_login', 1) // Only active users
            ->get();
        \Log::info("LOG 1 - { $user->name }  update ho raha ha  jis ka parent {$user->parent->name} ha , ham ny idr {$user->parent->name} k sary user ko get kr liya ha ");
        $rewardLevels = [
            ['level' => 1, 'reward_amount' => 150, 'users_required' => 7],
            ['level' => 2, 'reward_amount' => 300, 'users_required' => 9],
            ['level' => 3, 'reward_amount' => 1200, 'users_required' => 11],
            ['level' => 4, 'reward_amount' => 4000, 'users_required' => 13],
            ['level' => 5, 'reward_amount' => 10000, 'users_required' => 15],
            ['level' => 6, 'reward_amount' => 30000, 'users_required' => 17],
            ['level' => 7, 'reward_amount' => 48000, 'users_required' => 19],
        ];
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

        $directReferrals = User::where('sponsor_id', $userId)
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
        $wallet->save();

        // Log reward assignment
        \Log::info("Reward assigned", [
            'user_id' => $userId,
            'amount' => $amount,
            'level' => $level,
        ]);
    }

    private function assignCommissions($user)
    {
        // Fetch the immediate sponsor
        $parentUser = User::find($user->sponsor_id);
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
            $ancestorUser = User::find($ancestor->ancestor_id);
            $teamSize = $this->getTeamSize($ancestorUser->id); // Fetch team size
    
            // Define required team size for each level
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
        return User::where('sponsor_id', $userId)->count();
    } 
    public function roiPayments()
    {
        $users = User::where('can_login', true)->get();
        $payments = ROITransaction::get();
        return view('users.roi-payments', compact('users', 'payments'));
    }

    public function submitRoiPayments(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id', // User selection
            'commission_percentage' => 'required|numeric|min:0|max:100', // Commission percentage
        ]);
        $user = User::find($request->user_id); 
        $walletTotal = Wallet::where('user_id',$user->id)->sum('balance');
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
                'level' => '-',
                'commission_type' => 'Roi',
                'percentage' => $paymentPercentage,
            ]
        );

        ROITransaction::create([
            'user_id' => $user->id,
            'amount' => $roiPayment,
            'percentage' => $request->commission_percentage,
            'description' => 'Monthly ROI Generated',
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
            'percentage' => 'required|numeric|min:3|max:7',
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


    

    private function generateParentCommissions($user, $roiAmount)
    {
        $commissionLevels = [
            1 => 3.5,  // divide / 2 
            2 => 3,
            3 => 2.5,
            4 => 2,
            5 => 1.5,
            6 => 1,
            7 => 0.5,
        ];

        foreach ($commissionLevels as $level => $percentage) {
            $parent = $this->getAncestorByLevel($user, $level); // Method to find parent by level
            if ($parent) {
                $commissionAmount = ($roiAmount * $percentage) / 100;
                ROITransaction::create([
                    'user_id' => $parent->id,
                    'amount' => $commissionAmount,
                    'percentage' => $percentage,
                    'description' => "Level {$level} commission from user {$user->id} | {$user->name}",
                ]);

                $wallet = Wallet::Create(
                    [
                        'user_id' => $parent->id,
                        'wallet_type' => 'profit_share',
                        'balance' => $commissionAmount,
                        'level' => $level,
                        'commission_type' => 'profit_share',
                        'wallet_from' => $user->id,
                        'percentage' => $percentage,
                    ]
                );
            }
        }
    }

    private function getAncestorByLevel($user, $level)
    {
        return \DB::table('referral_trees')
            ->join('users', 'referral_trees.ancestor_id', '=', 'users.id')
            ->where('referral_trees.descendant_id', $user->id)
            ->where('referral_trees.level', $level)
            ->select('users.*')
            ->first();
    }
}
