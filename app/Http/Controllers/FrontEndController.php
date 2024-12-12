<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FrontEndController extends Controller
{
    public function index(){
        $endDate = Carbon::now()->addMonth()->startOfMonth()->addDays(4)->setTime(12, 0, 0);
        return view('demo.index',compact('endDate'));
    }
    public function emailSubmit(Request $request){
        $request->validate([
            'email' => 'required|email|unique:subscriptions,email',
        ]);
        Subscription::create([
            'email' => $request->email,
        ]);
        return redirect()->back()->with('success', 'Thank you for subscribing!');
    }
    public function dashboard(){
        return view('demo.dashboard');
    }
}
