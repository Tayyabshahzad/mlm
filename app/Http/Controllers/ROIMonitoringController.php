<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountManagementService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ROIMonitoringController extends Controller
{
    private AccountManagementService $accountService;

    public function __construct(AccountManagementService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * Display ROI monitoring dashboard
     */
    public function index(Request $request)
    {
          
        $filter = $request->get('filter', 'all'); // all, completed, active, stopped
        $search = $request->get('search');
        $perPage = $request->get('per_page', 25);

        // Get users with ROI data
        $query = User::where('roi_eligible_investment_amount', '>', 0);

        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        $users = $query->get();
        
        // Process users and add ROI stats
        $processedUsers = $users->map(function($user) {
            $stats = $this->accountService->getRoiAccountStats($user);
            $user->roi_stats = $stats;
            $user->completion_status = $this->getCompletionStatus($stats);
            return $user;
        });

        // Apply status filter
        if ($filter !== 'all') {
            $processedUsers = $processedUsers->filter(function($user) use ($filter) {
                switch ($filter) {
                    case 'completed':
                        return $user->completion_status === 'completed';
                    case 'active':
                        return $user->completion_status === 'active';
                    case 'stopped':
                        return $user->completion_status === 'stopped';
                    case 'expired':
                        return $user->completion_status === 'expired';
                    default:
                        return true;
                }
            });
        }

        // Sort by completion percentage (highest first)
        $processedUsers = $processedUsers->sortByDesc('roi_stats.completion_percentage');

        // Manual pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $processedUsers->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $paginatedUsers = new LengthAwarePaginator(
            $currentItems,
            $processedUsers->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Calculate summary statistics
        $summary = $this->calculateSummary($users);

        return view('roi-monitoring.index', compact('paginatedUsers', 'summary', 'filter', 'search'));
    }

    /**
     * Show detailed ROI information for a specific user
     */
    public function show(User $user)
    {
        $stats = $this->accountService->getRoiAccountStats($user);
        
        // Get recent ROI transactions
        $recentTransactions = $user->roiTransactions()
            ->latest()
            ->take(20)
            ->get();

        // Get wallet entries
        $walletEntries = $user->wallets()
            ->where('wallet_type', 'roi')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.roi-monitoring.show', compact('user', 'stats', 'recentTransactions', 'walletEntries'));
    }

    /**
     * Manually stop user's ROI account
     */
    public function stopAccount(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'custom_reason' => 'nullable|string|max:500',
            'message' => 'nullable|string|max:1000'
        ]);
        $this->accountService->stopRoiAccount($user, $request->reason,$request->message); 
        return redirect()->back()->with('success', "ROI account stopped for user: {$user->name}");
    }

    /**
     * Reactivate user's ROI account
     */
    public function reactivateAccount(User $user)
    {
        $this->accountService->reactivateRoiAccount($user);
        
        return redirect()->back()->with('success', "ROI account reactivated for user: {$user->name}");
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $userIds = $request->input('user_ids', []);

        if (empty($userIds)) {
            return redirect()->back()->with('error', 'No users selected');
        }

        $users = User::whereIn('id', $userIds)->get();
        $count = 0;

        foreach ($users as $user) {
            switch ($action) {
                case 'stop':
                    if ($this->accountService->canReceiveRoi($user)) {
                        $this->accountService->stopRoiAccount($user, 'bulk_admin_stop');
                        $count++;
                    }
                    break;
                case 'reactivate':
                    if ($user->roi_status === 'stopped') {
                        $this->accountService->reactivateRoiAccount($user);
                        $count++;
                    }
                    break;
                case 'check_2x':
                    if ($this->accountService->checkAndStopAccountAt2X($user)) {
                        $count++;
                    }
                    break;
            }
        }

        return redirect()->back()->with('success', "Bulk action completed on {$count} users");
    }

    /**
     * Export ROI data to CSV
     */
    public function export(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $users = User::where('roi_eligible_investment_amount', '>', 0)->get();

        $csvData = [];
        $csvData[] = [
            'User ID', 'Name', 'Email', 'Invested Amount', 'Total ROI Paid', 
            'Direct ROI', 'Commission Earned', '2X Limit', 'Remaining Amount', 
            'Completion %', 'Status', 'ROI Start Date', 'Last Payment Date'
        ];

        foreach ($users as $user) {
            $stats = $this->accountService->getRoiAccountStats($user);
            $status = $this->getCompletionStatus($stats);

            // Apply filter
            if ($filter !== 'all' && $status !== $filter) {
                continue;
            }

            $csvData[] = [
                $user->id,
                $user->name,
                $user->email,
                number_format($stats['invested_amount'], 2),
                number_format($stats['total_roi_paid'], 2),
                number_format($stats['direct_roi_paid'], 2),
                number_format($stats['commission_earned'], 2),
                number_format($stats['two_x_limit'], 2),
                number_format($stats['remaining_amount'], 2),
                number_format($stats['completion_percentage'], 2) . '%',
                ucfirst($status),
                $stats['roi_start_date'] ? $stats['roi_start_date']->format('Y-m-d') : '',
                $stats['last_payment_date'] ? $stats['last_payment_date']->format('Y-m-d') : ''
            ];
        }

        $filename = 'roi_monitoring_' . date('Y-m-d_H-i-s') . '.csv';
        
        return response()->streamDownload(function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, $filename);
    }

    /**
     * Get completion status
     */
    private function getCompletionStatus($stats): string
    {
        if ($stats['completion_percentage'] >= 100) {
            return 'completed';
        }
        
        if ($stats['roi_status'] === 'stopped') {
            return 'stopped';
        }
        
        if ($stats['is_expired']) {
            return 'expired';
        }
        
        return 'active';
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary($users): array
    {
        $totalUsers = $users->count();
        $completedUsers = 0;
        $activeUsers = 0;
        $stoppedUsers = 0;
        $expiredUsers = 0;
        $totalInvested = 0;
        $totalPaid = 0;

        foreach ($users as $user) {
            $stats = $this->accountService->getRoiAccountStats($user);
            $status = $this->getCompletionStatus($stats);
            
            $totalInvested += $stats['invested_amount'];
            $totalPaid += $stats['total_roi_paid'];
            
            switch ($status) {
                case 'completed':
                    $completedUsers++;
                    break;
                case 'active':
                    $activeUsers++;
                    break;
                case 'stopped':
                    $stoppedUsers++;
                    break;
                case 'expired':
                    $expiredUsers++;
                    break;
            }
        }

        return [
            'total_users' => $totalUsers,
            'completed_users' => $completedUsers,
            'active_users' => $activeUsers,
            'stopped_users' => $stoppedUsers,
            'expired_users' => $expiredUsers,
            'total_invested' => $totalInvested,
            'total_paid' => $totalPaid,
            'completion_rate' => $totalUsers > 0 ? ($completedUsers / $totalUsers) * 100 : 0
        ];
    }
}