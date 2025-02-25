<?php

namespace App\Http\Controllers;

use App\Mail\CompanyAgreement;
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
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class FrontEndController extends Controller
{
    public function index(){
        $endDate = Carbon::now()->addMonth()->startOfMonth()->addDays(4)->setTime(12, 0, 0); 
        return view('frontEnd.index',compact('endDate'));
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
    public function dashboard(){ 


        
        $referralCounts = DB::table('referral_trees')
        ->select('referral_trees.level', DB::raw('COUNT(referral_trees.descendant_id) as count'))
        ->join('users', 'referral_trees.descendant_id', '=', 'users.id') // Join with users table
        ->where('referral_trees.ancestor_id', Auth::user()->id) // Filter for the current user's referrals
        ->where('users.can_login', true) // Only include active users
        ->where('referral_trees.level', '<=', 7) // Limit to 7 levels
        ->groupBy('referral_trees.level')
        ->orderBy('referral_trees.level')
        ->get();
    
        // Ensure all levels from 1 to 7 are represented
        $levels = range(1, 7);
        $levelCounts = collect($levels)->mapWithKeys(function ($level) use ($referralCounts) {
            $count = $referralCounts->firstWhere('level', $level)->count ?? 0;
            return [$level => $count];
        });
        
        // Now $levelCounts will include counts of active users for each level. 
        $totalCount = $levelCounts->sum(); 
        $wallets  = Wallet::where('user_id', Auth::user()->id)->get();
        $authUsers =  User::where('sponsor_id',Auth::user()->id)->where('can_login',true);  
        $inactiveUsers = $authUsers->where('can_login',false)->count(); 
        $reward = $authUsers->with('descendants'); 
        $totalEarning = Wallet::where('user_id', Auth::user()->id)->get()->sum('total_amount');

        $teamSizing = User::where('sponsor_id', Auth::user()->id)
        ->where('can_login',true)
        ->withCount([
            'children as team_count' => function ($query) {
                $query->select(DB::raw('COUNT(*)')); // Count direct descendants
            },
        ])
        ->orderBy('team_count', 'desc') // Order by team count
        ->limit(10)
        ->get();
        $totalRewardUsers = $levelCounts->sum(); 
        $maxRewardTarget = 7610;
        $totalRewardPercentage = round(($totalRewardUsers / $maxRewardTarget) * 100,2);


         
        $data = [
            'online_wallet' => $wallets->where('wallet_type', 'online')->sum('balance'),
            'direct_indirect' => $wallets->where('wallet_type', 'direct_indirect')->sum('balance'),
            'rewardWallet' => $wallets->where('wallet_type', 'reward')->sum('balance'),
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
            'reward' =>$totalRewardPercentage
        ]; 
        return view('demo.dashboard',compact('data','reward'));
        //return Inertia::render('Dashboard');
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
                'bio' => 'required|string|max:255',
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
                 $this->updatePdf($user->profile);   
                 Mail::to($user->email)->send(new CompanyAgreement($user));
                // $user->agreement_sent = true;
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

    public function updatePdf($userDetails)
    { 
        $pdf = new Fpdi();  
        $existingPdfPath = public_path('documents/business-agreement-final.pdf');  
        $updatedPdfPath = public_path("documents/agreement-{$userDetails->user['id']}.pdf");  
        $pdf->AddPage();
        $pdf->setSourceFile($existingPdfPath);
        $template = $pdf->importPage(1);
        $pdf->useTemplate($template);  
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);  
        $pdf->SetXY(160, 49); 
        $pdf->Write(0, Carbon::now()->format('d F Y')); 

        $pdf->SetXY(40, 99); // X and Y coordinates
        $pdf->Write(0, $userDetails['first_name']);   
        $pdf->SetXY(133, 99); // X and Y coordinates
        $pdf->Write(0,$userDetails['last_name']);  
        $pdf->SetXY(100, 106); // X and Y coordinates
        $pdf->Write(0, $userDetails['cnic']);  
        $pdf->SetXY(80, 114); // X and Y coordinates
        $pdf->Write(0, $userDetails['address']);  
        // $pdf->SetXY(120, 205); // X and Y coordinates
        // $pdf->Write(0, $userDetails['first_name']);   

        $pdf->SetXY(115, 263); // X and Y coordinates
        $pdf->Write(0, $userDetails['first_name']);  
        $pdf->SetXY(143, 263); // X and Y coordinates
        $pdf->Write(0, $userDetails['last_name']);

        $pdf->SetXY(145, 275); // X and Y coordinates
        $pdf->Write(0, $userDetails['cnic']);   
        // Save the updated PDF
        $pdf->Output($updatedPdfPath, 'F'); 
        return $updatedPdfPath;
    }
        

    public function bulkRegisterUsers()
    {
        $parentUsername = 'arslan-1'; 
        $parent = User::where('username', $parentUsername)->firstOrFail();
        $parentId = $parent->id;  
        DB::beginTransaction();
        try {
            // Loop to create 50 users
            for ($i = 1; $i <= 9; $i++) {
                $name = "sikander-$i";
                $email = "sikander-$i@example.com";
                $password = Hash::make('password'); // Default password
                $username = "sikander-$i"; 
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

    


    


    


    

    
}
