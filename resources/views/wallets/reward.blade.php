@extends('demo.layout.app')
@section('title','Reward')
@section('content')
 <!--begin::Content-->
 <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-1"> 
                <!--begin::Page Heading-->
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <!--begin::Page Title-->
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Reward Wallet </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Wallets</a>
                        </li>   

                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Reward</a>
                        </li>
                    </ul> 
                </div> 
            </div>
             
        </div>
    </div> 
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="  flex-column-fluid"> 
        <div class="container"> 
            <div class="card card-custom gutter-b">
                <!--begin::Header-->
                <div class="row">
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
                                <p class="mb-0 small text-muted">All-time reward earnings</p>
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

                <!-- Action Buttons -->
                <div class="px-6 mt-3 mb-3 d-flex justify-content-between align-items-center">
                    @php
                        $blockedWallets = json_decode($setting->blocked_wallets ?? '{}', true);
                    @endphp
                    @if (!($blockedWallets['reward'] ?? false))
                        <a href="#" data-toggle="modal" data-target="#WithdrawModel" class="px-4 btn btn-info rounded-sm-pill">Transfer to Online Wallet</a>
                    @endif
                    <a href="{{ route('show.transaction.history') }}" class="px-4 btn btn-outline-primary rounded-sm-pill">Show Transaction History</a>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-0">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                            <thead>
                                <tr class="text-left"> 
                                    <th class="pl-0" style="">S#</th>
                                    <th style="min-width: 110px">Level</th>
                                    <th style="min-width: 110px">Earned (PV)</th>  
                                    <th style="min-width: 120px">Date</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($wallets as $reward)
                                <tr class="pl-0">
                                    <td>
                                        <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $loop->iteration }}</span>
                                    </td>  
                                    <td>
                                        <a class="text-dark-75 font-weight-bolder d-block font-size-sm"> {{ $reward->level }} </a> 
                                    </td>  
                                    <td>
                                        <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm"> {{ $reward->balance }} </span>
                                    </td>  
                                    <td>
                                        <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm">{{ $reward->created_at }}</span>
                                    </td>  
                                     
                                </tr>   
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Advance Table Widget 10-->
        </div>
        
    </div>
    <!--end::Entry-->
</div>
 
@if (!($blockedWallets['reward'] ?? false))
    @include("wallets.transfer_modal", ['wallet' => 'reward', 'currentBalance' => $currentBalance])
@endif  
 
<!--end::Content-->
@endsection  