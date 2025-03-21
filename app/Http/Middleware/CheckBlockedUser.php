<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Allow access to the blocked.index route
        if ($request->routeIs('blocked.index')) {
            return $next($request);
        } 
        if (Auth::check() && Auth::user()->blocked) {
            return redirect()->route('blocked.index')
                ->with('error', 'Your Account is Blocked, Please contact system Administrator');
        }

        return $next($request);
    }
}
