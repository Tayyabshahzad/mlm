@extends('demo.layout.app')
@section('content')
<style>
    :root {
        --primary-color: #667eea;
        --primary-dark: #5a67d8;
        --secondary-color: #764ba2;
        --accent-color: #f093fb;
        --success-color: #48bb78;
        --warning-color: #ed8936;
        --danger-color: #f56565;
        --info-color: #4299e1;
        --light-color: #f7fafc;
        --dark-color: #1a202c;
        --gray-100: #f7fafc;
        --gray-200: #edf2f7;
        --gray-300: #e2e8f0;
        --gray-400: #cbd5e0;
        --gray-500: #a0aec0;
        --gray-600: #718096;
        --gray-700: #4a5568;
        --gray-800: #2d3748;
        --gray-900: #1a202c;
        --card-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        --card-shadow-hover: 0 20px 40px rgba(0, 0, 0, 0.1);
        --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-success: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
        --gradient-warning: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
        --gradient-danger: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
        --gradient-info: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        color: var(--gray-800);
        line-height: 1.6;
    }

    /* Dashboard Container */
    .dashboard-container {
        padding: 2rem 0;
        min-height: 100vh;
    }

    /* Header Section */
    .dashboard-header {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--gray-200);
    }

    .dashboard-title {
        font-size: 2.5rem;
        font-weight: 800;
        background: var(--gradient-primary);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.5rem;
    }

    .dashboard-subtitle {
        color: var(--gray-600);
        font-size: 1.1rem;
        font-weight: 500;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--gray-200);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-shadow-hover);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--gradient);
    }

    .stat-card.primary::before { background: var(--gradient-primary); }
    .stat-card.success::before { background: var(--gradient-success); }
    .stat-card.warning::before { background: var(--gradient-warning); }
    .stat-card.danger::before { background: var(--gradient-danger); }
    .stat-card.info::before { background: var(--gradient-info); }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }

    .stat-icon.primary { background: var(--gradient-primary); }
    .stat-icon.success { background: var(--gradient-success); }
    .stat-icon.warning { background: var(--gradient-warning); }
    .stat-icon.danger { background: var(--gradient-danger); }
    .stat-icon.info { background: var(--gradient-info); }

    .stat-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .stat-change {
        font-size: 0.875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-change.positive { color: var(--success-color); }
    .stat-change.negative { color: var(--danger-color); }

    /* Pulse Animation for Danger Box */
    @keyframes pulse-danger {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(245, 101, 101, 0.7);
        }
        50% {
            box-shadow: 0 0 0 15px rgba(245, 101, 101, 0);
        }
    }

    /* Chart Section */
    .chart-container {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--gray-200);
    }

    .chart-header {
        display: flex;
        justify-content: between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .chart-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .chart-filters {
        display: flex;
        gap: 0.5rem;
    }

    .filter-btn {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        border: 2px solid var(--gray-300);
        background: white;
        color: var(--gray-600);
        font-weight: 600;
        transition: var(--transition);
        cursor: pointer;
    }

    .filter-btn.active {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: white;
    }

    .filter-btn:hover {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }

    /* Progress Cards */
    .progress-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .progress-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--gray-200);
        transition: var(--transition);
    }

    .progress-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-shadow-hover);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .progress-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .progress-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress-badge.success {
        background: rgba(72, 187, 120, 0.1);
        color: var(--success-color);
    }

    .progress-badge.danger {
        background: rgba(245, 101, 101, 0.1);
        color: var(--danger-color);
    }

    .progress-badge.warning {
        background: rgba(237, 137, 54, 0.1);
        color: var(--warning-color);
    }

    .progress-bar-container {
        background: var(--gray-200);
        border-radius: 10px;
        height: 12px;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        border-radius: 10px;
        transition: width 1s ease;
        position: relative;
    }

    .progress-bar.primary { background: var(--gradient-primary); }
    .progress-bar.success { background: var(--gradient-success); }
    .progress-bar.warning { background: var(--gradient-warning); }
    .progress-bar.danger { background: var(--gradient-danger); }

    .progress-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        text-align: center;
    }

    .progress-stat-item {
        padding: 1rem;
        background: var(--gray-100);
        border-radius: 12px;
    }

    .progress-stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.25rem;
    }

    .progress-stat-label {
        font-size: 0.75rem;
        color: var(--gray-600);
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Reward Announcement */
    .reward-announcement {
        background: var(--gradient-primary);
        border-radius: 20px;
        padding: 2rem;
        margin: 2rem 0;
        position: relative;
        overflow: hidden;
        color: white;
    }

    .reward-announcement::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }

    .reward-content {
        position: relative;
        z-index: 2;
    }

    .reward-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .reward-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-right: 1.5rem;
        backdrop-filter: blur(10px);
    }

    .reward-title {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0;
    }

    .reward-subtitle {
        font-size: 1rem;
        opacity: 0.9;
        margin: 0.5rem 0 0 0;
    }

    .reward-description {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 1.5rem;
        line-height: 1.6;
    }

    .reward-highlights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .reward-highlight {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 15px;
        padding: 1.5rem 1rem;
        text-align: center;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .reward-highlight-value {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .reward-highlight-label {
        font-size: 0.875rem;
        opacity: 0.9;
        font-weight: 600;
    }

    /* Target Cards */
    .target-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .target-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--gray-200);
        transition: var(--transition);
    }

    .target-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-shadow-hover);
    }

    .target-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .target-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .target-subtitle {
        color: var(--gray-600);
        font-size: 1rem;
    }

    .circular-progress {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto 2rem;
    }

    .circular-progress svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .circular-progress .progress-ring {
        fill: none;
        stroke: var(--gray-200);
        stroke-width: 8;
    }

    .circular-progress .progress-fill {
        fill: none;
        stroke: var(--primary-color);
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dasharray 1s ease;
    }

    .circular-progress .progress-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .progress-percentage {
        font-size: 2rem;
        font-weight: 800;
        color: var(--gray-900);
        display: block;
    }

    .progress-label {
        font-size: 0.875rem;
        color: var(--gray-600);
        font-weight: 600;
    }

    .target-btn {
        width: 100%;
        padding: 1rem 2rem;
        background: var(--gradient-primary);
        color: white;
        border: none;
        border-radius: 15px;
        font-size: 1rem;
        font-weight: 600;
        transition: var(--transition);
        cursor: pointer;
    }

    .target-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    /* Detail Cards */
    .detail-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        margin-top: 2rem;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--gray-200);
        display: none;
    }

    .detail-card.show {
        display: block;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .detail-header {
        border-bottom: 1px solid var(--gray-200);
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }

    .detail-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .level-grid {
        display: grid;
        gap: 1rem;
    }

    .level-item {
        display: grid;
        grid-template-columns: auto 1fr auto auto;
        gap: 1rem;
        align-items: center;
        padding: 1.5rem;
        background: var(--gray-100);
        border-radius: 15px;
        transition: var(--transition);
    }

    .level-item:hover {
        background: var(--gray-200);
    }

    .level-badge {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: var(--gradient-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }

    /* Rank Images */
    .rank-image {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary-color);
        margin: 0 auto 1rem;
        display: block;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .rank-item {
        text-align: center;
        padding: 1.5rem;
        background: var(--gray-100);
        border-radius: 15px;
        transition: var(--transition);
        border: 2px solid transparent;
    }

    .rank-item:hover {
        background: white;
        border-color: var(--primary-color);
        transform: translateY(-3px);
        box-shadow: var(--card-shadow);
    }

    .rank-item.achieved {
        background: linear-gradient(135deg, rgba(72, 187, 120, 0.1), rgba(56, 161, 105, 0.05));
        border-color: var(--success-color);
    }

    .rank-item.achieved .rank-image {
        border-color: var(--success-color);
        box-shadow: 0 8px 25px rgba(72, 187, 120, 0.3);
    }

    .rank-name {
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 0.5rem;
    }

    .rank-description {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-bottom: 1rem;
    }

    .rank-requirements {
        font-size: 0.75rem;
        color: var(--gray-500);
        font-weight: 600;
    }

    .rank-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .level-info h6 {
        margin: 0 0 0.25rem 0;
        font-weight: 700;
        color: var(--gray-900);
    }

    .level-info p {
        margin: 0;
        color: var(--gray-600);
        font-size: 0.875rem;
    }

    .level-progress {
        background: var(--gray-300);
        border-radius: 10px;
        height: 8px;
        min-width: 120px;
        overflow: hidden;
    }

    .level-progress-fill {
        height: 100%;
        background: var(--gradient-primary);
        border-radius: 10px;
        transition: width 1s ease;
    }

    .level-stats {
        text-align: right;
    }

    .level-count {
        font-weight: 700;
        color: var(--gray-900);
        font-size: 1.1rem;
    }

    .level-required {
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem 0;
        }

        .dashboard-header {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .dashboard-title {
            font-size: 2rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .stat-card {
            padding: 1.5rem;
        }

        .progress-grid,
        .target-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .reward-announcement {
            padding: 1.5rem;
        }

        .reward-header {
            flex-direction: column;
            text-align: center;
        }

        .reward-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }

        .reward-highlights {
            grid-template-columns: 1fr;
        }

        .chart-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .level-item {
            grid-template-columns: 1fr;
            gap: 1rem;
            text-align: center;
        }
    }

    /* Animation Classes */
    .fade-in {
        animation: fadeIn 0.6s ease forwards;
    }

    .slide-up {
        animation: slideUp 0.6s ease forwards;
    }

    .scale-in {
        animation: scaleIn 0.6s ease forwards;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="py-2 subheader py-lg-4 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <!--begin::Info-->
            <div class="flex-wrap mr-2 d-flex align-items-center">
                <!--begin::Page Title-->
                <h5 class="mt-2 mb-2 mr-5 text-dark font-weight-bold">Dashboard</h5>
                <div class="mt-2 mb-2 mr-4 bg-gray-200 subheader-separator subheader-separator-ver"></div>
                <span class="mr-4 text-muted font-weight-bold">Level 1 </span>
                <div class="kt-widget__content">
                    <div class="kt-widget__section">
                        <a href="#" class="kt-widget__username">
                            <i class="flaticon2-correct kt-font-success"></i>
                        </a>
                    </div>
                </div>
                <div class="mt-2 mb-2 ml-2 mr-4 bg-gray-200 subheader-separator subheader-separator-ver"></div>
                <span class="mr-4 text-muted font-weight-bold">Available Balance </span>
                <div class="kt-widget__content">
                    <div class="kt-widget__section">
                        <strong>${{ Auth::user()->roi_eligible_investment_amount }}</strong>
                    </div>
                </div>
                @role('admin')
                <span class="text-muted font-weight-bold ml-15">Time & Time Zone </span>
                <div class="ml-5 kt-widget__content">
                    <div class="kt-widget__section">
                        <strong>
                             {{ now()->format('h:i A') }}
                            ({{ config('app.timezone') }})</strong>
                    </div>
                </div>
                @endrole
            </div>
        </div>
    </div>
    <!--end::Subheader-->

    <!--begin::Dashboard Content-->
    <div class="dashboard-container">
        <div class="container">

            <!-- Dashboard Header -->
            <div class="dashboard-header fade-in">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="dashboard-title">Welcome back, {{ Auth::user()->name }}!</h1>
                        <p class="dashboard-subtitle">Here's what's happening with your Global Visioners account today.</p>
                    </div>
                    <div class="col-lg-4 text-lg-right">
                        <div class="d-flex align-items-center justify-content-lg-end">
                            <div class="mr-3">
                                <div class="text-muted small">Last Login</div>
                                <div class="font-weight-bold">{{ now()->format('M d, Y') }}</div>
                            </div>
                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="text-white fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid fade-in">
                <!-- Online Wallet -->

                <div class="stat-card primary">
                    <div class="stat-icon primary">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-label">Total Earnings</div>
                    <div class="stat-value">${{ number_format($data['total_earning'], 2) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>All-time earnings</span>
                    </div>
                </div>

                <!-- Your Rank -->
                <div class="stat-card success">
                    <div class="stat-icon success">
                        <i class="fas fa-crown"></i>
                    </div>
                    <div class="stat-label">Your Rank</div>
                    <div class="stat-value">VISIONER</div>
                    <div class="stat-change positive">
                        <i class="fas fa-star"></i>
                        <span>Active member status</span>
                    </div>
                </div>
                
                <div class="stat-card primary">
                    <div class="stat-icon primary">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-label">Online Wallet</div>
                    <div class="stat-value">${{ number_format($data['online_wallet'], 2) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>12.5% from last month</span>
                    </div>
                </div>

                <!-- ROI Wallet -->
                <div class="stat-card success">
                    <div class="stat-icon success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-label">ROI Earnings</div>
                    <div class="stat-value">${{ number_format($data['roi'], 2) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>8.3% from last week</span>
                    </div>
                </div>

                <!-- Direct/Indirect -->
                <div class="stat-card warning">
                    <div class="stat-icon warning">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-label">Direct / Indirect</div>
                    <div class="stat-value">${{ number_format($data['direct_indirect'], 2) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>15.2% from last month</span>
                    </div>
                </div>

                <!-- Total Team -->
                <div class="stat-card info">
                    <div class="stat-icon info">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div class="stat-label">Total Team Size</div>
                    <div class="stat-value">{{ number_format($data['totalTeam']) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>{{ $data['totalTeam'] > 0 ? '+' . rand(1,5) : '0' }} new members</span>
                    </div>
                </div>

                <!-- Profit Share -->
                <div class="stat-card danger">
                    <div class="stat-icon danger">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-label">Profit Sharing</div>
                    <div class="stat-value">${{ number_format($data['profit_share'], 2) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Monthly distribution</span>
                    </div>
                </div>

                <!-- Rewards -->
                <div class="stat-card primary">
                    <div class="stat-icon primary">
                        <i class="fas fa-gift"></i>
                    </div>
                    <div class="stat-label">Rewards Earned</div>
                    <div class="stat-value">${{ number_format($data['rewardWallet'], 2) }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Achievement bonuses</span>
                    </div>
                </div>
            </div>

            <!-- Admin Only: ROI Missing Alert Box -->
            @role('admin')
            @if($data['missed_roi_count'] > 0)
            <a href="{{ route('roi.submission.monitoring') }}" class="text-decoration-none">
                <div class="stat-card danger" style="cursor: pointer; animation: pulse-danger 2s infinite; mb-10">
                    <div class="stat-icon danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-label">⚠️ ROI NOT SUBMITTED</div>
                    <div class="stat-value">{{ $data['missed_roi_count'] }} Users</div>
                    <div class="stat-change negative">
                        <i class="fas fa-users-slash"></i>
                        <span>Users missing today's ROI - Click to review</span>
                    </div>
                </div>
            </a>
            @endif
            @endrole

            <!-- Business Analytics Chart -->
            <div class="chart-container slide-up mt-7">
                <div class="chart-header">
                    <h3 class="chart-title">Business Analytics</h3>
                    <div class="chart-filters">
                        <button class="filter-btn active" data-period="7d">7 Days</button>
                        <button class="filter-btn" data-period="30d">30 Days</button>
                        <button class="filter-btn" data-period="90d">90 Days</button>
                        <button class="filter-btn" data-period="1y">1 Year</button>
                    </div>
                </div>
                <div id="businessChart" style="height: 400px;"></div>
            </div>

            <!-- ROI Progress Cards -->
            <div class="progress-grid scale-in">
                <!-- 2X Investment Progress -->
                <div class="progress-card">
                    <div class="progress-header">
                        <h4 class="progress-title">2X ROI Progress</h4>
                        <span class="progress-badge {{ $data['roi_stats']['has_reached_2x'] ? 'danger' : 'success' }}">
                            {{ $data['roi_stats']['has_reached_2x'] ? 'Completed' : 'Active' }}
                        </span>
                    </div>

                    <div class="progress-bar-container">
                        <div class="progress-bar {{ $data['roi_stats']['has_reached_2x'] ? 'danger' : 'primary' }}"
                             style="width: {{ min($data['roi_stats']['completion_percentage'], 100) }}%"></div>
                    </div>

                    <div class="progress-stats">
                        <div class="progress-stat-item">
                            <div class="progress-stat-value">${{ number_format($data['roi_stats']['invested_amount'], 2) }}</div>
                            <div class="progress-stat-label">Investment</div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-value">${{ number_format($data['roi_stats']['total_roi_paid'], 2) }}</div>
                            <div class="progress-stat-label">Earned</div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-value">${{ number_format($data['roi_stats']['remaining_amount'], 2) }}</div>
                            <div class="progress-stat-label">Remaining</div>
                        </div>
                    </div>

                    @if($data['roi_stats']['has_reached_2x'])
                    <div class="mt-3 text-center alert alert-danger" style="margin-bottom: 0; border-radius: 12px;">
                        <i class="fas fa-check-circle"></i> 2X ROI Target Achieved!
                    </div>
                    @endif
                </div>

                <!-- 7X Withdrawal Control -->
                <div class="progress-card">
                    <div class="progress-header">
                        <h4 class="progress-title">7X Withdrawal Control</h4>
                        <span class="progress-badge {{ $data['roi_stats']['has_reached_7x'] ? 'danger' : 'warning' }}">
                            {{ $data['roi_stats']['withdrawal_enabled'] ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>

                    <div class="progress-bar-container">
                        <div class="progress-bar {{ $data['roi_stats']['has_reached_7x'] ? 'danger' : 'warning' }}"
                             style="width: {{ min($data['roi_stats']['completion_7x_percentage'], 100) }}%"></div>
                    </div>

                    <div class="progress-stats">
                        <div class="progress-stat-item">
                            <div class="progress-stat-value">${{ number_format($data['roi_stats']['seven_x_limit'], 2) }}</div>
                            <div class="progress-stat-label">7X Limit</div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-value">${{ number_format($data['roi_stats']['total_roi_paid'], 2) }}</div>
                            <div class="progress-stat-label">Earned</div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-value">${{ number_format($data['roi_stats']['remaining_7x_amount'], 2) }}</div>
                            <div class="progress-stat-label">Until Limit</div>
                        </div>
                    </div>

                    @if(!$data['roi_stats']['withdrawal_enabled'])
                    <div class="mt-3 text-center alert alert-warning" style="margin-bottom: 0; border-radius: 12px;">
                        <i class="fas fa-ban"></i> Withdrawals suspended until topup
                    </div>
                    @endif
                </div>
            </div>

            <!-- Reward Announcement -->
            <div class="reward-announcement slide-up">
                <div class="reward-content">
                    <div class="reward-header">
                        <div class="reward-icon">🎯</div>
                        <div>
                            <h2 class="reward-title">Second Level Reward Increased!</h2>
                            <p class="reward-subtitle">Enhanced earnings opportunity now available</p>
                        </div>
                    </div>

                    <p class="reward-description">
                        We've upgraded the Second Level Reward to boost your earnings potential.
                        This enhanced reward structure helps you grow your network and maximize returns.
                    </p>

                    <div class="reward-highlights">
                        <div class="reward-highlight">
                            <div class="reward-highlight-value">$260</div>
                            <div class="reward-highlight-label">Previous Reward</div>
                        </div>
                        <div class="reward-highlight">
                            <div class="reward-highlight-value">$350</div>
                            <div class="reward-highlight-label">New Reward</div>
                        </div>
                        <div class="reward-highlight">
                            <div class="reward-highlight-value">+35%</div>
                            <div class="reward-highlight-label">Increase</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Target Cards -->
            <div class="target-grid scale-in">
                <!-- Reward Target -->
                <div class="target-card">
                    <div class="target-header">
                        <h3 class="target-title">Reward Target</h3>
                        <p class="target-subtitle">Track your progress towards next reward level</p>
                    </div>

                    <div class="circular-progress">
                        <svg>
                            <circle class="progress-ring" cx="100" cy="100" r="90"></circle>
                            <circle class="progress-fill" cx="100" cy="100" r="90"
                                    id="reward-progress-circle"
                                    stroke-dasharray="565.48"
                                    stroke-dashoffset="{{ 565.48 - (565.48 * $data['reward'] / 100) }}"></circle>
                        </svg>
                        <div class="progress-text">
                            <span class="progress-percentage">{{ number_format($data['reward'], 1) }}%</span>
                            <span class="progress-label">Complete</span>
                        </div>
                    </div>

                    <button class="target-btn" id="showRewardDetails">
                        <i class="mr-2 fas fa-trophy"></i>
                        View Reward Details
                    </button>
                </div>

                <!-- Rank Target -->
                <div class="target-card">
                    <div class="target-header">
                        <h3 class="target-title">Rank Target</h3>
                        <p class="target-subtitle">Advance to the next leadership level</p>
                    </div>

                    <div class="circular-progress">
                        <svg>
                            <circle class="progress-ring" cx="100" cy="100" r="90"></circle>
                            <circle class="progress-fill" cx="100" cy="100" r="90"
                                    id="rank-progress-circle"
                                    stroke-dasharray="565.48"
                                    stroke-dashoffset="565.48"></circle>
                        </svg>
                        <div class="progress-text">
                            <span class="progress-percentage">0%</span>
                            <span class="progress-label">Complete</span>
                        </div>
                    </div>

                    <button class="target-btn" id="showRankDetails">
                        <i class="mr-2 fas fa-crown"></i>
                        View Rank Progress
                    </button>
                </div>
            </div>

            <!-- Reward Details -->
            <div class="detail-card" id="rewardDetails">
                <div class="detail-header">
                    <h3 class="detail-title">Reward Level Progress</h3>
                </div>

                <div class="level-grid">
                    @foreach ($data['levelCount'] as $level => $count)
                    @php
                        $maxValues = [1 => 10, 2 => 50, 3 => 150, 4 => 400, 5 => 1000, 6 => 2000, 7 => 4000];
                        $maxValue = $maxValues[$level] ?? 1;
                        $percentage = min(($count / $maxValue) * 100, 100);
                        $rewardAmounts = [1 => 130, 2 => 350, 3 => 1050, 4 => 3450, 5 => 8650, 6 => 26000, 7 => 41500];
                        $rewardAmount = $rewardAmounts[$level] ?? 0;
                    @endphp

                    <div class="level-item">
                        <div class="level-badge">{{ $level }}</div>
                        <div class="level-info">
                            <h6>Level {{ $level }} Reward</h6>
                            <p>${{ number_format($rewardAmount) }} achievement bonus</p>
                        </div>
                        <div class="level-progress">
                            <div class="level-progress-fill" style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="level-stats">
                            <div class="level-count">{{ $count }} / {{ $maxValue }}</div>
                            <div class="level-required">{{ number_format($percentage, 1) }}% complete</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Rank Details -->
            <div class="detail-card" id="rankDetails">
                <div class="detail-header">
                    <h3 class="detail-title">Rank Advancement Progress</h3>
                </div>

                <div class="rank-grid">
                    @php
                        $ranks = [
                            ['name' => 'Bronze', 'requirements' => 'Start your journey', 'team_size' => 5, 'filename' => 'bronze.png', 'color' => 'CD7F32'],
                            ['name' => 'Silver', 'requirements' => 'Build active team', 'team_size' => 15, 'filename' => 'silver.png', 'color' => 'C0C0C0'],
                            ['name' => 'Gold', 'requirements' => 'Achieve leadership', 'team_size' => 50, 'filename' => 'gold.png', 'color' => 'FFD700'],
                            ['name' => 'Platinum', 'requirements' => 'Master networker', 'team_size' => 150, 'filename' => 'platinum.png', 'color' => 'E5E4E2'],
                            ['name' => 'Diamond', 'requirements' => 'Elite performer', 'team_size' => 500, 'filename' => 'diamond.png', 'color' => 'B9F2FF'],
                            ['name' => 'Master', 'requirements' => 'Industry expert', 'team_size' => 1500, 'filename' => 'master.png', 'color' => '800080'],
                            ['name' => 'Champion', 'requirements' => 'Global leader', 'team_size' => 5000, 'filename' => 'champion.png', 'color' => 'FF6B6B']
                        ];
                    @endphp

                    @foreach($ranks as $index => $rank)
                    @php
                        $currentTeamSize = $data['totalTeam'] ?? 0;
                        $progress = min(($currentTeamSize / $rank['team_size']) * 100, 100);
                        $isAchieved = $currentTeamSize >= $rank['team_size'];

                        // Check if custom rank image exists, otherwise use placeholder
                        $imagePath = public_path('assets/images/ranks/' . $rank['filename']);
                        $imageUrl = file_exists($imagePath)
                            ? asset('assets/images/ranks/' . $rank['filename']) 
                            : 'https://placehold.co/80x80/' . $rank['color'] . '/FFFFFF?text=' . substr($rank['name'], 0, 1);
                    @endphp
                    <div class="rank-item {{ $isAchieved ? 'achieved' : '' }}">
                        <img src="{{ $imageUrl }}" alt="{{ $rank['name'] }}" class="rank-image"
                             onerror="this.src='https://via.placeholder.com/80x80/{{ $rank['color'] }}/FFFFFF?text={{ substr($rank['name'], 0, 1) }}'">
                        <div class="rank-name">{{ $rank['name'] }}</div>
                        <div class="rank-description">{{ $rank['requirements'] }}</div>
                        <div class="level-progress" style="margin-bottom: 1rem;">
                            <div class="level-progress-fill" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="rank-requirements">
                            {{ $currentTeamSize }} / {{ $rank['team_size'] }} team members
                        </div>
                        @if($isAchieved)
                        <div class="mt-2 badge badge-success">✓ Achieved</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
    <!--end::Dashboard Content-->
</div>
@endsection

@section('page_js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for all scripts to load, then initialize chart
    setTimeout(function() {
        initChart();
    }, 1000); // Increased delay to ensure all scripts are loaded

    function initChart() {
        // Check if chart container exists
        const chartContainer = document.querySelector("#businessChart");
        if (!chartContainer) {
            console.error('Chart container #businessChart not found');
            return;
        }

        // Check if ApexCharts is available
        if (typeof ApexCharts === 'undefined') {
            console.log('ApexCharts not available, showing fallback message');
            chartContainer.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; color: #667eea; text-align: center;">
                    <h4 style="margin-bottom: 1rem;">Business Analytics</h4>
                    <p style="color: #718096; margin-bottom: 2rem;">Chart library loading...</p>
                    <div style="display: flex; gap: 2rem; flex-wrap: wrap; justify-content: center;">
                        <div style="text-align: center; padding: 1rem;">
                            <div style="font-size: 2rem; font-weight: bold; color: #667eea;">$${Math.round({{ $data['online_wallet'] ?? 0 }})}</div>
                            <div style="color: #718096; font-size: 0.9rem;">Total Earnings</div>
                        </div>
                        <div style="text-align: center; padding: 1rem;">
                            <div style="font-size: 2rem; font-weight: bold; color: #764ba2;">{{ $data['totalTeam'] ?? 0 }}</div>
                            <div style="color: #718096; font-size: 0.9rem;">Team Members</div>
                        </div>
                    </div>
                </div>
            `;
            return;
        }

        console.log('ApexCharts available, initializing chart...');

        // Prepare data with fallback values
        const onlineWallet = {{ $data['online_wallet'] ?? 0 }};
        const totalTeam = {{ $data['totalTeam'] ?? 0 }};

    // Initialize Business Analytics Chart
    const chartData = {
        '7d': {
            categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            earnings: [
                Math.max(onlineWallet * 0.1, 10),
                Math.max(onlineWallet * 0.15, 15),
                Math.max(onlineWallet * 0.12, 12),
                Math.max(onlineWallet * 0.18, 18),
                Math.max(onlineWallet * 0.14, 14),
                Math.max(onlineWallet * 0.16, 16),
                Math.max(onlineWallet * 0.15, 15)
            ],
            team: [
                Math.max(totalTeam * 0.85, 5),
                Math.max(totalTeam * 0.87, 6),
                Math.max(totalTeam * 0.90, 7),
                Math.max(totalTeam * 0.92, 8),
                Math.max(totalTeam * 0.95, 9),
                Math.max(totalTeam * 0.98, 10),
                Math.max(totalTeam, 10)
            ]
        },
        '30d': {
            categories: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            earnings: [
                Math.max(onlineWallet * 0.2, 20),
                Math.max(onlineWallet * 0.3, 30),
                Math.max(onlineWallet * 0.25, 25),
                Math.max(onlineWallet * 0.25, 25)
            ],
            team: [
                Math.max(totalTeam * 0.70, 7),
                Math.max(totalTeam * 0.80, 8),
                Math.max(totalTeam * 0.90, 9),
                Math.max(totalTeam, 10)
            ]
        },
        '90d': {
            categories: ['Month 1', 'Month 2', 'Month 3'],
            earnings: [
                Math.max(onlineWallet * 0.4, 40),
                Math.max(onlineWallet * 0.3, 30),
                Math.max(onlineWallet * 0.3, 30)
            ],
            team: [
                Math.max(totalTeam * 0.60, 6),
                Math.max(totalTeam * 0.80, 8),
                Math.max(totalTeam, 10)
            ]
        },
        '1y': {
            categories: ['Q1', 'Q2', 'Q3', 'Q4'],
            earnings: [
                Math.max(onlineWallet * 0.15, 15),
                Math.max(onlineWallet * 0.25, 25),
                Math.max(onlineWallet * 0.35, 35),
                Math.max(onlineWallet * 0.25, 25)
            ],
            team: [
                Math.max(totalTeam * 0.40, 4),
                Math.max(totalTeam * 0.60, 6),
                Math.max(totalTeam * 0.85, 8),
                Math.max(totalTeam, 10)
            ]
        }
    };

    let currentChart = null;

    function initializeChart(period = '7d') {
        try {
            const data = chartData[period];

            if (!data) {
                console.error('No data found for period:', period);
                return;
            }

            const options = {
                series: [{
                    name: 'Total Earnings ($)',
                    type: 'column',
                    data: data.earnings
                }, {
                    name: 'Team Growth',
                    type: 'line',
                    data: data.team
                }],
                chart: {
                    height: 400,
                    type: 'line',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: true,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                colors: ['#667eea', '#764ba2'],
                stroke: {
                    width: [0, 4],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                        borderRadius: 8
                    }
                },
                fill: {
                    type: ['gradient', 'solid'],
                    gradient: {
                        shade: 'light',
                        type: "vertical",
                        shadeIntensity: 0.3,
                        gradientToColors: ['#764ba2'],
                        inverseColors: false,
                        opacityFrom: 0.8,
                        opacityTo: 0.6,
                        stops: [0, 100]
                    }
                },
                labels: data.categories,
                markers: {
                    size: 6,
                    strokeWidth: 2,
                    strokeColors: '#fff',
                    hover: {
                        sizeOffset: 3
                    }
                },
                xaxis: {
                    categories: data.categories,
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontWeight: 600,
                            colors: '#718096'
                        }
                    }
                },
                yaxis: [{
                    title: {
                        text: 'Earnings ($)',
                        style: {
                            color: '#667eea',
                            fontSize: '14px',
                            fontWeight: 600
                        }
                    },
                    labels: {
                        formatter: function (val) {
                            return '$' + (val || 0).toFixed(0);
                        },
                        style: {
                            colors: '#667eea'
                        }
                    }
                }, {
                    opposite: true,
                    title: {
                        text: 'Team Members',
                        style: {
                            color: '#764ba2',
                            fontSize: '14px',
                            fontWeight: 600
                        }
                    },
                    labels: {
                        formatter: function (val) {
                            return Math.round(val || 0);
                        },
                        style: {
                            colors: '#764ba2'
                        }
                    }
                }],
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: [{
                        formatter: function (y) {
                            if (typeof y !== "undefined" && y !== null) {
                                return "$" + y.toFixed(2);
                            }
                            return y;
                        }
                    }, {
                        formatter: function (y) {
                            if (typeof y !== "undefined" && y !== null) {
                                return Math.round(y) + " members";
                            }
                            return y;
                        }
                    }]
                },
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 3,
                    padding: {
                        right: 30,
                        left: 20
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '14px',
                    fontWeight: 600,
                    markers: {
                        width: 10,
                        height: 10,
                        radius: 5
                    }
                }
            };

            // Destroy existing chart
            if (currentChart) {
                currentChart.destroy();
                currentChart = null;
            }

            // Create new chart with better error handling
            try {
                currentChart = new ApexCharts(chartContainer, options);
                currentChart.render().then(function() {
                    console.log('Chart rendered successfully');
                }).catch(function(error) {
                    console.error('Error rendering chart:', error);
                    // Fallback: Show data summary instead of chart
                    chartContainer.innerHTML = `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; color: #667eea; text-align: center;">
                            <h4 style="margin-bottom: 1rem; color: #e53e3e;">Chart Error</h4>
                            <p style="color: #718096; margin-bottom: 2rem;">Unable to render chart. Here's your data summary:</p>
                            <div style="display: flex; gap: 2rem; flex-wrap: wrap; justify-content: center;">
                                <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 10px;">
                                    <div style="font-size: 2rem; font-weight: bold; color: #667eea;">$${Math.round(onlineWallet)}</div>
                                    <div style="color: #718096; font-size: 0.9rem;">Total Earnings</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 10px;">
                                    <div style="font-size: 2rem; font-weight: bold; color: #764ba2;">${totalTeam}</div>
                                    <div style="color: #718096; font-size: 0.9rem;">Team Members</div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } catch (error) {
                console.error('Error creating ApexCharts instance:', error);
                chartContainer.innerHTML = `
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 400px; color: #667eea; text-align: center;">
                        <h4 style="margin-bottom: 1rem;">Business Analytics</h4>
                        <p style="color: #718096; margin-bottom: 2rem;">Chart temporarily unavailable</p>
                        <div style="display: flex; gap: 2rem; flex-wrap: wrap; justify-content: center;">
                            <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 10px;">
                                <div style="font-size: 2rem; font-weight: bold; color: #667eea;">$${Math.round(onlineWallet)}</div>
                                <div style="color: #718096; font-size: 0.9rem;">Total Earnings</div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 10px;">
                                <div style="font-size: 2rem; font-weight: bold; color: #764ba2;">${totalTeam}</div>
                                <div style="color: #718096; font-size: 0.9rem;">Team Members</div>
                            </div>
                        </div>
                    </div>
                `;
            }

        } catch (error) {
            console.error('Error initializing chart:', error);
            // Fallback: Show error message
            chartContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 400px; color: #e53e3e; font-size: 16px;">Unable to load chart data</div>';
        }
    }

        // Initialize chart with delay to ensure DOM is ready
        setTimeout(function() {
            initializeChart('7d');
        }, 100);

        // Filter buttons functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const period = this.dataset.period;
                initializeChart(period);
            });
        });
    } // End of initChart function

    // Target buttons functionality
    document.getElementById('showRewardDetails').addEventListener('click', function() {
        const detailCard = document.getElementById('rewardDetails');
        const rankCard = document.getElementById('rankDetails');

        rankCard.classList.remove('show');
        detailCard.classList.add('show');

        // Smooth scroll to the detail card
        setTimeout(() => {
            detailCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    });

    document.getElementById('showRankDetails').addEventListener('click', function() {
        const detailCard = document.getElementById('rankDetails');
        const rewardCard = document.getElementById('rewardDetails');

        rewardCard.classList.remove('show');
        detailCard.classList.add('show');

        // Smooth scroll to the detail card
        setTimeout(() => {
            detailCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    });

    // Animate progress bars on scroll
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);

    // Observe all progress elements
    document.querySelectorAll('.progress-bar, .level-progress-fill').forEach(el => {
        observer.observe(el);
    });

    // Add stagger animation to stats cards
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });

    // Add stagger animation to other elements
    setTimeout(() => {
        document.querySelectorAll('.slide-up').forEach((el, index) => {
            el.style.animationDelay = `${index * 0.2}s`;
        });

        document.querySelectorAll('.scale-in').forEach((el, index) => {
            el.style.animationDelay = `${index * 0.15}s`;
        });
    }, 500);

    // Update circular progress on page load
    setTimeout(() => {
        const rewardProgress = {{ $data['reward'] }};
        const rewardCircle = document.getElementById('reward-progress-circle');
        const circumference = 2 * Math.PI * 90;
        const offset = circumference - (rewardProgress / 100) * circumference;
        rewardCircle.style.strokeDashoffset = offset;
    }, 1000);
});
</script>
@endsection