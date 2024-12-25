<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = FacadesAuth::user();
        if($user){
            // if ($user->phone_verified == 0 &&  $user->phone_verified == 0) {
            //     if (!in_array($request->route()->getName(), ['profile.edit', 'account.information','social.account.information','profile.change.password','update.password','user.profile.update','phone.verify','generate.otp','verify.otp','logout','verification.notice','verification.verify','profile.bank.details'])) {
            //         return redirect()->route('profile.edit')->with('error', 'Please verify your phone & complete your profile.');
            //     }
            // } 
            // if ($user->phone_verified == 0 && $user->is_active == 1) {
            //     if (!in_array($request->route()->getName(), ['profile.edit', 'account.information','social.account.information','profile.change.password','update.password','user.profile.update','phone.verify','generate.otp','verify.otp','logout','verification.notice','verification.verify','profile.bank.details'])) {
            //         return redirect()->route('profile.edit')->with('warning', 'Your Account has been approved please verify your phone to complete your profile.');
            //     }
            // }
            // if($user->is_active == 0 &&  $user->phone_verified == 1){
            //     if (!in_array($request->route()->getName(), ['approval.waiting','logout','verification.notice'])) {
            //         return redirect()->route('approval.waiting')->with('success', 'We are checking your details. This may take up to 24 hours, so please wait.');
            //     }
            // }

            if($user->can_login == 0 ){
                if (!in_array($request->route()->getName(), ['approval.waiting','logout','verification.notice'])) {
                    return redirect()->route('approval.waiting')->with('success', 'We are checking your details. This may take up to 24 hours, so please wait.');
                } 
            }

           
        }
        
        return $next($request);
    }
}
