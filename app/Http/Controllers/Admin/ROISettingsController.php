<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Week;
use App\Models\User;
use Illuminate\Http\Request;

class ROISettingsController extends Controller
{
    /**
     * Show ROI percentage settings (VIP and Standard)
     */
    public function index()
    {
        $week = Week::first();

        return view('admin.roi-settings.index', compact('week'));
    }

    /**
     * Update ROI percentages for both VIP and Standard plans
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'standard_percentage' => 'required|numeric|min:0|max:100',
            'vip_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $week = Week::first();

        if (!$week) {
            return back()->with('error', 'Week configuration not found!');
        }

        $week->update([
            'standard_percentage' => $validated['standard_percentage'],
            'vip_percentage' => $validated['vip_percentage'],
        ]);

        return back()->with('success', 'ROI percentages updated successfully!');
    }

    /**
     * Show user plan assignment page
     */
    public function userPlans()
    {
        $users = User::select('id', 'name', 'username', 'email', 'user_plan', 'roi_eligible_investment_amount')
            ->whereNotNull('roi_eligible_investment_amount')
            ->where('roi_eligible_investment_amount', '>', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Get accurate statistics for all users (not just paginated)
        $totalUsers = User::whereNotNull('roi_eligible_investment_amount')
            ->where('roi_eligible_investment_amount', '>', 0)
            ->count();

        $standardCount = User::whereNotNull('roi_eligible_investment_amount')
            ->where('roi_eligible_investment_amount', '>', 0)
            ->where('user_plan', 'standard')
            ->count();

        $vipCount = User::whereNotNull('roi_eligible_investment_amount')
            ->where('roi_eligible_investment_amount', '>', 0)
            ->where('user_plan', 'vip')
            ->count();

        return view('admin.roi-settings.user-plans', compact('users', 'totalUsers', 'standardCount', 'vipCount'));
    }

    /**
     * Update user's plan (VIP or Standard)
     */
    public function updateUserPlan(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'user_plan' => 'required|in:standard,vip',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->update(['user_plan' => $validated['user_plan']]);

        return back()->with('success', "User plan updated to {$validated['user_plan']} successfully!");
    }

    /**
     * Bulk migrate all users to appropriate plans based on their investment amount
     */
    public function bulkMigrateUsers(Request $request)
    {
        $setting = \App\Models\Setting::first();
        if (!$setting) {
            return back()->with('error', 'Settings not found! Please configure package ranges first.');
        }

        $standardMin = $setting->standard_package_min ?? 50;
        $standardMax = $setting->standard_package_max ?? 344;
        $vipMin = $setting->vip_package_min ?? 345;

        // Get all users with investments
        $users = User::whereNotNull('roi_eligible_investment_amount')
            ->where('roi_eligible_investment_amount', '>', 0)
            ->get();

        $migratedCount = 0;
        $upgradedToVip = 0;
        $downgradeToStandard = 0;
        $noChange = 0;

        foreach ($users as $user) {
            $investment = $user->roi_eligible_investment_amount;
            $currentPlan = $user->user_plan ?? 'standard';

            // Determine new plan based on investment
            $newPlan = 'standard';
            if ($investment >= $vipMin) {
                $newPlan = 'vip';
            } elseif ($investment >= $standardMin && $investment <= $standardMax) {
                $newPlan = 'standard';
            }

            // Update if plan changed
            if ($currentPlan !== $newPlan) {
                $user->update(['user_plan' => $newPlan]);
                $migratedCount++;

                if ($newPlan === 'vip' && $currentPlan === 'standard') {
                    $upgradedToVip++;
                } elseif ($newPlan === 'standard' && $currentPlan === 'vip') {
                    $downgradeToStandard++;
                }

                \Log::info("User plan migrated automatically", [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'previous_plan' => $currentPlan,
                    'new_plan' => $newPlan,
                    'investment_amount' => $investment,
                    'admin_id' => auth()->id()
                ]);
            } else {
                $noChange++;
            }
        }

        $message = "Migration completed! Total users processed: {$users->count()}. ";
        $message .= "Migrated: {$migratedCount} (Upgraded to VIP: {$upgradedToVip}, Downgraded to Standard: {$downgradeToStandard}, No change: {$noChange})";

        return back()->with('success', $message);
    }

    /**
     * Assign a specific plan to ALL users
     */
    public function assignAllUsers(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:standard,vip',
        ]);

        $plan = $validated['plan'];

        // Get all users with investments
        $users = User::whereNotNull('roi_eligible_investment_amount')
            ->where('roi_eligible_investment_amount', '>', 0)
            ->get();

        $updatedCount = 0;

        foreach ($users as $user) {
            $user->update(['user_plan' => $plan]);
            $updatedCount++;

            \Log::info("User plan assigned to all users", [
                'user_id' => $user->id,
                'username' => $user->username,
                'assigned_plan' => $plan,
                'admin_id' => auth()->id()
            ]);
        }

        $planLabel = ucfirst($plan);
        $message = "Successfully assigned {$planLabel} plan to {$updatedCount} users!";

        return back()->with('success', $message);
    }

    /**
     * Assign a plan to selected users
     */
    public function bulkAssignSelected(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|json',
            'plan' => 'required|in:standard,vip',
        ]);

        $userIds = json_decode($validated['user_ids'], true);
        $plan = $validated['plan'];

        if (empty($userIds)) {
            return back()->with('error', 'No users selected!');
        }

        $updatedCount = 0;

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->update(['user_plan' => $plan]);
                $updatedCount++;

                \Log::info("User plan assigned via bulk selection", [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'assigned_plan' => $plan,
                    'admin_id' => auth()->id()
                ]);
            }
        }

        $planLabel = ucfirst($plan);
        $message = "Successfully assigned {$planLabel} plan to {$updatedCount} selected user(s)!";

        return back()->with('success', $message);
    }
}
