@extends('demo.layout.app')
@section('title','Return On Investment')
@section('content')

@php
    $blockedWallets = json_decode($setting->blocked_wallets ?? '{}', true);
@endphp

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">ROI Wallet</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Wallets</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">ROI</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- ── 3 Summary Cards ──────────────────────────────────────────── --}}
            <div class="row mb-6">

                {{-- Card 1: Total ROI (all time, both types) --}}
                <div class="col-md-4 mb-4">
                    <div class="card card-custom shadow-sm h-100" style="border-left:4px solid #6f42c1;">
                        <div class="card-body py-5 d-flex align-items-center">
                            <div class="mr-4" style="width:52px;height:52px;border-radius:50%;background:rgba(111,66,193,.12);display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-chart-line fa-lg" style="color:#6f42c1;"></i>
                            </div>
                            <div>
                                <div class="text-muted font-size-sm font-weight-bold text-uppercase mb-1">Total ROI Earned</div>
                                <div class="font-weight-bolder" style="font-size:1.5rem;color:#6f42c1;">${{ number_format($totalEarning, 2) }}</div>
                                <div class="text-muted font-size-xs">Standard + Saving combined</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Standard ROI Balance --}}
                <div class="col-md-4 mb-4">
                    <div class="card card-custom shadow-sm h-100" style="border-left:4px solid #0078d4;">
                        <div class="card-body py-5 d-flex align-items-center">
                            <div class="mr-4" style="width:52px;height:52px;border-radius:50%;background:rgba(0,120,212,.12);display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-wallet fa-lg" style="color:#0078d4;"></i>
                            </div>
                            <div>
                                <div class="text-muted font-size-sm font-weight-bold text-uppercase mb-1">Standard ROI Balance</div>
                                <div class="font-weight-bolder" style="font-size:1.5rem;color:#0078d4;">${{ number_format($stdCurrentBalance, 2) }}</div>
                                <div class="text-muted font-size-xs">Total earned: ${{ number_format($stdTotalEarning, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Saving ROI Balance --}}
                <div class="col-md-4 mb-4">
                    <div class="card card-custom shadow-sm h-100" style="border-left:4px solid #1bc5bd;">
                        <div class="card-body py-5 d-flex align-items-center">
                            <div class="mr-4" style="width:52px;height:52px;border-radius:50%;background:rgba(27,197,189,.12);display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-piggy-bank fa-lg" style="color:#1bc5bd;"></i>
                            </div>
                            <div>
                                <div class="text-muted font-size-sm font-weight-bold text-uppercase mb-1">Saving ROI Balance</div>
                                <div class="font-weight-bolder" style="font-size:1.5rem;color:#1bc5bd;">
                                    @if($isSavingUser)
                                        ${{ number_format($savCurrentBalance, 2) }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div class="text-muted font-size-xs">
                                    @if($isSavingUser)
                                        Total earned: ${{ number_format($savTotalEarning, 2) }}
                                    @else
                                        Not enrolled in saving plan
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Tabs ──────────────────────────────────────────────────────── --}}
            <div class="card card-custom gutter-b">

                {{-- Tab Nav --}}
                <div class="card-header border-0 pt-5 pb-0">
                    <ul class="nav nav-tabs nav-bold nav-tabs-line" id="roiTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="std-tab" data-toggle="tab" href="#tab-standard" role="tab">
                                <span class="nav-icon"><i class="fas fa-chart-bar text-primary"></i></span>
                                <span class="nav-text font-weight-bold">Standard ROI</span>
                                @if($stdCurrentBalance > 0)
                                    <span class="ml-2 label label-sm label-primary">${{ number_format($stdCurrentBalance, 2) }}</span>
                                @endif
                            </a>
                        </li>
                        @if($isSavingUser)
                        <li class="nav-item">
                            <a class="nav-link" id="sav-tab" data-toggle="tab" href="#tab-saving" role="tab">
                                <span class="nav-icon"><i class="fas fa-piggy-bank text-success"></i></span>
                                <span class="nav-text font-weight-bold">Saving ROI</span>
                                @if($savCurrentBalance > 0)
                                    <span class="ml-2 label label-sm label-success">${{ number_format($savCurrentBalance, 2) }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="card-body pt-3">
                    <div class="tab-content" id="roiTabContent">

                        {{-- ── Standard ROI Tab ───────────────────────────────── --}}
                        <div class="tab-pane fade show active" id="tab-standard" role="tabpanel">

                            {{-- Transfer button --}}
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                @if(!($blockedWallets['roi'] ?? false) && $stdCurrentBalance > 0)
                                    <a href="#" data-toggle="modal" data-target="#WithdrawModel"
                                        class="btn btn-primary btn-sm font-weight-bold px-5">
                                        <i class="fas fa-exchange-alt mr-1"></i> Transfer to Online Wallet
                                    </a>
                                @else
                                    <span></span>
                                @endif
                                <a href="{{ route('show.transaction.history') }}"
                                    class="btn btn-outline-primary btn-sm font-weight-bold px-5">
                                    <i class="fas fa-history mr-1"></i> Transaction History
                                </a>
                            </div>

                            {{-- Standard ROI Table --}}
                            @if($stdWallets->isEmpty())
                                <div class="text-center py-10">
                                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No standard ROI payments yet.</p>
                                </div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover table-head-custom table-vertical-center">
                                    <thead>
                                        <tr>
                                            <th class="pl-0">#</th>
                                            <th>Day</th>
                                            <th>Month</th>
                                            <th>Percentage</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stdWallets as $payment)
                                        <tr>
                                            <td><span class="text-dark-75 font-weight-bold font-size-sm">{{ $loop->iteration }}</span></td>
                                            <td><span class="text-dark-75 font-weight-bold font-size-sm">{{ $payment->created_at->format('D') }}</span></td>
                                            <td><span class="text-dark-75 font-weight-bold font-size-sm">{{ $payment->created_at->format('M Y') }}</span></td>
                                            <td>
                                                <span class="label label-inline label-light-primary font-weight-bold">
                                                    {{ $payment->percentage }}%
                                                </span>
                                            </td>
                                            <td><span class="text-success font-weight-bolder font-size-sm">${{ number_format($payment->balance, 2) }}</span></td>
                                            <td><span class="text-muted font-size-sm">{{ $payment->created_at->format('d M Y, h:i A') }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-4">
                                {{ $stdWallets->links('pagination::bootstrap-4') }}
                            </div>
                            @endif
                        </div>

                        {{-- ── Saving ROI Tab ──────────────────────────────────── --}}
                        @if($isSavingUser)
                        <div class="tab-pane fade" id="tab-saving" role="tabpanel">

                            {{-- Instalment lock notice --}}
                            @if($totalInstalments > 0)
                                @php $progressPct = $totalInstalments > 0 ? round(($paidInstalments / $totalInstalments) * 100) : 0; @endphp
                                <div class="alert @if($roiTransferLocked) alert-warning @else alert-success @endif d-flex align-items-center mb-4" style="border-radius:8px;">
                                    <i class="fas @if($roiTransferLocked) fa-lock @else fa-lock-open @endif mr-3 fa-lg"></i>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between mb-1">
                                            <strong>
                                                @if($roiTransferLocked)
                                                    ROI Transfer Locked — {{ $paidInstalments }}/{{ $totalInstalments }} instalments paid
                                                @else
                                                    ROI Transfer Unlocked — All {{ $totalInstalments }} instalments complete!
                                                @endif
                                            </strong>
                                            <span class="font-size-sm">{{ $progressPct }}%</span>
                                        </div>
                                        <div class="progress" style="height:6px;">
                                            <div class="progress-bar @if($roiTransferLocked) bg-warning @else bg-success @endif"
                                                 style="width:{{ $progressPct }}%"></div>
                                        </div>
                                        @if($roiTransferLocked)
                                            <small class="d-block mt-1">
                                                {{ $totalInstalments - $paidInstalments }} instalment(s) remaining before ROI can be transferred.
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Transfer button --}}
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                @if(!$roiTransferLocked && $savCurrentBalance > 0)
                                    <button type="button" class="btn btn-success btn-sm font-weight-bold px-5"
                                        data-toggle="modal" data-target="#savingRoiTransferModal">
                                        <i class="fas fa-exchange-alt mr-1"></i> Transfer to Online Wallet
                                    </button>
                                @elseif($roiTransferLocked)
                                    <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-5" disabled>
                                        <i class="fas fa-lock mr-1"></i> Locked ({{ $paidInstalments }}/{{ $totalInstalments }})
                                    </button>
                                @else
                                    <span></span>
                                @endif
                                <span class="text-muted font-size-sm">
                                    Available: <strong class="text-success">${{ number_format($savCurrentBalance, 2) }}</strong>
                                </span>
                            </div>

                            {{-- Saving ROI Table --}}
                            @if($savWallets->isEmpty())
                                <div class="text-center py-10">
                                    <i class="fas fa-piggy-bank fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No saving ROI payments yet.</p>
                                </div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover table-head-custom table-vertical-center">
                                    <thead>
                                        <tr>
                                            <th class="pl-0">#</th>
                                            <th>Day</th>
                                            <th>Month</th>
                                            <th>Amount</th>
                                            <th>Description</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($savWallets as $payment)
                                        <tr>
                                            <td><span class="text-dark-75 font-weight-bold font-size-sm">{{ $loop->iteration }}</span></td>
                                            <td><span class="text-dark-75 font-weight-bold font-size-sm">{{ $payment->created_at->format('D') }}</span></td>
                                            <td><span class="text-dark-75 font-weight-bold font-size-sm">{{ $payment->created_at->format('M Y') }}</span></td>
                                            <td><span class="text-success font-weight-bolder font-size-sm">${{ number_format($payment->balance, 2) }}</span></td>
                                            <td><span class="text-muted font-size-sm">{{ $payment->description ?? 'Daily saving ROI' }}</span></td>
                                            <td><span class="text-muted font-size-sm">{{ $payment->created_at->format('d M Y, h:i A') }}</span></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center mt-4">
                                {{ $savWallets->links('pagination::bootstrap-4') }}
                            </div>
                            @endif
                        </div>
                        @endif

                    </div>{{-- /tab-content --}}
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Standard ROI Transfer Modal --}}
@if(!($blockedWallets['roi'] ?? false))
    @include("wallets.transfer_modal", ['wallet' => 'roi', 'currentBalance' => $stdCurrentBalance])
