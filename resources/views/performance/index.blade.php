@extends('demo.layout.app')
@section('title', 'Team Performance Tracker')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">
                        <i class="fas fa-chart-bar text-primary mr-2"></i>Team Performance Tracker
                    </h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Team Performance</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            {{-- ── FILTER FORM ──────────────────────────────────── --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">
                        <i class="fas fa-filter text-primary mr-2"></i>Filter Performance
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <form method="GET" action="{{ route('team.performance') }}" id="perfForm">

                        {{-- Admin: user selector --}}
                        @if($isSuperAdmin)
                        <div class="row mb-4">
                            <div class="col-12">
                                <label class="font-weight-bold font-size-sm mb-2">
                                    <i class="fas fa-user mr-1 text-primary"></i>View Performance For
                                </label>
                                <select name="user_id" class="form-control form-control-solid" id="userSelect">
                                    <option value="">— My Own Performance —</option>
                                    @foreach($allUsers as $u)
                                        <option value="{{ $u->id }}"
                                            {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->username }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <div class="row align-items-end">
                            {{-- Date From --}}
                            <div class="col-12 col-md-3 mb-3 mb-md-0">
                                <label class="font-weight-bold font-size-sm mb-2">From Date</label>
                                <input type="date" name="from" class="form-control form-control-solid"
                                    value="{{ request('from', now()->startOfMonth()->format('Y-m-d')) }}" required>
                            </div>

                            {{-- Date To --}}
                            <div class="col-12 col-md-3 mb-3 mb-md-0">
                                <label class="font-weight-bold font-size-sm mb-2">To Date</label>
                                <input type="date" name="to" class="form-control form-control-solid"
                                    value="{{ request('to', now()->format('Y-m-d')) }}" required>
                            </div>

                            {{-- Level multi-select --}}
                            <div class="col-12 col-md-4 mb-3 mb-md-0">
                                <label class="font-weight-bold font-size-sm mb-2">
                                    Select Levels <span class="text-muted font-weight-normal">(multiple)</span>
                                </label>
                                <div class="d-flex flex-wrap" style="gap:0.4rem;">
                                    @foreach(range(1,7) as $lvl)
                                    @php
                                        $selectedLevels = request()->has('levels') ? (array) request('levels') : range(1,7);
                                        $isChecked = in_array($lvl, array_map('intval', $selectedLevels));
                                    @endphp
                                    <label class="level-chip {{ $isChecked ? 'active' : '' }}" for="lvl{{ $lvl }}">
                                        <input type="checkbox" name="levels[]" value="{{ $lvl }}"
                                            id="lvl{{ $lvl }}" {{ $isChecked ? 'checked' : '' }}
                                            class="d-none level-cb">
                                        Level {{ $lvl }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="col-12 col-md-2">
                                <button type="submit" class="btn btn-primary font-weight-bold w-100">
                                    <i class="fas fa-search mr-1"></i>Search
                                </button>
                            </div>
                        </div>

                        {{-- Quick presets --}}
                        <div class="mt-3 d-flex flex-wrap align-items-center" style="gap:0.4rem;">
                            <span class="text-muted font-size-xs font-weight-bold mr-1">Quick:</span>
                            <a href="#" class="btn btn-sm btn-light-primary font-weight-bold preset-btn"
                               data-from="{{ now()->startOfMonth()->format('Y-m-d') }}"
                               data-to="{{ now()->format('Y-m-d') }}">This Month</a>
                            <a href="#" class="btn btn-sm btn-light-primary font-weight-bold preset-btn"
                               data-from="{{ now()->subMonths(6)->format('Y-m-d') }}"
                               data-to="{{ now()->format('Y-m-d') }}">Last 6 Months</a>
                            <a href="#" class="btn btn-sm btn-light-primary font-weight-bold preset-btn"
                               data-from="{{ now()->startOfYear()->format('Y-m-d') }}"
                               data-to="{{ now()->format('Y-m-d') }}">This Year</a>
                            <a href="#" class="btn btn-sm btn-light-primary font-weight-bold preset-btn"
                               data-from="{{ now()->subYear()->format('Y-m-d') }}"
                               data-to="{{ now()->format('Y-m-d') }}">Last 1 Year</a>
                        </div>

                    </form>
                </div>
            </div>

            @if(request()->has('levels'))

            {{-- ── SUMMARY LEVEL CARDS ─────────────────────────── --}}
            @php
                $levelColors = [
                    1 => ['bg'=>'#4f46e5','icon'=>'fa-star'],
                    2 => ['bg'=>'#0891b2','icon'=>'fa-user-plus'],
                    3 => ['bg'=>'#059669','icon'=>'fa-users'],
                    4 => ['bg'=>'#d97706','icon'=>'fa-network-wired'],
                    5 => ['bg'=>'#db2777','icon'=>'fa-diagram-project'],
                    6 => ['bg'=>'#7c3aed','icon'=>'fa-layer-group'],
                    7 => ['bg'=>'#dc2626','icon'=>'fa-crown'],
                ];
            @endphp

            <div class="row mb-5">
                {{-- Total card --}}
                <div class="col-12 col-md-3 mb-3">
                    <div class="card card-custom h-100" style="background:linear-gradient(135deg,#1e1b4b,#2e1065);color:#fff;">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.6rem;opacity:.8;margin-bottom:.4rem;"><i class="fas fa-users"></i></div>
                            <div style="font-size:2rem;font-weight:800;line-height:1;">{{ $totalCount }}</div>
                            <div style="font-size:.78rem;opacity:.8;margin-top:.3rem;">Total Joined</div>
                            <div style="font-size:.7rem;opacity:.55;margin-top:.2rem;">
                                {{ request('from') }} → {{ request('to') }}
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($levelCounts as $level => $count)
                @php $c = $levelColors[$level] ?? ['bg'=>'#64748b','icon'=>'fa-circle']; @endphp
                <div class="col-6 col-md mb-3">
                    <div class="card card-custom h-100" style="background:{{ $c['bg'] }};color:#fff;">
                        <div class="card-body py-4 text-center">
                            <div style="font-size:1.3rem;opacity:.75;margin-bottom:.3rem;"><i class="fas {{ $c['icon'] }}"></i></div>
                            <div style="font-size:1.75rem;font-weight:800;line-height:1;">{{ $count }}</div>
                            <div style="font-size:.75rem;opacity:.85;margin-top:.3rem;">Level {{ $level }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── RESULTS TABLE ────────────────────────────────── --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4 d-flex align-items-center justify-content-between flex-wrap">
                    <h3 class="card-title font-weight-bolder text-dark mb-0">
                        Members Joined
                        <span class="text-muted font-weight-normal font-size-sm ml-2">
                            ({{ request('from') }} to {{ request('to') }})
                        </span>
                    </h3>
                    <span class="badge badge-light-primary font-size-sm">
                        Viewing: <strong>{{ $targetUser->name }}</strong>
                        @if($targetUser->id !== auth()->id())
                            ({{ $targetUser->username }})
                        @endif
                    </span>
                </div>
                <div class="card-body pt-0">
                    @if($users->isEmpty())
                        <div class="text-center py-10 text-muted">
                            <i class="fas fa-users-slash fa-3x mb-3 d-block" style="opacity:.25;"></i>
                            No members joined in the selected date range and levels.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-head-custom table-vertical-center" id="perfTable">
                                <thead>
                                    <tr>
                                        <th class="pl-0" style="width:40px">#</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Sponsor</th>
                                        <th>Level</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $i => $member)
                                    @php $c = $levelColors[$member->level] ?? ['bg'=>'#64748b','icon'=>'fa-circle']; @endphp
                                    <tr>
                                        <td class="pl-0 text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <span class="font-weight-bold text-dark">{{ $member->name }}</span>
                                        </td>
                                        <td class="text-muted">{{ $member->username }}</td>
                                        <td>
                                            @if($member->sponsor_name)
                                                <span class="font-weight-bold">{{ $member->sponsor_name }}</span>
                                                <span class="text-muted font-size-xs ml-1">({{ $member->sponsor_username }})</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge font-weight-bold"
                                                style="background:{{ $c['bg'] }}20;color:{{ $c['bg'] }};border:1px solid {{ $c['bg'] }}40;padding:.35rem .7rem;border-radius:50px;">
                                                Level {{ $member->level }}
                                            </span>
                                        </td>
                                        <td class="text-muted font-size-sm">
                                            {{ \Carbon\Carbon::parse($member->created_at)->format('d M Y') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Level breakdown footer --}}
                        <div class="mt-4 pt-4 border-top d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            <span class="font-weight-bold text-muted font-size-sm mr-2">Breakdown:</span>
                            @foreach($levelCounts as $level => $cnt)
                            @php $c = $levelColors[$level] ?? ['bg'=>'#64748b','icon'=>'fa-circle']; @endphp
                            <span class="badge"
                                style="background:{{ $c['bg'] }}18;color:{{ $c['bg'] }};border:1px solid {{ $c['bg'] }}35;padding:.3rem .7rem;border-radius:50px;font-size:.78rem;">
                                Level {{ $level }}: <strong>{{ $cnt }}</strong>
                            </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @endif {{-- end @if has levels --}}

        </div>
    </div>
</div>

<style>
.level-chip {
    display: inline-flex; align-items: center; padding: .32rem .85rem;
    border-radius: 50px; border: 1.5px solid #cbd5e1;
    background: #f8fafc; color: #475569; font-size: .78rem; font-weight: 700;
    cursor: pointer; user-select: none; transition: all .15s ease;
}
.level-chip.active {
    background: #4f46e5; border-color: #4f46e5; color: #fff;
}
.level-chip:hover:not(.active) { border-color: #4f46e5; color: #4f46e5; }
.preset-btn { border-radius: 50px !important; font-size: .72rem !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Level chip toggle
    document.querySelectorAll('.level-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var cb = chip.querySelector('.level-cb');
            cb.checked = !cb.checked;
            chip.classList.toggle('active', cb.checked);
        });
    });

    // Quick preset buttons
    var fromInput = document.querySelector('input[name="from"]');
    var toInput   = document.querySelector('input[name="to"]');
    document.querySelectorAll('.preset-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            fromInput.value = btn.dataset.from;
            toInput.value   = btn.dataset.to;
            // Select all levels on preset click
            document.querySelectorAll('.level-cb').forEach(function (cb) {
                cb.checked = true;
                cb.closest('.level-chip').classList.add('active');
            });
        });
    });

    // Select2 for user dropdown (if available)
    if (typeof $ !== 'undefined' && $.fn.select2 && document.getElementById('userSelect')) {
        $('#userSelect').select2({ placeholder: '— My Own Performance —', allowClear: true });
    }
});
</script>
@endsection
