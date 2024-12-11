<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class FrontEndController extends Controller
{
    public function index(){
        $endDate = Carbon::now()->addMonth()->startOfMonth()->addDays(4)->setTime(12, 0, 0);
        return view('demo.index',compact('endDate'));
    }
    public function dashboard(){
        return view('demo.dashboard');
    }
}
