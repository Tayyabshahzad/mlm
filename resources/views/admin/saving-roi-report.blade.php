@extends('demo.layout.app')
@section('title', 'Saving ROI Report')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline flex-wrap mr-5">
                <h5 class="text-dark font-weight-bold my-1 mr-5">Saving ROI Report</h5>
                <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm p-0 my-2">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Saving Accounts</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">ROI Report</a></li>
                </ul>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-warning font-weight-bold btn-sm"
                        data-toggle="modal" data-target="#assignRoiModal">
                    <i class="la la-plus-circle mr-1"></i> Assign Manual ROI to User
                </button>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            {{-- Filters --}}
            <div class="card card-custom gutter-b">
                <div class="card-body py-4">
                    <form method="GET" action="{{ route('admin.saving.roi.report') }}" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="font-weight-bold font-size-sm">From Date</label>
                                <input type="date" name="start_date" class="form-control form-control-sm form-control-solid"
                                    value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold font-size-sm">To Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm form-control-solid"
                                    value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="font-weight-bold font-size-sm">Filter by User</label>
                                <select name="user_id" class="form-control form-control-sm form-control-solid">
                                    <option value="">— All Users —</option>
                                    @foreach($savingUsers as $u)
                                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->username }} ({{ $u->name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end gap-2 mt-3 mt-md-0">
                                <button type="submit" class="btn btn-primary btn-sm font-weight-bold mr-2">
                                    <i class="la la-search"></i> Apply
                                </button>
                                <a href="{{ route('admin.saving.roi.report') }}" class="btn btn-light btn-sm mr-2">Reset</a>
                                <a href="{{ route('admin.saving.roi.report.export', request()->query()) }}"
                                   class="btn btn-success btn-sm font-weight-bold">
                                    <i class="la la-download"></i> Download Excel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Manual ROI Run --}}
            <div class="card card-custom gutter-b border-warning">
                <div class="card-header border-0 py-4 bg-light-warning">
                    <h3 class="card-title font-weight-bolder text-warning">
                        <i class="la la-play-circle text-warning mr-2" style="font-size:1.4rem;"></i>
                        Manual ROI Run
                    </h3>
                </div>
                <div class="card-body">

                    {{-- Flash results --}}
                    @if(session('roi_success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <strong>{{ session('roi_success') }}</strong>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif
                    @if(session('roi_warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            <strong>{{ session('roi_warning') }}</strong>
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    {{-- Detailed results table --}}
                    @if(session('roi_results'))
                        @php $res = session('roi_results'); @endphp
                        @if(count($res['processed']))
                        <div class="mb-3">
                            <strong class="text-success">Processed ({{ count($res['processed']) }}):</strong>
                            <ul class="mb-0 mt-1">
                                @foreach($res['processed'] as $r)
                                    <li>{{ $r['username'] }} — <span class="text-success font-weight-bold">${{ number_format($r['amount'], 2) }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        @if(count($res['skipped']))
                        <div class="mb-3">
                            <strong class="text-warning">Skipped ({{ count($res['skipped']) }}):</strong>
                            <ul class="mb-0 mt-1">
                                @foreach($res['skipped'] as $r)
                                    <li>{{ $r['username'] }} — <span class="text-muted">{{ $r['reason'] }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    @endif

                    <form method="POST" action="{{ route('admin.saving.roi.manual.run') }}" id="manualRoiForm">
                        @csrf

                        {{-- Date selector --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="font-weight-bold font-size-sm">ROI Date <span class="text-danger">*</span></label>
                                <input type="date" name="roi_date" id="roiDate"
                                    class="form-control form-control-solid @error('roi_date') is-invalid @enderror"
                                    value="{{ old('roi_date', now()->format('Y-m-d')) }}"
                                    max="{{ now()->format('Y-m-d') }}"
                                    required>
                                @error('roi_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Cannot select a future date. Users already paid on the selected date will be skipped.</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <button type="button" id="selectAll" class="btn btn-xs btn-light-primary font-weight-bold mr-2">Select All</button>
                                <button type="button" id="deselectAll" class="btn btn-xs btn-light font-weight-bold">Deselect All</button>
                            </div>
                            <span class="text-muted font-size-sm" id="selectedCount">0 selected</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-head-custom table-vertical-center">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">
                                            <label class="checkbox checkbox-single">
                                                <input type="checkbox" id="checkAll">
                                                <span></span>
                                            </label>
                                        </th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Last Saving ROI Paid</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allSavingUsers as $u)
                                    @php
                                        $paidToday = $u->last_saving_roi_payment_date &&
                                                     \Carbon\Carbon::parse($u->last_saving_roi_payment_date)->isToday();
                                    @endphp
                                    <tr>
                                        <td>
                                            <label class="checkbox checkbox-single">
                                                <input type="checkbox" name="user_ids[]" value="{{ $u->id }}" class="user-checkbox">
                                                <span></span>
                                            </label>
                                        </td>
                                        <td class="font-weight-bold">{{ $u->username }}</td>
                                        <td>{{ $u->name }}</td>
                                        <td>
                                            @if($u->last_saving_roi_payment_date)
                                                {{ \Carbon\Carbon::parse($u->last_saving_roi_payment_date)->format('d M Y') }}
                                            @else
                                                <span class="text-muted">Never</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($paidToday)
                                                <span class="badge badge-success">Paid Today</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($allSavingUsers->isEmpty())
                            <p class="text-muted">No eligible saving users found.</p>
                        @else
                        <div class="mt-4">
                            <button type="submit" class="btn btn-warning font-weight-bold" id="runBtn" disabled>
                                <i class="la la-play"></i> Run ROI for Selected Users
                            </button>
                            <span class="text-muted font-size-sm ml-3">Users already paid on the selected date will be skipped automatically.</span>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row gutter-b">
                <div class="col-md-4">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem;font-weight:700;">${{ number_format($totalRoi, 2) }}</div>
                            <div class="font-size-sm mt-1">Total ROI in Period</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem;font-weight:700;">{{ $userTotals->count() }}</div>
                            <div class="font-size-sm mt-1">Users Received ROI</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-info text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem;font-weight:700;">{{ $totalEntries }}</div>
                            <div class="font-size-sm mt-1">Total ROI Payments</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Daily ROI Distribution</h3>
                </div>
                <div class="card-body pt-0">
                    <div id="savingRoiChart" style="min-height:300px;"></div>
                </div>
            </div>

            {{-- Per-User Summary --}}
            @if($userTotals->count() > 0)
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Per-User Summary</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Full Name</th>
                                    <th>Days Paid</th>
                                    <th>Total ROI</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userTotals as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="font-weight-bold">{{ $row->user->username ?? '—' }}</td>
                                    <td>{{ $row->user->name ?? '—' }}</td>
                                    <td>{{ $row->days_paid }}</td>
                                    <td class="font-weight-bold text-success">${{ number_format($row->total, 2) }}</td>
                                    <td>
                                        <a href="{{ route('admin.saving.roi.report', array_merge(request()->query(), ['user_id' => $row->user_id])) }}"
                                           class="btn btn-xs btn-light-primary font-weight-bold">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            {{-- Detailed Entries --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Detailed ROI Entries</h3>
                </div>
                <div class="card-body pt-0">
                    @if($entries->isEmpty())
                        <p class="text-muted">No ROI payments found for the selected period.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $i => $entry)
                                <tr>
                                    <td>{{ ($entries->currentPage() - 1) * $entries->perPage() + $i + 1 }}</td>
                                    <td>
                                        <span class="font-weight-bold">{{ $entry->user->username ?? '—' }}</span>
                                        <span class="text-muted d-block font-size-sm">{{ $entry->user->name ?? '' }}</span>
                                    </td>
                                    <td class="font-weight-bold text-success">${{ number_format($entry->balance, 2) }}</td>
                                    <td class="text-muted">{{ $entry->description ?? 'Daily saving account ROI' }}</td>
                                    <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $entries->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════
     Assign Manual ROI to User — Modal
═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="assignRoiModal" tabindex="-1" role="dialog" aria-labelledby="assignRoiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#FFF3CD;">
                <h5 class="modal-title font-weight-bold text-dark" id="assignRoiModalLabel">
                    <i class="la la-plus-circle text-warning mr-2" style="font-size:1.3rem;"></i>
                    Assign Manual ROI to User
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="la la-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.saving.roi.manual.run') }}" id="assignRoiForm">
                @csrf
                <div class="modal-body">

                    <div class="alert alert-info py-2 mb-4" style="font-size:0.9rem;">
                        <i class="la la-info-circle mr-1"></i>
                        <strong>Duplicate entries are allowed.</strong>
                        If ROI was already assigned for the selected date, a new entry will be added without removing the old one.
                    </div>

                    {{-- ROI Type --}}
                    <div class="form-group">
                        <label class="font-weight-bold font-size-sm">ROI Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 mt-2" style="gap:12px;">
                            <label id="labelSavingRoi"
                                   style="cursor:pointer;flex:1;border:2px solid #FFA800;background:#FFF8E7;border-radius:8px;padding:10px 16px;display:flex;align-items:center;">
                                <input type="radio" name="roi_type" value="saving_roi" id="roiTypeSaving" checked style="margin-right:10px;">
                                <div>
                                    <div class="font-weight-bold text-warning" style="font-size:0.95rem;">Saving Plan ROI</div>
                                    <small class="text-muted">Credits <code>saving_roi</code> wallet</small>
                                </div>
                            </label>
                            <label id="labelStandardRoi"
                                   style="cursor:pointer;flex:1;border:2px solid #dee2e6;background:#fff;border-radius:8px;padding:10px 16px;display:flex;align-items:center;">
                                <input type="radio" name="roi_type" value="roi" id="roiTypeStandard" style="margin-right:10px;">
                                <div>
                                    <div class="font-weight-bold text-primary" style="font-size:0.95rem;">Standard Plan ROI</div>
                                    <small class="text-muted">Credits <code>roi</code> wallet</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- User Select --}}
                    <div class="form-group">
                        <label class="font-weight-bold font-size-sm">
                            Select User <span class="text-danger">*</span>
                            <span id="userTypeBadge" class="badge badge-light-warning ml-2 font-size-xs">Saving account users</span>
                        </label>
                        <input type="text" id="modalUserSearch"
                               class="form-control form-control-solid form-control-sm mb-2"
                               placeholder="&#128269; Type username or name to filter...">
                        <select name="user_ids[]" id="modalUserSelect"
                                class="form-control form-control-solid"
                                required size="7"
                                style="border-radius:4px;">
                            @foreach($allSavingUsers as $u)
                                @php
                                    $uBase    = (float)$u->saving_total_deposited;
                                    $uBaseStr = $uBase > 0 ? '| Base: $' . number_format($uBase, 2) : '| No base';
                                @endphp
                                <option value="{{ $u->id }}"
                                        data-search="{{ strtolower($u->username . ' ' . $u->name) }}"
                                        data-type="saving">
                                    {{ $u->username }} - {{ $u->name }} {{ $uBaseStr }}
                                </option>
                            @endforeach
                            @foreach($standardRoiUsers as $u)
                                @php
                                    $uBase    = max((float)$u->roi_eligible_investment_amount, (float)$u->saving_total_deposited);
                                    $uBaseStr = $uBase > 0 ? '| Base: $' . number_format($uBase, 2) : '| No base';
                                @endphp
                                <option value="{{ $u->id }}"
                                        data-search="{{ strtolower($u->username . ' ' . $u->name) }}"
                                        data-type="standard"
                                        hidden>
                                    {{ $u->username }} - {{ $u->name }} {{ $uBaseStr }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="userSelectHint">Click a row to select. Showing saving account users.</small>
                    </div>

                    <div class="row">
                        {{-- ROI Date --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold font-size-sm">
                                    ROI Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="roi_date" id="modalRoiDate"
                                       class="form-control form-control-solid"
                                       value="{{ now()->format('Y-m-d') }}"
                                       max="{{ now()->format('Y-m-d') }}"
                                       required>
                                <small class="text-muted">Cannot be a future date.</small>
                            </div>
                        </div>

                        {{-- Percentage --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold font-size-sm">
                                    Percentage
                                    <span class="text-muted font-size-xs">(optional)</span>
                                </label>
                                <div class="input-group input-group-solid">
                                    <input type="number" name="custom_rate" id="modalRate"
                                           class="form-control form-control-solid"
                                           step="0.001" min="0.001" max="100"
                                           placeholder="e.g. 0.1">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <small class="text-muted">Applied on deposit base. Leave blank = system default.</small>
                            </div>
                        </div>

                        {{-- Fixed Amount --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold font-size-sm">
                                    Fixed Amount
                                    <span class="text-muted font-size-xs">(optional)</span>
                                </label>
                                <div class="input-group input-group-solid">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="fixed_amount" id="modalFixed"
                                           class="form-control form-control-solid"
                                           step="0.01" min="0.01"
                                           placeholder="e.g. 5.00">
                                </div>
                                <small class="text-muted">If entered, this exact $ is credited (overrides %).</small>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group mb-0">
                        <label class="font-weight-bold font-size-sm">
                            Description
                            <span class="text-muted font-size-xs">(optional)</span>
                        </label>
                        <input type="text" name="description" id="modalDesc"
                               class="form-control form-control-solid"
                               maxlength="300"
                               placeholder="e.g. Manual ROI — March bonus">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light font-weight-bold" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning font-weight-bold" id="assignRoiSubmitBtn">
                        <i class="la la-check mr-1"></i> Assign ROI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('page_js')
<script>
(function () {
    // ── Checkbox logic ────────────────────────────────────────────
    const checkboxes  = document.querySelectorAll('.user-checkbox');
    const checkAll    = document.getElementById('checkAll');
    const selectAll   = document.getElementById('selectAll');
    const deselectAll = document.getElementById('deselectAll');
    const runBtn      = document.getElementById('runBtn');
    const countLabel  = document.getElementById('selectedCount');

    function updateState() {
        const checked = document.querySelectorAll('.user-checkbox:checked').length;
        if (countLabel) countLabel.textContent = checked + ' selected';
        if (runBtn)     runBtn.disabled = checked === 0;
        if (checkAll)   checkAll.indeterminate = checked > 0 && checked < checkboxes.length;
        if (checkAll)   checkAll.checked = checked === checkboxes.length && checkboxes.length > 0;
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateState));

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateState();
        });
    }

    if (selectAll) {
        selectAll.addEventListener('click', function () {
            checkboxes.forEach(cb => cb.checked = true);
            updateState();
        });
    }

    if (deselectAll) {
        deselectAll.addEventListener('click', function () {
            checkboxes.forEach(cb => cb.checked = false);
            updateState();
        });
    }

    // Confirm before submitting
    const form = document.getElementById('manualRoiForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            const count = document.querySelectorAll('.user-checkbox:checked').length;
            const date  = document.getElementById('roiDate')?.value || 'selected date';
            if (!confirm('Run saving ROI for ' + count + ' user(s) on date: ' + date + '?\n\nUsers already paid on that date will be skipped automatically.')) {
                e.preventDefault();
            }
        });
    }

    updateState();
})();

// ── Assign ROI Modal ──────────────────────────────────────────────
(function () {
    const searchInput    = document.getElementById('modalUserSearch');
    const userSelect     = document.getElementById('modalUserSelect');
    const assignForm     = document.getElementById('assignRoiForm');
    const userTypeBadge  = document.getElementById('userTypeBadge');
    const userSelectHint = document.getElementById('userSelectHint');
    const labelSaving    = document.getElementById('labelSavingRoi');
    const labelStandard  = document.getElementById('labelStandardRoi');

    // ── Filter users by ROI type ──────────────────────────────────
    function applyTypeFilter() {
        const selectedType = assignForm
            ? (assignForm.querySelector('input[name="roi_type"]:checked')?.value || 'saving_roi')
            : 'saving_roi';
        const wantDataType = selectedType === 'saving_roi' ? 'saving' : 'standard';

        // Reset search
        if (searchInput) searchInput.value = '';

        Array.from(userSelect.options).forEach(function (opt) {
            const typeMatch = opt.dataset.type === wantDataType;
            opt.hidden   = !typeMatch;
            opt.selected = false;
        });

        // Badge and hint text
        if (selectedType === 'saving_roi') {
            if (userTypeBadge)  { userTypeBadge.textContent = 'Saving account users'; userTypeBadge.className = 'badge badge-light-warning ml-2 font-size-xs'; }
            if (userSelectHint) userSelectHint.textContent = 'Click a row to select. Showing saving account users.';
            if (labelSaving)    { labelSaving.style.borderColor = '#FFA800'; labelSaving.style.background = '#FFF8E7'; }
            if (labelStandard)  { labelStandard.style.borderColor = '#dee2e6'; labelStandard.style.background = '#fff'; }
        } else {
            if (userTypeBadge)  { userTypeBadge.textContent = 'Standard plan users'; userTypeBadge.className = 'badge badge-light-primary ml-2 font-size-xs'; }
            if (userSelectHint) userSelectHint.textContent = 'Click a row to select. Showing standard plan users.';
            if (labelStandard)  { labelStandard.style.borderColor = '#3699FF'; labelStandard.style.background = '#EEF3FF'; }
            if (labelSaving)    { labelSaving.style.borderColor = '#dee2e6'; labelSaving.style.background = '#fff'; }
        }
    }

    // Listen to roi_type radio changes
    if (assignForm) {
        assignForm.querySelectorAll('input[name="roi_type"]').forEach(function (radio) {
            radio.addEventListener('change', applyTypeFilter);
        });
    }

    // Live search filter (respects current type)
    if (searchInput && userSelect) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const selectedType = assignForm
                ? (assignForm.querySelector('input[name="roi_type"]:checked')?.value || 'saving_roi')
                : 'saving_roi';
            const wantDataType = selectedType === 'saving_roi' ? 'saving' : 'standard';

            Array.from(userSelect.options).forEach(function (opt) {
                const typeMatch   = opt.dataset.type === wantDataType;
                const searchMatch = !q || (opt.dataset.search && opt.dataset.search.includes(q));
                opt.hidden = !(typeMatch && searchMatch);
                if (opt.hidden && opt.selected) opt.selected = false;
            });
        });
    }

    // Confirm on submit
    if (assignForm) {
        assignForm.addEventListener('submit', function (e) {
            if (!userSelect || !userSelect.value) {
                e.preventDefault();
                alert('Please select a user from the list.');
                return;
            }
            const selectedText = userSelect.options[userSelect.selectedIndex]
                ? userSelect.options[userSelect.selectedIndex].text.trim()
                : '(unknown)';
            const date    = document.getElementById('modalRoiDate')?.value || '';
            const rate    = document.getElementById('modalRate')?.value;
            const fixed   = document.getElementById('modalFixed')?.value;
            const desc    = document.getElementById('modalDesc')?.value;
            const roiType = assignForm.querySelector('input[name="roi_type"]:checked')?.value || 'saving_roi';
            const typeLabel = roiType === 'saving_roi' ? 'Saving Plan ROI (saving_roi wallet)' : 'Standard Plan ROI (roi wallet)';

            let modeStr = fixed ? 'Fixed: $' + parseFloat(fixed).toFixed(2)
                        : rate  ? rate + '% of deposit base'
                        :         'system default rate';

            let msg = 'Assign ROI to:\n' + selectedText
                    + '\n\nDate: ' + date
                    + '\nType: ' + typeLabel
                    + '\nMode: ' + modeStr;
            if (desc) msg += '\nDescription: ' + desc;
            msg += '\n\n⚠ Duplicate entries ARE allowed — a new record will be created even if ROI already exists for this date.\n\nProceed?';

            if (!confirm(msg)) e.preventDefault();
        });
    }

    // Reset modal on open
    const modalEl = document.getElementById('assignRoiModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function () {
            // Reset radio to saving_roi
            const savingRadio = document.getElementById('roiTypeSaving');
            if (savingRadio) savingRadio.checked = true;

            const rate  = document.getElementById('modalRate');
            const fixed = document.getElementById('modalFixed');
            const desc  = document.getElementById('modalDesc');
            if (rate)  rate.value  = '';
            if (fixed) fixed.value = '';
            if (desc)  desc.value  = '';

            // Apply filter for default type
            applyTypeFilter();
        });
    }

    // Run on page load to set initial state
    applyTypeFilter();
})();

// ── Chart ─────────────────────────────────────────────────────────
(function () {
    const dates  = @json($chartDates);
    const totals = @json($chartTotals);

    if (!dates.length) {
        document.getElementById('savingRoiChart').innerHTML =
            '<p class="text-muted text-center pt-10">No data for selected period.</p>';
        return;
    }

    const options = {
        series: [{ name: 'ROI Paid ($)', data: totals }],
        chart: {
            type: 'bar',
            height: 300,
            fontFamily: "'Poppins', sans-serif",
            background: 'transparent',
            toolbar: { show: true, tools: { download: true, zoom: true, reset: true } },
            animations: { enabled: true, easing: 'easeinout', speed: 600 },
        },
        colors: ['#1BC5BD'],
        plotOptions: {
            bar: { columnWidth: '50%', borderRadius: 5, dataLabels: { position: 'top' } }
        },
        dataLabels: {
            enabled: true,
            formatter: v => '$' + v.toFixed(2),
            offsetY: -18,
            style: { fontSize: '11px', colors: ['#304758'] }
        },
        xaxis: {
            categories: dates,
            labels: { rotate: -45, style: { fontSize: '11px' } }
        },
        yaxis: {
            labels: { formatter: v => '$' + v.toFixed(2) }
        },
        tooltip: {
            y: { formatter: v => '$' + v.toFixed(2) }
        },
        grid: { borderColor: '#f1f1f1' },
    };

    new ApexCharts(document.getElementById('savingRoiChart'), options).render();
})();
</script>
@endsection
@endsection
