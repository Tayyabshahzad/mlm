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
            'min_withdrawal_limit' => 'required|numeric|min:0',
            'min_member_transfer' => 'required|numeric|min:0',
            'min_wallet_transfer' => 'required|numeric|min:0',
            'bank_withdrawal_fee_percent' => 'required|numeric|min:0|max:100',
            'cash_withdrawal_fee_percent' => 'required|numeric|min:0|max:100',
            'usdt_withdrawal_discount_percent' => 'required|numeric|min:0|max:100',
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
            'min_withdrawal_limit' => $request->min_withdrawal_limit,
            'min_member_transfer' => $request->min_member_transfer,
            'min_wallet_transfer' => $request->min_wallet_transfer,
            'bank_withdrawal_fee_percent' => $request->bank_withdrawal_fee_percent,
            'cash_withdrawal_fee_percent' => $request->cash_withdrawal_fee_percent,
            'usdt_withdrawal_discount_percent' => $request->usdt_withdrawal_discount_percent,
        ]);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function updateUSDT(){
        Artisan::call('app:update-setting');
        $output = Artisan::output();
        return back()->with('status', $output);
    }

    public function savingSettings()
    {
        $setting = Setting::first();
        return view('setting.saving', compact('setting'));
    }

    public function updateSavingSettings(Request $request)
    {
        $request->validate([
            'saving_registration_fee'    => 'required|numeric|min:0',
            'saving_min_deposit'         => 'required|numeric|min:0',
            'saving_monthly_instalment'  => 'required|numeric|min:0',
            'saving_plan_months'         => 'required|integer|min:1',
            'saving_roi_daily_rate'      => 'required|numeric|min:0|max:100',
            'saving_commission_l1'       => 'required|numeric|min:0|max:100',
            'saving_commission_l2'       => 'required|numeric|min:0|max:100',
            'saving_commission_l3'       => 'required|numeric|min:0|max:100',
            'saving_commission_l4'       => 'required|numeric|min:0|max:100',
            'saving_commission_l5'       => 'required|numeric|min:0|max:100',
            'saving_commission_l6'       => 'required|numeric|min:0|max:100',
            'saving_commission_l7'       => 'required|numeric|min:0|max:100',
        ]);

        $setting = Setting::first();
        $setting->update([
            'saving_registration_fee'   => $request->saving_registration_fee,
            'saving_min_deposit'        => $request->saving_min_deposit,
            'saving_monthly_instalment' => $request->saving_monthly_instalment,
            'saving_plan_months'        => $request->saving_plan_months,
            'saving_roi_daily_rate'     => $request->saving_roi_daily_rate,
            'saving_commission_l1'      => $request->saving_commission_l1,
            'saving_commission_l2'      => $request->saving_commission_l2,
            'saving_commission_l3'      => $request->saving_commission_l3,
            'saving_commission_l4'      => $request->saving_commission_l4,
            'saving_commission_l5'      => $request->saving_commission_l5,
            'saving_commission_l6'      => $request->saving_commission_l6,
            'saving_commission_l7'      => $request->saving_commission_l7,
        ]);

        return back()->with('success', 'Saving account settings updated successfully.');
    }
}
