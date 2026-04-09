@extends('demo.layout.app')
@section('title', 'Saving Account Wallet')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Saving Account Wallet</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Wallets</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Saving Account</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            {{-- Summary Cards --}}
            <div class="row mb-6">
                <div class="col-md-4">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalRoi, 2) }}</div>
                            <div class="font-size-sm mt-1">Total Saving ROI Earned</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalDirect, 2) }}</div>
                            <div class="font-size-sm mt-1">Direct Commissions</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-info text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalIndirect, 2) }}</div>
                            <div class="font-size-sm mt-1">Indirect Commissions</div>
                        </div>
                    </div>
                </div>
            </div>

            @if(!$user->saving_registration_completed)
                <div class="alert alert-warning">
                    <strong>Account not yet active.</strong> Complete Instalment #1 to start earning daily ROI and commissions.
                </div>
            @endif

            {{-- Daily ROI Table --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Daily Saving ROI</h3>
                </div>
                <div class="card-body pt-0">
                    @if($roiEntries->isEmpty())
                        <p class="text-muted">No ROI payments yet.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roiEntries as $entry)
                                <tr>
                                    <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                    <td class="font-weight-bold text-success">${{ number_format($entry->balance, 2) }}</td>
                                    <td class="text-muted">{{ $entry->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $roiEntries->links() }}
                    @endif
                </div>
            </div>

            {{-- Commissions Table --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Saving Referral Commissions</h3>
                </div>
                <div class="card-body pt-0">
                    @if($commissionEntries->isEmpty())
                        <p class="text-muted">No commissions yet.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Level</th>
                                    <th>Amount</th>
                                    <th>From</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissionEntries as $entry)
                                <tr>
                                    <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($entry->commission_type === 'direct')
                                            <span class="badge badge-light-primary">Direct</span>
                                        @else
                                            <span class="badge badge-light-info">Indirect</span>
                                        @endif
                                    </td>
                                    <td>Level {{ $entry->level }}</td>
                                    <td class="font-weight-bold text-primary">${{ number_format($entry->balance, 2) }}</td>
                                    <td class="text-muted">
                                        @if($entry->wallet_from)
                                            {{ optional(\App\Models\User::find($entry->wallet_from))->username ?? '—' }}
                                        @else —
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $commissionEntries->links() }}
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
