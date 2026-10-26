@extends('demo.layout.app')
@section('title', 'Savings Program — Direct / Indirect Commissions')

@section('custom_css')
<style>
    /* Violet wallet theme (theme "info" violet #8950FC) — this page only */
    .wv-card { color: #fff; border: 0; }
    .wv-card--direct   { background: linear-gradient(135deg, #8950fc 0%, #6f3fe6 100%); }
    .wv-card--indirect { background: linear-gradient(135deg, #a47bff 0%, #8950fc 100%); }
    .wv-card--balance  { background: #eee5ff; color: #5b2bc9; border: 1px solid #d9c6ff; }
    .wv-card--balance .wv-muted { color: #7a52d6; }

    .wv-option {
        display: flex; align-items: center; width: 100%; text-align: left;
        background: #faf7ff; border: 2px solid #c9adff; border-radius: 12px;
        padding: 1rem 1.25rem; cursor: pointer;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease, transform .1s ease;
    }
    .wv-option:hover { background: #f3ecff; border-color: #8950fc; box-shadow: 0 6px 18px rgba(137, 80, 252, .18); }
    .wv-option:focus { outline: none; }
    .wv-option:focus-visible { box-shadow: 0 0 0 .25rem rgba(137, 80, 252, .35); border-color: #8950fc; }
    .wv-option:active, .wv-option.is-selected { background: #e9dcff; border-color: #6f3fe6; transform: scale(.995); }
    .wv-option[disabled] { cursor: not-allowed; opacity: .55; box-shadow: none; transform: none; background: #faf7ff; border-color: #e2d6ff; }

    .wv-option__icon {
        flex: 0 0 48px; height: 48px; border-radius: 50%; margin-right: 1rem;
        display: flex; align-items: center; justify-content: center;
        background: #8950fc; color: #fff; font-size: 1.5rem;
    }
    .wv-option__title { font-weight: 700; color: #3f2a7a; font-size: 1.1rem; }
    .wv-chip {
        display: inline-block; padding: .2rem .6rem; border-radius: 20px; font-size: .8rem; font-weight: 600;
        background: #eee5ff; color: #6f3fe6; margin: .25rem .35rem 0 0;
    }
    .wv-option__amount { margin-left: auto; text-align: right; padding-left: 1rem; }
    .wv-option__amount strong { display: block; color: #5b2bc9; font-size: 1.35rem; }

    .wv-btn { background: #8950fc; border-color: #8950fc; color: #fff; }
    .wv-btn:hover, .wv-btn:focus { background: #7337ee; border-color: #7337ee; color: #fff; }
    .wv-btn:active { background: #5f2bd4 !important; border-color: #5f2bd4 !important; }
    .wv-btn:focus-visible { box-shadow: 0 0 0 .2rem rgba(137, 80, 252, .4); }

    #savingCommissionTransferModal .modal-header { background: #eee5ff; border-bottom-color: #d9c6ff; }
    #savingCommissionTransferModal .modal-title { color: #4b23b5; }
    #savingCommissionTransferModal .form-control:focus { border-color: #8950fc; box-shadow: 0 0 0 .2rem rgba(137, 80, 252, .2); background-color: #fff; }
</style>
@endsection

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
                    <div class="card card-custom wv-card wv-card--direct h-100">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.4rem;font-weight:700;">${{ number_format($totalDirect, 2) }}</div>
                            <div class="font-size-sm mt-1">Saving Direct (Level 1)</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <div class="card card-custom wv-card wv-card--indirect h-100">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.4rem;font-weight:700;">${{ number_format($totalIndirect, 2) }}</div>
                            <div class="font-size-sm mt-1">Saving Indirect (Levels 2–7)</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <div class="card card-custom wv-card wv-card--balance h-100">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.4rem;font-weight:700;">${{ number_format($currentBalance, 2) }}</div>
                            <div class="font-size-sm mt-1 wv-muted">Available to Transfer</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transfer option: Saving Direct + Indirect → Online Wallet --}}
            <div class="mb-6">
                <button type="button" class="wv-option" data-toggle="modal" data-target="#savingCommissionTransferModal"
                        @if($currentBalance <= 0) disabled @endif>
                    <span class="wv-option__icon"><i class="la la-exchange-alt"></i></span>
                    <span>
                        <span class="wv-option__title d-block">Transfer to Online Wallet</span>
                        <span class="wv-chip">Saving Direct ${{ number_format($totalDirect, 2) }}</span>
                        <span class="wv-chip">Saving Indirect ${{ number_format($totalIndirect, 2) }}</span>
                        <span class="d-block text-muted font-size-sm mt-1">
                            @if($currentBalance > 0)
                                Minimum transfer: ${{ number_format($setting->saving_commission_min_transfer ?? 10.70, 2) }}
                            @else
                                Nothing available to transfer yet.
                            @endif
                        </span>
                    </span>
                    <span class="wv-option__amount">
                        <span class="text-muted font-size-sm">Available</span>
                        <strong>${{ number_format($currentBalance, 2) }}</strong>
                    </span>
                </button>
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
                    <h5 class="modal-title font-weight-bold"><i class="la la-exchange-alt mr-1"></i> Saving Direct / Indirect → Online Wallet</h5>
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
                            <strong style="color:#6f3fe6;">Available Balance: ${{ number_format($currentBalance, 2) }}</strong><br>
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
                    <button type="submit" class="btn wv-btn btn-sm font-weight-bold">Confirm Transfer</button>
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
