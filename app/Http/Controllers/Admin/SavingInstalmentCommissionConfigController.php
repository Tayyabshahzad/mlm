<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingInstalmentCommissionConfig;
use App\Models\Setting;
use Illuminate\Http\Request;

class SavingInstalmentCommissionConfigController extends Controller
{
    public function index()
    {
        $setting = Setting::first();

        // Default rates fallback (from settings or hardcoded)
        $defaults = [];
        for ($level = 1; $level <= 7; $level++) {
            $field = "saving_commission_l{$level}";
            $defaults[$level] = $setting?->$field ?? [1=>7,2=>2,3=>1,4=>1,5=>1,6=>1,7=>1][$level];
        }

        // Load all configs keyed by [instalment][level]
        $configs = SavingInstalmentCommissionConfig::all()
            ->groupBy('instalment_number')
            ->map(fn($rows) => $rows->keyBy('level'));

        $totalCustom = SavingInstalmentCommissionConfig::count();

        return view('admin.saving.instalment-commission-config', compact('configs', 'defaults', 'setting', 'totalCustom'));
    }

    public function updateLevel(Request $request)
    {
        $request->validate([
            'instalment_number' => 'required|integer|between:1,25',
            'level'             => 'required|integer|between:1,7',
        ]);

        if ($request->input('action') === 'reset') {
            SavingInstalmentCommissionConfig::where('instalment_number', $request->instalment_number)
                ->where('level', $request->level)
                ->delete();
            return back()->with('success', "Instalment #{$request->instalment_number} Level {$request->level} reset to default.");
        }

        $request->validate(['percentage' => 'required|numeric|min:0|max:100']);

        SavingInstalmentCommissionConfig::updateOrCreate(
            [
                'instalment_number' => $request->instalment_number,
                'level'             => $request->level,
            ],
            ['percentage' => $request->percentage]
        );

        return back()->with('success', "Instalment #{$request->instalment_number} Level {$request->level} set to {$request->percentage}%.");
    }

    public function deleteLevel(Request $request)
    {
        $request->validate([
            'instalment_number' => 'required|integer|between:1,25',
            'level'             => 'required|integer|between:1,7',
        ]);

        SavingInstalmentCommissionConfig::where('instalment_number', $request->instalment_number)
            ->where('level', $request->level)
            ->delete();

        return back()->with('success', "Instalment #{$request->instalment_number} Level {$request->level} reset to default.");
    }

    public function deleteInstalment(int $instalmentNumber)
    {
        SavingInstalmentCommissionConfig::where('instalment_number', $instalmentNumber)->delete();
        return back()->with('success', "All custom rates for Instalment #{$instalmentNumber} have been cleared.");
    }

    public function toggleInstalmentConfig(Request $request)
    {
        $setting = Setting::first();
        $enabled = $request->boolean('saving_instalment_config_enabled');
        $setting->update(['saving_instalment_config_enabled' => $enabled]);
        return back()->with('success', 'Custom instalment commission rates ' . ($enabled ? 'ENABLED — custom rates are now active.' : 'DISABLED — default rates are now active.'));
    }
}
