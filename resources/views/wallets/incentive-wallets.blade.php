@extends('demo.layout.app')
@section('title', 'Designation Incentive Wallet')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Designation Incentive Wallet</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Wallets</a></li>
                        <li class="breadcrumb-item"><a href="" class="text-muted">Designation Incentive</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--end::Subheader-->

    <div class="flex-column-fluid">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-0">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-0">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <!-- Balance Cards -->
            <div class="row mb-4">
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body text-center py-5">
                            <div class="p-3 mb-3 bg-warning bg-opacity-10 rounded-circle d-inline-block">
                                <i class="fas fa-star fa-2x text-warning"></i>
                            </div>
                            <h6 class="text-muted text-uppercase font-weight-bold">Total Earned</h6>
                            <h3 class="font-weight-bolder text-warning mt-2">${{ number_format($totalEarning, 2) }}</h3>
                            <p class="mb-0 small text-muted">All-time incentive earnings</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body text-center py-5">
                            <div class="p-3 mb-3 bg-success bg-opacity-10 rounded-circle d-inline-block">
                                <i class="fas fa-wallet fa-2x text-success"></i>
                            </div>
                            <h6 class="text-muted text-uppercase font-weight-bold">Current Balance</h6>
                            <h3 class="font-weight-bolder text-success mt-2">${{ number_format($currentBalance, 2) }}</h3>
                            <p class="mb-0 small text-muted">Available to transfer</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transfer Button & Info -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                @if($currentBalance > 0)
                    <button class="btn btn-warning rounded-0 font-weight-bold px-5" data-toggle="modal" data-target="#transferModal">
                        <i class="fas fa-exchange-alt mr-2"></i> Transfer to Online Wallet
                    </button>
                @else
                    <span class="text-muted"><i class="fas fa-info-circle mr-1"></i> No balance available to transfer.</span>
                @endif
                <a href="{{ route('show.transaction.history') }}" class="btn btn-outline-primary btn-sm rounded-0">
                    <i class="la la-history"></i> Transaction History
                </a>
            </div>

            <div class="alert alert-info rounded-0 mb-4">
                <i class="fas fa-info-circle mr-2"></i>
                A <strong>5% charge</strong> applies on every transfer to Online Wallet. Minimum transfer is <strong>$5</strong>.
                This wallet income is included in your <strong>2x plan</strong>.
            </div>

            <!-- Transaction Table -->
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title">
                        <span class="card-label font-weight-bolder text-dark">Designation Incentive History</span>
                    </h3>
                </div>
                <div class="card-body py-0">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr class="text-left">
                                    <th>S#</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wallets as $entry)
                                    <tr class="text-left">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <strong class="{{ $entry->transaction_type == 'credit' ? 'text-success' : 'text-danger' }}">
                                                {{ $entry->transaction_type == 'credit' ? '+' : '-' }}${{ number_format(abs($entry->balance), 2) }}
                                            </strong>
                                        </td>
                                        <td>{{ $entry->description ?? '--' }}</td>
                                        <td>{{ $entry->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            No incentive transactions yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.transfer.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="designation_incentive">
                <div class="modal-header">
                    <h5 class="modal-title">Transfer to Online Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-center text-danger font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-1"></i> 5% charge applies on every transfer
                    </p>
                    <div class="form-group">
                        <label class="font-weight-bold">Transfer Amount ($)</label>
                        <input type="number" name="amount" class="form-control rounded-0"
                            id="transferAmount" min="{{ $setting->min_wallet_transfer ?? 7.35 }}" step="0.01"
                            max="{{ $currentBalance }}"
                            placeholder="Minimum ${{ $setting->min_wallet_transfer ?? 7.35 }}" required>
                        <small class="text-muted">Available Balance: <strong class="text-success">${{ number_format($currentBalance, 2) }}</strong></small>
                    </div>
                    <div class="alert alert-light rounded-0 p-2">
                        <small>
                            <strong>After 5% charge you will receive:</strong>
                            <strong class="text-success" id="netAmount">$0.00</strong>
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary btn-sm rounded-0" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning btn-sm rounded-0 font-weight-bold">
                        <i class="fas fa-exchange-alt mr-1"></i> Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('page_js')
<script>
    $('#transferAmount').on('input', function () {
        var amount = parseFloat($(this).val()) || 0;
        var net = amount - (amount * 0.05);
        $('#netAmount').text('$' + (net > 0 ? net.toFixed(2) : '0.00'));
    });
</script>
@endsection
