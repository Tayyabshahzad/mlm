@extends('demo.layout.app')
@section('title','Return On Investment')
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
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">ROI Wallet </h5>
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
                            <a href="" class="text-muted">ROI</a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="flex-column-fluid">
        <div class="container">
            <div class="card card-custom gutter-b">
                <!--begin::Header-->
               <div class="row">
    <!-- Total ROI Earning Card -->
                    <div class="mb-4 col-md-6">
                        <div class="border-0 shadow-lg card rounded-3 h-100">
                            <div class="text-center card-body d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3 mb-3 bg-primary bg-opacity-10 rounded-circle">
                                    <i class="fas fa-chart-line fa-2x text-primary"></i>
                                </div>
                                <h6 class="text-muted text-uppercase fw-bold">Total ROI Earning</h6>
                                <h2 class="mt-2 fw-bold text-primary">
                                    ${{ number_format($totalEarning, 2) }}
                                </h2>
                                <p class="mb-0 small text-muted">All-time ROI generated</p>
                            </div>
                        </div>
                    </div>

                    <!-- Current ROI Balance Card -->
                    <div class="mb-4 col-md-6">
                        <div class="border-0 shadow-lg card rounded-3 h-100">
                            <div class="text-center card-body d-flex flex-column align-items-center justify-content-center">
                                <div class="p-3 mb-3 bg-success bg-opacity-10 rounded-circle">
                                    <i class="fas fa-wallet fa-2x text-success"></i>
                                </div>
                                <h6 class="text-muted text-uppercase fw-bold">Current ROI Balance</h6>
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

                    @if (!($blockedWallets['roi'] ?? false))
                    <a href="#" data-toggle="modal" data-target="#WithdrawModel"
                        class="px-4 btn btn-info rounded-sm-pill">
                        Transfer to Online Wallet
                    </a>
                    @endif

                    <a href="{{ route('show.transaction.history') }}"
                        class="px-4 btn btn-outline-primary rounded-sm-pill">
                        Show Transaction History
                    </a>
                </div>

                <div class="py-0 card-body">
                    <!--begin::Table-->
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center" id="kt_advance_table_widget_4">
                            <thead>
                                <tr class="text-left">
                                    <th class="pl-0" style="">S#</th>
                                    <th style="min-width: 110px">Day</th>
                                    <th style="min-width: 110px">Month</th>
                                    <th style="min-width: 110px">Percentage</th>
                                    <th style="min-width: 110px">Amount</th>
                                    <th style="min-width: 120px">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($wallets as $payment)
                                <tr class="pl-0">
                                    <td>
                                        <span href="#" class="text-dark-75 font-weight-bolder d-block font-size-sm">{{
                                            $loop->iteration }}</span>
                                    </td>
                                    <td>
                                        <a class="text-dark-75 font-weight-bolder d-block font-size-sm">{{
                                            $payment->created_at->format('D') }}</a>
                                    </td>
                                    <td>
                                        <a class="text-dark-75 font-weight-bolder d-block font-size-sm">{{
                                            $payment->created_at->format('M') }}</a>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{
                                            $payment->percentage }} % </span>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{
                                            $payment->balance }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-sm">{{
                                            $payment->created_at }}</span>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                    <!--end::Table-->

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4 px-3">
                        <div style="max-width: fit-content;">
                            {{ $wallets->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Advance Table Widget 10-->
        </div>

    </div>
    <!--end::Entry-->
</div>

@if (!($blockedWallets['roi'] ?? false))
@include("wallets.transfer_modal", ['wallet' => 'roi'])
@endif

@endsection