@endif

{{-- Saving ROI Transfer Modal --}}
@if($isSavingUser && !$roiTransferLocked && $savCurrentBalance > 0)
<div class="modal fade" id="savingRoiTransferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.transfer.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="saving_roi">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Transfer Saving ROI to Online Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success py-2 font-size-sm mb-4">
                        <i class="fas fa-check-circle mr-1"></i>
                        All {{ $totalInstalments }} instalments complete — transfer is unlocked.
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Transfer Amount</label>
                        <input type="number" name="amount" class="form-control form-control-solid"
                            min="{{ $setting->min_wallet_transfer ?? 7.35 }}" step="0.01"
                            max="{{ $savCurrentBalance }}" required
                            placeholder="Enter amount">
                        <div class="d-flex justify-content-between mt-2">
                            <small class="text-muted">Min: ${{ number_format($setting->min_wallet_transfer ?? 7.35, 2) }}</small>
                            <small class="text-success font-weight-bold">Available: ${{ number_format($savCurrentBalance, 2) }}</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold px-6">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Keep active tab on page reload (after flash message) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var activeTab = sessionStorage.getItem('roiActiveTab');
        if (activeTab) {
            var el = document.querySelector('[href="' + activeTab + '"]');
            if (el) { el.click(); }
        }
        document.querySelectorAll('#roiTabs .nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                sessionStorage.setItem('roiActiveTab', this.getAttribute('href'));
            });
        });
    });
</script>

@endsection
