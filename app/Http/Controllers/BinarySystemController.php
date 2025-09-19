<?php

namespace App\Http\Controllers;

use App\Models\BinarySystem;
use App\Models\UserRank;
use App\Services\BinarySystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BinarySystemController extends Controller
{
    protected $binaryService;

    public function __construct(BinarySystemService $binaryService)
    {
        $this->binaryService = $binaryService;
    }

    public function index()
    {
        $user = Auth::user();
        $status = $this->binaryService->getBinarySystemStatus($user->id);
        $levels = BinarySystem::getLevels();
        $ranks = UserRank::getRankLevels();
         
        return view('binary-system.index', compact('status', 'levels', 'ranks'));
    }

    public function initializeSystem(Request $request)
    {
        $request->validate([
            'system_type' => 'required|in:2x,7x',
            'investment_amount' => 'required|numeric|min:100'
        ]);

        try {
            $user = Auth::user();

            $system = $this->binaryService->initializeBinarySystem(
                $user->id,
                $request->system_type,
                $request->investment_amount
            );

            return response()->json([
                'success' => true,
                'message' => "Successfully initialized {$request->system_type} binary system!",
                'system' => $system
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function progressLevel(Request $request)
    {
        $request->validate([
            'system_id' => 'required|exists:binary_system,id'
        ]);

        try {
            $user = Auth::user();
            $system = BinarySystem::where('id', $request->system_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$system) {
                throw new \Exception('Binary system not found');
            }

            $result = $this->binaryService->progressToNextLevel($system);

            return response()->json([
                'success' => true,
                'message' => 'Successfully progressed to next level!',
                'system' => $system->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function toggleAutoProgress(Request $request)
    {
        $request->validate([
            'system_id' => 'required|exists:binary_system,id',
            'auto_next_level' => 'required|boolean'
        ]);

        $user = Auth::user();
        $system = BinarySystem::where('id', $request->system_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$system) {
            return response()->json([
                'success' => false,
                'message' => 'Binary system not found'
            ], 404);
        }

        $system->update(['auto_next_level' => $request->auto_next_level]);

        return response()->json([
            'success' => true,
            'message' => 'Auto progression setting updated successfully!'
        ]);
    }

    public function getSystemHistory($systemId)
    {
        $user = Auth::user();
        $system = BinarySystem::where('id', $systemId)
            ->where('user_id', $user->id)
            ->first();

        if (!$system) {
            return response()->json([
                'success' => false,
                'message' => 'Binary system not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'history' => $system->level_history ?? [],
            'current_level' => $system->current_level,
            'total_earned' => $system->total_earned
        ]);
    }

    public function upgradeRank()
    {
        try {
            $user = Auth::user();
            $newRank = UserRank::updateUserRank($user->id);

            if (!$newRank) {
                return response()->json([
                    'success' => false,
                    'message' => 'No rank upgrade available at this time'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => "Congratulations! You've been upgraded to {$newRank->rank_name}!",
                'rank' => $newRank
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function fixOnlineIncome()
    {
        try {
            $user = Auth::user();
            $result = $this->binaryService->fixOnlineIncomeConnection($user->id);

            if (!$result) {
                throw new \Exception('Failed to fix online income connection');
            }

            return response()->json([
                'success' => true,
                'message' => 'Online income connection has been fixed and disconnected from binary systems'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // Admin methods
    public function adminIndex()
    {
        $this->authorize('view-admin-panel');

        $systems = BinarySystem::with(['user'])
            ->latest()
            ->paginate(50);

        $stats = [
            'total_2x_systems' => BinarySystem::type('2x')->active()->count(),
            'total_7x_systems' => BinarySystem::type('7x')->active()->count(),
            'completed_systems' => BinarySystem::whereNotNull('completed_at')->count(),
            'total_earnings' => BinarySystem::sum('total_earned')
        ];

        return view('admin.binary-system.index', compact('systems', 'stats'));
    }

    public function adminShow(BinarySystem $system)
    {
        $this->authorize('view-admin-panel');

        $system->load(['user']);
        $earnings = $system->user->wallets()
            ->where('wallet_type', 'binary_earning')
            ->where('commission_type', $system->system_type)
            ->latest()
            ->get();

        return view('admin.binary-system.show', compact('system', 'earnings'));
    }

    public function adminProcessEarnings(Request $request)
    {
        $this->authorize('manage-admin-panel');

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'source' => 'required|string'
        ]);

        try {
            $result = $this->binaryService->processEarnings(
                $request->user_id,
                $request->amount,
                $request->source
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Earnings processed successfully!' : 'Failed to process earnings'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}