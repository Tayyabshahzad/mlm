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

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="row mb-5">
                <div class="col-12 col-md-3 mb-3">
                    <div class="card card-custom bg-primary text-white h-100">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.4rem;font-weight:700;">${{ number_format($totalEarning, 2) }}</div>
                            <div class="font-size-sm mt-1">Total Earned</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <div class="card card-custom bg-success text-white h-100">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.4rem;font-weight:700;">${{ number_format($totalDirect, 2) }}</div>
                            <div class="font-size-sm mt-1">Direct (Level 1)</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <div class="card card-custom bg-info text-white h-100">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.4rem;font-weight:700;">${{ number_format($totalIndirect, 2) }}</div>
                            <div class="font-size-sm mt-1">Indirect (Levels 2–7)</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <div class="card card-custom bg-warning text-white h-100">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.4rem;font-weight:700;">${{ number_format($currentBalance, 2) }}</div>
                            <div class="font-size-sm mt-1">Available Balance</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transfer Button --}}
            @if($currentBalance > 0)
            <div class="mb-6">
                <button type="button" class="btn btn-warning font-weight-bold"
                    data-toggle="modal" data-target="#savingCommissionTransferModal">
                    <i class="la la-exchange-alt"></i> Transfer to Online Wallet
                </button>
                <span class="text-muted font-size-sm ml-3">Minimum transfer: ${{ number_format($setting->saving_commission_min_transfer ?? 10.70, 2) }}</span>
            </div>
            @endif

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
                                    <td class="font-weight-bold text-primary">${{ number_format($entry->direct_balance + $entry->indirect_balance, 2) }}</td>
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
{{-- Transfer Modal --}}
<div class="modal fade" id="savingCommissionTransferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.saving.commission.to.online') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold">Transfer Saving Commissions to Online Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Transfer Amount ($)</label>
                        <input type="number" name="amount" id="transferAmount"
                            class="form-control form-control-solid"
                            min="{{ $setting->saving_commission_min_transfer ?? 10.70 }}"
                            max="{{ $currentBalance }}"
                            step="0.01" required
                            placeholder="Enter amount">
                        <div class="mt-2">
                            <strong class="text-warning">Available Balance: ${{ number_format($currentBalance, 2) }}</strong><br>
                            <small class="text-muted">Minimum Transfer: ${{ number_format($setting->saving_commission_min_transfer ?? 10.70, 2) }}</small>
                        </div>
                    </div>

                    <div class="alert alert-light-info mt-3" id="transferPreview" style="display:none;">
                        <table class="table table-sm mb-0">
                            <tr><td class="text-muted">Transfer Amount</td><td class="font-weight-bold" id="previewAmount">—</td></tr>
                            <tr><td class="text-muted font-weight-bold">You Receive</td><td class="text-success font-weight-bold" id="previewFinal">—</td></tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm font-weight-bold">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('page_js')
<script>
document.getElementById('transferAmount')?.addEventListener('input', function () {
    const amount = parseFloat(this.value);
    const preview = document.getElementById('transferPreview');
    if (!isNaN(amount) && amount > 0) {
        document.getElementById('previewAmount').textContent = '$' + amount.toFixed(2);
        document.getElementById('previewFinal').textContent  = '$' + amount.toFixed(2);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});
</script>
@endsection

@endsection
