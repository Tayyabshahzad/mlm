<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstalmentPaymentAudit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Admin-only: grant or remove the Office Admin role. */
class OfficeAdminController extends Controller
{
    public const ROLE = 'office-admin';

    public function index()
    {
        return view('admin.reconciliation.office-admins', [
            'officeAdmins' => User::role(self::ROLE)->orderBy('name')->get(['id', 'name', 'username', 'email', 'phone_number', 'can_login']),
            'history'      => InstalmentPaymentAudit::with(['actor:id,name,username', 'member:id,name,username'])
                ->whereIn('action', [InstalmentPaymentAudit::ROLE_ASSIGNED, InstalmentPaymentAudit::ROLE_REMOVED])
                ->latest('id')->limit(20)->get(),
        ]);
    }

    public function assign(Request $request): RedirectResponse
    {
        $request->validate(['account' => 'required|string|max:255']);

        $user = User::where('username', $request->account)->orWhere('email', $request->account)->first();
        if (!$user) {
            return back()->withInput()->with('error', 'No user found with that username or email.');
        }
        if ($user->blocked) {
            return back()->withInput()->with('error', 'This user is blocked and cannot be made an Office Admin.');
        }
        if ($user->hasRole(self::ROLE)) {
            return back()->with('error', "{$user->name} is already an Office Admin.");
        }

        $user->assignRole(self::ROLE);
        $this->audit(InstalmentPaymentAudit::ROLE_ASSIGNED, $user, $request);

        return back()->with('success', "{$user->name} (@{$user->username}) is now an Office Admin.");
    }

    public function remove(Request $request, User $user): RedirectResponse
    {
        if ($user->hasRole(self::ROLE)) {
            $user->removeRole(self::ROLE);
            $this->audit(InstalmentPaymentAudit::ROLE_REMOVED, $user, $request);
        }

        return back()->with('success', "Office Admin access removed from {$user->name}.");
    }

    private function audit(string $action, User $user, Request $request): void
    {
        InstalmentPaymentAudit::create([
            'action'      => $action,
            'actor_id'    => Auth::id(),
            'user_id'     => $user->id,
            'description' => ($action === InstalmentPaymentAudit::ROLE_ASSIGNED ? 'Office Admin role assigned to @' : 'Office Admin role removed from @') . $user->username,
            'ip_address'  => $request->ip(),
        ]);
    }
}
