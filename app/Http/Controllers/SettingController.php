<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
class SettingController extends Controller
{
    public function index(){
        $setting = Setting::first();
        $walletSettings = json_decode($setting->blocked_wallets, true);
        return view('setting.index',compact('setting','walletSettings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'pv_amount' => 'required|numeric',
            'description' => 'required|string',
            'activation_code' => 'required|numeric',
            'withdraw_block' => 'required|boolean',
            'registration_fee' => 'required|numeric',
            'block_wallet' => 'nullable|array',
            'standard_package_min' => 'required|numeric|min:0',
            'standard_package_max' => 'required|numeric|min:0|gt:standard_package_min',
            'vip_package_min' => 'required|numeric|min:0|gte:standard_package_max',
        ]);

        $setting = Setting::find($request->id);
        if (!$setting) {
            return redirect()->back()->with('error', 'Setting not found.');
        }
        $blockWallets = $request->input('block_wallet', []);
        $setting->update([
            'site_name' => $request->site_name,
            'pv_amount' => $request->pv_amount,
            'description' => $request->description,
            'activation_code' =>$request->activation_code,
            'withdraw_block' =>$request->withdraw_block,
            'registration_fee' =>$request->registration_fee,
            'blocked_wallets' => json_encode($blockWallets),
            'standard_package_min' => $request->standard_package_min,
            'standard_package_max' => $request->standard_package_max,
            'vip_package_min' => $request->vip_package_min,
        ]);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function updateUSDT(){
        Artisan::call('app:update-setting');
        $output = Artisan::output();
        return back()->with('status', $output);
    }

    
}
