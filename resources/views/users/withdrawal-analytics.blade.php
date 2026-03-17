@extends('demo.layout.app')
@section('title', 'Withdrawal Analytics')
@section('custom_css')
<style>
    .stats-card {
        border-radius: 12px;
        border: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .stats-card .card-body {
        padding: 1.5rem;
    }
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .stats-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .stats-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }
    .trend-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 20px;
    }
    .trend-up {
        background-color: rgba(40, 167, 69, 0.15);
        color: #28a745;
    }
    .trend-down {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }
    .chart-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .chart-card .card-header {
        background: transparent;
        border-bottom: 1px solid #eef2f7;
        padding: 1.25rem 1.5rem;
    }
    .chart-card .card-header h5 {
        font-weight: 600;
        color: #344767;
        margin: 0;
    }
    .filter-card {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        border-radius: 12px;
        color: white;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 8px;
    }
    .filter-card .btn-filter {
        background: white;
        color: #e74c3c;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        padding: 0.5rem 1.5rem;
    }
    .filter-card .btn-filter:hover {
        background: #f8f9fa;
    }
    .export-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
    }
    .export-btn {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        border: none;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: transform 0.2s ease;
    }
    .export-btn:hover {
        transform: scale(1.02);
        color: white;
    }
    .table-modern {
        border-radius: 12px;
        overflow: hidden;
    }
    .table-modern thead th {
        background: #f8f9fa;
        border: none;
        font-weight: 600;
        color: #344767;
        padding: 1rem;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-modern tbody td {
        padding: 0.875rem;
        vertical-align: middle;
        border-color: #eef2f7;
        font-size: 0.875rem;
    }
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 14px;
    }
    .status-badge {
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .status-pending {
        background-color: rgba(255, 193, 7, 0.15);
        color: #d39e00;
    }
    .status-approved {
        background-color: rgba(40, 167, 69, 0.15);
        color: #28a745;
    }
    .status-rejected {
        background-color: rgba(220, 53, 69, 0.15);
        color: #dc3545;
    }
    .amount-highlight {
        font-weight: 700;
        color: #e74c3c;
    }
    .page-header-gradient {
        background: linear-gradient(135deg, #c0392b 0%, #e74c3c 50%, #f39c12 100%);
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
        color: white;
    }
    .page-header-gradient h4 {
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    .page-header-gradient .breadcrumb-item a {
        color: rgba(255,255,255,0.7);
    }
    .page-header-gradient .breadcrumb-item.active {
        color: rgba(255,255,255,0.9);
    }
    .user-details-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 0.5rem;
    }
    .user-details-card .detail-label {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }
    .user-details-card .detail-value {
        font-size: 0.85rem;
        font-weight: 600;
        color: #344767;
        word-break: break-all;
    }
    /* Pagination Styles */
    .pagination {
        margin: 0;
        display: flex;
        gap: 5px;
    }
    .pagination .page-item .page-link {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 14px;
        color: #e74c3c;
        font-weight: 500;
        font-size: 14px;
        background: white;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
    }
    .pagination .page-item .page-link:hover {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        border-color: #e74c3c;
    }
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        border-color: #e74c3c;
    }
    .pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background: #f8f9fa;
        border-color: #dee2e6;
        cursor: not-allowed;
    }
    .mini-stat-card {
        background: white;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .mini-stat-card .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-size: 20px;
    }
