<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
class SettingController extends Controller
{
    public function index(){
        $setting = Setting::first();
        return view('setting.index',compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'pv_amount' => 'required|numeric',
            'description' => 'required|string',
        ]);

        $setting = Setting::find($request->id);
        if (!$setting) {
            return redirect()->back()->with('error', 'Setting not found.');
        }  
        $response = Http::withOptions([
            'verify' => false, // Disables SSL verification
        ])->get('https://api.currencyfreaks.com/v2.0/rates/latest', [
            'apikey' => '911275d23aa24a51a37d66ed3eae27d2',
        ]); 
        
        if ($response->successful()) { 
            $data = $response->json(); 
            $usdToPkrRate = $data['rates']['PKR'];    
        }  

        $setting->update([
            'site_name' => $request->site_name,
            'pv_amount' => $request->pv_amount,
            'description' => $request->description,
            'usd' => $usdToPkrRate,  
        ]);  

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
