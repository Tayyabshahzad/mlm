<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Staff account access rules (enforced server-side on every web request):
 *
 *  - A staff account (created from Staff Roles) that is deactivated, or has
 *    lost every role, is logged out. It must never fall back to the member
 *    portal.
 *  - Data Entry Operators (staff-only 'operator' accounts) may use the payment
 *    sheet tool and nothing else.
 */
class RestrictDataEntryOperator
{
    /** Route names an operator-only account may open. */
    private const ALLOWED = [
        'dashboard',
        'payment-sheets.*',
        'logout',
        'approval.waiting',
        'csrf.refresh',
        'verification.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->is_staff_account && (!$user->can_login || $user->isStaffWithoutAccess())) {
            $message = !$user->can_login
                ? 'Your staff account has been deactivated. Please contact the administrator.'
                : 'Your staff access has been removed. Please contact the administrator.';

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => $message], 401)
                : redirect()->route('login')->with('error', $message);
        }

        if ($user && $user->isDataEntryOperatorOnly()) {
            $name = $request->route()?->getName();

            if (!$name || !$request->routeIs(...self::ALLOWED)) {
                abort(403, 'Data Entry Operators can only use Upload Payment Sheet.');
            }
        }

        return $next($request);
    }
}
