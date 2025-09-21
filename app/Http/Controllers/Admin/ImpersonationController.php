<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a user
     */
    public function impersonate(Request $request, $userId)
    {
        // Check if current user is admin
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('super-admin')) {
            abort(403, 'Only admins can impersonate users');
        }

        $userToImpersonate = User::findOrFail($userId);

        // Prevent impersonating other admins
        if ($userToImpersonate->hasRole('admin') || $userToImpersonate->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Cannot impersonate admin users');
        }

        // Store the original admin ID
        session(['original_admin_id' => Auth::id()]);
        session(['impersonating_user_id' => $userId]);
        session(['impersonation_started_at' => now()]);

        // Log the impersonation
        Log::info('Admin impersonation started', [
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name,
            'impersonated_user_id' => $userId,
            'impersonated_user_name' => $userToImpersonate->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return redirect()->route('dashboard')->with('success', "You are now logged in as {$userToImpersonate->name}");
    }

    /**
     * Stop impersonating and return to admin account
     */
    public function stopImpersonation()
    {
        if (!session()->has('impersonating_user_id')) {
            return redirect()->route('dashboard')->with('error', 'Not currently impersonating anyone');
        }

        $originalAdminId = session('original_admin_id');
        $impersonatedUserId = session('impersonating_user_id');
        $impersonationStarted = session('impersonation_started_at');

        // Get original admin user
        $originalAdmin = User::find($originalAdminId);

        if (!$originalAdmin) {
            // Clear sessions and logout if original admin not found
            session()->forget(['original_admin_id', 'impersonating_user_id', 'impersonation_started_at']);
            Auth::logout();
            return redirect()->route('login')->with('error', 'Original admin account not found');
        }

        // Calculate impersonation duration
        $duration = $impersonationStarted ? now()->diffInMinutes($impersonationStarted) : 0;

        // Log the end of impersonation
        Log::info('Admin impersonation ended', [
            'admin_id' => $originalAdminId,
            'admin_name' => $originalAdmin->name,
            'impersonated_user_id' => $impersonatedUserId,
            'duration_minutes' => $duration,
            'ip_address' => request()->ip()
        ]);

        // Clear impersonation session data
        session()->forget(['original_admin_id', 'impersonating_user_id', 'impersonation_started_at']);

        // Login back as original admin
        Auth::login($originalAdmin);

        return redirect()->route('dashboard')->with('success', 'Stopped impersonating user. You are now back as admin.');
    }

    /**
     * Show users list for impersonation
     */
    public function index(Request $request)
    {
        // Check if current user is admin
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('super-admin')) {
            abort(403, 'Only admins can access this page');
        }

        $query = User::query()
            ->whereDoesntHave('roles', function ($q) {
                $q->whereIn('name', ['admin', 'super-admin']);
            })
            ->with('roles');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('blocked', false)->where('can_login', true);
                    break;
                case 'blocked':
                    $query->where('blocked', true);
                    break;
                case 'cannot_login':
                    $query->where('can_login', false);
                    break;
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.impersonation.index', compact('users'));
    }
}