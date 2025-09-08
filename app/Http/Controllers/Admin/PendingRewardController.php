<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendingReward;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PendingRewardController extends Controller
{
    protected $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
        
    }

    /**
     * Display pending rewards for admin review
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        $perPage = $request->get('per_page', 20);

        $query = PendingReward::with(['user', 'approvedBy']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $pendingRewards = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Add current verification for each pending reward
        $pendingRewards->getCollection()->transform(function ($pendingReward) {
            if ($pendingReward->status === 'pending') {
                $pendingReward->current_verification = [
                    'current_team_count' => $this->rewardService->calculateTeamCountForLevelFixed($pendingReward->user_id, $pendingReward->level),
                    'still_eligible' => $this->rewardService->verifyCurrentEligibility($pendingReward),
                    'verified_at' => now()->toISOString(),
                ];
            }

            return $pendingReward;
        });

        return view('admin.pending-rewards.index', compact('pendingRewards', 'status'))
            ->with('stats', [
                'pending_count' => PendingReward::pending()->count(),
                'approved_count' => PendingReward::approved()->count(),
                'denied_count' => PendingReward::denied()->count(),
            ]);
    }

    /**
     * Show detailed view of a pending reward
     */
    public function show(PendingReward $pendingReward)
    {
        $pendingReward->load(['user', 'approvedBy']);

        // Add current verification data
        $pendingReward->current_verification = [
            'current_team_count' => $this->rewardService->calculateTeamCountForLevelFixed($pendingReward->user_id, $pendingReward->level),
            'still_eligible' => $this->rewardService->verifyCurrentEligibility($pendingReward),
            'validation_details' => $this->rewardService->validateRewardEligibility($pendingReward->user_id, $pendingReward->level),
            'team_breakdown' => $this->rewardService->debugTeamCounts($pendingReward->user_id),
            'verified_at' => now()->toISOString(),
        ];

        return view('admin.pending-rewards.show', compact('pendingReward'));
    }

    /**
     * Approve a pending reward
     */
    public function approve(Request $request, PendingReward $pendingReward)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->rewardService->approvePendingReward(
                $pendingReward->id,
                auth()->id(),
                $request->get('notes', '')
            );

            return redirect()->back()->with('success', 'Reward approved successfully!');

        } catch (\Exception $e) {
            Log::error("Failed to approve pending reward {$pendingReward->id}: ".$e->getMessage());

            return redirect()->back()->with('error', 'Failed to approve reward: '.$e->getMessage());
        }
    }

    /**
     * Deny a pending reward
     */
    public function deny(Request $request, PendingReward $pendingReward)
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        try {
            $this->rewardService->denyPendingReward(
                $pendingReward->id,
                auth()->id(),
                $request->get('notes')
            );

            return redirect()->back()->with('success', 'Reward denied successfully!');

        } catch (\Exception $e) {
            Log::error("Failed to deny pending reward {$pendingReward->id}: ".$e->getMessage());

            return redirect()->back()->with('error', 'Failed to deny reward: '.$e->getMessage());
        }
    }

    /**
     * Bulk approve multiple pending rewards
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'reward_ids' => 'required|array',
            'reward_ids.*' => 'exists:pending_rewards,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $successCount = 0;
        $errors = [];

        foreach ($request->reward_ids as $rewardId) {
            try {
                $this->rewardService->approvePendingReward(
                    $rewardId,
                    auth()->id(),
                    $request->get('notes', '')
                );
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Reward ID {$rewardId}: ".$e->getMessage();
            }
        }

        if ($successCount > 0) {
            $message = "Successfully approved {$successCount} rewards.";
            if (! empty($errors)) {
                $message .= ' Errors: '.implode(', ', $errors);
            }

            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', 'Failed to approve any rewards: '.implode(', ', $errors));
    }

    /**
     * Re-verify eligibility for a pending reward
     */
    public function reverify(PendingReward $pendingReward)
    {
        if ($pendingReward->status !== 'pending') {
            return redirect()->back()->with('error', 'Can only re-verify pending rewards');
        }

        $validation = $this->rewardService->validateRewardEligibility(
            $pendingReward->user_id,
            $pendingReward->level
        );

        return response()->json([
            'validation' => $validation,
            'current_verification' => [
                'current_team_count' => $this->rewardService->calculateTeamCountForLevelFixed($pendingReward->user_id, $pendingReward->level),
                'still_eligible' => $this->rewardService->verifyCurrentEligibility($pendingReward),
                'verified_at' => now()->toISOString(),
            ],
        ]);
    }
}
