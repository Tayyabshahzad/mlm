<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\PendingReward;
use App\Models\RewardSetting;
use App\Models\RewardTransaction;
use App\Services\RewardService;
use App\Services\BinarySystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardReviewController extends Controller
{
    protected $rewardService;
    protected $binarySystemService;

    public function __construct(RewardService $rewardService, BinarySystemService $binarySystemService)
    {
        $this->rewardService = $rewardService;
        $this->binarySystemService = $binarySystemService;
    }

    /**
     * Display reward assignments for review
     */
    public function index(Request $request)
    {
        // Get all users who have received rewards
        $query = User::query()
            ->whereHas('wallets', function($q) {
                $q->where('wallet_type', 'reward')
                  ->where('commission_type', 'reward')
                  ->where('balance', '>', 0);
            });

        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.id', 'like', "%{$search}%");
            });
        }

        // Add level filter
        if ($request->filled('level')) {
            $level = $request->input('level');
            $query->whereHas('wallets', function($q) use ($level) {
                $q->where('wallet_type', 'reward')
                  ->where('commission_type', 'reward')
                  ->where('level', $level)
                  ->where('balance', '>', 0);
            });
        }

        $users = $query->orderBy('users.id')->paginate(50);

        // Get reward levels for filtering
        $rewardLevels = RewardSetting::where('is_active', true)->orderBy('level')->get();

        // Get statistics
        $stats = $this->getRewardStatistics();

        return view('admin.reward-review.index', compact('users', 'rewardLevels', 'stats'));
    }

    /**
     * Show detailed reward information for a specific user
     */
    public function show(User $user)
    {
        // Get user's reward wallet entries
        $rewardWallets = Wallet::where('user_id', $user->id)
            ->where('wallet_type', 'reward')
            ->where('commission_type', 'reward')
            ->orderBy('level')
            ->get();

        // Get user's pending rewards
        $pendingRewards = PendingReward::where('user_id', $user->id)
            ->with('approvedBy')
            ->orderBy('level')
            ->get();

        // Get reward transaction history
        $rewardTransactions = RewardTransaction::where('user_id', $user->id)
            ->with(['processedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get current team count analysis
        $teamAnalysis = $this->getTeamCountAnalysis($user->id);

        // Check for potential issues
        $issues = $this->identifyPotentialIssues($user, $rewardWallets, $teamAnalysis);

        return view('admin.reward-review.show', compact(
            'user',
            'rewardWallets',
            'pendingRewards',
            'rewardTransactions',
            'teamAnalysis',
            'issues'
        ));
    }

    /**
     * Get reward statistics
     */
    protected function getRewardStatistics()
    {
        $rewardLevels = RewardSetting::where('is_active', true)->get();
        $stats = [];

        foreach ($rewardLevels as $level) {
            // Count users who received this level reward
            $usersWithReward = Wallet::where('wallet_type', 'reward')
                ->where('commission_type', 'reward')
                ->where('level', $level->level)
                ->where('balance', '>', 0)
                ->distinct('user_id')
                ->count('user_id');

            // Count users who currently meet the team requirement - simplified for now
            $usersEligible = 0; // Will be calculated differently - this is just for stats

            $stats[] = [
                'level' => $level->level,
                'reward_amount' => $level->reward_amount,
                'users_required' => $level->users_required,
                'users_with_reward' => $usersWithReward,
                'users_currently_eligible' => $usersEligible,
                'potential_over_rewards' => max(0, $usersWithReward - $usersEligible)
            ];
        }

        return $stats;
    }

    /**
     * Get team count analysis for a user
     */
    protected function getTeamCountAnalysis(int $userId)
    {
        $analysis = [];
        $rewardLevels = RewardSetting::where('is_active', true)->orderBy('level')->get();

        foreach ($rewardLevels as $level) {
            $currentTeamCount = $this->rewardService->calculateTeamCountForLevelFixed($userId, $level->level);
            
            $analysis[$level->level] = [
                'level' => $level->level,
                'required_count' => $level->users_required,
                'current_count' => $currentTeamCount,
                'meets_requirement' => $currentTeamCount >= $level->users_required,
                'has_reward' => Wallet::where('user_id', $userId)
                    ->where('wallet_type', 'reward')
                    ->where('commission_type', 'reward')
                    ->where('level', $level->level)
                    ->where('balance', '>', 0)
                    ->exists()
            ];
        }

        return $analysis;
    }

    /**
     * Identify potential issues with reward assignments
     */
    protected function identifyPotentialIssues(User $user, $rewardWallets, $teamAnalysis)
    {
        $issues = [];

        foreach ($rewardWallets as $wallet) {
            $level = (int) $wallet->level;
            $analysis = $teamAnalysis[$level] ?? null;

            if (!$analysis) {
                $issues[] = [
                    'type' => 'invalid_level',
                    'level' => $level,
                    'message' => "Invalid reward level {$level} found in wallet"
                ];
                continue;
            }

            // Check if user still meets team requirement
            if (!$analysis['meets_requirement']) {
                $issues[] = [
                    'type' => 'insufficient_team',
                    'level' => $level,
                    'message' => "User has level {$level} reward but only {$analysis['current_count']} team members (requires {$analysis['required_count']})",
                    'severity' => 'high'
                ];
            }

            // Check if user skipped previous levels
            if ($level > 1) {
                $previousLevel = $level - 1;
                $hasPreviousReward = Wallet::where('user_id', $user->id)
                    ->where('wallet_type', 'reward')
                    ->where('commission_type', 'reward')
                    ->where('level', $previousLevel)
                    ->where('balance', '>', 0)
                    ->exists();

                if (!$hasPreviousReward) {
                    $issues[] = [
                        'type' => 'skipped_level',
                        'level' => $level,
                        'message' => "User has level {$level} reward but missing level {$previousLevel} reward",
                        'severity' => 'medium'
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * Reverse a reward (for incorrectly assigned rewards)
     */
    public function reverseReward(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'level' => 'required|integer|min:1|max:10',
            'reason' => 'required|string|min:10|max:500'
        ]);
       
        $userId = $request->input('user_id');
        $level = $request->input('level');
        $reason = trim($request->input('reason'));

        try {
            DB::beginTransaction();

            // Add initial debugging
            Log::info('Starting reward reversal process', [
                'user_id' => $userId,
                'level' => $level,
                'reason_length' => strlen($reason)
            ]);

            // Verify user exists and get user info
            $user = User::findOrFail($userId);

            // Find the reward wallet entry
            $wallet = Wallet::where('user_id', $userId)
                ->where('wallet_type', 'reward')
                ->where('commission_type', 'reward')
                ->where('level', $level)
                ->where('balance', '>', 0)
                ->first();

            if (!$wallet) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "No active level {$level} reward found for user {$user->name} (ID: {$userId})"
                ], 404);
            }

            // Store original amounts before updating
            $originalBalance = $wallet->balance;
            $originalTotalAmount = $wallet->total_amount;

            // Validate the reward amount is reasonable
            if ($originalBalance <= 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reverse reward with zero or negative balance'
                ], 400);
            }

            // Generate unique reference number for this reversal
            $referenceNumber = RewardTransaction::generateReferenceNumber('reward_reversed');

            // Create transaction record for the reversal
            $transaction = RewardTransaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'transaction_type' => 'reward_reversed',
                'level' => $level,
                'amount' => $originalBalance,
                'previous_balance' => $originalBalance,
                'new_balance' => 0,
                'reason' => $reason,
                'processed_by' => auth()->id(),
                'reference_number' => $referenceNumber,
                'metadata' => [
                    'original_reward_date' => $wallet->created_at->toDateTimeString(),
                    'reversal_date' => now()->toDateTimeString(),
                    'admin_user' => auth()->user()->name ?? 'System',
                    'user_details' => [
                        'name' => $user->name,
                        'email' => $user->email
                    ],
                    'original_total_amount' => $originalTotalAmount,
                    'total_earnings_impact' => $originalTotalAmount
                ]
            ]);

            // Update wallet balance and total_amount to 0 (so it affects total earnings calculation)
            $wallet->update([
                'balance' => 0,
                'total_amount' => 0,
                'updated_at' => now()
            ]);

            // Reverse any associated binary earnings
            $binaryReversalResult = $this->binarySystemService->reverseRewardBasedBinaryEarnings(
                $userId,
                $level,
                "Reward level {$level} reversed - {$reason}"
            );

            // Log the reversal action including binary reversal details
            Log::info('Reward reversed with transaction tracking', [
                'transaction_id' => $transaction->id,
                'reference_number' => $referenceNumber,
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                'user_name' => $user->name,
                'level' => $level,
                'original_balance' => $originalBalance,
                'reason' => $reason,
                'reversed_by' => auth()->id(),
                'binary_reversal' => $binaryReversalResult
            ]);

            DB::commit();

            // Prepare success message with binary reversal info
            $successMessage = "Level {$level} reward ($" . number_format($originalTotalAmount) . ") successfully reversed for {$user->name}. Total earnings reduced by $" . number_format($originalTotalAmount);

            if ($binaryReversalResult['success']) {
                $binaryInfo = [];
                if ($binaryReversalResult['total_2x_reversed'] > 0) {
                    $binaryInfo[] = "2x: $" . number_format($binaryReversalResult['total_2x_reversed']);
                }
                if ($binaryReversalResult['total_7x_reversed'] > 0) {
                    $binaryInfo[] = "7x: $" . number_format($binaryReversalResult['total_7x_reversed']);
                }

                if (!empty($binaryInfo)) {
                    $successMessage .= ". Binary earnings also reversed: " . implode(", ", $binaryInfo);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'transaction_reference' => $referenceNumber,
                'transaction_id' => $transaction->id,
                'binary_reversal_details' => $binaryReversalResult
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reversing reward', [
                'user_id' => $userId,
                'level' => $level,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error reversing reward. Please try again or contact support.'
            ], 500);
        }
    }

    /**
     * Export reward data for analysis
     */
    public function export()
    {
        $data = User::join('wallets', 'users.id', '=', 'wallets.user_id')
            ->where('wallets.wallet_type', 'reward')
            ->where('wallets.commission_type', 'reward')
            ->where('wallets.balance', '>', 0)
            ->select(
                'users.id as user_id',
                'users.name',
                'users.email',
                'wallets.level',
                'wallets.balance',
                'wallets.created_at as reward_received_at'
            )
            ->orderBy('users.id')
            ->orderBy('wallets.level')
            ->get();

        $filename = 'reward_assignments_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'User ID',
                'Name', 
                'Email',
                'Reward Level',
                'Reward Amount',
                'Received At'
            ]);

            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->user_id,
                    $row->name,
                    $row->email,
                    $row->level,
                    $row->balance,
                    $row->reward_received_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Assign a missing reward to a user
     */
    public function assignReward(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'level' => 'required|integer|min:1|max:10',
            'reason' => 'nullable|string|max:500'
        ]);

        $userId = $request->input('user_id');
        $level = $request->input('level');
        $reason = trim($request->input('reason', 'Manual assignment by admin'));

        try {
            DB::beginTransaction();

            // Verify user exists
            $user = User::findOrFail($userId);

            // Get reward level settings
            $rewardLevelSetting = RewardSetting::where('level', $level)
                ->where('is_active', true)
                ->first();

            if (!$rewardLevelSetting) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "No active reward configuration found for level {$level}"
                ], 404);
            }

            // Check if user already has this reward
            $existingReward = Wallet::where('user_id', $userId)
                ->where('wallet_type', 'reward')
                ->where('commission_type', 'reward')
                ->where('level', $level)
                ->where('balance', '>', 0)
                ->first();

            if ($existingReward) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "User {$user->name} already has level {$level} reward"
                ], 400);
            }

            // Validate eligibility using the RewardService
            $validation = $this->rewardService->validateRewardEligibility($userId, $level);

            if (!$validation['eligible']) {
                // Allow manual override but log the issues
                Log::warning("Manual reward assignment despite eligibility issues", [
                    'user_id' => $userId,
                    'level' => $level,
                    'issues' => $validation['reasons'],
                    'assigned_by' => auth()->id(),
                    'override_reason' => $reason
                ]);
            }

            // Generate unique reference number
            $referenceNumber = RewardTransaction::generateReferenceNumber('reward_assigned');

            // Create wallet entry directly (bypass pending approval since this is manual admin assignment)
            $wallet = Wallet::create([
                'user_id' => $userId,
                'wallet_type' => 'reward',
                'commission_type' => 'reward',
                'level' => $level,
                'balance' => $rewardLevelSetting->reward_amount,
                'total_amount' => $rewardLevelSetting->reward_amount,
            ]);

            // Create transaction record
            $transaction = RewardTransaction::create([
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'transaction_type' => 'reward_assigned',
                'level' => $level,
                'amount' => $rewardLevelSetting->reward_amount,
                'previous_balance' => 0,
                'new_balance' => $rewardLevelSetting->reward_amount,
                'reason' => $reason,
                'processed_by' => auth()->id(),
                'reference_number' => $referenceNumber,
                'metadata' => [
                    'assignment_type' => 'manual_admin',
                    'assignment_date' => now()->toDateTimeString(),
                    'admin_user' => auth()->user()->name ?? 'System',
                    'user_details' => [
                        'name' => $user->name,
                        'email' => $user->email
                    ],
                    'eligibility_check' => $validation,
                    'team_count_at_assignment' => $validation['current_stats']['team_count'] ?? 0
                ]
            ]);

            Log::info('Reward manually assigned', [
                'transaction_id' => $transaction->id,
                'reference_number' => $referenceNumber,
                'wallet_id' => $wallet->id,
                'user_id' => $userId,
                'user_name' => $user->name,
                'level' => $level,
                'amount' => $rewardLevelSetting->reward_amount,
                'reason' => $reason,
                'assigned_by' => auth()->id(),
                'eligibility_override' => !$validation['eligible']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Level {$level} reward ($" . number_format($rewardLevelSetting->reward_amount) . ") successfully assigned to {$user->name}",
                'transaction_reference' => $referenceNumber,
                'transaction_id' => $transaction->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning reward', [
                'user_id' => $userId,
                'level' => $level,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error assigning reward. Please try again or contact support.'
            ], 500);
        }
    }
}