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
            <div class="mb-6 row">
                <div class="col-md-4">
                    <div class="text-white card card-custom bg-success">
                        <div class="py-5 text-center card-body">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalRoi, 2) }}</div>
                            <div class="mt-1 font-size-sm">Total Saving ROI Earned</div> 
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-white card card-custom bg-primary">
                        <div class="py-5 text-center card-body">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalDirect, 2) }}</div>
                            <div class="mt-1 font-size-sm">Direct Commissions</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-white card card-custom bg-info">
                        <div class="py-5 text-center card-body">
                            <div style="font-size:1.6rem;font-weight:700;">${{ number_format($totalIndirect, 2) }}</div>
                            <div class="mt-1 font-size-sm">Indirect Commissions</div>
                        </div>
                    </div>
                </div>
            </div>
            @if($totalRoi > 0)
                <button type="button" class="mt-3 mb-3 btn btn-sm btn-info font-weight-bold"
                    data-toggle="modal" data-target="#roiTransferModal">
                    Transfer to Online
                </button>
            @endif

            @if($user->account_type === 'saving' && !$user->saving_registration_completed)
                <div class="alert alert-warning">
                    <strong>Account not yet active.</strong> Complete Instalment #1 to start earning daily ROI and commissions.
                </div>
            @endif

            {{-- Daily ROI Table --}}
            <div class="card card-custom gutter-b">
                <div class="py-5 border-0 card-header">
                    <h3 class="card-title font-weight-bolder text-dark">Daily Saving ROI</h3>
                </div>
                <div class="pt-0 card-body">
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
                <div class="py-5 border-0 card-header">
                    <h3 class="card-title font-weight-bolder text-dark">Saving Referral Commissions</h3>
                </div>
                <div class="pt-0 card-body">
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

{{-- Saving ROI → Online Wallet transfer modal --}}
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
                            max="{{ $totalRoi }}" required
                            placeholder="Enter amount">
                        <strong class="text-danger">Available Balance: ${{ number_format($totalRoi, 2) }}</strong>
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
