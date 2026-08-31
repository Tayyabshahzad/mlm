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

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-5" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-5" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            @if($user->account_type === 'saving' && !$user->saving_registration_completed)
                <div class="alert alert-warning mb-5">
                    <strong>Account not yet active.</strong> Complete Instalment #1 to start earning daily ROI and commissions.
                </div>
            @endif

            {{-- 5 Summary Cards --}}
            <div class="row mb-4">
                <div class="col-12 col-md mb-3">
                    <div class="card card-custom h-100" style="background: linear-gradient(135deg,#6f42c1,#a855f7); color:#fff;">
                        <div class="card-body py-4 text-center">
                            <div class="mb-2" style="font-size:1.6rem;opacity:.85;"><i class="fas fa-wallet"></i></div>
                            <div style="font-size:1.25rem;font-weight:700;">${{ number_format($totalEarned, 2) }}</div>
                            <div class="mt-1" style="font-size:.73rem;opacity:.9;">Total Earned</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md mb-3">
                    <div class="card card-custom h-100" style="background: linear-gradient(135deg,#0d6efd,#3b82f6); color:#fff;">
                        <div class="card-body py-4 text-center">
                            <div class="mb-2" style="font-size:1.6rem;opacity:.85;"><i class="fas fa-coins"></i></div>
                            <div style="font-size:1.25rem;font-weight:700;">${{ number_format($totalCurrentBalance, 2) }}</div>
                            <div class="mt-1" style="font-size:.73rem;opacity:.9;">Current Balance</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md mb-3">
                    <div class="card card-custom h-100" style="background: linear-gradient(135deg,#fd7e14,#f97316); color:#fff;">
                        <div class="card-body py-4 text-center">
                            <div class="mb-2" style="font-size:1.6rem;opacity:.85;"><i class="fas fa-chart-line"></i></div>
                            <div style="font-size:1.25rem;font-weight:700;">${{ number_format($roiTotalEarned, 2) }}</div>
                            <div class="mt-1" style="font-size:.73rem;opacity:.9;">Saving ROI Total</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md mb-3">
                    <div class="card card-custom h-100" style="background: linear-gradient(135deg,#198754,#22c55e); color:#fff;">
                        <div class="card-body py-4 text-center">
                            <div class="mb-2" style="font-size:1.6rem;opacity:.85;"><i class="fas fa-arrow-right"></i></div>
                            <div style="font-size:1.25rem;font-weight:700;">${{ number_format($directBalance, 2) }}</div>
                            <div class="mt-1" style="font-size:.73rem;opacity:.9;">Direct Commission</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md mb-3">
                    <div class="card card-custom h-100" style="background: linear-gradient(135deg,#0dcaf0,#06b6d4); color:#fff;">
                        <div class="card-body py-4 text-center">
                            <div class="mb-2" style="font-size:1.6rem;opacity:.85;"><i class="fas fa-sitemap"></i></div>
                            <div style="font-size:1.25rem;font-weight:700;">${{ number_format($indirectBalance, 2) }}</div>
                            <div class="mt-1" style="font-size:.73rem;opacity:.9;">Indirect Commission</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabs Navigation --}}
            <div class="card card-custom">
                <div class="card-header border-0 pt-5 pb-0">
                    <ul class="nav nav-tabs nav-tabs-line nav-tabs-line-primary" id="savingTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="roi-tab" data-toggle="tab" href="#tab-roi" role="tab">
                                <i class="fas fa-chart-line mr-1"></i> Saving ROI
                                <span class="badge badge-light-success ml-1">${{ number_format($roiBalance, 2) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="direct-tab" data-toggle="tab" href="#tab-direct" role="tab">
                                <i class="fas fa-arrow-right mr-1"></i> Direct Commission
                                <span class="badge badge-light-primary ml-1">${{ number_format($directBalance, 2) }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="indirect-tab" data-toggle="tab" href="#tab-indirect" role="tab">
                                <i class="fas fa-sitemap mr-1"></i> Indirect Commission
                                <span class="badge badge-light-info ml-1">${{ number_format($indirectBalance, 2) }}</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body pt-4">
                    <div class="tab-content" id="savingTabsContent">

                        {{-- ═══ TAB 1: Saving ROI ═══ --}}
                        <div class="tab-pane fade show active" id="tab-roi" role="tabpanel">

                            {{-- Instalment Progress --}}
                            @if($totalInstalments > 0)
                                @php $progressPct = round(($paidInstalments / $totalInstalments) * 100); @endphp
                                <div class="p-4 mb-4 rounded border @if($roiTransferLocked) border-warning bg-light-warning @else border-success bg-light-success @endif">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="font-weight-bold">
                                            @if($roiTransferLocked)
                                                🔒 ROI Transfer Locked
                                            @else
                                                ✅ ROI Transfer Unlocked
                                            @endif
                                        </span>
                                        <span class="font-size-sm text-muted">{{ $paidInstalments }} / {{ $totalInstalments }} instalments paid</span>
                                    </div>
                                    <div class="progress mb-2" style="height:8px;">
                                        <div class="progress-bar @if($roiTransferLocked) bg-warning @else bg-success @endif"
                                             style="width:{{ $progressPct }}%"></div>
                                    </div>
                                    @if($roiTransferLocked)
                                        <small class="text-warning">Complete all {{ $totalInstalments }} instalments to unlock ROI transfer. {{ $totalInstalments - $paidInstalments }} remaining.</small>
                                    @else
                                        <small class="text-success">All instalments complete — ROI transfer is now available.</small>
                                    @endif
                                </div>
                            @endif

                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <span class="text-muted font-size-sm">Total Earned:</span>
                                    <strong class="ml-1 text-success">${{ number_format($roiTotalEarned, 2) }}</strong>
                                    <span class="mx-3 text-muted">|</span>
                                    <span class="text-muted font-size-sm">Current Balance:</span>
                                    <strong class="ml-1 text-primary">${{ number_format($roiBalance, 2) }}</strong>
                                </div>
                                @if($roiBalance > 0 && !$roiTransferLocked)
                                    <button type="button" class="btn btn-sm btn-success font-weight-bold"
                                        data-toggle="modal" data-target="#roiTransferModal">
                                        <i class="fas fa-exchange-alt mr-1"></i> Transfer to Online
                                    </button>
                                @elseif($roiBalance > 0 && $roiTransferLocked)
                                    <button type="button" class="btn btn-sm btn-secondary font-weight-bold" disabled
                                        title="Complete all {{ $totalInstalments }} instalments to unlock">
                                        🔒 Transfer Locked ({{ $paidInstalments }}/{{ $totalInstalments }})
                                    </button>
                                @endif
                            </div>

                            @if($roiEntries->isEmpty())
                                <div class="text-center py-8 text-muted">
                                    <i class="fas fa-chart-line fa-3x mb-3 d-block" style="opacity:.3;"></i>
                                    No ROI payments yet.
                                </div>
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
                                                <td class="text-muted font-size-sm">{{ $entry->created_at->format('d M Y H:i') }}</td>
                                                <td><span class="font-weight-bold text-success">${{ number_format($entry->balance, 2) }}</span></td>
                                                <td class="text-muted">{{ $entry->description ?? 'Daily saving account ROI' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                {{ $roiEntries->appends(request()->except('roi_page'))->links() }}
                            @endif
                        </div>

                        {{-- ═══ TAB 2: Saving Direct ═══ --}}
                        <div class="tab-pane fade" id="tab-direct" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <span class="text-muted font-size-sm">Total Earned:</span>
                                    <strong class="ml-1 text-success">${{ number_format($directTotalEarned, 2) }}</strong>
                                    <span class="mx-3 text-muted">|</span>
                                    <span class="text-muted font-size-sm">Current Balance:</span>
                                    <strong class="ml-1 text-primary">${{ number_format($directBalance, 2) }}</strong>
                                </div>
                                @if($directBalance > 0)
                                    <button type="button" class="btn btn-sm btn-primary font-weight-bold"
                                        data-toggle="modal" data-target="#directTransferModal">
                                        <i class="fas fa-exchange-alt mr-1"></i> Transfer to Online
                                    </button>
                                @endif
                            </div>

                            @if($directEntries->isEmpty())
                                <div class="text-center py-8 text-muted">
                                    <i class="fas fa-arrow-right fa-3x mb-3 d-block" style="opacity:.3;"></i>
                                    No direct commissions yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-head-custom table-vertical-center">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Level</th>
                                                <th>Amount</th>
                                                <th>From User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($directEntries as $entry)
                                            <tr>
                                                <td class="text-muted font-size-sm">{{ $entry->created_at->format('d M Y H:i') }}</td>
                                                <td><span class="badge badge-light-primary">Level {{ $entry->level }}</span></td>
                                                <td><span class="font-weight-bold text-primary">${{ number_format($entry->balance, 2) }}</span></td>
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
                                {{ $directEntries->appends(request()->except('dir_page'))->links() }}
                            @endif
                        </div>

                        {{-- ═══ TAB 3: Saving Indirect ═══ --}}
                        <div class="tab-pane fade" id="tab-indirect" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <span class="text-muted font-size-sm">Total Earned:</span>
                                    <strong class="ml-1 text-success">${{ number_format($indirectTotalEarned, 2) }}</strong>
                                    <span class="mx-3 text-muted">|</span>
                                    <span class="text-muted font-size-sm">Current Balance:</span>
                                    <strong class="ml-1 text-primary">${{ number_format($indirectBalance, 2) }}</strong>
                                </div>
                                @if($indirectBalance > 0)
                                    <button type="button" class="btn btn-sm btn-info font-weight-bold"
                                        data-toggle="modal" data-target="#indirectTransferModal">
                                        <i class="fas fa-exchange-alt mr-1"></i> Transfer to Online
                                    </button>
                                @endif
                            </div>

                            @if($indirectEntries->isEmpty())
                                <div class="text-center py-8 text-muted">
                                    <i class="fas fa-sitemap fa-3x mb-3 d-block" style="opacity:.3;"></i>
                                    No indirect commissions yet.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-hover table-head-custom table-vertical-center">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Level</th>
                                                <th>Amount</th>
                                                <th>From User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($indirectEntries as $entry)
                                            <tr>
                                                <td class="text-muted font-size-sm">{{ $entry->created_at->format('d M Y H:i') }}</td>
                                                <td><span class="badge badge-light-info">Level {{ $entry->level }}</span></td>
                                                <td><span class="font-weight-bold text-info">${{ number_format($entry->balance, 2) }}</span></td>
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
                                {{ $indirectEntries->appends(request()->except('ind_page'))->links() }}
                            @endif
                        </div>

                    </div>{{-- /tab-content --}}
                </div>
            </div>{{-- /card --}}

        </div>
    </div>
</div>

{{-- Modal: ROI Transfer --}}
<div class="modal fade" id="roiTransferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.transfer.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="saving_roi">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-chart-line text-success mr-2"></i>Transfer Saving ROI</h5>
                    <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 font-size-sm mb-4">
                        Only your <strong>Saving ROI</strong> balance will be transferred. Direct and indirect commissions are not included.
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Transfer Amount</label>
                        <input type="number" name="amount" class="form-control form-control-sm form-control-solid"
                            min="{{ $setting->min_wallet_transfer ?? 7.35 }}" step="0.01"
                            max="{{ $roiBalance }}" required placeholder="Enter amount">
                        <strong class="text-danger d-block mt-1">Available: ${{ number_format($roiBalance, 2) }}</strong>
                        <small class="text-muted">Minimum Transfer: ${{ $setting->min_wallet_transfer ?? 7.35 }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm font-weight-bold">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Direct Commission Transfer --}}
<div class="modal fade" id="directTransferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.saving.commission.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="commission_type" value="direct">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-arrow-right text-primary mr-2"></i>Transfer Direct Commission</h5>
                    <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 font-size-sm mb-4">
                        Only your <strong>Direct Commission</strong> balance will be transferred. ROI and indirect commissions are not included.
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Transfer Amount</label>
                        <input type="number" name="amount" class="form-control form-control-sm form-control-solid"
                            min="{{ $setting->saving_commission_min_transfer ?? 10.70 }}" step="0.01"
                            max="{{ $directBalance }}" required placeholder="Enter amount">
                        <strong class="text-danger d-block mt-1">Available: ${{ number_format($directBalance, 2) }}</strong>
                        <small class="text-muted">Minimum Transfer: ${{ $setting->saving_commission_min_transfer ?? 10.70 }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Indirect Commission Transfer --}}
<div class="modal fade" id="indirectTransferModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.saving.commission.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="commission_type" value="indirect">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-sitemap text-info mr-2"></i>Transfer Indirect Commission</h5>
                    <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 font-size-sm mb-4">
                        Only your <strong>Indirect Commission</strong> balance will be transferred. ROI and direct commissions are not included.
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Transfer Amount</label>
                        <input type="number" name="amount" class="form-control form-control-sm form-control-solid"
                            min="{{ $setting->saving_commission_min_transfer ?? 10.70 }}" step="0.01"
                            max="{{ $indirectBalance }}" required placeholder="Enter amount">
                        <strong class="text-danger d-block mt-1">Available: ${{ number_format($indirectBalance, 2) }}</strong>
                        <small class="text-muted">Minimum Transfer: ${{ $setting->saving_commission_min_transfer ?? 10.70 }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm font-weight-bold">Confirm Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Persist active tab across page reloads
document.addEventListener('DOMContentLoaded', function () {
    var savedTab = sessionStorage.getItem('savingAccountTab');
    if (savedTab) {
        var el = document.querySelector('#savingTabs a[href="' + savedTab + '"]');
        if (el) { el.click(); }
    }
    document.querySelectorAll('#savingTabs a[data-toggle="tab"]').forEach(function (el) {
        el.addEventListener('shown.bs.tab', function (e) {
            sessionStorage.setItem('savingAccountTab', e.target.getAttribute('href'));
        });
    });
});
</script>
@endsection
