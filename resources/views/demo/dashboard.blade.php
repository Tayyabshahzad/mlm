@extends('demo.layout.app')
@section('content')
<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --accent-color: #4cc9f0;
        --success-color: #4ad66d;
        --warning-color: #f8961e;
        --danger-color: #f94144;
        --light-color: #f8f9fa;
        --dark-color: #212529;
        --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
    }
    
    /* Modern Card Styling */
    .modern-card {
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        overflow: hidden;
        background: white;
        margin-bottom: 24px;
    }
    
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    }
    
    .modern-card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 20px 25px;
        background: transparent;
    }
    
    .modern-card-title {
        font-weight: 600;
        color: var(--dark-color);
        font-size: 1.1rem;
        margin: 0;
    }
    
    /* Wallet Cards */
    .wallet-card {
        border-radius: 12px;
        color: white;
        padding: 20px;
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
        height: 130px;
        transition: var(--transition);
    }
    
    .wallet-card:hover {
        transform: translateY(-3px);
    }
    
    .wallet-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
    }
    
    .wallet-card .wallet-title {
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 5px;
        opacity: 0.9;
    }
    
    .wallet-card .wallet-amount {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 15px;
    }
    
    /* Progress Circles */
    .progress-circle-container {
        display: flex;
        justify-content: center;
        margin: 20px 0;
    }
    
    .progress-circle {
        position: relative;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: conic-gradient(var(--primary-color) 0%, var(--primary-color) var(--progress), #e9ecef var(--progress));
    }
    
    .progress-circle .inner-circle {
        position: absolute;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: white;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.05);
    }
    
    .progress-circle .percentage {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--dark-color);
    }
    
    /* Reward Levels */
    .reward-level {
        position: relative;
        padding: 15px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .reward-level:last-child {
        border-bottom: none;
    }
    
    .reward-progress {
        height: 12px;
        border-radius: 6px;
        margin: 10px 0;
    }
    
    .reward-badge {
        position: absolute;
        right: 45px;
        top: 50%;
        transform: translateY(-50%);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    /* Announcement Banner */
    .announcement-banner {
        background: linear-gradient(135deg, #4361ee, #4cc9f0);
        color: white;
        border-radius: 12px;
        padding: 25px;
        margin: 30px 0;
        position: relative;
        overflow: hidden;
    }
    
    .announcement-banner::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .announcement-icon {
        font-size: 2.5rem;
        margin-right: 15px;
        animation: bounce 2s infinite;
    }
    
    .announcement-title {
        font-weight: 700;
        font-size: 1.3rem;
        margin-bottom: 10px;
    }
    
    .announcement-text {
        opacity: 0.9;
        margin-bottom: 15px;
    }
    
    .announcement-link {
        color: white;
        text-decoration: underline;
        font-weight: 500;
        transition: var(--transition);
    }
    
    .announcement-link:hover {
        color: rgba(255, 255, 255, 0.8);
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    
    /* Investment Progress Cards */
    .investment-card {
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
        background: white;
        box-shadow: var(--card-shadow);
    }
    
    .investment-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background-size: contain;
        background-repeat: no-repeat;
        opacity: 0.1;
    }
    
    .investment-title {
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 15px;
        color: var(--dark-color);
    }
    
    .progress-label {
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .progress-value {
        font-weight: 700;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .wallet-card {
            height: auto;
            padding: 15px;
        }
        
        .wallet-card .wallet-amount {
            font-size: 1.5rem;
        }
        
        .progress-circle {
            width: 150px;
            height: 150px;
        }
        
        .progress-circle .inner-circle {
            width: 120px;
            height: 120px;
        }
    }

    .target-section {
        display: block !important;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

</style>

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-2">
                <!--begin::Page Title-->
                <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Dashboard</h5> 
                <div class="subheader-separator subheader-separator-ver mt-2 mb-2 mr-4 bg-gray-200"></div>
                <span class="text-muted font-weight-bold mr-4">Level 1 </span>
                <div class="kt-widget__content">
                    <div class="kt-widget__section">
                        <a href="#" class="kt-widget__username">
                            <i class="flaticon2-correct kt-font-success"></i>
                        </a>
                    </div>
                </div> 
                <div class="subheader-separator subheader-separator-ver mt-2 mb-2 mr-4 bg-gray-200 ml-2"></div>
                <span class="text-muted font-weight-bold mr-4">Available Balance </span>
                <div class="kt-widget__content">
                    <div class="kt-widget__section">
                        <strong>${{ Auth::user()->roi_eligible_investment_amount }}</strong> 
                    </div> 
                </div> 
                @role('admin')
                <span class="text-muted font-weight-bold ml-15">Time & Time Zone </span>
                <div class="kt-widget__content ml-5">
                    <div class="kt-widget__section">
                        <strong>{{ now()->format('Y-m-d H:i:s') }} ({{ config('app.timezone') }})</strong> 
                    </div> 
                </div> 
                @endrole
            </div> 
        </div>
    </div>
    <!--end::Subheader-->
    
    <!--begin::Dashboard Content-->
    <div class="d-flex flex-column-fluid"> 
        <div class="container">   
            <div class="row">  
                <!-- Leaderboard Card -->
                <div class="col-xl-4"> 
                    <div class="modern-card"> 
                        <div class="modern-card-header">
                            <h5 class="modern-card-title">Weekly Sales Stats</h5>
                            <div class="font-size-sm text-muted mt-1">0 PV</div>
                        </div>
                        <div class="card-body">
                            <h4 class="font-weight-bolder mb-4">Leaderboard</h4>
                            
                            @foreach ($data['team_size'] as $team)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50 symbol-light mr-3 flex-shrink-0">
                                        <div class="symbol-label">
                                            <img src="{{ asset($team->getFirstMediaUrl('user_profile_images')) }}" class="h-50" alt="{{ $team->name }}" />
                                        </div>
                                    </div>
                                    <div>
                                        <a href="#" class="font-weight-bolder text-dark">{{ ucfirst($team->name) }}</a>
                                        <div class="text-muted font-size-sm">Direct</div>
                                    </div>
                                </div>
                                <span class="badge badge-light badge-pill font-weight-bold py-2 px-3">
                                    {{ $team->team->count() }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <!-- Wallet Cards -->
                <div class="col-xl-8">
                    <div class="row">
                        <!-- Online Wallet -->
                        <div class="col-md-6 col-lg-4">
                            <div class="wallet-card" style="background: linear-gradient(135deg, #4361ee, #3a0ca3);">
                                <div class="wallet-title">Online Wallet</div>
                                <div class="wallet-amount">${{ $data['online_wallet'] }}</div>
                                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Direct/Indirect Wallet -->
                        <div class="col-md-6 col-lg-4">
                            <div class="wallet-card" style="background: linear-gradient(135deg, #7209b7, #560bad);">
                                <div class="wallet-title">Direct/Indirect Wallet</div>
                                <div class="wallet-amount">${{ $data['direct_indirect'] }}</div>
                                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: 45%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Reward Wallet -->
                        <div class="col-md-6 col-lg-4">
                            <div class="wallet-card" style="background: linear-gradient(135deg, #f72585, #b5179e);">
                                <div class="wallet-title">Reward Wallet</div>
                                <div class="wallet-amount">${{ $data['rewardWallet'] }}</div>
                                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: 30%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ROI -->
                        <div class="col-md-6 col-lg-4">
                            <div class="wallet-card" style="background: linear-gradient(135deg, #4895ef, #3f37c9);">
                                <div class="wallet-title">ROI</div>
                                <div class="wallet-amount">${{ $data['roi'] }}</div>
                                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: 75%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profit Share -->
                        <div class="col-md-6 col-lg-4">
                            <div class="wallet-card" style="background: linear-gradient(135deg, #4cc9f0, #4895ef);">
                                <div class="wallet-title">Profit Share</div>
                                <div class="wallet-amount">${{ $data['profit_share'] }}</div>
                                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: 50%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rank Achievement -->
                        <div class="col-md-6 col-lg-4">
                            <div class="wallet-card" style="background: linear-gradient(135deg, #4ad66d, #38b000);">
                                <div class="wallet-title">Your Rank</div>
                                <div class="wallet-amount">Visioners</div>
                                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: 80%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Total Team Size -->
                        <div class="col-12">
                            <div class="wallet-card" style="background: linear-gradient(135deg, #f8961e, #f3722c);">
                                <div class="wallet-title">Total Team Size</div>
                                <div class="wallet-amount">{{ $data['totalTeam'] }}</div>
                                <div class="progress" style="height: 4px; background: rgba(255,255,255,0.2);">
                                    <div class="progress-bar bg-white" style="width: 65%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Earnings Card -->
                    <div class="modern-card" style="background: linear-gradient(135deg, #4AB58E, #3a86ff); color: white;">
                        <div class="card-body d-flex align-items-center">
                            <div>
                                <h3 class="text-white font-weight-bolder mb-2">Total Earnings: ${{ number_format($data['total_earning'], 2) }}</h3>
                                <p class="text-white opacity-80 mb-0">
                                    Overview of All Your Wallets
                                </p>
                            </div>
                            <div class="ml-auto">
                                <i class="fas fa-wallet fa-3x opacity-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Announcement Banner -->
            <div class="announcement-banner">
                <div class="d-flex align-items-start">
                    <div class="announcement-icon">🎯</div>
                    <div>
                        <h3 class="announcement-title">Second Level Reward Increased!</h3>
                        <p class="announcement-text">
                            We've upgraded the Second Level Reward from $260 to $350 to boost your earnings. 
                            Take advantage of this enhanced reward and grow your network today!
                        </p>
                        <a href="#" class="announcement-link">Learn more about this offer</a>
                    </div>
                </div>
            </div>
            
            <!-- Business Summary Chart -->
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title">Business Summary</h5>
                </div>
                <div class="card-body">
                    <div id="chart_3" style="height: 300px;"></div>
                </div>
            </div>
            
            <!-- Investment Progress Cards -->
            <div class="row">
                <!-- 2X Investment -->
                <div class="col-lg-6">
                    <div class="investment-card">
                        <h4 class="investment-title">Personal Investment: ${{ $data['initial_investment'] }} <span class="badge badge-success ml-2">2X</span></h4>
                        
                        <div class="d-flex align-items-center mb-3">
                            <span class="progress-label mr-3">Progress</span>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: {{ $data['total_roi_earned_pv'] }}%"></div>
                            </div>
                            <span class="progress-value ml-3">{{ $data['total_roi_earned_pv'] }}%</span>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="mb-1">
                                    <i class="fas fa-piggy-bank text-muted"></i>
                                </div>
                                <div class="font-weight-bold">Total Earned</div>
                                <div>${{ $data['total_roi_earned_pv'] }}</div>
                            </div>
                            <div class="col-4">
                                <div class="mb-1">
                                    <i class="fas fa-gift text-muted"></i>
                                </div>
                                <div class="font-weight-bold">This Month</div>
                                <div>${{ $data['total_roi_earned_pv'] }}</div>
                            </div>
                            <div class="col-4">
                                <div class="mb-1">
                                    <i class="fas fa-chart-pie text-muted"></i>
                                </div>
                                <div class="font-weight-bold">Remaining</div>
                                <div>${{ 200 - $data['total_roi_earned_pv'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 7X Investment -->
                <div class="col-lg-6">
                    <div class="investment-card">
                        <h4 class="investment-title">Investment Cap <span class="badge badge-primary ml-2">7X</span></h4>
                        
                        <div class="d-flex align-items-center mb-3">
                            <span class="progress-label mr-3">Progress</span>
                            <div class="progress flex-grow-1" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: {{ ($data['total_roi_earned_pv'] / 700) * 100 }}%"></div>
                            </div>
                            <span class="progress-value ml-3">{{ round(($data['total_roi_earned_pv'] / 700) * 100, 2) }}%</span>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="mb-1">
                                    <i class="fas fa-piggy-bank text-muted"></i>
                                </div>
                                <div class="font-weight-bold">Total Earned</div>
                                <div>${{ $data['total_roi_earned_pv'] }}</div>
                            </div>
                            <div class="col-4">
                                <div class="mb-1">
                                    <i class="fas fa-gift text-muted"></i>
                                </div>
                                <div class="font-weight-bold">This Month</div>
                                <div>${{ $data['total_roi_earned_pv'] }}</div>
                            </div>
                            <div class="col-4">
                                <div class="mb-1">
                                    <i class="fas fa-chart-pie text-muted"></i>
                                </div>
                                <div class="font-weight-bold">Remaining</div>
                                <div>${{ 700 - $data['total_roi_earned_pv'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reward and Rank Targets -->
            <div class="row">
                <!-- Reward Target -->
                <div class="col-lg-6" id="reward-target-wrapper"> 
                    <div class="modern-card"> 
                        <div class="modern-card-header">
                            <h5 class="modern-card-title">Reward Target</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="progress-circle-container">
                                <div class="progress-circle" style="--progress: {{ $data['reward'] }}%">
                                    <div class="inner-circle">
                                        <span class="percentage">{{ $data['reward'] }}%</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted mb-4">
                                Notes: Click to get more details for your upcoming rewards
                            </p>
                            <button id="generateRewardTargetReport" class="btn btn-primary btn-lg w-100 py-3">
                                View Reward Target
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Rank Target -->
                <div class="col-lg-6" id="rank-target-wrapper"> 
                    <div class="modern-card"> 
                        <div class="modern-card-header">
                            <h5 class="modern-card-title">Rank Target</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="progress-circle-container">
                                <div class="progress-circle" style="--progress: 0%">
                                    <div class="inner-circle">
                                        <span class="percentage">0%</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted mb-4">
                                Notes: Click to get more details for your upcoming Targets
                            </p>
                            <button id="generateRankTargetReport" class="btn btn-primary btn-lg w-100 py-3">
                                View Rank Target
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Reward Target Details -->
                <div class="col-lg-12 d-none" id="reward-target-details"> 
                    <div class="modern-card"> 
                        <div class="modern-card-header">
                            <h5 class="modern-card-title">Reward Target Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="reward-levels">
                                @foreach ($data['levelCount'] as $level => $count)
                                @php
                                    $maxValues = [1 => 10, 2 => 50, 3 => 150, 4 => 400, 5 => 1000, 6 => 2000, 7 => 4000];
                                    $maxValue = $maxValues[$level] ?? 1;
                                    $percentage = ($count / $maxValue) * 100;
                                    $rewardImages = [
                                        1 => '130$.jpg',
                                        2 => '350$.jpg',
                                        3 => '875$.jpg',
                                        4 => '3450$.jpg',
                                        5 => '8650$.jpg',
                                        6 => '26000$.jpg',
                                        7 => '41500$.jpg',
                                    ];
                                    $rewardImage = $rewardImages[$level] ;
                                    $rewardAmount = str_replace('.jpg', '', $rewardImage);
                                @endphp
                                
                                <div class="reward-level">
                                    <h6 class="font-weight-bold mb-2">Level {{ $level }} Reward</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="flex-grow-1">
                                            <div class="progress reward-progress">
                                                <div class="progress-bar 
                                                    @if($level % 7 == 0) bg-primary 
                                                    @elseif($level % 6 == 0) bg-secondary 
                                                    @elseif($level % 5 == 0) bg-success 
                                                    @elseif($level % 4 == 0) bg-danger 
                                                    @elseif($level % 3 == 0) bg-warning 
                                                    @elseif($level % 2 == 0) bg-info 
                                                    @else bg-light @endif" 
                                                    style="width: {{ $percentage }}%">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-3 font-weight-bold">
                                            {{ $count }} / {{ $maxValue }}
                                        </div>
                                    </div>
                                    <div class="reward-badge" style="background-image: url({{ asset('assets/custom-images/reward/' . $rewardImage) }})">
                                        
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Rank Target Details -->
                <div class="col-lg-12 d-none" id="rank-target-details"> 
                    <div class="modern-card"> 
                        <div class="modern-card-header">
                            <h5 class="modern-card-title">Rank Target Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="reward-levels">
                                @for($i = 1; $i <= 7; $i++)
                                <div class="reward-level">
                                    <h6 class="font-weight-bold mb-2">Rank {{ $i }}</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="flex-grow-1">
                                            <div class="progress reward-progress">
                                                <div class="progress-bar bg-primary" style="width: 0%"></div>
                                            </div>
                                        </div>
                                        <div class="ml-3 font-weight-bold">0 / 10</div>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Dashboard Content-->
</div>
@endsection

@section('page_js')
<script src="{{ asset('assets/js/pages/features/charts/apexcharts.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {  
        $('.progress-circle').each(function () {
            const styleAttr = $(this).attr('style') || '';
            const match = styleAttr.match(/--progress:\s*(\d+)%/);

            if (match) {
                const progress = match[1];
                $(this).css('background', `conic-gradient(var(--primary-color) 0%, var(--primary-color) ${progress}%, #e9ecef ${progress}%)`);
            }
        });

        
        // Handle Reward Target button click
        $('#generateRewardTargetReport').on('click', function(e) { 
            e.preventDefault();
            $('#reward-target-details').removeClass('d-none').addClass('target-section');
            $('#rank-target-details').addClass('d-none');
            $('html, body').animate({
                scrollTop: $('#reward-target-details').offset().top - 20
            }, 300);
        });

        // Handle Rank Target button click
        $('#generateRankTargetReport').on('click', function(e) {
            e.preventDefault();
            $('#rank-target-details').removeClass('d-none').addClass('target-section');
            $('#reward-target-details').addClass('d-none');
            $('html, body').animate({
                scrollTop: $('#rank-target-details').offset().top - 20
            }, 300);
        });

        
    });
</script>
@endsection