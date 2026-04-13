<?php

namespace App\Http\Controllers;

use App\Mail\CompanyAgreement;
use App\Models\InvestmentSlab;
use App\Models\Otp;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\TransactionLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Rules\OtpExists;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Mail;
use App\Models\ReferralLink;
use App\Models\UserInvestment;
use App\Models\Wallet;
use App\Services\AccountManagementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FrontEndController extends Controller
{
    public function index(){
        $endDate = Carbon::now()->addMonth()->startOfMonth()->addDays(4)->setTime(12, 0, 0); 
        return view('frontEnd.index',compact('endDate'));
    }

    private function getAccessToken(): ?string
    {
        $response = Http::asForm()->post(env('PAYBOTIC_TOKEN_URL'), [
            'grant_type' => 'client_credentials',
            'client_id' => env('PAYBOTIC_CLIENT_ID'),
            'client_secret' => env('PAYBOTIC_CLIENT_SECRET'),
            'audience' => env('PAYBOTIC_AUDIENCE'),
        ]);

        if ($response->ok() && isset($response['access_token'])) {
            return $response['access_token'];
        }

        return null;
    }


     public function apiTest(){
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'error' => 'Unable to retrieve access token',
            ];
        }

        // Static payload you provided
        $payload = [
            "merchant_id" => 660,
            "amount" => 100,
            "order_id" => "ORDER786",
            "memo" => "Test Payment Link"
        ];

        $results = [];

        // Test multiple endpoint variations
        $endpoints = [
            '/api/payment-link',
            '/api/payment-links',
            '/api/payments',
            '/api/purchases',
            '/api/links',
            '/api/transactions'
        ];

        foreach ($endpoints as $endpoint) {
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post(env('PAYBOTIC_API_BASE') . $endpoint, $payload);

            $results[] = [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->status() !== 404 ? $response->body() : 'Not Found'
            ];
        }

        return [
            'token_preview' => substr($token, 0, 50) . '...',
            'api_base' => env('PAYBOTIC_API_BASE'),
            'results' => $results
        ];
    }

    public function apiTestGet(){
        $token = $this->getAccessToken();

        if (!$token) {
            return [
                'success' => false,
                'error' => 'Unable to retrieve access token',
            ];
        }

        $results = [];

        // Test GET requests to see what's available
        $endpoints = [
            '/api/merchants',
            '/api/businesses',
            '/api/users',
            '/api/dashboard',
            '/api/profile',
            '/api/purchases'
        ];

        foreach ($endpoints as $endpoint) {
            $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ])
                ->get(env('PAYBOTIC_API_BASE') . $endpoint);

            $results[] = [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $response->status() !== 404 ? substr($response->body(), 0, 200) . '...' : 'Not Found'
            ];
        }

        return [
            'token_preview' => substr($token, 0, 50) . '...',
            'api_base' => env('PAYBOTIC_API_BASE'),
            'results' => $results
        ];
    }



    public function emailSubmit(Request $request){
        $request->validate([
            'email' => 'required|email|unique:subscriptions,email',
        ]);
        Subscription::create([
            'email' => $request->email,
        ]);
        return redirect()->back()->with('success', 'Thank you for subscribing!');
    }
    public function dashboard11(){ 

        // $referralCounts = DB::table('referral_trees')
        // ->select('level', DB::raw('COUNT(descendant_id) as count'))
        // ->where('ancestor_id', Auth::user()->id)
        // ->where('level', '<=', 7) // Limit to 7 levels
        // ->groupBy('level')
        // ->orderBy('level')
        // ->get();
        // $levels = range(1, 7);
        // $levelCounts = collect($levels)->mapWithKeys(function ($level) use ($referralCounts) {
        //     $count = $referralCounts->firstWhere('level', $level)->count ?? 0;
        //     return [$level => $count];
        // }); 
        // $totalCount = $levelCounts->sum(); 
        // $wallets  = Wallet::where('user_id', Auth::user()->id)->get();
        // $authUsers =  User::where('sponsor_id',Auth::user()->id);  
        // $inactiveUsers = $authUsers->where('can_login',false)->count(); 
        // $reward = $authUsers->with('descendants'); 
        // $totalEarning = Wallet::where('user_id', Auth::user()->id)->get()->sum('balance');

        // $teamSizing = User::where('sponsor_id', Auth::user()->id)
        // ->withCount([
        //     'children as team_count' => function ($query) {
        //         $query->select(DB::raw('COUNT(*)')); // Count direct descendants
        //     },
        // ])
        // ->orderBy('team_count', 'desc') // Order by team count
        // ->limit(10)
        // ->get();
        
         
        // $data = [
        //     'online_wallet' => $wallets->where('wallet_type', 'online')->sum('balance'),
        //     'direct_indirect' => $wallets->where('wallet_type', 'direct_indirect')->sum('balance'),
        //     'reward' => $wallets->where('wallet_type', 'reward')->sum('balance'),
        //     'roi' => $wallets->where('wallet_type', 'roi')->sum('balance'),
        //     'profit_share' => $wallets->where('wallet_type', 'profit_share')->sum('balance'),
        //     'rank' => 0,
        //     'total_earning'=>$totalEarning,
        //     'team_size' => $teamSizing, // Customize based on your business logic
        //     'total_roi_earned_pv' => $totalEarning, // Assuming this is a user field
        //     'initial_investment' => Auth::user()->current_pv_balance, // Assuming this is a user field
        //     'total_roi_earned_this_month' => Auth::user()->roi_wallet_balance, // Assuming this is a user field
        //     'total_roi_remaining' => Auth::user()->roi_wallet_balance, // Assuming this is a user field
        //     'roi_status' => true, // Customize based on your business logic
        //     'levelCount' => $levelCounts,
        //     'totalTeam' => $levelCounts->sum(),
        // ];

        $referralCounts = DB::table('referral_trees')
        ->select('level', DB::raw('COUNT(descendant_id) as count'))
        ->where('ancestor_id', Auth::user()->id)
        ->where('level', '<=', 7) // Limit to 7 levels
        ->groupBy('level')
        ->orderBy('level')
        ->get();
        $levels = range(1, 7);
        $levelCounts = collect($levels)->mapWithKeys(function ($level) use ($referralCounts) {
            $count = $referralCounts->firstWhere('level', $level)->count ?? 0;
            return [$level => $count];
        }); 
        $totalCount = $levelCounts->sum(); 
        $wallets  = Wallet::where('user_id', Auth::user()->id)->get();
        $authUsers =  User::where('sponsor_id',Auth::user()->id);  
        $inactiveUsers = $authUsers->where('can_login',false)->count(); 
        $reward = $authUsers->with('descendants'); 
        $totalEarning = Wallet::where('user_id', Auth::user()->id)->get()->sum('balance');

        $teamSizing = User::where('sponsor_id', Auth::user()->id)
        ->withCount([
            'children as team_count' => function ($query) {
                $query->select(DB::raw('COUNT(*)')); // Count direct descendants
            },
        ])
        ->orderBy('team_count', 'desc') // Order by team count
        ->limit(10)
        ->get();
        
        $totalRewardUsers = $levelCounts->sum();
        dd($totalRewardUsers);
        $maxRewardTarget = 4000;
        $totalRewardPercentage = ($totalRewardUsers / $maxRewardTarget) * 100;
        $data = [
            'online_wallet' => $wallets->where('wallet_type', 'online')->sum('balance'),
            'direct_indirect' => $wallets->where('wallet_type', 'direct_indirect')->sum('balance'),
            'reward' => $wallets->where('wallet_type', 'reward')->sum('balance'),
            'roi' => $wallets->where('wallet_type', 'roi')->sum('balance'),
            'profit_share' => $wallets->where('wallet_type', 'profit_share')->sum('balance'),
            'rank' => 0,
            'total_earning'=>$totalEarning,
            'team_size' => $teamSizing, // Customize based on your business logic
            'total_roi_earned_pv' => $totalEarning, // Assuming this is a user field
            'initial_investment' => Auth::user()->current_pv_balance, // Assuming this is a user field
            'total_roi_earned_this_month' => Auth::user()->roi_wallet_balance, // Assuming this is a user field
            'total_roi_remaining' => Auth::user()->roi_wallet_balance, // Assuming this is a user field
            'roi_status' => true, // Customize based on your business logic
            'levelCount' => $levelCounts,
            'totalTeam' => $levelCounts->sum(),
            'totalRwardPercentage' => $totalRewardPercentage,
        ];

        
        return view('demo.dashboard',compact('data','reward'));
        //return Inertia::render('Dashboard');
    }
    public function dashboard(AccountManagementService $accountService){

        $user = Auth::user();

        // ── Dedicated saving-account users get their own dashboard ───────────
        if ($user->account_type === 'saving') {
            return $this->savingDashboard($user, $accountService);
        }

        // ── Standard investment dashboard ─────────────────────────────────
        $referralCounts = DB::table('referral_trees')
        ->select('referral_trees.level', DB::raw('COUNT(referral_trees.descendant_id) as count'))
        ->join('users', 'referral_trees.descendant_id', '=', 'users.id')
        ->where('referral_trees.ancestor_id', $user->id)
        ->where('referral_trees.tree_type', 'standard')
        ->where('users.can_login', true)
        ->where('referral_trees.level', '<=', 7)
        ->groupBy('referral_trees.level')
        ->orderBy('referral_trees.level')
        ->get();

        $levels = range(1, 7);
        $levelCounts = collect($levels)->mapWithKeys(function ($level) use ($referralCounts) {
            $count = $referralCounts->firstWhere('level', $level)->count ?? 0;
            return [$level => $count];
        });

        $totalCount = $levelCounts->sum();
        $wallets  = Wallet::where('user_id', $user->id)->get();
        $authUsers =  User::where('sponsor_id',$user->id)->where('can_login',true);
        $inactiveUsers = $authUsers->where('can_login',false)->count();
        $reward = $authUsers->with('descendants');
        $totalEarning = Wallet::where('user_id', $user->id)->get()->sum('total_amount');

        $teamSizing = User::where('sponsor_id', $user->id)
        ->where('can_login',true)
        ->withCount([
            'children as team_count' => function ($query) {
                $query->select(DB::raw('COUNT(*)'));
            },
        ])
        ->orderBy('team_count', 'desc')
        ->limit(10)
        ->get();
        $totalRewardUsers = $levelCounts->sum();
        $maxRewardTarget = 7610;
        $totalRewardPercentage = round(($totalRewardUsers / $maxRewardTarget) * 100,2);

        $roiStats = $accountService->getRoiAccountStats($user);

        $missedRoiCount = 0;
        if ($user->hasRole('admin')) {
            $missedRoiCount = User::where('roi_eligible_investment_amount', '>', 0)
                ->where('blocked', false)
                ->where('can_login', true)
                ->where('account_type', 'standard_investment')
                ->where('freez_wallet', false)
                ->where(function ($query) {
                    $query->whereNull('roi_status')
                        ->orWhere('roi_status', 'active');
                })
                ->where(function ($query) {
                    $query->whereNull('last_roi_payment_date')
                        ->orWhereDate('last_roi_payment_date', '<', now()->toDateString());
                })
                ->count();
        }

        // ── Saving Program wallet totals (for saving_enrolled standard users) ──
        $savingData = [];
        if ($user->saving_enrolled) {
            $savingService = app(\App\Services\SavingAccountService::class);
            $savingData = [
                'saving_enrolled'        => true,
                'saving_deposit'         => $wallets->where('wallet_type', 'saving')->sum('balance'),
                'saving_direct'          => Wallet::where('user_id', $user->id)
                                                ->where('wallet_type', 'direct_indirect')
                                                ->where('source_type', 'saving_instalment')
                                                ->sum('direct_balance'),
                'saving_indirect'        => Wallet::where('user_id', $user->id)
                                                ->where('wallet_type', 'direct_indirect')
                                                ->where('source_type', 'saving_instalment')
                                                ->sum('indirect_balance'),
                'saving_roi'             => $wallets->where('wallet_type', 'saving_roi')->sum('balance'),
                'instalment_summary'     => $savingService->getInstalmentSummary($user),
                'saving_plan_activated'  => $user->saving_registration_completed,
            ];
        }

        // ── Admin-only: aggregated saving plan stats across all users ──────────
        $adminSavingStats = [];
        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            $adminSavingStats = [
                'admin_saving_total_invested'        => \App\Models\User::where(function ($q) {
                    $q->where('account_type', 'saving')
                      ->orWhere('saving_enrolled', true);
                })->sum('saving_total_deposited'),

                'admin_saving_total_roi'             => \App\Models\Wallet::where('wallet_type', 'saving_roi')
                                                            ->sum('balance'),

                'admin_saving_total_direct'          => \App\Models\Wallet::where('wallet_type', 'direct_indirect')
                                                            ->where('source_type', 'saving_instalment')
                                                            ->sum('direct_balance'),

                'admin_saving_total_indirect'        => \App\Models\Wallet::where('wallet_type', 'direct_indirect')
                                                            ->where('source_type', 'saving_instalment')
                                                            ->sum('indirect_balance'),

                'admin_saving_total_users'           => \App\Models\User::where(function ($q) {
                    $q->where('account_type', 'saving')
                      ->orWhere('saving_enrolled', true);
                })->where('saving_registration_completed', true)->count(),
            ];
        }

        $data = [
            'online_wallet'          => $wallets->where('wallet_type', 'online')->sum('balance'),
            'direct_indirect'        => $wallets->where('wallet_type', 'direct_indirect')->sum('balance'),
            'rewardWallet'           => $wallets->where('wallet_type', 'reward')->sum('balance'),
            'roi'                    => $wallets->where('wallet_type', 'roi')->sum('balance'),
            'profit_share'           => $wallets->where('wallet_type', 'profit_share')->sum('balance'),
            'designation_incentive'  => $wallets->where('wallet_type', 'designation_incentive')->sum('balance'),
            'user_plan'              => $user->user_plan ?? 'standard',
            'rank'                   => 0,
            'total_earning'          => $totalEarning,
            'team_size'              => $teamSizing,
            'initial_investment'     => $roiStats['invested_amount'],
            'levelCount'             => $levelCounts,
            'totalTeam'              => $levelCounts->sum(),
            'reward'                 => $totalRewardPercentage,
            'roi_stats'              => $roiStats,
            'missed_roi_count'       => $missedRoiCount,
        ] + $savingData + $adminSavingStats;

        return view('demo.dashboard',compact('data','reward'));
        //return Inertia::render('Dashboard');
    }

    // ── Saving account dashboard ──────────────────────────────────────────
    private function savingDashboard(User $user, AccountManagementService $accountService)
    {
        // Referral counts within the SAVING tree only
        $referralCounts = DB::table('referral_trees')
            ->select('referral_trees.level', DB::raw('COUNT(referral_trees.descendant_id) as count'))
            ->join('users', 'referral_trees.descendant_id', '=', 'users.id')
            ->where('referral_trees.ancestor_id', $user->id)
            ->where('referral_trees.tree_type', 'saving')
            ->where('users.can_login', true)
            ->where('referral_trees.level', '<=', 7)
            ->groupBy('referral_trees.level')
            ->orderBy('referral_trees.level')
            ->get();

        $levels     = range(1, 7);
        $levelCounts = collect($levels)->mapWithKeys(function ($level) use ($referralCounts) {
            return [$level => $referralCounts->firstWhere('level', $level)->count ?? 0];
        });

        $wallets     = Wallet::where('user_id', $user->id)->get();
        $totalEarning = $wallets->sum('total_amount');

        // Instalment summary
        $savingService  = app(\App\Services\SavingAccountService::class);
        $instalmentSummary = $savingService->getInstalmentSummary($user);

        $savingBalance  = $wallets->where('wallet_type', 'saving')->sum('balance');
        $roiStats       = $accountService->getRoiAccountStats($user);

        $savingDirect   = \App\Models\Wallet::where('user_id', $user->id)
                            ->where('wallet_type', 'direct_indirect')
                            ->where('source_type', 'saving_instalment')
                            ->sum('direct_balance');
        $savingIndirect = \App\Models\Wallet::where('user_id', $user->id)
                            ->where('wallet_type', 'direct_indirect')
                            ->where('source_type', 'saving_instalment')
                            ->sum('indirect_balance');
        $savingRoi      = $wallets->where('wallet_type', 'saving_roi')->sum('balance');

        $data = [
            'account_type'          => 'saving',
            // Map saving_wallet → online_wallet so the blade template works without changes
            'online_wallet'         => $savingBalance,
            'saving_wallet'         => $savingBalance,
            'direct_indirect'       => $wallets->where('wallet_type', 'direct_indirect')->sum('balance'),
            'roi'                   => $savingRoi,
            'total_earning'         => $totalEarning,
            'levelCount'            => $levelCounts,
            'totalTeam'             => $levelCounts->sum(),
            'initial_investment'    => $user->saving_total_deposited,
            'user_plan'             => 'saving',
            'rewardWallet'          => 0,
            'profit_share'          => 0,
            'designation_incentive' => 0,
            'rank'                  => 0,
            'reward'                => 0,
            'team_size'             => collect(),
            'roi_stats'             => $roiStats,
            'missed_roi_count'      => 0,
            // Saving-specific extras
            'instalment_summary'    => $instalmentSummary,
            'registration_complete' => $user->saving_registration_completed,
            'plan_activated'        => $user->can_login,
            // Saving card keys (shared with enrolled standard users)
            'saving_enrolled'       => true,
            'saving_deposit'        => $savingBalance,
            'saving_roi'            => $savingRoi,
            'saving_direct'         => $savingDirect,
            'saving_indirect'       => $savingIndirect,
        ];

        $reward = collect(); // saving users have no reward tree
        return view('demo.dashboard', compact('data', 'reward'));
    }

    public function profile(){
        $user = Auth::user();
        $profile = $user->profile; 
        return view('users.profile.index',compact('profile')); 
    }

    public function accountInformation(){
        $user = Auth::user();
        $profile = $user->profile; 
        return view('users.profile.account-info',compact('profile')); 
    }

    public function socialAccountInformation(){
        $user = Auth::user();
        $profile = $user->profile; 
        return view('users.profile.social-account-info',compact('profile')); 
    } 

    public function updateProfile(Request $request)
    {    

        if ($request->step == 1) {
            $validated = $request->validate([
                'profile_avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|in:0,1',
                'phone' => 'required|string|max:15|phone:PK',
                'cnic' => 'required|string|max:255',
                'cnic_front' => 'image|mimes:jpg,jpeg,png|max:2048',
                'cnic_back' => 'image|mimes:jpg,jpeg,png|max:2048', 
                'address' => 'required|string|max:255', 
            ],
            [
                'phone.phone' =>'Please provide a valid phone number in the correct format for Pakistan.'
            ]);
           
        } 
        elseif ($request->step == 2) {
            
            $validated = $request->validate([
                'email' => 'nullable|string|email|max:255',
                'address' => 'string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'country' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:255',
                
            ]);
        }  
        elseif ($request->step == 3) {
            $validated = $request->validate([
                'facebook' => 'string|max:255',
                'twitter' => 'required|string|max:255',
                'instagram' => 'required|string|max:255',
                'occupation' => 'required|string|max:255',
                //'skills' => 'required|string|max:255', 
            ]); 
        }elseif($request->step == 4){ 
            $validated = $request->validate([
                'account_title' => 'string|max:255',
                'account_number' => 'required|string|max:255',
                'ibn_number' => 'required|string|max:255',
                'account_number' => 'required|string|max:255',
               // 'branch_code' => 'required|string|max:255', 
                'bank_name' => 'required|string|max:255', 
            ]); 
 
        } 
        $user = Auth::user();  
        if ($request->hasFile('profile_avatar')) { 
            if ($user->hasMedia('user_profile_images')) {
                $user->getMedia('user_profile_images')->each(function ($media) {
                    $media->delete();  // Delete old media files
                });
            }
            $user->addMedia($request->file('profile_avatar'))
            ->toMediaCollection('user_profile_images');
        }   
        
        if ($request->hasFile('cnic_front')) { 
            if ($user->hasMedia('user_document_cnic_front')) {
                $user->getMedia('user_document_cnic_front')->each(function ($media) {
                    $media->delete();  // Delete old media files
                });
            }
            $user->addMedia($request->file('cnic_front'))
            ->toMediaCollection('user_document_cnic_front');
        }    

        if ($request->hasFile('cnic_back')) { 
            if ($user->hasMedia('user_document_cnic_back')) {
                $user->getMedia('user_document_cnic_back')->each(function ($media) {
                    $media->delete();  // Delete old media files
                });
            }
            $user->addMedia($request->file('cnic_back'))
            ->toMediaCollection('user_document_cnic_back');
        }     
        $profile = $user->profile ?: new Profile();  
        if (!empty($validated['skills'])) { 
            $skillsArray = explode(',', $validated['skills']); 
            $profile->skills = json_encode($skillsArray);  
        } else {
            $profile->skills = null;  // Or handle as needed
        }
        $profile->fill($validated);
        $profile->user_id = $user->id;  
        if($request['phone']){
            $user->phone_number = $validated['phone'];
            $user->save();
        } 
        $profile->save();  
        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function agreementRequest(){
        $user = auth::user(); 
        if($user->profile){
            if($user->profile->cnic == null || $user->profile->first_name == null ||   $user->profile->last_name == null){
                return redirect()->back()->with('error', 'Please Add CNIC Details First.');
            }else{
                 $this->updatePdf($user->profile,$user);    
                 Mail::to($user->email)->send(new CompanyAgreement($user));
                 $user->agreement_sent = true;
                 $user->save();
                 return redirect()->back()->with('success', 'An Agreement has been sent to your email.');
            }
        }else{
            return redirect()->back()->with('error', 'Please Complete Your Profile First.');
        } 
      
    }

    public function changePassword($id = null){
        
         
        if($id){
            $user = User::where('id',$id)->first();
        }else{
            $user = Auth::user();
        }
       
        return view('users.profile.change-password',compact('user','id')); 
    }

    public function bankDetails(){
        $user = Auth::user(); 
        $profile = $user->profile; 
        return view('users.profile.bank-information',compact('profile')); 
    }

    

    public function updatePassword(Request $request, $id=null)
    {
        // Validate the incoming data
       
        $validated = $request->validate([
            //'current_password' => 'required|string',
            //'new_password' => 'required|string|min:8|confirmed',
            //'confirm_new_password' => 'required|string|min:8',
        ]);

        // Check if the current password matches the user's password
        // if (!Hash::check($request->current_password, Auth::user()->password)) {
        //     return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        // } 
        if($id){
            $user = User::find($id);
        }else{
            $user = Auth::user();
        }
       
        $user->update([
            'password' => Hash::make($request->new_password),
        ]); 
        return redirect()->back()->with('success', 'Password updated successfully.');
    }
    public function verifyPhone(){
        $user = Auth::user(); 
        return redirect()->back()->with('success', 'Phone Number has been verified successfully.');
    }

    public function generateOtp(Request $request){
            $user =  Auth::user();  
            if (!$user->phone_number) {
                return response()->json([
                    'status' => true,
                    'message' => 'Please Add Phone Number First.', 
                ]); 
            } 
            $lastOtp = Otp::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
            if ($lastOtp && $lastOtp->otpAttempts >= 3) {
                $timeSinceLastAttempt = now()->diffInMinutes($lastOtp->created_at);
                if ($timeSinceLastAttempt < 10) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You have exceeded the maximum number of OTP attempts. Please wait ' . (10 - $timeSinceLastAttempt) . ' more minutes.'
                    ]);
                } else { 
                    $lastOtp->update(['otpAttempts' => 0]);
                }
            }
            $existingOtp = Otp::where('user_id', $user->id)
                      ->where('expires_at', '>', now())
                      ->first(); 
            if ($existingOtp) {
                return response()->json([
                    'status' => true,
                    'message' => 'OTP already sent. Please check your phone.',
                    'otp' => $existingOtp->otp
                ]);
            }
            $otpCode = rand(100000, 999999);
            Otp::create([
                'user_id' => $user->id,
                'otp' => $otpCode,
                'phone_number' => $user->phone_number,
                'expires_at' => now()->addMinutes(5),
                'otpAttempts' => 1, // Start from 1 for this attempt
                'last_attempt_at' => now(),
            ]);
            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully.',
                'otp' => $otpCode // Optionally, include the OTP in the response for debugging purposes
            ]);
             
             
    }

    public function verifyOtp(Request $request){
         
        $validated = $request->validate([
            'otp' => ['required'],
        ]);
        $otp = Otp::where('otp', $request->otp)
        ->where('expires_at', '>', now()) // Ensure OTP is not expired
        ->first();
        if (!$otp) {
            return redirect()->back()->with('error', 'Please Enter Valid OTP.');
        }
        $user = Auth::user();
        $user->phone_verified = true;
        $user->save();
        return redirect()->back()->with('success', 'Phone Number Verification has been completed.');

    }

    public function updatePdf($userDetails,$user)
    { 
        $pdf = new Fpdi();  
        $existingPdfPath = public_path('documents/business-agreement-final.pdf');  
        $updatedPdfPath = public_path("documents/agreements/{$userDetails->user->username}.pdf");  
        $pdf->AddPage();
        $pdf->setSourceFile($existingPdfPath);
        $template = $pdf->importPage(1);
        $pdf->useTemplate($template);  
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);  
        $pdf->SetXY(150, 43.3); 
        $pdf->Write(0, Carbon::now()->format('d F Y')); 

        $pdf->SetXY(45, 79); // X and Y coordinates
        $pdf->Write(0, $userDetails['first_name']);   
        $pdf->SetXY(95, 79); // X and Y coordinates
        $pdf->Write(0,$userDetails['last_name']);  
        $pdf->SetXY(30, 85); // X and Y coordinates
        $pdf->Write(0, $userDetails['cnic']);  
        $pdf->SetXY(85, 85); // X and Y coordinates
        $pdf->Write(0, $userDetails['address']);  
        $pdf->SetXY(70, 130); // X and Y coordinates
        $pdf->Write(0, $user->roi_eligible_investment_amount);    
        $pdf->SetXY(115, 130); // X and Y coordinates
        $pdf->Write(0, $user->username);   

        $pdf->SetXY(115, 263); // X and Y coordinates
        $pdf->Write(0, $userDetails['first_name']);  
        $pdf->SetXY(130, 263); // X and Y coordinates
        $pdf->Write(0, $userDetails['last_name']);

        $pdf->SetXY(145, 275); // X and Y coordinates
        $pdf->Write(0, $userDetails['cnic']);   
        // Save the updated PDF
        $pdf->Output($updatedPdfPath, 'F'); 
        return $updatedPdfPath;
    }
        

    public function bulkRegisterUsers()
    {
        $parentUsername = 'zmeer'; 
        $parent = User::where('username', $parentUsername)->firstOrFail();
        $parentId = $parent->id;  
        DB::beginTransaction();
        try { 
            for ($i = 1; $i <= 10; $i++) {
                $name = "zmeer_child_$i";
                $email = "zmeer_child_$i@example.com";
                $password = Hash::make('password'); // Default password
                $username = "zmeer_child_$i"; 
                // Create user
                $newUser = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'username' => $username,
                    'transaction_id'=>'222',
                    'is_active' => true,
                    'phone_verified' => true,
                    'sponsor_id' => $parentId,  
                    'transaction_id' => "admin-child--$i",
                    'phone_number' => "223333-$i",
                    'payment_method' => "cash_slip",
                    'usdt_rate' => 234,  
                    'transferred_amount' => 2343,
                    'converted_usdt_amount' => 2343,
                    'fee_deducted' => 10, 
                    'net_invested_usdt_amount' => 2343,  
                    'roi_eligible_investment_amount' => 2343,  


                ]); 
                // Assign default role
                $newUser->assignRole('member');

                // Generate referral link
                ReferralLink::create([
                    'user_id' => $newUser->id,
                    'link' => $username,
                ]);

                // Update referral tree
                $this->registerUser($parentId, $newUser->id);
            }

            DB::commit();
            return response()->json(['message' => '50 users successfully registered under ' . $parentUsername]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }

       
    }

    function registerUser($parentId, $newUserId) {
        DB::beginTransaction(); 
        try {
            // Step 1: Insert direct relationship (level = 1)
            DB::table('referral_trees')->insertOrIgnore([
                'ancestor_id' => $parentId,
                'descendant_id' => $newUserId,
                'level' => 1,
            ]);
    
            // Step 2: Propagate ancestor relationships
            $ancestors = DB::table('referral_trees')
                ->where('descendant_id', $parentId)
                ->get();
    
            foreach ($ancestors as $ancestor) {
                DB::table('referral_trees')->insertOrIgnore([
                    'ancestor_id' => $ancestor->ancestor_id,
                    'descendant_id' => $newUserId,
                    'level' => $ancestor->level + 1,
                ]);
            }
    
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function buyProduct(){
        $products = Product::get(); 
        return view('genealogy.product',compact('products')); 
    }

    public function processUsersUsdtInvestment()
    {   
        try {
            DB::beginTransaction(); 
            $usdtRate = 290;
            $convertedUsdt = 110;
            $convertedPKR = $convertedUsdt * $usdtRate; // 31900
            $requiredPkr = 27500;
            $fee = 10;
            $netUsdt = 100;
            $negativePointPKR = ($convertedPKR - $requiredPkr); // 4400
            $negativePointUSDT = ($negativePointPKR /$usdtRate ); //15.17
            $usernames = [
                'jaweria786', 'kanwal7700', 'agha9514', 'amjad786', 'fazal73',
                'syed14', 'admin786', 'javed786', 'hussain92', 'khawaja-1',
                'beqasoorkhandik', 'raasif2004', 'nadim786', 'asia', 'ablangrial',
                'afzal786', 'khawajapdk', 'bhurban', 'asmat786', 'khalil786',
                'jugnu70', 'yousaf1984', 'rajgan1', 'malikyasir', 'sana512',
                'ideal007', 'murshad1', 'malik007', 'khan786', 'pari007',
            ];
            $users = User::whereNotIn('username', $usernames)->get();  
            foreach ($users as $user) {
                // $user->transferred_amount = $convertedPKR;
                // $user->converted_usdt_amount = $convertedUsdt;
                // $user->fee_deducted = $fee;
                // $user->usdt_rate = $usdtRate;
                // $user->net_invested_usdt_amount = $netUsdt;
                // $user->roi_eligible_investment_amount = $netUsdt;
                $user->negative_pv = $negativePointUSDT;   
                $user->save();
                // $this->createInitialInvestment($user);
                // $this->checkAndCreateSlabs($user);
            }
            DB::commit();
            return response()->json(['message' => 'Users processed successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function processUsersUsdtInvestment50User()
    {   
        try {
            DB::beginTransaction();  
            $usdtRate = 281.55;
            $convertedUsdt = 60;
            $convertedPKR = $convertedUsdt * $usdtRate; 
            $fee = 10;
            $netUsdt = 50;
            $usernames = [
                'jaweria786', 'kanwal7700', 'agha9514', 'amjad786', 'fazal73',
                'syed14', 'admin786', 'javed786', 'hussain92', 'khawaja-1',
                'beqasoorkhandik', 'raasif2004', 'nadim786', 'asia', 'ablangrial',
                'afzal786', 'khawajapdk', 'bhurban', 'asmat786', 'khalil786',
                'jugnu70', 'yousaf1984', 'rajgan1', 'malikyasir', 'sana512',
                'ideal007', 'murshad1', 'malik007', 'khan786', 'pari007',
            ];
    
            $users = User::whereIn('username', $usernames)->get();  
            foreach ($users as $user) {
                $user->transferred_amount = $convertedPKR;
                $user->converted_usdt_amount = $convertedUsdt;
                $user->fee_deducted = $fee;
                $user->usdt_rate = $usdtRate;
                $user->net_invested_usdt_amount = $netUsdt;
                $user->roi_eligible_investment_amount = $netUsdt;
                $user->negative_pv = 0;   
                $user->save();
                $this->createInitialInvestment($user); 
            }
            DB::commit();
            return response()->json(['message' => 'Users processed successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function processUsersUsdtInvestment11()
    {
        $usdtRate = $this->getLiveUsdtRate(); 
        dd($usdtRate);
        try {
            DB::beginTransaction();

            $usdtRate = $this->getLiveUsdtRate();   
            $convertedUsdt = 110;
            $convertedPKR = $usdtRate * $convertedUsdt;
            $requiredPkr = 27500;
            $fee = 10;
            $netUsdt = 100;

            $users = User::all();

            foreach ($users as $user) {
                // ✅ Check if investment already exists
                $investmentExists = UserInvestment::where('user_id', $user->id)->exists();

                if ($investmentExists) {
                    // If already processed, only run the slabs
                    $this->checkAndCreateSlabs($user);
                    continue;
                }

                // ✅ If not yet processed, run full logic
                $user->transferred_amount = $convertedPKR;
                $user->converted_usdt_amount = $convertedUsdt;
                $user->fee_deducted = $fee;
                $user->net_invested_usdt_amount = $netUsdt;
                $user->roi_eligible_investment_amount = $netUsdt;

                $extraPkr = $convertedPKR - $requiredPkr;

                if ($extraPkr > 0) {
                    $wallet = Wallet::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'wallet_type' => 'online'
                        ],
                        [
                            'balance' => 0.00,
                            'direct_balance' => 0.00,
                            'indirect_balance' => 0.00,
                            'commission_type' => 'online',
                            'percentage' => 0.00,
                            'level' => '-',
                            'total_amount' => 0.00,
                        ]
                    );

                    $wallet->balance += $extraPkr;
                    $wallet->save();

                    $user->negative_pv += $extraPkr;
                }

                $user->save(); 
                $this->createInitialInvestment($user);
                $this->checkAndCreateSlabs($user);
            }

            DB::commit();
            return response()->json(['message' => 'Users processed successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Investment Processing Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }


    

    protected function createInitialInvestment(User $user): void
    {
        try {
            if (!$user->net_invested_usdt_amount || $user->net_invested_usdt_amount <= 0) {
                throw new \InvalidArgumentException('Invalid invested amount for user ID: ' . $user->id);
            }
            UserInvestment::create([
                'user_id' => $user->id,
                'amount' => $user->net_invested_usdt_amount,
                'type' => 'join',
                'description' => 'Initial payment during account creation',
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to create initial investment for user ID: {$user->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    } 

    private function checkAndCreateSlabs(User $user): void
    {
        DB::beginTransaction(); 
        try {
            $totalInvestment = UserInvestment::where('user_id', $user->id)->sum('amount');
            $totalSlabs = InvestmentSlab::where('user_id', $user->id)->count(); 
            $availableInvestment = $totalInvestment - ($totalSlabs * 100);
            if ($availableInvestment < 100) {
                DB::commit(); // Nothing to do, exit early.
                return;
            } 
            while ($availableInvestment >= 100) {
                $achievedAt = now();
                $willPayAt = $achievedAt->copy()->addMonths(24)->startOfMonth()->addMonth();
                InvestmentSlab::create([
                    'user_id' => $user->id,
                    'slab_count' => ++$totalSlabs,
                    'amount' => 100,
                    'achived_at' => $achievedAt,
                    'current_balance' => $user->roi_eligible_investment_amount,
                    'maturity_date' => $achievedAt->copy()->addMonths(24),
                    'will_pay_at' => $willPayAt,
                    'status' => 'No',
                ]);
                $availableInvestment -= 100;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Failed to create slabs for user ID: {$user->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    } 

    private function getLiveUsdtRate()
    {
        try { 

            $response = Http::withOptions([
                'verify' => false, // 👈 this disables the cert check
            ])->withHeaders([
                'X-CMC_PRO_API_KEY' => '00e22837-061a-4d8a-8253-a949bc097fb1'
            ])->get('https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest', [
                'symbol' => 'USDT',
                'convert' => 'PKR',
            ]); 
            
            $rate = $response->json()['data']['USDT']['quote']['PKR']['price'];
            return round($rate, 2);

            throw new \Exception("Failed to fetch USDT rate.");
        } catch (\Exception $e) {
            dd($e); 
            return 280;  
        }
    } 

    public function processUsersWithLowInvestment()
    {
        try {
            DB::beginTransaction(); 

            $usdtRate = $this->getLiveUsdtRate();    
            $convertedUsdt = 60;
            $convertedPKR = 17400; // e.g., 110 x 280 = 30800
            $requiredPkr = 17400;
            $fee = 10;
            $netUsdt = 50;  
            $usernames = [
                'jaweria786', 'kanwal7700', 'agha9514', 'amjad786', 'fazal73',
                'syed14', 'admin786', 'javed786', 'hussain92', 'khawaja-1',
                'beqasoorkhandik', 'raasif2004', 'nadim786', 'asia', 'ablangrial',
                'afzal786', 'khawajapdk', 'bhurban', 'asmat786', 'khalil786',
                'jugnu70', 'yousaf1984', 'rajgan1', 'malikyasir', 'sana512',
                'ideal007', 'murshad1', 'malik007', 'khan786', 'pari007',
            ]; 
            $users = User::whereIn('username', $usernames)->get();
            foreach ($users as $user) { 
                 $extraPkr = $convertedPKR - $requiredPkr;

                 $user->transferred_amount = $convertedPKR;
                 $user->converted_usdt_amount = $convertedUsdt;
                 $user->fee_deducted = $fee;
                 $user->net_invested_usdt_amount = $netUsdt;
                 $user->roi_eligible_investment_amount = $netUsdt;
                 $user->negative_pv += $extraPkr;
                 $user->save();
                 $this->updateInitialInvestmentsForSelectedUsers($user);
                 $this->updateSlabs($user);
                 $this->deleteLastOnlineWallet($user);
                // // Calculate and transfer excess PKR to online wallet
                // $extraPkr = $convertedPKR - $requiredPkr;

                // if ($extraPkr > 0) {
                //     $wallet = Wallet::firstOrCreate(
                //         [
                //             'user_id' => $user->id,
                //             'wallet_type' => 'online'
                //         ],
                //         [
                //             'balance' => 0.00,
                //             'direct_balance' => 0.00,
                //             'indirect_balance' => 0.00,
                //             'commission_type' => 'online',
                //             'percentage' => 0.00,
                //             'level' => '-',
                //             'total_amount' => 0.00,
                //         ]
                //     );

                //     $wallet->balance += $extraPkr;
                //     $wallet->save();

                //     // Add to negative points
                //     $user->negative_pv += $extraPkr;
                // }

                // $user->save();
               
            }

            DB::commit();
            return response()->json(['message' => 'Users processed successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            // Optional: Log the error
            // Log::error('Investment Processing Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }

    public function updateInitialInvestmentsForSelectedUsers($user)
    {  
        try {
            if (!$user->net_invested_usdt_amount || $user->net_invested_usdt_amount <= 0) {
                Log::warning("Skipping user {$user->username} due to invalid invested amount."); 
            }else{
                $investment = UserInvestment::where('user_id', $user->id)->first(); 
                if ($investment) {
                    $investment->amount = $user->net_invested_usdt_amount;
                    $investment->description = 'Updated initial payment based on latest net investment';
                    $investment->save();
                } else {
                    Log::warning("No initial investment found for user {$user->username}.");
                }
            } 
            
        } catch (\Throwable $e) {
            Log::error("Failed to update initial investment for user {$user->username}: " . $e->getMessage());
        }

        return true;
    }

    private function updateSlabs(User $user): void
    { 
        DB::beginTransaction(); 
        try {
           
            $slabs = InvestmentSlab::where('user_id', $user->id)->first(); 
            if($slabs){
                $slabs->delete();
            }  
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack(); 
            throw $e;
        }
    }
    public function deleteLastOnlineWallet($user)
    {
        $wallet = Wallet::where('user_id', $user->id)
        ->where('wallet_type', 'online')
        ->orderByDesc('id') // or 'created_at'
        ->first();

        if ($wallet) {
            $wallet->delete(); 
        } 
    }
    

    

    


    


    


    

    
}
