<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithDrawalequest;
use App\Exports\WithdrawalReportExport;
use App\Notifications\WithdrawalSubmittedNotification;
use App\Notifications\WithdrawalStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

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

        try {
            Auth::user()->notify(new WithdrawalSubmittedNotification(
                amount:      $withdrawableAmount,
                fee:         $calculatedFee,
                requestType: $request->withdrawal_option ?? 'online',
            ));
        } catch (\Throwable) {}

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
        $username  = trim($request->get('username', ''));
        $fromDate  = $request->get('from_date');
        $toDate    = $request->get('to_date');
        $minAmount = $request->get('min_amount');
        $maxAmount = $request->get('max_amount');
        $status    = $request->get('status');

        $query = WithDrawalequest::with(['user', 'user.profile', 'user.parent'])
            ->orderBy('created_at', 'desc');

        if ($username) {
            $query->whereHas('user', fn ($q) =>
                $q->where('username', 'like', '%'.$username.'%')
                  ->orWhere('name', 'like', '%'.$username.'%')
            );
        }
        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }
        if ($minAmount !== null && $minAmount !== '') {
            $query->where('amount', '>=', (float) $minAmount);
        }
        if ($maxAmount !== null && $maxAmount !== '') {
            $query->where('amount', '<=', (float) $maxAmount);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $withDrawsRequests = $query->get();

        $filters = [
            'username'   => $username,
            'from_date'  => $fromDate,
            'to_date'    => $toDate,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'status'     => $status,
        ];

        return view('users.widthdrawls-request', compact('withDrawsRequests', 'filters'));
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

        try {
            $withDrawalequest->user->notify(new WithdrawalStatusNotification(
                amount:      $withDrawalequest->amount,
                status:      $withDrawalequest->status,
                requestType: $withDrawalequest->request_type ?? 'online',
            ));
        } catch (\Throwable) {}

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

    public function analytics(Request $request)
    {
        $selectedMonth = $request->get('month', Carbon::now()->month);
        $selectedYear = $request->get('year', Carbon::now()->year);
        $selectedStatus = $request->get('status', 'all');

        // Get monthly statistics
        $monthlyStats = $this->getWithdrawalMonthlyStats($selectedMonth, $selectedYear);

        // Get daily breakdown
        $dailyData = $this->getWithdrawalDailyBreakdown($selectedMonth, $selectedYear);

        // Get monthly trend (12 months)
        $monthlyTrend = $this->getWithdrawalMonthlyTrend();

        // Get status breakdown for pie chart
        $statusBreakdown = $this->getStatusBreakdown($selectedMonth, $selectedYear);

        // Get request type breakdown
        $typeBreakdown = $this->getTypeBreakdown($selectedMonth, $selectedYear);

        // Get recent withdrawals with user details
        $query = WithDrawalequest::with(['user', 'user.profile'])
            ->whereMonth('created_at', $selectedMonth)
            ->whereYear('created_at', $selectedYear);

        if ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        $recentWithdrawals = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get available years
        $availableYears = WithDrawalequest::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        if ($availableYears->isEmpty()) {
            $availableYears = collect([Carbon::now()->year]);
        }

        return view('users.withdrawal-analytics', compact(
            'monthlyStats',
            'dailyData',
            'monthlyTrend',
            'statusBreakdown',
            'typeBreakdown',
            'recentWithdrawals',
            'selectedMonth',
            'selectedYear',
            'selectedStatus',
            'availableYears'
        ));
    }

    public function exportWithdrawals(Request $request)
    {
        $fromDate  = $request->get('from_date');
        $toDate    = $request->get('to_date');
        $username  = $request->get('username');
        $minAmount = $request->get('min_amount');
        $maxAmount = $request->get('max_amount');
        $status    = $request->get('status');

        $filename = 'withdrawals_';
        if ($fromDate && $toDate) {
            $filename .= Carbon::parse($fromDate)->format('Ymd') . '_to_' . Carbon::parse($toDate)->format('Ymd');
        } elseif ($fromDate) {
            $filename .= 'from_' . Carbon::parse($fromDate)->format('Ymd');
        } else {
            $filename .= Carbon::now()->format('Ymd_His');
        }
        $filename .= '.xlsx';

        return Excel::download(
            new WithdrawalReportExport($fromDate, $toDate, $username, $minAmount, $maxAmount, $status),
            $filename
        );
    }

    private function getWithdrawalMonthlyStats($month, $year)
    {
        $withdrawals = WithDrawalequest::whereMonth('created_at', $month)
            ->whereYear('created_at', $year);

        $totalAmount = (clone $withdrawals)->sum('amount');
        $totalRequests = (clone $withdrawals)->count();
        $approvedAmount = (clone $withdrawals)->where('status', 'approved')->sum('amount');
        $pendingCount = (clone $withdrawals)->where('status', 'pending')->count();
        $approvedCount = (clone $withdrawals)->where('status', 'approved')->count();
        $rejectedCount = (clone $withdrawals)->where('status', 'rejected')->count();

        // Previous month comparison
        $prevMonth = Carbon::createFromDate($year, $month, 1)->subMonth();
        $prevWithdrawals = WithDrawalequest::whereMonth('created_at', $prevMonth->month)
            ->whereYear('created_at', $prevMonth->year);

        $prevTotalAmount = $prevWithdrawals->sum('amount');
        $percentageChange = $prevTotalAmount > 0
            ? round((($totalAmount - $prevTotalAmount) / $prevTotalAmount) * 100, 1)
            : 0;

        return [
            'total_amount' => $totalAmount,
            'total_requests' => $totalRequests,
            'approved_amount' => $approvedAmount,
            'pending_count' => $pendingCount,
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'percentage_change' => $percentageChange,
        ];
    }

    private function getWithdrawalDailyBreakdown($month, $year)
    {
        $dailyData = WithDrawalequest::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $labels = [];
        $amounts = [];
        $counts = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
            $labels[] = $day;

            $dayData = $dailyData->firstWhere('date', $date);
            $amounts[] = $dayData ? round($dayData->total, 2) : 0;
            $counts[] = $dayData ? $dayData->count : 0;
        }

        return [
            'labels' => $labels,
            'amounts' => $amounts,
            'counts' => $counts,
        ];
    }

    private function getWithdrawalMonthlyTrend()
    {
        $months = [];
        $amounts = [];
        $counts = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $monthData = WithDrawalequest::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->selectRaw('SUM(amount) as total, COUNT(*) as count')
                ->first();

            $amounts[] = $monthData ? round($monthData->total ?? 0, 2) : 0;
            $counts[] = $monthData ? ($monthData->count ?? 0) : 0;
        }

        return [
            'labels' => $months,
            'amounts' => $amounts,
            'counts' => $counts,
        ];
    }

    private function getStatusBreakdown($month, $year)
    {
        $data = WithDrawalequest::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'pending' => [
                'count' => $data->get('pending')->count ?? 0,
                'amount' => $data->get('pending')->total ?? 0,
            ],
            'approved' => [
                'count' => $data->get('approved')->count ?? 0,
                'amount' => $data->get('approved')->total ?? 0,
            ],
            'rejected' => [
                'count' => $data->get('rejected')->count ?? 0,
                'amount' => $data->get('rejected')->total ?? 0,
            ],
        ];
    }

    private function getTypeBreakdown($month, $year)
    {
        return WithDrawalequest::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->selectRaw('request_type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('request_type')
            ->get();
    }
}
