<?php

namespace App\Http\Controllers;

use App\Mail\CompanyAgreement;
use App\Mail\InvoiceEmail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransactions;
use Illuminate\Http\Request;
use App\Services\PVService;
use App\Services\WalletService;
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

    public function index(){
       
        $teamMembers = User::with('team')->where('id','!=',auth()->user()->id)->orderBy('can_login','asc')->paginate(20);; 
        return view('users.index',compact('teamMembers'));
    }

    public function updateStatus(Request $request){ 
        $user = User::find($request->member_id); 
        $user->can_login = true;
        $user->save();  
       
        
        // Sending mail to user
        //Mail::to($user->email)->send(new CompanyAgreement($user));
        //Mail::to($user->email)->send(new WelcomeEmail($user));
        //Mail::to($user->email)->send(new InvoiceEmail($user));
        //$this->pvService->assignInitialPV($user);
        //$this->assignCommissions($user);  
        $this->checkAndAssignRewards($user->sponsor_id);
        return redirect()->back()->with('success','Member Status has been Updated');
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
                    'status' => $user->can_login ? 'Active':'Inactive',
                    'amount_proof' => $user->getFirstMediaUrl('user_amount_source'),
                ],
            ]);
        } 
        return response()->json(['success' => false, 'message' => 'User not found.']);
    }

    public function checkAndAssignRewards($userId)
    {
        // Fetch all direct children of the user
        $directChildren = User::where('sponsor_id', $userId)
            ->where('can_login', 1) // Only active users
            ->get();
    
        $rewardLevels = [
            ['level' => 1, 'reward_amount' => 150, 'users_required' => 7],
            ['level' => 2, 'reward_amount' => 300, 'users_required' => 9],
            ['level' => 3, 'reward_amount' => 1200, 'users_required' => 11],
            ['level' => 4, 'reward_amount' => 4000, 'users_required' => 13],
            ['level' => 5, 'reward_amount' => 10000, 'users_required' => 15],
            ['level' => 6, 'reward_amount' => 30000, 'users_required' => 17],
            ['level' => 7, 'reward_amount' => 48000, 'users_required' => 19],
        ];
    
        foreach ($rewardLevels as $level) {
            // Check if reward for this level has already been assigned
            $existingReward = Wallet::where([
                ['user_id', '=', $userId],
                ['wallet_type', '=', 'reward'],
                ['commission_type', '=', 'reward'],
                ['level', '=', $level['level']],
            ])->exists();
    
            if ($existingReward) {
                // Skip if reward for this level is already assigned
                continue;
            }
    
            if ($level['level'] === 1) {
                // For Level 1, use direct referrals count
                $directReferralsCount = $this->calculateDirectReferrals($userId);
                if ($directReferralsCount >= $level['users_required']) {
                    $this->assignReward($userId, $level['reward_amount'], $level['level']);
                    \Log::info("Level 1 reward assigned to user {$userId}");
                    break;
                }
            } else {
                // For other levels, calculate combined team size of direct children
                $totalTeamSize = 0;
    
                foreach ($directChildren as $child) {
                    $childTeamSize = $this->calculateTeamSize($child->id);
                    $totalTeamSize += $childTeamSize;
                    \Log::info("Child {$child->name} has team size of: {$childTeamSize}");
                }
    
                \Log::info("Total team size for Level {$level['level']} check: {$totalTeamSize}");
    
                if ($totalTeamSize >= $level['users_required']) {
                    $this->assignReward($userId, $level['reward_amount'], $level['level']);
                    \Log::info("Level {$level['level']} reward assigned to user {$userId} with total team size {$totalTeamSize}");
                    break;
                }
            }
        }
    }
    
    
  
    
 
    public function calculateDirectReferrals($userId)
    {
        return \DB::table('users')->where('sponsor_id', $userId)->where('can_login',1)->count();
    } 
    public function calculateTeamSize($userId)
    {
        // Debug: Check if user ID is valid
        $user = User::find($userId);
        if (!$user) {
            \Log::error("Invalid user ID: {$userId}");
            return 0;
        }
    
        // Direct referrals query
        $directReferrals = User::where('sponsor_id', $userId)
            ->where('can_login', 1)
            ->get();
    
        $directReferralsCount = $directReferrals->count();
    
        // Log direct referrals
        \Log::info("User {$userId} has {$directReferralsCount} direct referrals.");
    
        // Downline team size calculation (recursively)
        $downlineTeamSize = $directReferrals->sum(function ($child) {
            return $this->calculateTeamSize($child->id);
        });
    
        // Total team size
        $totalTeamSize = $directReferralsCount + $downlineTeamSize;
    
        // Log team size calculation
        \Log::info("Team size for user {$userId}: Direct Referrals = {$directReferralsCount}, Downline = {$downlineTeamSize}, Total = {$totalTeamSize}");
    
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
            $this->walletService->assignCommission($parentUser->id, $directCommissionAmount, 'direct', $user,1);
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
            $this->walletService->assignCommission($ancestor->ancestor_id, $indirectCommissionAmount, 'indirect', $user,$level);
        }
    }



    
}
