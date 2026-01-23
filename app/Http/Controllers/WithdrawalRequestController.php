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
        $settings = Setting::first();
        $minWithdrawalLimit = $settings->min_withdrawal_limit ?? 25;

        $request->validate([
            'amount' => 'required|numeric|min:' . $minWithdrawalLimit,
            'review_notes' => 'string',
            'withdrawal_option' => 'required|in:usdt,bank,cash',
        ]);
        if (auth::user()->negative_pv > 0) {
            return redirect()->back()->with('error', 'Clear your negative points before withdrawal.'); 
        }
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

        // Calculate fee/discount based on withdrawal option
        $calculatedFee = 0;
        $withdrawableAmount = $actualAmount;

        if ($request->withdrawal_option == 'bank') {
            // Apply bank withdrawal fee
            $feePercent = $settings->bank_withdrawal_fee_percent ?? 2;
            $calculatedFee = $actualAmount * ($feePercent / 100);
            $withdrawableAmount = $actualAmount - $calculatedFee;
        } elseif ($request->withdrawal_option == 'cash') {
            // Apply cash withdrawal fee
            $feePercent = $settings->cash_withdrawal_fee_percent ?? 0;
            $calculatedFee = $actualAmount * ($feePercent / 100);
            $withdrawableAmount = $actualAmount - $calculatedFee;
        } elseif ($request->withdrawal_option == 'usdt') {
            // Apply USDT discount (negative fee means bonus)
            $discountPercent = $settings->usdt_withdrawal_discount_percent ?? 2;
            $calculatedFee = -($actualAmount * ($discountPercent / 100)); // Negative for discount
            $withdrawableAmount = $actualAmount + abs($calculatedFee); // Add discount
        }  
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
        $settings = Setting::first();
        $minMemberTransfer = $settings->min_member_transfer ?? 7;

        $request->validate([
            'amount' => 'required|numeric|min:' . $minMemberTransfer,
            'description' => 'required|string',
            'member_account'=> 'required',
            'wallet_type' =>'required|in:member_transfer'
        ]);       

        $sender = Auth::user(); 
        if ($sender->negative_pv > 0) {
            return redirect()->back()->with('error', 'Clear your negative points before withdrawal.'); 
        } 
        $recipient = User::where('username', $request->member_account)
                        ->orWhere('email', $request->member_account)
                        ->first();

        if (!$recipient) {
            return redirect()->back()->with('error', 'Recipient not exists');
        }  
        $amount = $request->amount;  
        $onlineWalletSum = Wallet::where('wallet_type', 'online')
                                ->where('user_id', $sender->id)
                                ->sum('balance');  
        if ($amount > $onlineWalletSum) {
            return redirect()->back()->with('error','Insufficient balance in the online wallet.');
        }   
        Wallet::create([
            'user_id' => $sender->id,
            'wallet_type' => 'online',
            'balance' => -$amount,   
            'transaction_type' => 'debit',
            'direct_balance' => 0.00,
            'indirect_balance' => 0.00,
            'level' => '-',
            'wallet_from' => $recipient->id,
            'commission_type' => 'Member Transfer',
            'description' => "Transferred {$amount} PV to {$recipient->username}",
            'total_earning' => 0, // not added to earnings
        ]); 
        Wallet::create([
            'user_id' => $recipient->id,
            'wallet_type' => 'online',
            'balance' => $amount,  
             'transaction_type' => 'credit',
            'direct_balance' => 0.00,
            'indirect_balance' => 0.00,
            'level' => '-',
            'wallet_from' => $sender->id,
            'commission_type' => 'Member Transfer',
            'description' => "Received {$amount} PV from {$sender->username}",
            'total_earning' => 0, // not added to earnings
        ]); 
        $this->logTransaction($sender->id,'online','online',-$amount,$amount, "You transferred {$amount} PV to {$recipient->username} via member transfer.");
        $this->logTransaction($recipient->id,'online','online',$amount,$amount, "You received {$amount} PV from {$sender->username} via member transfer."); 

        return redirect()->back()->with('success', "Funds transferred: {$amount} PV to {$recipient->username} via member transfer successfully.");
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
        if($withDrawalequest->status === 'rejected'){ 
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

    private function logTransaction($userId,$toAddress,$fromAddress,$amount, $finalAmount,$description)
    {
        TransactionLog::create([
            'user_id' => $userId,
            'from_wallet_type' => $toAddress,
            'to_wallet_type' => $fromAddress,
            'charge' =>0, 
            'amount' => $amount, 
            'final_amount' => $finalAmount,
            'description' => $description,
        ]);
    }


    



    
}
