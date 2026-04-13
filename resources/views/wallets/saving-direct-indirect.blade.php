@extends('demo.layout.app')
@section('title', 'Savings Program — Direct / Indirect Commissions')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Savings Program — Direct / Indirect</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Wallets</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Savings Direct / Indirect</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @if(!auth()->user()->saving_enrollment_activated && auth()->user()->saving_enrolled && auth()->user()->account_type !== 'saving')
                <div class="alert alert-warning mb-4">
                    <strong>Savings Program not yet activated.</strong>
                    Your enrollment is pending admin verification. Commissions will appear here once your account is activated.
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="row mb-6">
                <div class="col-md-4">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalEarning, 2) }}</div>
                            <div class="font-size-sm mt-1">Total Commissions Earned</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalDirect, 2) }}</div>
                            <div class="font-size-sm mt-1">Direct Commissions (Level 1)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-info text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalIndirect, 2) }}</div>
                            <div class="font-size-sm mt-1">Indirect Commissions (Levels 2–7)</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Commission Rate Info --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Savings Program Commission Rates</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex flex-wrap" style="gap:0.5rem;">
                        @foreach([1=>7,2=>2,3=>1,4=>1,5=>1,6=>1,7=>1] as $lvl => $rate)
                        <span class="badge badge-light-{{ $lvl === 1 ? 'success' : 'primary' }}" style="font-size:0.85rem; padding:0.4rem 0.75rem;">
                            Level {{ $lvl }}: {{ $rate }}%
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Commission History</h3>
                </div>
                <div class="card-body pt-0">
                    @if($entries->isEmpty())
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
                                @foreach($entries as $entry)
                                <tr>
                                    <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($entry->commission_type === 'direct')
                                            <span class="badge badge-light-success">Direct</span>
                                        @else
                                            <span class="badge badge-light-info">Indirect</span>
                                        @endif
                                    </td>
                                    <td>Level {{ $entry->level }}</td>
                                    <td class="font-weight-bold text-primary">${{ number_format($entry->balance, 2) }}</td>
                                    <td class="text-muted">
                                        {{ optional(\App\Models\User::find($entry->wallet_from))->username ?? '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $entries->links() }}
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
