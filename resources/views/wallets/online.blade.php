@extends('demo.layout.app')
@section('title','Online')
@section('content')
 <!--begin::Content-->
 <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <!--begin::Info-->
            <div class="flex-wrap mr-1 d-flex align-items-center"> 
                <!--begin::Page Heading-->
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <!--begin::Page Title-->
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Online  Wallet </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Wallets</a>
                        </li>   

                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Online Wallet</a>
                        </li>
                    </ul> 
                </div> 
            </div>
             
        </div>
    </div> 
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class=" flex-column-fluid"> 
        <div class="container"> 


            <div class="order-1 col-lg-12 col-xxl-12 order-xxl-2"> 
                @if(!Auth::user()->freez_wallet )   
                @if(!(float) Auth::user()->negative_pv > 0)
                        <div class="card card-custom card-stretch gutter-b ">  
                            <div class="border-0 card-header">  
                                @php
                                    $blockedWallets = json_decode($setting->blocked_wallets ?? '{}', true); 
                                @endphp
                                @if (!($blockedWallets['online'] ?? false) && auth()->user()->account_type !== 'saving')
                                    @if($setting->withdraw_block == false)
                                        @if($walletSum >= 122700)
                                            <div class="pt-5 align-items-center justify-content-center" >
                                                <a href="#" disabled  class="mb-5 mr-3 disabled rounded-0 btn btn-info font-weight-bolder font-size-sm">Withdrawal Request</a>
                                                <a href="#" disabled   class="mb-5 mr-3 disabled rounded-0 btn btn-primary font-weight-bolder font-size-sm">Transfer to Member </a>
                                            </div>
                                        @elseif(!$roiStats['withdrawal_enabled'])
                                            <!-- 7x Limit Reached - Withdrawal Disabled -->
                                            <div class="pt-5 align-items-center justify-content-center">
                                                <a href="#" disabled class="mb-5 mr-3 disabled rounded-0 btn btn-danger font-weight-bolder font-size-sm">
                                                    Withdrawal Disabled (7X Reached)
                                                </a>
                                                <a href="#" disabled class="mb-5 mr-3 disabled rounded-0 btn btn-secondary font-weight-bolder font-size-sm">
                                                    Transfer Disabled
                                                </a>
                                            </div>
                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                <strong>Withdrawal Disabled:</strong> You've reached your 7X earnings limit.
                                                <strong>Topup your account</strong> to increase the limit and re-enable withdrawals.
                                            </div>
                                        @else
                                            <div class="pt-5 align-items-center justify-content-center">
                                                    <a href="#"   data-toggle="modal"   data-target="#WithdrawModel"  class="mb-5 mr-3 rounded-0 btn btn-info font-weight-bolder font-size-sm">Create Withdrawal Request</a>
                                                    <a href="#"   data-toggle="modal"    data-target="#WithdrawModelTransfer"  class="mb-5 mr-3 rounded-0 btn btn-primary font-weight-bolder font-size-sm">Member Transfer </a>
                                            </div>
                                        @endif
                                    @else
                                @endif 
                                @endif

                                <div class="pt-5 align-items-center justify-content-center" > 
                                    <a href="#"  data-toggle="modal"   data-target="#TopUpAccount"  class="mb-5 mr-3 rounded-0 btn btn-success font-weight-bolder font-size-sm">TopUp Your Account</a>    
                                </div>

                            </div> 
                        </div>
                    @endif
                @endif
               
               
               
                <div class="card card-custom card-stretch gutter-b">
                    <!-- Total Earning and Current Balance Row -->
                    <div class="row p-6">
                        <!-- Total Earning Card -->
                        <div class="mb-4 col-md-6">
                            <div class="border-0 shadow-lg card rounded-3 h-100">
                                <div class="text-center card-body d-flex flex-column align-items-center justify-content-center">
                                    <div class="p-3 mb-3 bg-primary bg-opacity-10 rounded-circle">
                                        <i class="fas fa-chart-line fa-2x text-primary"></i>
                                    </div>
                                    <h6 class="text-muted text-uppercase fw-bold">Total Earning</h6>
                                    <h2 class="mt-2 fw-bold text-primary">
                                        ${{ number_format($totalEarning, 2) }}
                                    </h2>
                                    <p class="mb-0 small text-muted">All-time online wallet earnings</p>
                                </div>
                            </div>
                        </div>

                        <!-- Current Balance Card -->
                        <div class="mb-4 col-md-6">
                            <div class="border-0 shadow-lg card rounded-3 h-100">
                                <div class="text-center card-body d-flex flex-column align-items-center justify-content-center">
                                    <div class="p-3 mb-3 bg-success bg-opacity-10 rounded-circle">
                                        <i class="fas fa-wallet fa-2x text-success"></i>
                                    </div>
                                    <h6 class="text-muted text-uppercase fw-bold">Current Balance</h6>
                                    <h2 class="mt-2 fw-bold text-success">
                                        ${{ number_format($currentBalance, 2) }}
                                    </h2>
                                    <p class="mb-0 small text-muted">Available balance in wallet</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-dark">Account Balance Details</h3>
                    </div>

                    <div class="pt-0 card-body"> 
                        <div class="mb-10">
                            <!--begin::Section-->
                            <div class="d-flex align-items-center">
                                <!--begin::Symbol-->
                                <div class="mr-5 symbol symbol-45 symbol-light">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class="flaticon2-wallet text-success"></i>
                                        </span>
                                    </span>
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="mb-1 font-weight-bold text-dark-75 text-hover-primary font-size-lg">Available Balance</a>
                                    <small class="text-muted">Current spendable balance in your online wallet</small>
                                    <span class="font-weight-bold text-success">${{ number_format($availableBalance, 2) }}</span>
                                </div>  
                                <div class="mr-5 symbol symbol-45 symbol-light">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class=" flaticon-calendar-3 text-warning"></i>
                                        </span>
                                    </span>
                                </div> 
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="mb-1 font-weight-bold text-dark-75 text-hover-primary font-size-lg">Last Transaction Date</a>
                                    <a href="{{ route('show.transaction.history') }}">View Transaction History</a>
                                    <span class="font-weight-bold">
                                        {{ $onlineWallets->sortByDesc('created_at')->first()->created_at ?? 'No records found' }}  
                                    </span>
                                </div>   


                                <div class="d-flex flex-column flex-grow-1 text-danger">
                                    <a href="#" class="mb-1 font-weight-bold text-dark-75 text-hover-primary font-size-lg">
                                        Negative Points
                                    </a>
                                     @if(Auth::user()->negative_pv > 0)
                                        <small>
                                            <button class="mt-1 mb-3 btn btn-sm btn-danger no-radius rounded-0" data-toggle="modal" data-target="#clearNegativeModal">
                                                Clear Points Now
                                            </button>
                                        </small>
                                    @endif

                                    <span class="font-weight-bold">
                                        -${{ round(Auth::user()->negative_pv,2) }}
                                    </span>
                                </div>   
                            </div> 

                            <div class="mt-10 d-flex align-items-center">
                                <!--begin::Symbol-->
                                <div class="mr-5 symbol symbol-45 symbol-light">
                                    <span class="symbol-label">
                                        <span class="navi-icon">
                                            <i class="flaticon2-graph-1 text-primary"></i>
                                        </span>
                                    </span>
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <a href="#" class="mb-1 font-weight-bold text-dark-75 text-hover-primary font-size-lg">Total Lifetime Earnings</a>
                                    <small class="text-muted">Total income from commissions, ROI, rewards, transfers & withdrawals</small>
                                    <span class="font-weight-bold text-primary">${{ number_format($totalEarned, 2) }}</span>

                                    <!-- Earnings Breakdown Toggle -->
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-light-primary" type="button" data-toggle="collapse" data-target="#earningsBreakdown" aria-expanded="false">
                                            <i class="fas fa-chart-pie"></i> View Breakdown
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Earnings Breakdown Collapse -->
                            <div class="collapse mt-5" id="earningsBreakdown">
                                <div class="card card-custom">
                                    <div class="card-header">
                                        <h5 class="card-title">Earnings Breakdown</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Current Earnings in Wallets -->
                                            <div class="col-md-6">
                                                <h6 class="font-weight-bold mb-3">Current Earnings in Wallets</h6>
                                                @foreach($earningsBreakdown['wallet_breakdown'] as $walletType => $amount)
                                                    @if($amount > 0)
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $walletType)) }}:</span>
                                                        <span class="font-weight-bold">${{ number_format($amount, 2) }}</span>
                                                    </div>
                                                    @endif
                                                @endforeach
                                                <hr>
                                                <div class="d-flex justify-content-between">
                                                    <span class="font-weight-bold">Subtotal:</span>
                                                    <span class="font-weight-bold text-success">${{ number_format($earningsBreakdown['current_earnings'], 2) }}</span>
                                                </div>
                                            </div>

                                            <!-- Utilized Earnings -->
                                            <div class="col-md-6">
                                                <h6 class="font-weight-bold mb-3">Utilized Earnings</h6>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Transferred to Online:</span>
                                                    <span class="font-weight-bold">${{ number_format($earningsBreakdown['transferred_to_online'], 2) }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-muted">Withdrawn:</span>
                                                    <span class="font-weight-bold">${{ number_format($earningsBreakdown['withdrawn'], 2) }}</span>
                                                </div>
                                                <hr>
                                                <div class="d-flex justify-content-between">
                                                    <span class="font-weight-bold">Subtotal:</span>
                                                    <span class="font-weight-bold text-info">${{ number_format($earningsBreakdown['transferred_to_online'] + $earningsBreakdown['withdrawn'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Grand Total -->
                                        <div class="mt-4 p-3 bg-light-primary rounded">
                                            <div class="d-flex justify-content-between">
                                                <span class="font-weight-bold text-primary font-size-lg">Total Lifetime Earnings:</span>
                                                <span class="font-weight-bold text-primary font-size-lg">${{ number_format($totalEarned, 2) }}</span>
                                            </div>
                                            <small class="text-muted">This represents all income you've earned from the system since you joined.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2X ROI Progress Card -->
                <div class="card card-custom card-stretch gutter-b">
                    <div class="border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-dark">
                            2X ROI Progress
                            @if($roiStats['completion_percentage'] >= 100)
                                <span class="badge badge-danger ml-2">ROI Permanently Stopped</span>
                            @else
                                <span class="badge badge-success ml-2">ROI Active</span>
                            @endif
                        </h3>
                    </div>
                    <div class="pt-0 card-body">
                        <div class="mb-5">
                            <!-- Investment & Earnings Overview -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">Total Investment</span>
                                        <span class="font-weight-bold font-size-h6 text-primary">${{ number_format($roiStats['invested_amount'], 2) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">2X Target</span>
                                        <span class="font-weight-bold font-size-h6 text-info">${{ number_format($roiStats['two_x_limit'], 2) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">Total Earned</span>
                                        <span class="font-weight-bold font-size-h6 text-{{ $roiStats['completion_percentage'] >= 100 ? 'success' : 'warning' }}">${{ number_format($roiStats['total_roi_paid'], 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mt-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted font-size-sm">Progress to 2X Target</span>
                                    <span class="font-weight-bold font-size-sm">{{ number_format($roiStats['completion_percentage'], 1) }}%</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar {{ $roiStats['completion_percentage'] >= 100 ? 'bg-success' : 'bg-primary' }}"
                                         role="progressbar"
                                         style="width: {{ min(100, $roiStats['completion_percentage']) }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Status & Remaining -->
                            <div class="row mt-5">
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">ROI Status</span>
                                        @if($roiStats['completion_percentage'] >= 100)
                                            <span class="badge badge-danger">🛑 Permanently Stopped</span>
                                            <small class="text-muted mt-1">ROI will never restart once 2X is reached</small>
                                        @elseif($roiStats['roi_status'] === 'stopped')
                                            <span class="badge badge-warning">Temporarily Stopped</span>
                                        @else
                                            <span class="badge badge-success">✅ Active</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">Remaining to 2X</span>
                                        <span class="font-weight-bold font-size-h6 text-{{ $roiStats['remaining_amount'] <= 0 ? 'success' : 'primary' }}">
                                            ${{ number_format(max(0, $roiStats['remaining_amount']), 2) }}
                                        </span>
                                        @if($roiStats['remaining_amount'] <= 0)
                                            <small class="text-success">✅ Target achieved!</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Info -->
                            @if($roiStats['estimated_completion_date'] && $roiStats['remaining_amount'] > 0)
                            <div class="mt-4 p-3 bg-light-info rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-alt text-info mr-2"></i>
                                    <div>
                                        <span class="font-weight-bold text-info">Estimated 2X Completion:</span>
                                        <span class="ml-2">{{ \Carbon\Carbon::parse($roiStats['estimated_completion_date'])->format('M j, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- 2X Limit Reached - ROI Permanently Stopped -->
                            @if($roiStats['completion_percentage'] >= 100)
                            <div class="mt-4 p-3 bg-light-danger rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-stop-circle text-danger mr-2"></i>
                                    <div>
                                        <span class="font-weight-bold text-danger">ROI Permanently Stopped!</span>
                                        <div class="mt-1">
                                            <small class="text-muted">You've earned 2X your investment. ROI will never restart, but you can still withdraw funds until 7X limit.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- 7X Limit Warning -->
                            @if($roiStats['completion_7x_percentage'] >= 100)
                            <div class="mt-4 p-3 bg-light-danger rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-ban text-danger mr-2"></i>
                                    <div>
                                        <span class="font-weight-bold text-danger">7X Limit Reached - Withdrawals Disabled!</span>
                                        <div class="mt-1">
                                            <small class="text-muted">You've earned 7X your investment. Withdrawals are disabled. Topup to increase your 7X cap and re-enable withdrawals. ROI remains permanently stopped.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- 7X ROI Progress Card -->
                <div class="card card-custom card-stretch gutter-b">
                    <div class="border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-dark">
                            7X ROI Progress
                            @if(!$roiStats['withdrawal_enabled'])
                                <span class="badge badge-danger ml-2">Withdrawal Disabled</span>
                            @else
                                <span class="badge badge-success ml-2">Withdrawal Enabled</span>
                            @endif
                        </h3>
                    </div>
                    <div class="pt-0 card-body">
                        <div class="mb-5">
                            <!-- Investment & 7X Overview -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">Total Investment</span>
                                        <span class="font-weight-bold font-size-h6 text-primary">${{ number_format($roiStats['invested_amount'], 2) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">7X Target</span>
                                        <span class="font-weight-bold font-size-h6 text-warning">${{ number_format($roiStats['seven_x_limit'], 2) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">Total Earned</span>
                                        <span class="font-weight-bold font-size-h6 text-{{ $roiStats['completion_7x_percentage'] >= 100 ? 'danger' : 'success' }}">${{ number_format($roiStats['total_roi_paid'], 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- 7X Progress Bar -->
                            <div class="mt-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted font-size-sm">Progress to 7X Target</span>
                                    <span class="font-weight-bold font-size-sm">{{ number_format($roiStats['completion_7x_percentage'], 1) }}%</span>
                                </div>
                                <div class="progress progress-sm">
                                    <div class="progress-bar {{ $roiStats['completion_7x_percentage'] >= 100 ? 'bg-danger' : 'bg-warning' }}"
                                         role="progressbar"
                                         style="width: {{ min(100, $roiStats['completion_7x_percentage']) }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- 7X Status & Remaining -->
                            <div class="row mt-5">
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">Withdrawal Status</span>
                                        @if(!$roiStats['withdrawal_enabled'])
                                            <span class="badge badge-danger">🚫 Disabled (7X Reached)</span>
                                            <small class="text-muted mt-1">Topup to re-enable withdrawals</small>
                                        @else
                                            <span class="badge badge-success">✅ Enabled</span>
                                            <small class="text-muted mt-1">You can withdraw funds</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
                                        <span class="text-muted font-size-sm">Remaining to 7X</span>
                                        <span class="font-weight-bold font-size-h6 text-{{ $roiStats['remaining_7x_amount'] <= 0 ? 'danger' : 'warning' }}">
                                            ${{ number_format(max(0, $roiStats['remaining_7x_amount']), 2) }}
                                        </span>
                                        @if($roiStats['remaining_7x_amount'] <= 0)
                                            <small class="text-danger">🚫 7X limit reached!</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- 7X Warning -->
                            @if($roiStats['completion_7x_percentage'] >= 90 && $roiStats['completion_7x_percentage'] < 100)
                            <div class="mt-4 p-3 bg-light-warning rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                                    <div>
                                        <span class="font-weight-bold text-warning">Approaching 7X Limit!</span>
                                        <div class="mt-1">
                                            <small class="text-muted">You're close to reaching the 7X limit. Once reached, withdrawals will be disabled until you topup.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card card-custom card-stretch gutter-b">
                    <div class="border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-dark">Withdrawal Requests</h3> 
                    </div>
                    <div class="pt-0 card-body"> 
                        <div class="mb-10">
                            <div class="table-responsive">
                                <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                                    <thead>
                                        <tr class="text-left"> 
                                            <th class="pl-0" style="">S#</th>  
                                            <th style="min-width: 110px">Amount</th>
                                            <th style="min-width: 110px">Status</th> 
                                            <th style="min-width: 120px">Request Type</th>
                                            <th style="min-width: 120px">Fee <small class="text-danger">(In case Bank)</small></th> 
                                            <th style="min-width: 120px">Date</th> 
                                            <th style="min-width: 120px">Action</th> 
                                        </tr>
                                    </thead>
                                    <tbody>  
                                        @foreach($withDrawsRequests as $withDrawsRequest)
                                            <tr class="pl-0">
                                                <td>
                                                    <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $loop->iteration }}</span>
                                                </td>  
                                                <td>
                                                    <a class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $withDrawsRequest->amount    }}</a> 
                                                </td>   
                                                <td>
                                                    <span class="text-dark-75 font-weight-bolder d-block font-size-sm text-warning ">{{ ucfirst($withDrawsRequest->status) }} </span>  
                                                </td> 

                                                <td>
                                                    <span class="text-dark-75 font-weight-bolder d-block font-size-sm text-warning ">{{ ucfirst($withDrawsRequest->request_type) }} </span>  
                                                </td> 


                                                <td>
                                                    <span class="text-dark-75 font-weight-bolder d-block font-size-sm text-warning ">{{ ucfirst($withDrawsRequest->transfer_fee) }} </span>  
                                                </td> 

                                                
                                                <td>
                                                    <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $withDrawsRequest->created_at->format('d-m-Y') }}</span> 
                                                </td> 
                                                 
                                                <td>
                                                @if($withDrawsRequest->status == 'pending' )
                                                    <button 
                                                        type="button"
                                                        class="rounded-0 btn btn-sm btn-light-danger WithdrawDelete"
                                                        data-toggle="modal" 
                                                        data-target="#WithdrawDelete"
                                                        data-id="{{ $withDrawsRequest->id }}">
                                                    Delete Request
                                                    </button>  
                                                    @else
                                                        ----------
                                                    @endif
                                                </td> 
                                            </tr>  
                                        @endforeach
        
        
        
                                       
                                    </tbody>
                                </table>
                            </div>
                           
                        </div>  
                    </div> 
                </div> 
            </div> 
        </div>
        
    </div>
    <!--end::Entry-->
</div>

<div class="modal fade" id="WithdrawModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('withdrawals.store') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="online" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Withdrawal</h5>
                    
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body"> 
                    <p><strong>Available Balance: {{ $onlineWallets->sum('balance') }} </strong></p>
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="mb-2 form-control form-control-sm form-control-solid"
                             name="amount" min="{{ $setting->min_withdrawal_limit ?? 25 }}" step="0.01"
                             required
                             placeholder="Enter Amount"
                             >
                             <small class="text-danger">Minimum Withdrawal Amount: ${{ $setting->min_withdrawal_limit ?? 25 }}</small> 
                        </div>  
                        
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Description 
                            </label>
                            <textarea name="target_account_details" id="target_account_details" class="mb-2 form-control form-control-sm form-control-solid" required></textarea>
                        </div>  
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mb-10 mr-2 font-weight-bold ">
                                Choose Withdraw Option <span class="text-danger"> *</span>
                            </label>
                            <div>
                                <input type="radio" value="bank" class="" name="withdrawal_option">
                                <span>Bank <small class="text-warning">({{ $setting->bank_withdrawal_fee_percent ?? 2 }}% fee applies)</small></span>
                                <br>
                                <input type="radio" value="usdt" name="withdrawal_option">
                                <span>USDT <small class="text-success">({{ $setting->usdt_withdrawal_discount_percent ?? 2 }}% discount!)</small></span>
                                <br>
                                <input type="radio" value="cash" name="withdrawal_option">
                                <span>Cash
                                    @if(($setting->cash_withdrawal_fee_percent ?? 0) > 0)
                                        <small class="text-warning">({{ $setting->cash_withdrawal_fee_percent }}% fee applies)</small>
                                    @else
                                        <small class="text-muted">(No fees)</small>
                                    @endif
                                </span>
                            </div>

                        </div>  
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">Withdrawal </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="WithdrawModelTransfer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('withdrawals.member.transfer') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="member_transfer" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Member Transfer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body"> 
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Username / Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="mb-2 form-control form-control-sm form-control-solid" 
                             name="member_account"   
                             required  
                             placeholder="Enter Member Account email or password"
                             >  
                        </div>  

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="mb-2 form-control form-control-sm form-control-solid"
                             name="amount" min="{{ $setting->min_member_transfer ?? 7 }}" step="0.01"
                             required
                             max="{{ $onlineWallets->sum('balance') }}"
                             placeholder="Enter Amount"
                             >
                             <small class="text-danger">Minimum Transfer Amount: ${{ $setting->min_member_transfer ?? 7 }}</small> 
                        </div>  

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Description 
                            </label>
                            <textarea name="description" id="description" class="mb-2 form-control form-control-sm form-control-solid"></textarea>
                            
                        </div>  
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">Transfer </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="WithdrawDelete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('withdrawals.delete') }}" method="POST">
                @csrf
                <input type="hidden" name="request_id" id="request-id" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete Withdrawal Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class=""> 
                    <div class="form-group row">  
                        <p class="p-12 pb-1 text-danger-75 font-weight-bolder text-danger">
                           Are You Sure to Delete Withdrawal Request ? 
                        </p> 
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-danger btn-sm">Delete Request </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="TopUpAccount" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('account.topups.process') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="online" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">TopUp Your Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p><strong>Available Balance: {{ $onlineWallets->sum('balance') }} </strong></p>
                    <div class="form-group row">
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Amount <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="mb-2 form-control form-control-sm form-control-solid"
                             name="topUp_amount" min="5" max="{{ $onlineWallets->sum('balance') }}" step="0.01"
                             required
                             placeholder="Enter Amount"
                             >
                        </div>

                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Description
                            </label>
                            <textarea name="topUp_description"
                          class="mb-2 form-control form-control-sm form-control-solid"
                            required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">TopUp </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="clearNegativeModal" tabindex="-1" role="dialog" aria-labelledby="clearNegativeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <form action="{{ route('clear.negative.points') }}" method="POST">
          @csrf
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="clearNegativeModalLabel">Clear Negative Points</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                You currently have <strong>{{ Auth::user()->negative_pv }}</strong> negative points.<br>
                On clicking <strong>Submit</strong>, this amount will be deducted from your online wallet.
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-success btn-sm rounded-0">Yes, Clear Points</button>
              <button type="button" class="btn btn-secondary rounded-0" data-dismiss="modal">Cancel</button>
            </div>
          </div>
      </form>
    </div>
  </div>
  
  
<!--end::Content-->
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar');  
         $(document).ready(function() {
            $('.WithdrawDelete').on('click', function() {
                var requestId = $(this).data('id');
                $('#request-id').val(requestId);
            });
        });

    </script>
    
@endsection