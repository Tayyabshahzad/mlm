<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ReferralLink;
use App\Models\ReferralTree;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
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

        return view('auth.register',compact('ref'));
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
            'username' => 'required|string|max:255|unique:'.User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_link' => [
                'required',
                'string', 
                'exists:referral_links,link',
            ],
            'amount_src' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]); 
        $referralLink = ReferralLink::where('link', $request->referral_link)->first();   
        $baseUsername = Str::slug($request->name);
        $username = $baseUsername;
        $count = 1;
        while (User::where('username', $username)->exists()) { 
            $username = $baseUsername . '-' . $count;
            $count++;
        } 
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => $username,
            'is_active' => true,
            'phone_verified' => true,
            'sponsor_id'=> $referralLink->user->id
        ]);
        $user->assignRole('member'); 
        ReferralLink::create([
            'user_id' => $user->id,
            'link' => $this->generateReferralCode()
        ]); 
       
        if ($referralLink) {
            $parentUserId = $referralLink->user_id;
            $parentEntry = ReferralLink::where('user_id', $parentUserId)->first();
            $level = $parentEntry ? $parentEntry->level + 1 : 1;
        }

        ReferralTree::create([
            'user_id' => $user->id,
            'parent_id' => $parentUserId,
            'level' => $level,
        ]);
        

        if ($request->hasFile('amount_src')) { 
            $user->addMedia($request->file('amount_src'))
            ->toMediaCollection('user_amount_source'); 
        }     
        //event(new Registered($user)); 
        Auth::login($user); 
        return redirect(route('dashboard', absolute: false));
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
}
