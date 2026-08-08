@extends('demo.layout.app')
@section('title', 'Savings Program — ROI Wallet')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Savings Program — ROI Wallet</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Wallets</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Savings ROI</a></li>
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
                    Your enrollment is pending admin verification. Daily ROI will appear here once your account is activated.
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="row mb-6">
                <div class="col-md-6">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalEarning, 2) }}</div>
                            <div class="font-size-sm mt-1">Total ROI Earned (All-Time)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($currentBalance, 2) }}</div>
                            <div class="font-size-sm mt-1">Current Balance</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($currentBalance > 0)
                <button type="button" class="btn btn-sm btn-info font-weight-bold mb-4"
                    data-toggle="modal" data-target="#roiTransferModal">
                    Transfer to Online Wallet
                </button>
            @endif

            {{-- ROI Table --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Daily ROI History</h3>
                </div>
                <div class="card-body pt-0">
                    @if($entries->isEmpty())
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
                                @foreach($entries as $entry)
                                <tr>
                                    <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                    <td class="font-weight-bold text-success">${{ number_format($entry->balance, 2) }}</td>
                                    <td class="text-muted">{{ $entry->description ?? 'Daily saving account ROI' }}</td>
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

{{-- Transfer to Online Wallet Modal --}}
<div class="modal fade" id="roiTransferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.transfer.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="saving_roi">
                <div class="modal-header">
                    <h5 class="modal-title">Transfer Saving ROI to Online Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Transfer Amount</label>
                        <input type="number" name="amount" class="form-control form-control-sm form-control-solid"
                            min="{{ $setting->min_wallet_transfer ?? 7.35 }}" step="0.01"
                            max="{{ $currentBalance }}" required
                            placeholder="Enter amount">
                        <strong class="text-danger">Available Balance: ${{ number_format($currentBalance, 2) }}</strong>
                        <small class="text-muted d-block">Minimum Transfer: ${{ $setting->min_wallet_transfer ?? 7.35 }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary btn-sm rounded-0" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-0">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
