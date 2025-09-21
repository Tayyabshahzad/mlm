<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminImpersonation
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we're impersonating a user
        if (session()->has('impersonating_user_id')) {
            $impersonatingUserId = session('impersonating_user_id');
            $originalAdminId = session('original_admin_id');

            // Get the user we're impersonating
            $impersonatedUser = \App\Models\User::find($impersonatingUserId);

            if ($impersonatedUser) {
                // Login as the impersonated user
                Auth::login($impersonatedUser);

                // Add a header to identify this as an impersonation session
                $request->headers->set('X-Impersonating', 'true');
                $request->headers->set('X-Original-Admin', $originalAdminId);
            }
        }

        return $next($request);
    }
}