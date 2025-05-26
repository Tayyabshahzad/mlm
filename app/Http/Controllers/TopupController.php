<?php

namespace App\Http\Controllers;

use App\Models\InvestmentSlab;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Setting;
use App\Models\TransactionLog;
use App\Models\UserInvestment;
use App\Services\CommissionService;
use App\Services\InvestmentSlabService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TopupController extends Controller
{
    private const MIN_TOPUP_AMOUNT = 5;
    private const SLAB_AMOUNT = 100;
    private const SLAB_MATURITY_MONTHS = 24;

    private CommissionService $commissionService;
    private WalletService $walletService;

    public function __construct(CommissionService $commissionService, WalletService $walletService,private InvestmentSlabService $investmentSlabService )
    {
        $this->commissionService = $commissionService;
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $users = User::all();
        $wallets = Wallet::where('wallet_src','top_up')->orderBy('id','desc')->get();
        return view('users.account-topup', compact('users','wallets'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $amount = $validated['amount'];

        DB::beginTransaction();

        try {
            $wallet = $this->createTopupWallet($user, $amount);
            $this->logTopupTransaction($user, $amount, $wallet->balance);
            DB::commit();
            Log::info("Admin top-up successful", [
                'user_id' => $user->id,
                'amount' => $amount,
                'admin_id' => Auth::id()
            ]);
            return response()->json([
                'message' => 'Top-up successful!',
                'data' => [
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'wallet_balance' => $wallet->balance,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Admin top-up failed", [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Top-up failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function topUp(Request $request): RedirectResponse
    { 
        $user = Auth::user();
        $totalBalance = $this->getTotalOnlineBalance($user);
        $validated = $request->validate([
            'topUp_amount' => [
                'required', 
                'numeric', 
                "min:" . self::MIN_TOPUP_AMOUNT,
                "max:$totalBalance"
            ],
            'topUp_description' => 'required|string|max:255',
        ]);

        $amount = $validated['topUp_amount'];
        $description = $validated['topUp_description'];
        if ($totalBalance < $amount) {
            return back()->with('error', 'Insufficient balance in your online wallet.');
        }
        DB::beginTransaction(); 
        try {
            $this->deductFromOnlineWallets($user, $amount);
            $this->createUserInvestment($user, $amount, $description);
            $this->assignCommissionsForTopup($user, $amount);
            $this->investmentSlabService->createSlabsForUser($user);
            $this->logInvestmentTransaction($user, $amount);

            DB::commit();

            return back()->with('success', "Your account has been topped up with $$amount");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("User top-up failed", [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Top-up failed: ' . $e->getMessage());
        }
    }

    private function processDirectCommissionForTopup(User $user, float $amount): void
    {
        $sponsor = User::where('blocked', false)->find($user->sponsor_id);
        if (!$sponsor) {
            Log::info("No sponsor found for user {$user->id}");
            return;
        }

        // Check if sponsor qualifies (has at least 1 active direct referral)
        $activeDirectUsers = User::where('blocked', false)
            ->where('sponsor_id', $sponsor->id)
            ->count();

        if ($activeDirectUsers < 1) {
            Log::info("Sponsor {$sponsor->id} doesn't qualify for commission");
            return;
        }

        // Calculate 5% commission for Level 1
        $commissionPercentage = 5.0;
        $commissionAmount = ($amount * $commissionPercentage) / 100;

        // Assign commission
        $this->walletService->assignCommission(
            userId: $sponsor->id,
            amount: $commissionAmount,
            type: 'direct',
            sourceUser: $user,
            level: 1,
            percentage: $commissionPercentage
        );

        Log::info("Direct commission assigned: User {$sponsor->id}, Amount {$commissionAmount}");
    }

    private function processIndirectCommissionsForTopup(User $user, float $amount): void
    {
        // Get ancestors from referral tree (excluding direct sponsor)
        $ancestors = DB::table('referral_trees')
            ->select('ancestor_id', 'level')
            ->where('descendant_id', $user->id)
            ->where('level', '>', 1) // Exclude Level 1 (direct sponsor)
            ->where('level', '<=', 7)
            ->orderBy('level')
            ->get();

        $commissionRates = [
            2 => 2.00, 3 => 1.50, 4 => 1.25, 
            5 => 1.00, 6 => 0.75, 7 => 0.50
        ];

        $teamSizeRequirements = [
            2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7
        ];

        foreach ($ancestors as $ancestor) {
            $ancestorUser = User::where('blocked', false)->find($ancestor->ancestor_id);
            
            if (!$ancestorUser) {
                continue;
            }

            // Check team size requirement
            $activeDirectUsers = User::where('blocked', false)
                ->where('sponsor_id', $ancestorUser->id)
                ->count();

            $requiredTeamSize = $teamSizeRequirements[$ancestor->level] ?? 0;

            if ($activeDirectUsers < $requiredTeamSize) {
                Log::info("Ancestor {$ancestorUser->id} at level {$ancestor->level} doesn't meet team size requirement");
                continue;
            }

            // Calculate commission
            $commissionPercentage = $commissionRates[$ancestor->level] ?? 0;
            $commissionAmount = ($amount * $commissionPercentage) / 100;

            // Assign commission
            $this->walletService->assignCommission(
                userId: $ancestorUser->id,
                amount: $commissionAmount,
                type: 'indirect',
                sourceUser: $user,
                level: $ancestor->level,
                percentage: $commissionPercentage
            );

            Log::info("Indirect commission assigned: User {$ancestorUser->id}, Level {$ancestor->level}, Amount {$commissionAmount}");
        }
    }

    private function getCommissionSummary(User $user): array
    {
        // This could be useful to return commission information in the API response
        $summary = [
            'commissions_assigned' => 0,
            'total_commission_amount' => 0,
            'levels_processed' => []
        ];

        // You could query recent commissions for this user to build summary
        // This is optional and depends on your requirements

        return $summary;
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

     private function createTopupWallet(User $user, float $amount): Wallet
    {
        return Wallet::create([
            'user_id' => $user->id,
            'wallet_type' => 'online',
            'wallet_from' => Auth::id(),
            'commission_type' => 'online',
            'level' => 'online',
            'balance' => $amount,
            'wallet_src' => 'top_up'
        ]);
    }

    private function logTopupTransaction(User $user, float $amount, float $finalAmount): void
    {
        $setting = Setting::first();
        $companyName = $setting->site_name ?? 'GVI';
        $description = "{$companyName} topped up your online wallet with $$amount (Cash received by admin).";

        $this->logTransaction(
            $user->id,
            'topup',
            'online',
            $amount,
            $finalAmount,
            $description,
            'credit'
        );
    }

    private function deductFromOnlineWallets(User $user, float $amount): void
    {
        $wallets = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'online')
            ->where('balance', '>', 0)
            ->orderBy('created_at')
            ->get();

        $remainingAmount = $amount;

        foreach ($wallets as $wallet) {
            if ($remainingAmount <= 0) break;

            $deductAmount = min($wallet->balance, $remainingAmount);
            $wallet->decrement('balance', $deductAmount);
            $remainingAmount -= $deductAmount;
        }
    }

    private function assignCommissionsForTopup(User $user, float $amount): void
    {
        try {
            $this->processDirectCommissionForTopup($user, $amount);
            $this->processIndirectCommissionsForTopup($user, $amount);
            Log::info("Commissions processed", ['user_id' => $user->id,  'username' => $user->username, 'amount' => $amount]);

        } catch (\Exception $e) {
            Log::error("Commission processing failed", [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function getTotalOnlineBalance(User $user): float
    {
        return Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'online')
            ->sum('balance');
    }

    private function createUserInvestment(User $user, float $amount, string $description): void
    {
        UserInvestment::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 'topup',
            'description' => $description
        ]);

        $user->increment('roi_eligible_investment_amount', $amount);
    }

    private function logInvestmentTransaction(User $user, float $amount): void
    {
        $description = "You topped up your account with $$amount from your Online Wallet.";

        $this->logTransaction(
            $user->id,
            'investment',
            'online',
            $amount,
            $amount,
            $description,
            'debit'
        );
    }

}