</style>
@endsection

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header-gradient mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h4 class="mb-1">Withdrawal Analytics Dashboard</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('withdrawals.requests') }}">Withdrawals</a></li>
                                <li class="breadcrumb-item active">Analytics</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('withdrawals.requests') }}" class="btn btn-light btn-sm">
                            <i class="la la-arrow-left"></i> Back to Requests
                        </a>
                    </div>
                </div>
            </div>

            <!-- Month/Year Filter -->
            <div class="card filter-card mb-4">
                <div class="card-body py-3">
                    <form action="{{ route('withdrawals.analytics') }}" method="GET" class="row align-items-center g-3">
                        <div class="col-auto">
                            <label class="text-white-50 small mb-1">Select Period</label>
                        </div>
                        <div class="col-md-2">
                            <select name="month" class="form-select">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="year" class="form-select">
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="all" {{ $selectedStatus == 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="pending" {{ $selectedStatus == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $selectedStatus == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $selectedStatus == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-filter">
                                <i class="la la-filter"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="stats-label text-white-50 mb-1">Total Withdrawal Amount</p>
                                    <h3 class="stats-value mb-2">${{ number_format($monthlyStats['total_amount'], 2) }}</h3>
                                    @if($monthlyStats['percentage_change'] != 0)
                                        <span class="trend-badge {{ $monthlyStats['percentage_change'] > 0 ? 'trend-up' : 'trend-down' }}">
                                            <i class="la {{ $monthlyStats['percentage_change'] > 0 ? 'la-arrow-up' : 'la-arrow-down' }}"></i>
                                            {{ abs($monthlyStats['percentage_change']) }}% vs last month
                                        </span>
                                    @endif
                                </div>
                                <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                    <i class="la la-money-bill-wave text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="stats-label text-white-50 mb-1">Approved Amount</p>
                                    <h3 class="stats-value mb-2">${{ number_format($monthlyStats['approved_amount'], 2) }}</h3>
                                    <span class="text-white-50 small">{{ $monthlyStats['approved_count'] }} requests approved</span>
                                </div>
                                <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                    <i class="la la-check-circle text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="stats-label text-white-50 mb-1">Pending Requests</p>
                                    <h3 class="stats-value mb-2">{{ number_format($monthlyStats['pending_count']) }}</h3>
                                    <span class="text-white-50 small">Awaiting approval</span>
                                </div>
                                <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                    <i class="la la-clock text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="stats-label text-white-50 mb-1">Total Requests</p>
                                    <h3 class="stats-value mb-2">{{ number_format($monthlyStats['total_requests']) }}</h3>
                                    <span class="text-white-50 small">{{ $monthlyStats['rejected_count'] }} rejected</span>
                                </div>
                                <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                    <i class="la la-file-invoice-dollar text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <!-- Daily Breakdown Chart -->
                <div class="col-xl-8 mb-4">
                    <div class="card chart-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="la la-chart-bar text-danger mr-2"></i> Daily Withdrawal Breakdown</h5>
                            <span class="badge badge-light-danger">{{ Carbon\Carbon::create()->month($selectedMonth)->format('F') }} {{ $selectedYear }}</span>
                        </div>
                        <div class="card-body">
                            <div id="dailyBreakdownChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Status & Type Breakdown -->
                <div class="col-xl-4 mb-4">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <h5><i class="la la-pie-chart text-info mr-2"></i> Status Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <div id="statusPieChart" style="height: 200px;"></div>
                            <hr>
                            <h6 class="mb-3">By Payment Type</h6>
                            <div class="row">
                                @foreach($typeBreakdown as $type)
                                    <div class="col-4">
                                        <div class="mini-stat-card">
                                            <div class="stat-icon" style="background: {{ $type->request_type == 'usdt' ? 'rgba(52, 152, 219, 0.15)' : ($type->request_type == 'bank' ? 'rgba(155, 89, 182, 0.15)' : 'rgba(46, 204, 113, 0.15)') }};">
                                                <i class="la {{ $type->request_type == 'usdt' ? 'la-bitcoin text-primary' : ($type->request_type == 'bank' ? 'la-university text-purple' : 'la-money-bill text-success') }}"></i>
                                            </div>
                                            <div class="font-weight-bold">{{ $type->count }}</div>
                                            <small class="text-muted text-uppercase">{{ $type->request_type ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend Chart -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card chart-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="la la-chart-line text-success mr-2"></i> 12-Month Withdrawal Trend</h5>
                        </div>
                        <div class="card-body">
                            <div id="monthlyTrendChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="export-section">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="font-weight-bold mb-1"><i class="la la-download text-danger mr-2"></i> Export Withdrawal Data</h5>
                                <p class="text-muted mb-0">Download detailed withdrawal reports with user bank details</p>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('withdrawals.export') }}" method="GET" class="row g-2 justify-content-end">
                                    <div class="col-auto">
                                        <input type="date" name="start_date" class="form-control" placeholder="Start Date">
                                    </div>
                                    <div class="col-auto">
                                        <input type="date" name="end_date" class="form-control" placeholder="End Date">
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="export-btn">
                                            <i class="la la-file-excel mr-1"></i> Export to Excel
                                        </button>
                                    </div>
                                </form>
                                <div class="mt-2 text-right">
                                    <small class="text-muted">Or export current month: </small>
                                    <a href="{{ route('withdrawals.export', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                                       class="btn btn-sm btn-outline-danger">
                                        Export {{ Carbon\Carbon::create()->month($selectedMonth)->format('F') }} {{ $selectedYear }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Withdrawals Table with User Details -->
            <div class="row">
                <div class="col-12">
                    <div class="card chart-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="la la-list text-info mr-2"></i> Recent Withdrawal Requests</h5>
                            <span class="badge badge-danger">{{ $recentWithdrawals->total() }} Total Records</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-modern mb-0">
                                    <thead>
                                        <tr>
                                            <th>S#</th>
                                            <th>User Info</th>
                                            <th>Contact & Bank</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentWithdrawals as $index => $withdrawal)
                                            <tr>
                                                <td>{{ $recentWithdrawals->firstItem() + $index }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar mr-3" style="width: 35px; height: 35px; font-size: 12px;">
                                                            {{ strtoupper(substr($withdrawal->user->name ?? 'U', 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">{{ $withdrawal->user->username ?? 'N/A' }}</div>
                                                            <small class="text-muted">{{ $withdrawal->user->name ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="user-details-card">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="detail-label">Phone</div>
                                                                <div class="detail-value">{{ $withdrawal->user->profile->phone ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="detail-label">Bank</div>
                                                                <div class="detail-value">{{ $withdrawal->user->profile->bank_name ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="col-6">
                                                                <div class="detail-label">Account #</div>
                                                                <div class="detail-value">{{ $withdrawal->user->profile->account_number ?? 'N/A' }}</div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="detail-label">IBAN</div>
                                                                <div class="detail-value">{{ $withdrawal->user->profile->ibn_number ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2">
                                                            <div class="col-12">
                                                                <div class="detail-label">Account Title</div>
                                                                <div class="detail-value">{{ $withdrawal->user->profile->account_title ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="amount-highlight font-size-lg">${{ number_format($withdrawal->amount, 2) }}</span>
                                                    @if($withdrawal->transfer_fee)
                                                        <br><small class="text-muted">Fee: ${{ number_format($withdrawal->transfer_fee, 2) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-{{ $withdrawal->request_type == 'usdt' ? 'primary' : ($withdrawal->request_type == 'bank' ? 'info' : 'success') }}">
                                                        {{ ucfirst($withdrawal->request_type ?? 'N/A') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-{{ $withdrawal->status }}">
                                                        {{ ucfirst($withdrawal->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div>{{ Carbon\Carbon::parse($withdrawal->created_at)->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ Carbon\Carbon::parse($withdrawal->created_at)->format('h:i A') }}</small>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="la la-inbox la-3x mb-3 d-block"></i>
                                                        No withdrawal requests found for this period
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($recentWithdrawals->hasPages())
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="text-muted small mb-2 mb-md-0">
                                        Showing {{ $recentWithdrawals->firstItem() }} to {{ $recentWithdrawals->lastItem() }} of {{ $recentWithdrawals->total() }} entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            @if ($recentWithdrawals->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link">&laquo; Previous</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $recentWithdrawals->appends(request()->query())->previousPageUrl() }}">&laquo; Previous</a>
                                                </li>
                                            @endif

                                            @foreach ($recentWithdrawals->appends(request()->query())->getUrlRange(1, $recentWithdrawals->lastPage()) as $page => $url)
                                                @if ($page == $recentWithdrawals->currentPage())
                                                    <li class="page-item active">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                            @if ($recentWithdrawals->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $recentWithdrawals->appends(request()->query())->nextPageUrl() }}">Next &raquo;</a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                    <span class="page-link">Next &raquo;</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Breakdown Chart
    var dailyOptions = {
        series: [{
            name: 'Amount ($)',
            type: 'column',
            data: @json($dailyData['amounts'])
        }, {
            name: 'Requests',
            type: 'line',
            data: @json($dailyData['counts'])
        }],
        chart: {
            height: 350,
            type: 'line',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                }
            },
            fontFamily: 'Poppins, sans-serif'
        },
        stroke: {
            width: [0, 3],
            curve: 'smooth'
        },
        colors: ['#e74c3c', '#3498db'],
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '60%',
            }
        },
        fill: {
            type: ['solid', 'solid'],
            opacity: [0.9, 1]
        },
        labels: @json($dailyData['labels']),
        markers: {
            size: 4,
            hover: {
                size: 6
            }
        },
        xaxis: {
            title: {
                text: 'Day of Month'
            },
            labels: {
                style: {
                    colors: '#6c757d'
                }
            }
        },
        yaxis: [{
            title: {
                text: 'Amount ($)',
            },
            labels: {
                formatter: function(val) {
                    return '$' + val.toLocaleString();
                },
                style: {
                    colors: '#e74c3c'
                }
            }
        }, {
            opposite: true,
            title: {
                text: 'Requests',
            },
            labels: {
                style: {
                    colors: '#3498db'
                }
            }
        }],
        tooltip: {
            shared: true,
            intersect: false,
            y: [{
                formatter: function(val) {
                    return '$' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            }, {
                formatter: function(val) {
                    return val + ' requests';
                }
            }]
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4
        }
    };

    var dailyChart = new ApexCharts(document.querySelector("#dailyBreakdownChart"), dailyOptions);
    dailyChart.render();

    // Status Pie Chart
    var statusPieOptions = {
        series: [{{ $statusBreakdown['pending']['count'] }}, {{ $statusBreakdown['approved']['count'] }}, {{ $statusBreakdown['rejected']['count'] }}],
        chart: {
            type: 'donut',
            height: 200
        },
        labels: ['Pending', 'Approved', 'Rejected'],
        colors: ['#f39c12', '#27ae60', '#e74c3c'],
        legend: {
            position: 'bottom'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '14px',
                            fontWeight: 600
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false
        }
    };

    var statusPieChart = new ApexCharts(document.querySelector("#statusPieChart"), statusPieOptions);
    statusPieChart.render();

    // Monthly Trend Chart
    var monthlyOptions = {
        series: [{
            name: 'Total Amount ($)',
            type: 'area',
            data: @json($monthlyTrend['amounts'])
        }, {
            name: 'Total Requests',
            type: 'line',
            data: @json($monthlyTrend['counts'])
        }],
        chart: {
            height: 350,
            type: 'line',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                }
            },
            fontFamily: 'Poppins, sans-serif'
        },
        stroke: {
            width: [0, 3],
            curve: 'smooth'
        },
        colors: ['#e74c3c', '#9b59b6'],
        fill: {
            type: ['gradient', 'solid'],
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.5,
                gradientToColors: ['#f39c12'],
                opacityFrom: 0.7,
                opacityTo: 0.2,
            }
        },
        labels: @json($monthlyTrend['labels']),
        markers: {
            size: [0, 5],
            hover: {
                size: 7
            }
        },
        xaxis: {
            labels: {
                rotate: -45,
                style: {
                    colors: '#6c757d',
                    fontSize: '11px'
                }
            }
        },
        yaxis: [{
            title: {
                text: 'Amount ($)',
            },
            labels: {
                formatter: function(val) {
                    return '$' + (val / 1000).toFixed(1) + 'K';
                },
                style: {
                    colors: '#e74c3c'
                }
            }
        }, {
            opposite: true,
            title: {
                text: 'Requests',
            },
            labels: {
                style: {
                    colors: '#9b59b6'
                }
            }
        }],
        tooltip: {
            shared: true,
            intersect: false,
            y: [{
                formatter: function(val) {
                    return '$' + val.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }
            }, {
                formatter: function(val) {
                    return val + ' requests';
                }
            }]
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right'
        },
        grid: {
            borderColor: '#f1f1f1',
            strokeDashArray: 4
        }
    };

    var monthlyChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyOptions);
    monthlyChart.render();
});
</script>
@endsection
