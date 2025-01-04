<?php

namespace App\Http\Controllers;

use App\Mail\CompanyAgreement;
use App\Mail\InvoiceEmail;
use App\Mail\WelcomeEmail;
use App\Models\ROITransaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransactions;
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
        Mail::to($user->email)->send(new CompanyAgreement($user));
        Mail::to($user->email)->send(new WelcomeEmail($user));
        Mail::to($user->email)->send(new InvoiceEmail($user));
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
        // Define commission percentages for each level (this can be retrieved from a config or database table)
        $commissionPercentages = [
            1 => 5,  // Level 1 gets 5%
            2 => 2,  // Level 2 gets 2%
            3 => 1.5, // Level 3 gets 1.5%
            4 => 1,   // Level 4 gets 1%
            5 => 0.8, // Level 5 gets 0.8%
            6 => 0.5, // Level 6 gets 0.5%
            7 => 0.1, // Level 7 gets 0.1%
        ];

        // Return commission percentage for the given level, or default to 0 if the level is not found
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
                ],
            ]);
        }
        return response()->json(['success' => false, 'message' => 'User not found.']);
    }
    public function getChildCountAtLevel($userId, $level)
    {
        if ($level == 1) {
            // Base case: Count direct children
            return User::where('sponsor_id', $userId)->where('can_login', true)->count();
        }

        // Recursive step: Traverse the children and decrement the level
        $directChildren = User::where('sponsor_id', $userId)->where('can_login', true)->pluck('id');

        $count = 0;
        foreach ($directChildren as $childId) {
            $count += $this->getChildCountAtLevel($childId, $level - 1);
        }

        return $count;
    }

    public function test($parentID,  $level)
    {
        if ($level > 7) {
            return;
        }
        $parent = User::find($parentID);
        $directChildCount = $this->getChildCountAtLevel($parentID, $level);
        $rewardLevels = collect([
            ['level' => 1, 'reward_amount' => 150, 'users_required' => 3],
            ['level' => 2, 'reward_amount' => 300, 'users_required' => 5],
            ['level' => 3, 'reward_amount' => 1200, 'users_required' => 7],
            ['level' => 4, 'reward_amount' => 4000, 'users_required' => 9],
            ['level' => 5, 'reward_amount' => 10000, 'users_required' => 11],
            ['level' => 6, 'reward_amount' => 30000, 'users_required' => 13],
            ['level' => 7, 'reward_amount' => 48000, 'users_required' => 15],
        ]);
        $specificRewardLevel = $rewardLevels->firstWhere('level', $level);
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
    public function checkAndAssignRewards($userId, $user)
    {

        //tayyab create ho raha , ab sary wo user ly aiw jis ke id shahzad ha 
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
        // $ancestors = $this->getAncestors($user)
        //     ->filter(function ($ancestor) use ($parentUser) {
        //         return $ancestor->ancestor_id !== $parentUser->id; // Exclude the direct sponsor
        //     });

        $ancestors = $this->getAncestors($user)
            ->filter(function ($ancestor) use ($user) {
                return $ancestor->ancestor_id !== $user->sponsor_id && $ancestor->level <= 7;
            });



        foreach ($ancestors as $ancestor) {
            $level = $ancestor->level; // Get level of ancestor
            $indirectCommissionPercentage = $this->getCommissionForLevel($level); // Get commission for the ancestor's level
            $indirectCommissionAmount = ($indirectCommissionPercentage / 100) * $user->current_pv_balance;

            // Assign Indirect Commission for Ancestors
            $this->walletService->assignCommission($ancestor->ancestor_id, $indirectCommissionAmount, 'indirect', $user, $level);
        }
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
        if ($user->roi_wallet_balance >= 100) {
            return response()->json(['message' => 'ROI already completed for this user.'], 400);
        }
        if (!$user->roi_start_date) {
            $user->roi_start_date = Carbon::now();
            $user->roi_end_date = Carbon::now()->addYears(2); // 2 years from start
            $user->save();
        }
        $monthsRemaining = Carbon::now()->diffInMonths($user->roi_end_date, false);
        $remainingPV = 100 - $user->roi_wallet_balance;
        $paymentPercentage = $request->commission_percentage;
        $maxMonthlyPayment = $remainingPV / $monthsRemaining;
        $roiPayment = min(($remainingPV * $paymentPercentage) / 100, $maxMonthlyPayment);
        $user->roi_wallet_balance += $roiPayment;
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

    private function generateParentCommissions($user, $roiAmount)
    {
        $commissionLevels = [
            1 => 7,
            2 => 6,
            3 => 5,
            4 => 4,
            5 => 3,
            6 => 2,
            7 => 1,
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
