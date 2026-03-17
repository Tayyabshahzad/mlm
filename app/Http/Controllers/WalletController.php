<?php

namespace App\Http\Controllers;

use App\Models\InvestmentSlab;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\UserInvestment;
use App\Models\Wallet;
use App\Models\WithDrawalequest;
use App\Services\AccountManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{

    protected $setting;
    protected AccountManagementService $accountManagementService;

    public function __construct(AccountManagementService $accountManagementService)
    {
        $this->setting = Setting::first();
        $this->accountManagementService = $accountManagementService;
        view()->share('setting', $this->setting); // Share with all views
    }


    public function online(){
        $userId = auth()->user()->id;

        // Available Balance - Current spendable balance in online wallet only
        $onlineWallets = Wallet::where('wallet_type','online')->where('user_id', $userId)->get();
        $availableBalance = Wallet::where('wallet_type','online')->where('user_id', $userId)->sum('balance');

        // Calculate Total Earning and Current Balance for online wallet specifically
        $walletQuery = Wallet::where('wallet_type','online')->where('user_id', $userId);
        $totalEarning = $walletQuery->sum('total_amount');
        $currentBalance = $walletQuery->sum('balance');

        // Total Earned - Lifetime earnings from ALL income sources
        $earningsBreakdown = $this->calculateTotalLifetimeEarningsWithBreakdown($userId);
        $totalEarned = $earningsBreakdown['total'];

        // Get 2x ROI progress information
        $roiStats = $this->accountManagementService->getRoiAccountStats(auth()->user());

        $withDrawsRequests = WithDrawalequest::where('user_id', $userId)->orderby('id','desc')->get();
        $walletSum = Wallet::where('user_id', $userId)->sum('balance');
        $setting = Setting::first();

        return view('wallets.online',compact('availableBalance','onlineWallets','withDrawsRequests','walletSum','setting','totalEarned','earningsBreakdown','roiStats','totalEarning','currentBalance'));
    }

    /**
     * Calculate total lifetime earnings from all sources with breakdown
     */
    private function calculateTotalLifetimeEarningsWithBreakdown($userId)
    {
        // Get all earning wallets (not online wallet which is for spending)
        $earningWalletTypes = ['direct_indirect', 'reward', 'roi', 'profit_share', 'binary'];

        // Calculate breakdown by wallet type
        $walletBreakdown = [];
        foreach ($earningWalletTypes as $walletType) {
            $walletBreakdown[$walletType] = Wallet::where('user_id', $userId)
                ->where('wallet_type', $walletType)
                ->sum('balance');
        }

        // Calculate total earned from all earning sources
        $totalFromWallets = array_sum($walletBreakdown);

        // Add total amounts that were transferred to online wallet (from transaction logs)
        $totalTransferredToOnline = TransactionLog::where('user_id', $userId)
            ->where('to_wallet_type', 'online')
            ->whereIn('from_wallet_type', $earningWalletTypes)
            ->sum('amount'); // Original amount before charges

        // Add withdrawn amounts (money that was withdrawn from system)
        $totalWithdrawn = WithDrawalequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->sum('amount');

        // Total lifetime earnings = Current earnings + Transferred + Withdrawn
        $total = $totalFromWallets + $totalTransferredToOnline + $totalWithdrawn;

        return [
            'total' => $total,
            'current_earnings' => $totalFromWallets,
            'transferred_to_online' => $totalTransferredToOnline,
            'withdrawn' => $totalWithdrawn,
            'wallet_breakdown' => $walletBreakdown
        ];
    }

    public function directIndirect(){
        $walletQuery = Wallet::where('wallet_type','direct_indirect')->where('user_id',auth()->user()->id);
        $totalEarning   = $walletQuery->sum('total_amount');
        $currentBalance = $walletQuery->sum('balance');
        $wallets = $walletQuery->orderBy('id', 'desc')
        ->orderBy('level','asc')
        ->get();
        return view('wallets.direct-indirect',compact('wallets', 'totalEarning', 'currentBalance'));
    }

    public function rewards(){
        $walletQuery = Wallet::where('wallet_type','reward')->where('user_id',auth()->user()->id);
        $totalEarning   = $walletQuery->sum('total_amount');
        $currentBalance = $walletQuery->sum('balance');
        $wallets = $walletQuery->get();
        return view('wallets.reward',compact('wallets', 'totalEarning', 'currentBalance'));
    }

    public function ROI()
    {
        $baseQuery = Wallet::where('wallet_type', 'roi')
            ->where('user_id', auth()->id());
        $totalEarning   = (clone $baseQuery)->sum('total_amount');
        $currentBalance = (clone $baseQuery)->sum('balance');
        $wallets = (clone $baseQuery)->orderBy('id', 'desc')->paginate(20);
        return view('wallets.roi', compact('wallets', 'totalEarning', 'currentBalance'));
    }


    public function profitShare(Request $request){
        $baseQuery = Wallet::where('wallet_type','profit_share')->where('user_id', auth()->user()->id);

        // Calculate totals without filters for the summary boxes
        $totalEarning   = (clone $baseQuery)->sum('total_amount');
        $currentBalance = (clone $baseQuery)->sum('balance');

        $query = clone $baseQuery;
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('level') && $request->level !== 'all') {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $query->whereHas('form', function($q) use ($request) {
                $q->where('username', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('min_percentage')) {
            $query->where('percentage', '>=', $request->min_percentage);
        }

        if ($request->filled('max_percentage')) {
            $query->where('percentage', '<=', $request->max_percentage);
        }

        if ($request->filled('min_balance')) {
            $query->where('balance', '>=', $request->min_balance);
        }

        if ($request->filled('max_balance')) {
            $query->where('balance', '<=', $request->max_balance);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = ['created_at', 'level', 'percentage', 'balance'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $wallets = $query->get();

        // Get unique levels for filter dropdown
        $levels = Wallet::where('wallet_type','profit_share')
                    ->where('user_id', auth()->user()->id)
                    ->distinct()
                    ->pluck('level')
                    ->sort();

    return view('wallets.profit-share', compact('wallets', 'levels', 'totalEarning', 'currentBalance'));
}

    public function rank(){ 
        return view('wallets.rank'); 
    }

    public function incentiveWallets()
    {
        $userId = auth()->id();

        $wallets = Wallet::where('user_id', $userId)
            ->where('wallet_type', 'designation_incentive')
            ->orderBy('created_at', 'desc')
            ->get();

        $totalEarning   = Wallet::where('user_id', $userId)->where('wallet_type', 'designation_incentive')->sum('total_amount');
        $currentBalance = Wallet::where('user_id', $userId)->where('wallet_type', 'designation_incentive')->sum('balance');

        return view('wallets.incentive-wallets', compact('wallets', 'totalEarning', 'currentBalance'));
    }

    public function transferToOnline(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5',
            'wallet_type' => 'required|in:direct_indirect,reward,roi,profit_share,designation_incentive',
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
