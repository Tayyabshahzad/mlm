<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RewardSettingsController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Display reward settings dashboard
     */
    public function index()
    {
        $rewardLevels = RewardSetting::getLevelsWithStats();
        
        // Calculate overview statistics
        $totalRewardsPaid = $rewardLevels->sum('total_amount_paid');
        $totalUsersRewarded = $rewardLevels->sum('rewards_paid_count');
        $totalPendingRewards = $rewardLevels->sum('pending_count');
        $activelevelsCount = $rewardLevels->where('is_active', true)->count();
        
        return view('admin.reward-settings.index', compact(
            'rewardLevels',
            'totalRewardsPaid',
            'totalUsersRewarded', 
            'totalPendingRewards',
            'activelevelsCount'
        ));
    }

    /**
     * Show form to edit specific level
     */
    public function edit(RewardSetting $rewardSetting)
    {
        return view('admin.reward-settings.edit', compact('rewardSetting'));
    }

    /**
     * Update reward level settings
     */
    public function update(Request $request, RewardSetting $rewardSetting)
    {
        $rules = RewardSetting::validationRules();
        $rules['level'] = 'required|integer|min:1|max:10|unique:reward_settings,level,' . $rewardSetting->id;
        
        $request->validate($rules);

        try {
            $rewardSetting->update([
                'level' => $request->level,
                'reward_amount' => $request->reward_amount,
                'users_required' => $request->users_required,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info("Reward level {$rewardSetting->level} updated by admin", [
                'admin_id' => auth()->id(),
                'changes' => $rewardSetting->getChanges()
            ]);

            return redirect()->route('admin.reward-settings.index')
                ->with('success', "Level {$rewardSetting->level} settings updated successfully!");

        } catch (\Exception $e) {
            Log::error("Failed to update reward level {$rewardSetting->level}: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update reward settings: ' . $e->getMessage());
        }
    }

    /**
     * Show form to create new level
     */
    public function create()
    {
        return view('admin.reward-settings.create');
    }

    /**
     * Store new reward level
     */
    public function store(Request $request)
    {
        $request->validate(RewardSetting::validationRules());

        try {
            $rewardSetting = RewardSetting::create([
                'level' => $request->level,
                'reward_amount' => $request->reward_amount,
                'users_required' => $request->users_required,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            Log::info("New reward level {$rewardSetting->level} created by admin", [
                'admin_id' => auth()->id(),
                'settings' => $rewardSetting->toArray()
            ]);

            return redirect()->route('admin.reward-settings.index')
                ->with('success', "Level {$rewardSetting->level} created successfully!");

        } catch (\Exception $e) {
            Log::error("Failed to create reward level: " . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create reward level: ' . $e->getMessage());
        }
    }

    /**
     * Toggle level active status
     */
    public function toggleStatus(RewardSetting $rewardSetting)
    {
        try {
            $rewardSetting->is_active = !$rewardSetting->is_active;
            $rewardSetting->save();

            $status = $rewardSetting->is_active ? 'activated' : 'deactivated';
            
            Log::info("Reward level {$rewardSetting->level} {$status} by admin", [
                'admin_id' => auth()->id()
            ]);

            return redirect()->back()
                ->with('success', "Level {$rewardSetting->level} has been {$status}!");

        } catch (\Exception $e) {
            Log::error("Failed to toggle reward level status: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update level status: ' . $e->getMessage());
        }
    }

    /**
     * Delete reward level (with confirmation)
     */
    public function destroy(RewardSetting $rewardSetting)
    {
        try {
            // Check if there are existing rewards for this level
            $existingRewards = \DB::table('wallets')
                ->where('wallet_type', 'reward')
                ->where('level', $rewardSetting->level)
                ->where('balance', '>', 0)
                ->count();

            $pendingRewards = \DB::table('pending_rewards')
                ->where('level', $rewardSetting->level)
                ->count();

            if ($existingRewards > 0 || $pendingRewards > 0) {
                return redirect()->back()
                    ->with('error', "Cannot delete Level {$rewardSetting->level}: {$existingRewards} existing rewards and {$pendingRewards} pending rewards found. Deactivate instead.");
            }

            $level = $rewardSetting->level;
            $rewardSetting->delete();

            Log::warning("Reward level {$level} deleted by admin", [
                'admin_id' => auth()->id()
            ]);

            return redirect()->route('admin.reward-settings.index')
                ->with('success', "Level {$level} deleted successfully!");

        } catch (\Exception $e) {
            Log::error("Failed to delete reward level: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete reward level: ' . $e->getMessage());
        }
    }

    /**
     * Reset to default settings
     */
    public function resetToDefaults()
    {
        try {
            RewardSetting::seedDefaults();

            Log::info("Reward settings reset to defaults by admin", [
                'admin_id' => auth()->id()
            ]);

            return redirect()->route('admin.reward-settings.index')
                ->with('success', 'Reward settings have been reset to default values!');

        } catch (\Exception $e) {
            Log::error("Failed to reset reward settings: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to reset settings: ' . $e->getMessage());
        }
    }

    /**
     * Export settings as JSON
     */
    public function export()
    {
        try {
            $settings = RewardSetting::orderBy('level')->get();
            
            $exportData = [
                'exported_at' => now()->toISOString(),
                'exported_by' => auth()->user()->name,
                'reward_settings' => $settings->toArray()
            ];

            $fileName = 'reward_settings_' . date('Y-m-d_H-i-s') . '.json';
            
            return response()->json($exportData)
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            Log::error("Failed to export reward settings: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to export settings: ' . $e->getMessage());
        }
    }
}
