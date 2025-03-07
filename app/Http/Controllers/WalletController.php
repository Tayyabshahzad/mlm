<?php

namespace App\Http\Controllers;

use App\Models\TransactionLog;
use App\Models\Wallet;
use App\Models\WithDrawalequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function online(){ 
        $onlineWallets = Wallet::where('wallet_type','online')->where('user_id',auth()->user()->id)->get();
        $withDrawsRequests = WithDrawalequest::where('user_id',auth()->user()->id)->orderby('id','desc')->get();
        $walletSum = Wallet::where('user_id',auth()->user()->id)->sum('balance');
        return view('wallets.online',compact('onlineWallets','withDrawsRequests','walletSum')); 
    }

    public function directIndirect(){ 
        $wallets = Wallet::where('wallet_type','direct_indirect')->where('user_id',auth()->user()->id)->orderBy('level','asc')->get();
        return view('wallets.direct-indirect',compact('wallets')); 
    }

    public function rewards(){ 
        $rewards = Wallet::where('wallet_type','reward')->where('user_id',auth()->user()->id)->get();
        return view('wallets.reward',compact('rewards')); 
    }

    public function ROI(){ 
        $payments = Wallet::where('wallet_type','roi')->where('user_id',auth()->user()->id)->get();
        return view('wallets.roi',compact('payments')); 
    }

    public function profitShare(){ 
        $profits = Wallet::where('wallet_type','profit_share')->where('user_id',auth()->user()->id)->get();
        return view('wallets.profit-share',compact('profits')); 
    }
    public function rank(){ 
        return view('wallets.rank'); 
    }
    public function transferToOnline(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:7',
            'wallet_type' => 'required',
        ]); 
        $userId = auth()->id();  
        $amountToTransfer = $request->input('amount');  
        if($request->wallet_type == 'reward-wallet'){
            $wallet_type = 'reward';
        }elseif($request->wallet_type == 'direct_indirect'){
            $wallet_type = 'direct_indirect';
        }elseif($request->wallet_type == 'roi_wallet'){
            $wallet_type = 'roi';
        }
        $wallets = Wallet::where('user_id', $userId)
                    ->where('wallet_type', $wallet_type)
                    ->get();  
        $totalBalance = $wallets->sum('balance'); 

        if ($totalBalance < $amountToTransfer) {
            return redirect()->back()->with('error', 'Insufficient balance in your '.$wallet_type.' wallet.');
        }  
        $chargePercentage = 5;
        $chargeAmount = ($chargePercentage / 100) * $amountToTransfer;  
        $finalTransferAmount = $amountToTransfer - $chargeAmount; 
        if ($finalTransferAmount <= 0) {
            return redirect()->back()->with('error', 'Transfer amount is too small after applying charges.');
        }  
        $remainingAmount = $amountToTransfer; 
        foreach ($wallets as $wallet) {
            if ($remainingAmount <= 0) break; 
            $deductAmount = min($wallet->balance, $remainingAmount); // Deduct either the full wallet balance or the remaining amount
            $wallet->balance -= $deductAmount;
            $wallet->save(); 
            $remainingAmount -= $deductAmount;
        } 
        $onlineWallet = Wallet::firstOrCreate(
            ['user_id' => $userId, 'wallet_type' => 'online' , 'commission_type' => 'online','level' => 'online'],
            ['balance' => 0.00]
        );
    
        // Add the transferred amount (after charges) to the online wallet
        $onlineWallet->balance += $finalTransferAmount;
        $onlineWallet->save(); 
        $userName = Auth::user()->username;
        $this->logTransaction( 
            $userId,
            'online',
            $wallet_type,
            $amountToTransfer,
            $finalTransferAmount,
            "You received {$finalTransferAmount} PV to {$userName} via member transfer.",
            'debit',
            $chargeAmount
        ); 
        $this->logTransaction( 
            $userId, 
            $wallet_type,
            'online',
            $amountToTransfer,
            $finalTransferAmount,
            "You transferred {$finalTransferAmount} PV to {$userName} via member transfer.", 
            'credit',
            $chargeAmount
        ); 
        return redirect()->back()->with('success', "Funds transferred to Online Wallet! Transfer Amount: $amountToTransfer PV, Charge: $chargeAmount PV, Final Transferred: $finalTransferAmount PV");
    }


    public function showTransactionHistory()
    {
        $userId = auth()->id();
        $transactions = TransactionLog::where('user_id', $userId)->orderBy('created_at','desc')->latest()->paginate(10); 
        return view('wallets.transaction-history', compact('transactions'));
    }
    private function logTransaction($userId,$toAddress,$fromAddress,$amount, $finalAmount,$description,$status,$chargeAmount)
    {
        TransactionLog::create([
            'user_id' => $userId,
            'to_wallet_type' => $toAddress,
            'from_wallet_type' => $fromAddress, 
            'charge' =>$chargeAmount, 
            'amount' => $amount, 
            'final_amount' => $finalAmount,
            'description' => $description,
            'status' =>$status
        ]);
    }



    

}
