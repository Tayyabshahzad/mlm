<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivationCode;
use App\Models\PVTransaction;
use App\Models\ReferralLink;
use App\Models\ReferralTree;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use App\Rules\ValidActivationCode;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request, $ref = null)
    {
          
        // return Inertia::render('Auth/Register', [
        //     'refLink' => $ref,  
        // ]);
        $setting= Setting::first();
        return view('auth.register',compact('ref','setting'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
      
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:' . User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'transaction_id' => 'required|max:50|string|unique:' . User::class,
            'phone_number'=>'required|unique:'.User::class,
            'cc' => 'required',
            'payment_method'=>'required|in:usdt,bank,cash_slip,activation_code',
            'referral_link' => [
                'required',
                'string',
                'exists:referral_links,link',
            ],
           'amount_src' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'activation_code' => [
                'nullable', // Only required if payment method is activation_code
                new ValidActivationCode($request->payment_method),
            ],
        ]);    
        $referralLink = ReferralLink::where('link', $request->referral_link)->where('is_active',true)->first(); 
        $activationCodeId = null;
        

        if(!$referralLink){
            return redirect()->back()->with('error','The Referral link is expired or invalid ');
        }
        $baseUsername = Str::slug($request->username);
        if(!$baseUsername){
            return redirect()->back()->with('error','Invalid Username format ');
        } 
        $username = $baseUsername;
        $count = 1; 
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.'-'.$count;
            $count++;
        }  
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $username,
            'is_active' => true,
            'phone_verified' => true,
            'sponsor_id' => $referralLink->user->id,
            'transaction_id' =>  $request->transaction_id,
            'phone_number' =>$request->cc .$request->phone_number,
            'payment_method' =>$request->payment_method, 
            
        ]); 
        if ($request->payment_method === 'activation_code') {
            $activationCode = ActivationCode::where('code', $request->activation_code)
                ->where('admin_approval', 'approved')
                ->where('status', 'unused')
                ->first();
            if ($activationCode) {
                $activationCodeId = $activationCode->id;
                $activationCode->update([
                        'status' => 'used',
                        'used_by' => $user->id,
                        'updated_at'=>Carbon::now()
                ]); 
            }
            $user->activation_code_id= $activationCodeId; 
            $user->save();
            $this->logTransaction(
                 $activationCode->generated_by,
                'activation_code',
                'activation_code', 
                 0,0,
                 "New user '{$user->name}' was created using an activation code.",
                'debit'
            );
            
        }
     
        $user->assignRole('member'); 
        ReferralLink::create([
            'user_id' => $user->id,
            'link' => $username,
        ]); 
        // Register user in referral tree
        $this->registerUser($referralLink->user_id, $user->id); 
        if ($request->hasFile('amount_src')) {
            $user->addMedia($request->file('amount_src'))
                ->toMediaCollection('user_amount_source');
        }
    
        Auth::login($user);
    
        return redirect(route('dashboard', absolute: false));
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


    protected function generateReferralCode()
    {
        // Generate a code with mix of letters and numbers
        $letters = Str::random(5); // 5 random letters
        $numbers = rand(10000, 99999); // 5 random numbers
        $code = strtoupper($letters . $numbers); // Combine and make uppercase
        
        // Check if code already exists and regenerate if needed
        while (ReferralLink::where('link', $code)->exists()) {
            $letters = Str::random(5);
            $numbers = rand(10000, 99999);
            $code = strtoupper($letters . $numbers);
        }
        
        return $code;
    }

    public function createUserWallet($userId)
    {
        UserWallet::create([
            'user_id' => $userId,
            'wallet_type' => 'INVESTMENT', // Assuming the default wallet type is 'ONLINE'
            'balance' => 0, // Initial balance
        ]);
    }

    public function assignPvToUser($userId, $amountPaid)
    {
        // Find the user and their wallet
        $user = User::findOrFail($userId);
        $userWallet = UserWallet::where('user_id', $userId)->first();
        $pvAssigned = 100;  // This can be dynamic based on amount paid, as per your business rules
        // Update the user's wallet with the credited PV
        $userWallet->balance += $pvAssigned;
        $userWallet->total_credited += $pvAssigned;
        $userWallet->last_transaction = now();
        $userWallet->save();
        // Create a transaction log for this PV credit
        PVTransaction::create([
            'user_id' => $userId,
            'transaction_type' => 'INVESTMENT',
            'pv_amount' => $pvAssigned,
            'previous_balance' => $userWallet->balance - $pvAssigned, // Balance before transaction
            'new_balance' => $userWallet->balance, // Balance after transaction
            'created_at' => now(),
            'remarks' => 'PV credited for registration payment',
        ]);
        // Optionally update the user’s overall PV balance in the `users` table
        $user->current_pv_balance += $pvAssigned;
        $user->save();
    }
    private function logTransaction($userId,$toAddress,$fromAddress,$amount, $finalAmount,$description,$status)
            {
                TransactionLog::create([
                    'user_id' => $userId,
                    'from_wallet_type' => $toAddress,
                    'to_wallet_type' => $fromAddress,
                    'charge' =>0, 
                    'amount' => $amount, 
                    'final_amount' => $finalAmount,
                    'description' => $description,
                    'status' =>$status
                ]);
            } 

    

}
