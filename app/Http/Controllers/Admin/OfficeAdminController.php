<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstalmentPaymentAudit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Admin-only: grant or remove staff roles (Office Admin, Data Entry Operator). */
class OfficeAdminController extends Controller
{
    /** role name => label */
    public const ROLES = [
        'office-admin' => 'Office Admin',
        'operator'     => 'Data Entry Operator',
    ];

    public function index()
    {
        return view('admin.reconciliation.office-admins', [
            'roles'   => self::ROLES,
            // Anyone holding a staff role, plus staff accounts whose role was removed
            'staff'   => User::where(fn ($q) => $q->role(array_keys(self::ROLES))->orWhere('is_staff_account', true))
                ->with('roles:id,name')->orderBy('name')
                ->get(['id', 'name', 'username', 'email', 'phone_number', 'can_login', 'is_staff_account']),
            'history' => InstalmentPaymentAudit::with(['actor:id,name,username', 'member:id,name,username'])
                ->whereIn('action', [
                    InstalmentPaymentAudit::ROLE_ASSIGNED, InstalmentPaymentAudit::ROLE_REMOVED,
                    InstalmentPaymentAudit::STAFF_CREATED, InstalmentPaymentAudit::STAFF_ACTIVATED,
                    InstalmentPaymentAudit::STAFF_DEACTIVATED, InstalmentPaymentAudit::STAFF_PASSWORD,
                ])
                ->latest('id')->limit(20)->get(),
        ]);
    }

    public function assign(Request $request): RedirectResponse
    {
        $request->validate([
            'account' => 'required|string|max:255',
            'role'    => 'nullable|in:' . implode(',', array_keys(self::ROLES)),
        ]);
        $role  = $request->input('role', 'office-admin');
        $label = self::ROLES[$role];

        $user = User::where('username', $request->account)->orWhere('email', $request->account)->first();
        if (!$user) {
            return back()->withInput()->with('error', 'No user found with that username or email.');
        }
        if ($user->blocked) {
            return back()->withInput()->with('error', "This user is blocked and cannot be made a {$label}.");
        }
        if ($user->hasRole($role)) {
            return back()->with('error', "{$user->name} is already a {$label}.");
        }

        $user->assignRole($role);
        $this->audit(InstalmentPaymentAudit::ROLE_ASSIGNED, $user, $request, "{$label} role assigned to @{$user->username}", $role);

        // A staff account that was switched off when it lost its role gets its login back.
        if ($user->is_staff_account && !$user->can_login) {
            $user->forceFill(['can_login' => true])->save();
            $this->audit(InstalmentPaymentAudit::STAFF_ACTIVATED, $user, $request, "Staff account re-activated with new role: @{$user->username}", $role);
        }

        return back()->with('success', "{$user->name} (@{$user->username}) is now a {$label}.");
    }

    /**
     * Create a brand-new staff login. Staff accounts are outside the MLM:
     * no sponsor, no referral link, no 'member' role, no wallets/instalments.
     */
    public function createAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'username'     => 'required|alpha_dash|max:255|unique:users,username',
            'email'        => 'required|email|max:255|unique:users,email',
            'phone_number' => 'nullable|string|max:30|unique:users,phone_number',
            'password'     => 'required|string|min:8|confirmed',
            'role'         => 'required|in:' . implode(',', array_keys(self::ROLES)),
        ]);

        $user = User::create([
            'name'           => $data['name'],
            'username'       => $data['username'],
            'email'          => $data['email'],
            'phone_number'   => $data['phone_number'] ?? null,
            'password'       => Hash::make($data['password']),
            'transaction_id' => 'STAFF-' . strtoupper(Str::random(8)),
            'can_login'      => true,
            'is_active'      => true,
            'phone_verified' => true,
        ]);
        $user->forceFill(['email_verified_at' => now(), 'is_staff_account' => true])->save();
        $user->assignRole($data['role']);

        $label = self::ROLES[$data['role']];
        $this->audit(InstalmentPaymentAudit::STAFF_CREATED, $user, $request, "New {$label} account created: @{$user->username}", $data['role']);

        return back()->with('success', "{$label} account @{$user->username} created. They can log in now.");
    }

    /** Activate / deactivate a staff login (can_login). */
    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->ensureManageableStaff($user);

        $activate = !$user->can_login;
        if ($activate && $user->isStaffWithoutAccess()) {
            return back()->with('error', "{$user->name} has no role. Give them a role first (Give Role to Existing Account) — that also re-activates the account.");
        }
        $user->forceFill(['can_login' => $activate, 'remember_token' => Str::random(60)])->save();

        $this->audit(
            $activate ? InstalmentPaymentAudit::STAFF_ACTIVATED : InstalmentPaymentAudit::STAFF_DEACTIVATED,
            $user, $request,
            ($activate ? 'Staff account activated: @' : 'Staff account deactivated: @') . $user->username,
            $user->roles->pluck('name')->intersect(array_keys(self::ROLES))->implode(',')
        );

        return back()->with('success', $activate
            ? "{$user->name} can log in again."
            : "{$user->name} is deactivated and can no longer use the portal.");
    }

    public function changePassword(Request $request, User $user): RedirectResponse
    {
        $this->ensureManageableStaff($user);

        $request->validate(['password' => 'required|string|min:8|confirmed']);

        $user->forceFill(['password' => Hash::make($request->password), 'remember_token' => Str::random(60)])->save();

        // Never log the password itself.
        $this->audit(InstalmentPaymentAudit::STAFF_PASSWORD, $user, $request, "Password changed for staff account @{$user->username}",
            $user->roles->pluck('name')->intersect(array_keys(self::ROLES))->implode(','));

        return back()->with('success', "Password changed for {$user->name}.");
    }

    /** Only staff accounts (not admins, not yourself) can be managed from this page. */
    private function ensureManageableStaff(User $user): void
    {
        abort_unless($user->hasAnyRole(array_keys(self::ROLES)) || $user->is_staff_account, 404);
        abort_if($user->hasRole('admin') || (int) $user->id === (int) Auth::id(), 403, 'Admin accounts cannot be changed from Staff Roles.');
    }

    public function remove(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role' => 'nullable|in:' . implode(',', array_keys(self::ROLES))]);
        $role  = $request->input('role', 'office-admin');
        $label = self::ROLES[$role];

        if ($user->hasRole($role)) {
            $user->removeRole($role);
            $this->audit(InstalmentPaymentAudit::ROLE_REMOVED, $user, $request, "{$label} role removed from @{$user->username}", $role);
        }

        // A staff-only account with no role left must not fall back to the member portal.
        if ($user->fresh()->isStaffWithoutAccess() && $user->can_login) {
            $user->forceFill(['can_login' => false, 'remember_token' => Str::random(60)])->save();
            $this->audit(InstalmentPaymentAudit::STAFF_DEACTIVATED, $user, $request,
                "Staff account automatically deactivated (no role left): @{$user->username}", $role);

            return back()->with('success', "{$label} access removed from {$user->name}. The account has no role left, so it was deactivated and logged out.");
        }

        return back()->with('success', "{$label} access removed from {$user->name}.");
    }

    private function audit(string $action, User $user, Request $request, string $description, string $role): void
    {
        InstalmentPaymentAudit::create([
            'action'      => $action,
            'actor_id'    => Auth::id(),
            'user_id'     => $user->id,
            'description' => $description,
            'details'     => ['role' => $role],
            'ip_address'  => $request->ip(),
        ]);
    }
}
