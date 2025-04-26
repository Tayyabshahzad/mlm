<?php

namespace App\Http\Controllers;

use App\Models\InvestmentSlab;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\UserInvestment;
use App\Models\Wallet;
use App\Models\WithDrawalequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{

    protected $setting;
    public function __construct()
    {
        $this->setting = Setting::first();
        view()->share('setting', $this->setting); // Share with all views
    }


    public function online(){ 
        $onlineWallets = Wallet::where('wallet_type','online')->where('user_id',auth()->user()->id)->get();
        $withDrawsRequests = WithDrawalequest::where('user_id',auth()->user()->id)->orderby('id','desc')->get();
        $walletSum = Wallet::where('user_id',auth()->user()->id)->sum('balance');
        $setting = Setting::first();
        return view('wallets.online',compact('onlineWallets','withDrawsRequests','walletSum','setting')); 
    }

    public function directIndirect(){ 
        $wallets = Wallet::where('wallet_type','direct_indirect')->where('user_id',auth()->user()->id)
        ->orderBy('id', 'desc')
        ->orderBy('level','asc')
        ->get();
        return view('wallets.direct-indirect',compact('wallets')); 
    }

    public function rewards(){ 
        $wallets = Wallet::where('wallet_type','reward')->where('user_id',auth()->user()->id)->get();
        return view('wallets.reward',compact('wallets')); 
    }

    public function ROI(){ 
        $wallets = Wallet::where('wallet_type','roi')->where('user_id',auth()->user()->id)->get();
        return view('wallets.roi',compact('wallets')); 
    }

    public function profitShare(){ 
        $wallets = Wallet::where('wallet_type','profit_share')->where('user_id',auth()->user()->id)->get();
        return view('wallets.profit-share',compact('wallets')); 
    }
    public function rank(){ 
        return view('wallets.rank'); 
    }

    public function transferToOnline(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5.27',
            'wallet_type' => 'required|in:direct_indirect,reward,roi,profit_share',
        ]);  
        $userId = auth()->id();  
        $amountToTransfer = $request->input('amount');  
        if ($request->wallet_type == 'reward-wallet') {
            $wallet_type = 'reward';
        } elseif ($request->wallet_type == 'direct_indirect') {
            $wallet_type = 'direct_indirect';
        } elseif ($request->wallet_type == 'roi') {
            $wallet_type = 'roi';
        } else {
            $wallet_type = $request->wallet_type;
        }

        $wallets = Wallet::where('user_id', $userId)
                    ->where('wallet_type', $wallet_type)
                    ->get();  

        $totalBalance = $wallets->sum('balance'); 

        if ($totalBalance < $amountToTransfer) {
            return redirect()->back()->with('error', 'Insufficient balance in your '.$wallet_type.' wallet.');
        }  

        DB::beginTransaction(); // Start DB transaction
        try {
            $chargePercentage = 5;
            $chargeAmount = ($chargePercentage / 100) * $amountToTransfer;  
            $finalTransferAmount = $amountToTransfer - $chargeAmount; 

            if ($finalTransferAmount <= 0) {
                throw new \Exception('Transfer amount is too small after applying charges.');
            }  

            $remainingAmount = $amountToTransfer; 
            foreach ($wallets as $wallet) {
                if ($remainingAmount <= 0) break; 
                $deductAmount = min($wallet->balance, $remainingAmount);
                $wallet->balance -= $deductAmount;
                $wallet->save(); 
                $remainingAmount -= $deductAmount;
            } 

            $onlineWallet = Wallet::firstOrCreate(
                ['user_id' => $userId, 'wallet_type' => 'online', 'commission_type' => 'online', 'level' => 'online'],
                ['balance' => 0.00]
            );

            $onlineWallet->balance += $finalTransferAmount;
            $onlineWallet->save(); 

            $userName = Auth::user()->username;

            $this->logTransaction( 
                $userId,
                'online',
                $wallet_type,
                $amountToTransfer,
                $finalTransferAmount,
                "You transferred {$amountToTransfer} PV (5% charge applied) from your {$wallet_type} Wallet to your Online Wallet via self-transfer.",  
                'debit',
                $chargeAmount
            ); 

            $this->logTransaction( 
                $userId, 
                $wallet_type,
                'online',
                $amountToTransfer,
                $finalTransferAmount,
                "You have received {$finalTransferAmount} PV in your Online Wallet from your {$wallet_type} Wallet via online transfer.", 
                'credit',
                $chargeAmount
            ); 

            DB::commit(); // Commit all changes
            return redirect()->back()->with('success', "Funds transferred to Online Wallet! Transfer Amount: $amountToTransfer PV, Charge: $chargeAmount PV, Final Transferred: $finalTransferAmount PV");

        } catch (\Exception $e) {
            DB::rollBack(); // Revert all changes if anything fails
            return redirect()->back()->with('error', 'Transaction failed: ' . $e->getMessage());
        }
    }

 
    public function investment(){  
        $investmentQuery = UserInvestment::where('user_id', auth()->id());

        $lastInvest = (clone $investmentQuery)->orderBy('id', 'desc')->first();
        $firstInvest = (clone $investmentQuery)->orderBy('id', 'asc')->first();
        $totalInvest = (clone $investmentQuery)->sum('amount');
        $investments = (clone $investmentQuery)->get(); 
        return view('wallets.investment',compact('lastInvest','firstInvest','totalInvest','investments')); 
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

    public function accountTopUp(Request $request)
    {
        $user = Auth::user();
        $wallets = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'online')
            ->get();
        $totalBalance = $wallets->sum('balance');
        $request->validate([
            'topUp_amount' => ['required', 'numeric', "min:5","max:$totalBalance"],
            'topUp_description' => 'required',
        ]);
        $amountToTransfer = $request->input('topUp_amount');
        $description = $request->input('topUp_description');
        if ($totalBalance < $amountToTransfer) {
            return redirect()->back()->with('error', 'Insufficient balance in your online wallet.');
        }
        try {
            DB::beginTransaction();
            $remainingAmount = $amountToTransfer;
            foreach ($wallets as $wallet) {
                if ($remainingAmount <= 0) break;
                $deductAmount = min($wallet->balance, $remainingAmount);
                $wallet->balance -= $deductAmount;
                $wallet->save();
                $remainingAmount -= $deductAmount;
            }
            $this->logTransaction($user->id,'investment','online',$amountToTransfer,$amountToTransfer,"You topped up your account with {$amountToTransfer} $ from your Online Wallet.",
                'debit',0);
            $this->createInitialInvestment($user,$amountToTransfer,$description);
            $this->checkAndCreateSlabs($user);
            DB::commit();

            return redirect()->back()->with('success', "Your Account has been topped up with {$amountToTransfer} $");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Something went wrong during top-up: ' . $e->getMessage());
        }
    }



    

    protected function createInitialInvestment(User $user, $amountToTransfer,$description): void
    {
        UserInvestment::create([
            'user_id' => $user->id,
            'amount' => $amountToTransfer,
            'type' => 'topup',
            'description' => $description
        ]); 
        $user->increment('roi_eligible_investment_amount', $amountToTransfer);
    }

    private function checkAndCreateSlabs($user)
    {
        $totalInvestment = UserInvestment::where('user_id', $user->id)->sum('amount');
        $totalSlabs = InvestmentSlab::where('user_id', $user->id)->count(); 
        $availableInvestment = $totalInvestment - ($totalSlabs * 100);
        while ($availableInvestment >= 100) {
            $achievedAt = now();
            $willPayAt = $achievedAt->copy()->addMonths(24)->startOfMonth()->addMonth();
            
            $newSlab = new InvestmentSlab();
            $newSlab->user_id = $user->id;
            $newSlab->slab_count = $totalSlabs + 1;
            $newSlab->amount = 100;
            $newSlab->achived_at = now();
            $newSlab->current_balance = $user->roi_eligible_investment_amount; 
            $newSlab->maturity_date = now()->addMonths(24);
            $newSlab->will_pay_at = $willPayAt;
            $newSlab->status = 'No';
            $newSlab->save();
            $totalSlabs++;
            $availableInvestment -= 100;
        }
    }

    public function clearNegativePoints(Request $request)
    {
        try {
            $user = auth()->user(); 
            if ($user->negative_pv <= 0) {
                return back()->with('info', 'You have no negative points to clear.');
            } 
            $totalBalance = $this->getUserWalletBalance($user->id);

            if ($totalBalance < $user->negative_pv) {
                return back()->with('error', 'Insufficient wallet balance to clear your negative points.');
            } 
            DB::transaction(function () use ($user) {
                $deductedAmount = $this->deductFromWallets($user); 
                $user->update(['negative_pv' => 0]); 
                $this->logTransaction(
                    $user->id,
                    'negative_clearance',
                    'online',
                     $deductedAmount,
                    $deductedAmount, 
                    "You cleared {$deductedAmount} PV of negative points from your Online Wallet.",
                    'credit', 
                    0
                ); 
                
            });

            return back()->with('success', 'Negative points cleared successfully from your online wallet.');

        } catch (\Exception $e) {
            Log::error('Error clearing negative points for user ID: ' . auth()->id(), [
                'message' => $e->getMessage()
            ]);
            dd($e);

            return back()->with('error', 'Something went wrong while clearing negative points. Please try again later.');
        }
    }

    private function getUserWalletBalance($userId)
    {
        return Wallet::where('user_id', $userId)
            ->where('wallet_type', 'online')
            ->sum('balance');
    }

    private function deductFromWallets($user)
    {
        $remaining = $user->negative_pv;
        $deducted = 0;

        Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'online')
            ->orderBy('id')
            ->chunk(50, function ($wallets) use (&$remaining, &$deducted) {
                foreach ($wallets as $wallet) {
                    if ($remaining <= 0) break;

                    $deduct = min($wallet->balance, $remaining);
                    $wallet->balance -= $deduct;
                    $wallet->save();

                    $remaining -= $deduct;
                    $deducted += $deduct;
                }
            });

        return $deducted;  
    }

        








    

}
