@extends('demo.layout.app')
@section('title', 'TopUp Analytics')
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        color: #667eea;
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
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-modern tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-color: #eef2f7;
    }
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 14px;
    }
    .rank-badge {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
    }
    .rank-1 { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: white; }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%); color: white; }
    .rank-3 { background: linear-gradient(135deg, #CD7F32 0%, #B8860B 100%); color: white; }
    .rank-default { background: #e9ecef; color: #6c757d; }
    .amount-highlight {
        font-weight: 700;
        color: #28a745;
    }
    .page-header-gradient {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
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
        color: #667eea;
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }
    .pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background: #f8f9fa;
        border-color: #dee2e6;
        cursor: not-allowed;
    }
    .pagination .page-item .page-link svg {
        width: 16px !important;
        height: 16px !important;
        max-width: 16px !important;
        max-height: 16px !important;
    }
    .card-footer .pagination {
        justify-content: flex-end;
    }
    /* Fix for large arrow icons */
    .card-footer svg,
    .pagination svg {
        width: 16px !important;
        height: 16px !important;
        max-width: 16px !important;
        max-height: 16px !important;
    }
    /* Override any inline SVG styles */
    .page-link[rel="prev"] svg,
    .page-link[rel="next"] svg {
        width: 14px !important;
        height: 14px !important;
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
                        <h4 class="mb-1">TopUp Analytics Dashboard</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb bg-transparent p-0 m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('account.topups.index') }}">TopUps</a></li>
                                <li class="breadcrumb-item active">Analytics</li>
                            </ol>
                        </nav>
                    </div>
                    <div>
                        <a href="{{ route('account.topups.index') }}" class="btn btn-light btn-sm">
                            <i class="la la-arrow-left"></i> Back to TopUps
                        </a>
                    </div>
                </div>
            </div>

            <!-- Month/Year Filter -->
            <div class="card filter-card mb-4">
                <div class="card-body py-3">
                    <form action="{{ route('account.topups.analytics') }}" method="GET" class="row align-items-center g-3">
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
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="stats-label text-white-50 mb-1">Total TopUp Amount</p>
                                    <h3 class="stats-value mb-2">${{ number_format($monthlyStats['total_amount'], 2) }}</h3>
                                    @if($monthlyStats['percentage_change'] != 0)
                                        <span class="trend-badge {{ $monthlyStats['percentage_change'] > 0 ? 'trend-up' : 'trend-down' }}">
                                            <i class="la {{ $monthlyStats['percentage_change'] > 0 ? 'la-arrow-up' : 'la-arrow-down' }}"></i>
                                            {{ abs($monthlyStats['percentage_change']) }}% vs last month
                                        </span>
                                    @endif
                                </div>
                                <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                    <i class="la la-dollar-sign text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="stats-label text-white-50 mb-1">Total Transactions</p>
                                    <h3 class="stats-value mb-2">{{ number_format($monthlyStats['total_transactions']) }}</h3>
                                    <span class="text-white-50 small">TopUp operations</span>
                                </div>
                                <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                    <i class="la la-exchange-alt text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="stats-label text-white-50 mb-1">Unique Users</p>
                                    <h3 class="stats-value mb-2">{{ number_format($monthlyStats['unique_users']) }}</h3>
                                    <span class="text-white-50 small">Active members</span>
                                </div>
                                <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                    <i class="la la-users text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <div class="card-body text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="stats-label text-white-50 mb-1">Average TopUp</p>
                                    <h3 class="stats-value mb-2">${{ number_format($monthlyStats['average_topup'], 2) }}</h3>
                                    <span class="text-white-50 small">Per transaction</span>
                                </div>
                                <div class="stats-icon" style="background: rgba(255,255,255,0.2);">
                                    <i class="la la-chart-line text-white"></i>
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
                            <h5><i class="la la-chart-bar text-primary mr-2"></i> Daily TopUp Breakdown</h5>
                            <span class="badge badge-light-primary">{{ Carbon\Carbon::create()->month($selectedMonth)->format('F') }} {{ $selectedYear }}</span>
                        </div>
                        <div class="card-body">
                            <div id="dailyBreakdownChart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Top Users -->
                <div class="col-xl-4 mb-4">
                    <div class="card chart-card h-100">
                        <div class="card-header">
                            <h5><i class="la la-trophy text-warning mr-2"></i> Top Users by TopUp</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        @forelse($topUsers as $index => $topUser)
                                            <tr>
                                                <td class="py-3 px-4">
                                                    <div class="d-flex align-items-center">
                                                        <span class="rank-badge {{ $index < 3 ? 'rank-' . ($index + 1) : 'rank-default' }} mr-3">
                                                            {{ $index + 1 }}
                                                        </span>
                                                        <div class="user-avatar mr-3">
                                                            {{ strtoupper(substr($topUser->investor->name ?? 'U', 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold text-dark">{{ $topUser->investor->username ?? 'N/A' }}</div>
                                                            <small class="text-muted">{{ $topUser->topup_count }} investments</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 text-right">
                                                    <span class="amount-highlight">${{ number_format($topUser->total_topup, 2) }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center py-4 text-muted">
                                                    No topup data for this month
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
                            <h5><i class="la la-chart-line text-success mr-2"></i> 12-Month TopUp Trend</h5>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-light-success mr-2">Amount</span>
                                <span class="badge badge-light-info">Count</span>
                            </div>
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
                                <h5 class="font-weight-bold mb-1"><i class="la la-download text-success mr-2"></i> Export TopUp Data</h5>
                                <p class="text-muted mb-0">Download detailed topup reports in Excel format</p>
                            </div>
                            <div class="col-md-6">
                                <form action="{{ route('account.topups.export') }}" method="GET" class="row g-2 justify-content-end">
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
                                    <a href="{{ route('account.topups.export', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
                                       class="btn btn-sm btn-outline-success">
                                        Export {{ Carbon\Carbon::create()->month($selectedMonth)->format('F') }} {{ $selectedYear }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent TopUps Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card chart-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5><i class="la la-list text-info mr-2"></i> Recent TopUp Transactions</h5>
                            <span class="badge badge-primary">{{ $recentTopups->total() }} Total Records</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-modern mb-0">
                                    <thead>
                                        <tr>
                                            <th>S#</th>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Date & Time</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentTopups as $index => $investment)
                                            <tr>
                                                <td>{{ $recentTopups->firstItem() + $index }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar mr-3" style="width: 35px; height: 35px; font-size: 12px;">
                                                            {{ strtoupper(substr($investment->investor->name ?? 'U', 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">{{ $investment->investor->username ?? 'N/A' }}</div>
                                                            <small class="text-muted">{{ $investment->investor->name ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="amount-highlight font-size-lg">${{ number_format($investment->amount, 2) }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-light-{{ $investment->type == 'topup' ? 'primary' : ($investment->type == 'join' ? 'success' : 'warning') }}">{{ ucfirst($investment->type) }}</span>
                                                </td>
                                                <td>
                                                    <div>{{ Carbon\Carbon::parse($investment->created_at)->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ Carbon\Carbon::parse($investment->created_at)->format('h:i A') }}</small>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="la la-inbox la-3x mb-3 d-block"></i>
                                                        No investment transactions found for this period
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($recentTopups->hasPages())
                            <div class="card-footer bg-white border-top">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="text-muted small mb-2 mb-md-0">
                                        Showing {{ $recentTopups->firstItem() }} to {{ $recentTopups->lastItem() }} of {{ $recentTopups->total() }} entries
                                    </div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination mb-0">
                                            {{-- Previous Page Link --}}
                                            @if ($recentTopups->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link">&laquo; Previous</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $recentTopups->appends(request()->query())->previousPageUrl() }}">&laquo; Previous</a>
                                                </li>
                                            @endif

                                            {{-- Pagination Elements --}}
                                            @foreach ($recentTopups->appends(request()->query())->getUrlRange(1, $recentTopups->lastPage()) as $page => $url)
                                                @if ($page == $recentTopups->currentPage())
                                                    <li class="page-item active">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                            {{-- Next Page Link --}}
                                            @if ($recentTopups->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $recentTopups->appends(request()->query())->nextPageUrl() }}">Next &raquo;</a>
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
            name: 'Transactions',
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
        colors: ['#667eea', '#f5576c'],
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
                    colors: '#667eea'
                }
            }
        }, {
            opposite: true,
            title: {
                text: 'Transactions',
            },
            labels: {
                style: {
                    colors: '#f5576c'
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
                    return val + ' transactions';
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

    // Monthly Trend Chart
    var monthlyOptions = {
        series: [{
            name: 'Total Amount ($)',
            type: 'area',
            data: @json($monthlyTrend['amounts'])
        }, {
            name: 'Total Transactions',
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
        colors: ['#11998e', '#4facfe'],
        fill: {
            type: ['gradient', 'solid'],
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.5,
                gradientToColors: ['#38ef7d'],
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
                    colors: '#11998e'
                }
            }
        }, {
            opposite: true,
            title: {
                text: 'Transactions',
            },
            labels: {
                style: {
                    colors: '#4facfe'
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
                    return val + ' transactions';
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
