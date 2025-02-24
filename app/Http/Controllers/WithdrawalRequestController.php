<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithDrawalequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestController extends Controller
{
    public function stor1e(Request $request)
    {
        $request->validate([ 
            'amount' => 'required|numeric|min:1',
            'review_notes' => 'string',
            'withdrawal_option' => 'required|in:usdt,bank,cash',
        ]); 
        
        if(!auth::user()->profile){
            return redirect()->back()->with('error','Please complete your profile first'); 
        }
        if($request->withdrawal_option == 'bank'){
            if(!auth::user()->profile->ibn_number){
                return redirect()->back()->with('error','Please complete your bank Details');
            }
        }
        if($request->withdrawal_option == 'usdt'){
            if(!auth::user()->profile->account_number){
                return redirect()->back()->with('error','Please add your USDT Address Details');
            }
        }
       
        $fee = $request->amount * 0.02;
        $finalAmount = $request->amount - $fee;

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
            'request_type' => $request->withdrawal_option,
            'review_notes' => $request->review_notes,
            'transfer_fee' => '1'
        ]); 
        return redirect()->route('wallets.online')->with('success', 'Withdrawal request submitted successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'review_notes' => 'string',
            'withdrawal_option' => 'required|in:usdt,bank,cash',
        ]);
        if (!auth::user()->profile) {
            return redirect()->back()->with('error', 'Please complete your profile first');
        }
        if ($request->withdrawal_option == 'bank' && !auth::user()->profile->ibn_number && !auth::user()->profile->bank_name &&!auth::user()->profile->account_title ) {
            return redirect()->back()->with('error', 'Please complete your bank details');
        }
        if ($request->withdrawal_option == 'usdt' && !auth::user()->profile->account_number) {
            return redirect()->back()->with('error', 'Please add your USDT Address Details');
        } 
        $actualAmount = $request->amount;
        $calculatedFee = ($request->withdrawal_option == 'bank') ? ($actualAmount * 0.02) : 0; 
        $withdrawableAmount = $actualAmount - $calculatedFee;  
        $onlineWalletSum = Wallet::where('wallet_type', 'online')
        ->where('user_id', Auth::id())
        ->sum('balance');
        if ($actualAmount > $onlineWalletSum) {
            return redirect()->back()->with('error', 'Insufficient balance in the online wallet.');
        }
        $remainingAmount = $actualAmount; 
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
            'profile_id' => auth::user()->profile->id,
            'wallet_type' => 'online',
            'amount' => $withdrawableAmount,
            'status' => 'pending',
            'request_type' => $request->withdrawal_option,
            'review_notes' => $request->review_notes,
            'transfer_fee' => $calculatedFee,
        ]);
        return redirect()->route('wallets.online')->with('success', 'Withdrawal request submitted successfully.');
    }


    public function memberTransfer(Request $request)
    {
        $request->validate([ 
            'amount' => 'required|numeric|min:1',
            'description' => 'string',
            'member_account'=> 'required',
            'wallet_type' =>'required|in:member_transfer'
        ]);        
        $recipient = User::where('username', $request->member_account)
                    ->orWhere('email', $request->member_account)
                    ->first();
        if (!$recipient) {
            return redirect()->back()->with('error', 'Recipient not exists');
        }
        
        $fee = $request->amount * 0.02;
        $finalAmount = $request->amount - $fee;

        $onlineWallet = Wallet::where('wallet_type', 'online')
        ->where('user_id', Auth::id());
        $onlineWalletSum = $onlineWallet->sum('balance'); 
        if ($request->amount > $onlineWalletSum) {
            return redirect()->back()->with('error','Insufficient balance in the online wallet.');
        }  
        $remainingAmount = $request->amount; // let say 10
        $onlineWallets = $onlineWallet->orderBy('id', 'asc')->get();   
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
        $wallet = Wallet::Create(
            [
                'user_id' => $recipient->id,
                'wallet_type' => 'online',
                'balance' => $request->amount,
                'direct_balance' => 0.00,
                'indirect_balance' => 0.00,
                'level' => '-',
                'wallet_from' => Auth::id(),
                'commission_type'=>'Member Transfer'
            ]
        );
        TransactionLog::create([
            'user_id' => Auth::id(),
            'from_wallet_type' => 'online',
            'to_wallet_type' => 'online',
            'amount' => $request->amount, 
            'final_amount' => $request->amount,
            'description' => 'An amount of ' . $request->amount . ' PV has been transferred to ' . $recipient->username . ' via member transfer.'
        ]);
        return redirect()->back()->with('success', 'Transfer successful');
    }
    

    public function requests(Request $request)
    {
        $withDrawsRequests = WithDrawalequest::orderby('created_at','desc')->get();
        return view('users.widthdrawls-request',compact('withDrawsRequests')); 
    }

    public function getWithdrawalRequest($id)
    {
        $setting = Setting::first();
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
            'bank_name' => $request->user->profile->bank_name ?? '--',
            'request_type' => $request->request_type ?? '--',
            'transfer_fee' => $request->transfer_fee,
            'current_usd' => $setting->usd,
            'payable_amount' => round(($request->amount * $setting->pv_amount),2  ),
            'withdraw_screenshot' => asset($request->getFirstMediaUrl('withdraw_screenshot')),
            
        ]);
    }

    public function updateWithdrawalRequest(Request $request){
        $request->validate([ 
            'request_id' => 'required|exists:with_drawalequests,id',
            'status' => 'string|in:approved,rejected', 
            'screenshot' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);
        $withDrawalequest = WithDrawalequest::with('user','user.profile')->find($request->request_id); 
        if($withDrawalequest->status != 'pending'){
            return redirect()->back()->with('error', 'Action Already Performed on this request.');
        }
        $withDrawalequest->status = $request->status;
        $withDrawalequest->save();
        if ($request->hasFile('screenshot')) { 
            $withDrawalequest->addMedia($request->file('screenshot'))
                ->toMediaCollection('withdraw_screenshot');
        }
        if($withDrawalequest->status = 'rejected'){
            $wallet = Wallet::where('user_id', $withDrawalequest->user->id)
            ->where('wallet_type', 'online') // Adjust the type if necessary
            ->first();
            if ($wallet) {
                $wallet->balance += $withDrawalequest->amount;
                $wallet->save();  
            } else {
                return redirect()->back()->with('error', 'Wallet not found.');
            }
        }
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
