<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WithDrawalequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([ 
            'amount' => 'required|numeric|min:1',
            'review_notes' => 'string',
        ]); 
        
        if(!auth::user()->profile){
            return redirect()->back()->with('error','Please complete your profile first'); 
        }
        if(!auth::user()->profile->account_title && !auth::user()->profile->account_number && !auth::user()->profile->branch_name){
            return redirect()->back()->with('error','Please complete your bank Details');
        }
        $onlineWalletSum = Wallet::where('wallet_type', 'online')
        ->where('user_id', Auth::id())
        ->sum('balance'); 

        if ($request->amount > $onlineWalletSum) {
            return redirect()->back()->with('error','Insufficient balance in the online wallet.');
        }  
        $remainingAmount = $request->amount; 
        $onlineWallets = Wallet::where('wallet_type', 'online')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'asc')
            ->get();
       
       
        foreach ($onlineWallets as $wallet) {
            if ($remainingAmount == 0) break; 
            if ($wallet->balance <= $remainingAmount) {
                $remainingAmount -= $wallet->balance;
                $wallet->update(['balance' => 0]);
            } else {
                $wallet->update(['balance' => $wallet->balance - $remainingAmount]);
                $remainingAmount = 0;
            }
        }   
     
        WithDrawalequest::create([
            'user_id' => Auth::id(),
            'profile_id'=> auth::user()->profile->id,
            'wallet_type' => 'online',
            'amount' => $request->amount,
            'status' => 'pending',
            'review_notes' => $request->review_notes
        ]); 
        return redirect()->route('wallets.online')->with('success', 'Withdrawal request submitted successfully.');
    }

    public function requests(Request $request)
    {
        $withDrawsRequests = WithDrawalequest::orderby('id','desc')->get();
        return view('users.widthdrawls-request',compact('withDrawsRequests')); 
    }

    public function getWithdrawalRequest($id)
    {
        $request = WithDrawalequest::with('user','user.profile')->find($id); 
        if (!$request) {
            return response()->json(['error' => 'Request not found'], 404);
        } 
        return response()->json([
            'id' => $request->id,
            'username' => $request->user->username,
            'amount' => $request->amount,
            'status' => $request->status,
            'created_at' => $request->created_at->format('Y-m-d H:i:s'),
            'account_title' => $request->user->profile->account_title ?? '--',
            'account_number' => $request->user->profile->account_number ?? '--',
            'ibn_number' =>$request->user->profile->ibn_number ?? '--',
            'branch_name' => $request->user->profile->branch_name ?? '--',
            'branch_code' => $request->user->profile->branch_code ?? '--',
            'bank_name' => $request->user->profile->bank_name ?? '--',
            
        ]);
    }

    public function updateWithdrawalRequest(Request $request){
        $request->validate([ 
            'request_id' => 'required|exists:with_drawalequests,id',
            'status' => 'string',
        ]); 
        $WithDrawalequest = WithDrawalequest::with('user','user.profile')->find($request->request_id); 
        $WithDrawalequest->status = $request->status;
        $WithDrawalequest->save();
        return redirect()->back()->with('success', 'Withdrawal request has been updated successfully.');
    }   


     


    public function delete(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:with_drawalequests,id',
        ]);

        DB::beginTransaction(); 
        try {
            // Fetch the withdrawal request
            $withdrawRequest = WithDrawalequest::find($request->request_id);
            if ($withdrawRequest->status != 'pending') {
                return redirect()->back()->with('error', 'Only pending requests can be deleted.');
            }
            // Update the wallet balance
            $wallet = Wallet::where('user_id', $withdrawRequest->user_id)
                            ->where('wallet_type', 'online') // Adjust the type if necessary
                            ->first();

            if ($wallet) {
                $wallet->balance += $withdrawRequest->amount;
                $wallet->save();
            } else {
                return redirect()->back()->with('error', 'Wallet not found.');
            }
            $withdrawRequest->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Withdrawal request deleted and amount returned to the wallet.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while processing the request.');
        }
    }


    



    
}
