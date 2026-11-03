{{--
    Saving commission income from ALL downline (new + existing): this month, previous month,
    and any older month picked with the month filter (?income_month=YYYY-MM).
    Needs: $savingIncome from DownlineIncomeService::savingMonthly()
    Optional: $smiId (unique prefix when included more than once on a page)
--}}
@php
    $smiId     = $smiId ?? 'smi';
    $smiPicked = $savingIncome['selected_month'] ?? null;
    $smiTabs   = ['current' => 'This Month', 'previous' => 'Previous Month'];
    if (isset($savingIncome['selected'])) {
        $smiTabs['selected'] = $savingIncome['selected']['from']->format('F Y');
    }
    $smiActive = isset($savingIncome['selected']) ? 'selected'
        : ($smiPicked && $smiPicked === $savingIncome['previous']['from']->format('Y-m') ? 'previous' : 'current');
    $smiPanes  = [];
    foreach ($smiTabs as $k => $l) {
        $smiPanes[$k] = [$l, $savingIncome[$k]];
    }
    $smiFirst  = \App\Models\Wallet::where('user_id', auth()->id())->where('wallet_type', 'direct_indirect')
        ->where('source_type', 'saving_instalment')->min('created_at');
@endphp
<style>
    .smi-card { border: 1px solid #e2d6ff; }
    .smi-title { color: #4b23b5; }
    .smi-tabs .nav-link { color: #7a52d6; font-weight: 600; border: 0; border-bottom: 2px solid transparent; padding: .5rem .9rem; }
    .smi-tabs .nav-link.active { color: #4b23b5; background: transparent; border-bottom-color: #8950fc; }
    .smi-total { background: linear-gradient(135deg, #8950fc 0%, #6f3fe6 100%); color: #fff; border: 0; }
    .smi-soft  { background: #eee5ff; color: #5b2bc9; border: 1px solid #d9c6ff; }
    .smi-soft .smi-muted { color: #7a52d6; }
</style>

<div class="card card-custom gutter-b smi-card" id="{{ $smiId }}">
    <div class="card-header border-0 py-4 d-flex align-items-center justify-content-between flex-wrap">
        <h3 class="card-title font-weight-bolder mb-0 smi-title">
            <i class="fas fa-calendar-check mr-2" style="color:#8950fc;"></i>
            Saving Commission Income
        </h3>
        <div class="d-flex align-items-center flex-wrap">
            <ul class="nav smi-tabs mr-3" role="tablist">
                @foreach($smiTabs as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ $key === $smiActive ? 'active' : '' }}" data-toggle="tab" role="tab"
                           href="#{{ $smiId }}-{{ $key }}">
                            {{ $label }}
                            @if($key !== 'selected')
                                <span class="text-muted font-weight-normal">({{ $savingIncome[$key]['from']->format('M Y') }})</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
            {{-- Pick any older month --}}
            <form method="GET" action="{{ url()->current() }}#{{ $smiId }}" class="d-flex align-items-center my-1">
                <input type="month" name="income_month" class="form-control form-control-sm" style="width:160px;"
                       value="{{ $smiPicked ?? '' }}" max="{{ now()->format('Y-m') }}"
                       @if($smiFirst) min="{{ \Carbon\Carbon::parse($smiFirst)->format('Y-m') }}" @endif
                       aria-label="Choose month">
                <button type="submit" class="btn btn-sm btn-light-primary font-weight-bold ml-2">Show</button>
                @if($smiPicked)
                    <a href="{{ url()->current() }}#{{ $smiId }}" class="btn btn-sm btn-light ml-1" title="Clear">&times;</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card-body pt-0">
        <div class="tab-content">
            @foreach($smiPanes as $key => [$tabLabel, $inc])
                <div class="tab-pane fade {{ $key === $smiActive ? 'show active' : '' }}" id="{{ $smiId }}-{{ $key }}" role="tabpanel">
                    <div class="text-muted font-size-sm mb-3">
                        {{ $inc['from']->format('d M') }} – {{ ($key === 'current' ? now() : $inc['to'])->format('d M Y') }} · from new &amp; existing downline
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-3 mb-3">
                            <div class="card card-custom smi-total h-100">
                                <div class="card-body py-4 text-center">
                                    <div class="font-size-sm" style="opacity:.85;">{{ $tabLabel }}</div>
                                    <div style="font-size:1.6rem;font-weight:800;line-height:1.2;">${{ number_format($inc['net'], 2) }}</div>
                                    <div style="font-size:.72rem;opacity:.8;">from {{ $inc['members'] }} member(s)</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md mb-3">
                            <div class="card card-custom smi-soft h-100">
                                <div class="card-body py-4 text-center">
                                    <div class="font-size-sm smi-muted">Direct (L1)</div>
                                    <div class="font-weight-bolder font-size-h4">${{ number_format($inc['direct'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md mb-3">
                            <div class="card card-custom smi-soft h-100">
                                <div class="card-body py-4 text-center">
                                    <div class="font-size-sm smi-muted">Indirect (L2–7)</div>
                                    <div class="font-weight-bolder font-size-h4">${{ number_format($inc['indirect'], 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md mb-3">
                            <div class="card card-custom h-100" style="background:#eefbf3;">
                                <div class="card-body py-4 text-center">
                                    <div class="text-muted font-size-sm">From New Downline</div>
                                    <div class="font-weight-bolder font-size-h4 text-success">${{ number_format($inc['from_new'], 2) }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $inc['new_members'] }} joined in {{ $inc['from']->format('F') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md mb-3">
                            <div class="card card-custom h-100" style="background:#f3f6f9;">
                                <div class="card-body py-4 text-center">
                                    <div class="text-muted font-size-sm">From Existing Downline</div>
                                    <div class="font-weight-bolder font-size-h4 text-dark">${{ number_format($inc['from_existing'], 2) }}</div>
                                    <div class="text-muted" style="font-size:.72rem;">{{ $inc['members'] - $inc['new_members'] }} joined earlier</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($inc['adjustments'] > 0)
                        <div class="text-muted font-size-sm mb-3">
                            Gross ${{ number_format($inc['gross'], 2) }} − corrections ${{ number_format($inc['adjustments'], 2) }}
                            = <strong>${{ number_format($inc['net'], 2) }}</strong>
                        </div>
                    @endif

                    @if($inc['top_members']->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm table-vertical-center mb-0">
                                <thead><tr class="text-muted"><th>Downline Member</th><th>Level</th><th>Joined</th><th class="text-right">{{ $tabLabel }}</th></tr></thead>
                                <tbody>
                                @foreach($inc['top_members'] as $m)
                                    <tr>
                                        <td><span class="font-weight-bold">{{ $m['name'] ?? 'Deleted member' }}</span>
                                            @if($m['username'])<span class="text-muted small">{{ '@' . $m['username'] }}</span>@endif</td>
                                        <td>{{ $m['level'] ?? '—' }}</td>
                                        <td class="small text-nowrap">
                                            {{ $m['joined_at']?->format('d M Y') ?? '—' }}
                                            @if($m['is_new'])
                                                <span class="label label-inline label-light-success font-weight-bold ml-1">New</span>
                                            @else
                                                <span class="label label-inline label-light-dark font-weight-bold ml-1">Existing</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold">${{ number_format($m['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No saving commission earned in {{ $inc['from']->format('F Y') }}.</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
