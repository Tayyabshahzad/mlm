<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\Profile;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Rules\OtpExists;
class FrontEndController extends Controller
{
    public function index(){
        $endDate = Carbon::now()->addMonth()->startOfMonth()->addDays(4)->setTime(12, 0, 0);
        return view('demo.index',compact('endDate'));
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
    public function dashboard(){
        return view('demo.dashboard');
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
            ],
            [
                'phone.phone' =>'Please provide a valid phone number in the correct format for Pakistan.'
            ]);
        }

        elseif ($request->step == 2) {
            
            $validated = $request->validate([
                'email' => 'string|email|max:255',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'postal_code' => 'string|max:255',
                'cnic_front' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'cnic_back' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'postal_code' => 'string|max:255',
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
                'branch_name' => 'required|string|max:255',
                'branch_code' => 'required|string|max:255', 
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
        // Return response (you can redirect or send back a success message)
        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function changePassword(){
        $user = Auth::user(); 
        return view('users.profile.change-password',compact('user')); 
    }

    public function bankDetails(){
        $user = Auth::user(); 
        $profile = $user->profile; 
        return view('users.profile.bank-information',compact('profile')); 
    }

    

    public function updatePassword(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'confirm_new_password' => 'required|string|min:8',
        ]);

        // Check if the current password matches the user's password
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        } 
        Auth::user()->update([
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
    


    


    


    

    
}
