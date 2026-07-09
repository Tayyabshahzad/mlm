@extends('demo.layout.app')
@section('title', 'Adjust Instalment Plan')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-baseline mr-5">
                <h5 class="text-dark font-weight-bold my-1 mr-5">Adjust Instalment Plan</h5>
                <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.saving.index') }}" class="text-muted">Saving Accounts</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Adjust Plan</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

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

            <div class="row">

                {{-- Step 1: User Search --}}
                <div class="col-xl-4">
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 py-4">
                            <h3 class="card-title font-weight-bolder">Step 1 — Select User</h3>
                        </div>
                        <div class="card-body pt-0">
                            <form method="GET" action="{{ route('admin.saving.adjust-plan') }}">
                                <div class="form-group">
                                    <label class="font-weight-bold">Username</label>
                                    <input type="text" name="username" class="form-control form-control-solid"
                                        placeholder="Enter exact username…"
                                        value="{{ request('username') }}" autofocus>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search mr-1"></i> Load User
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Info box --}}
                    <div class="card card-custom gutter-b bg-light-info">
                        <div class="card-body py-4 px-5">
                            <h6 class="font-weight-bold text-info mb-3"><i class="fas fa-info-circle mr-1"></i> How it works</h6>
                            <ul class="font-size-sm text-dark mb-0" style="padding-left:1.1rem;">
                                <li>Both instalments <strong>#1 and #2</strong> must be <strong>confirmed</strong>.</li>
                                <li>Enter the <strong>additional amount</strong> to add to each paid instalment.</li>
                                <li>All <strong>future pending instalments</strong> (3–25) will be regenerated at the new rate.</li>
                                <li><strong>Saving wallet</strong> is credited with the combined additional amount.</li>
                                <li><strong>Upline commissions</strong> are distributed automatically.</li>
                                <li>The operation runs in a <strong>single DB transaction</strong>.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Step 2: Adjustment Form --}}
                <div class="col-xl-8">
                    @if(!$selectedUser && !request('username'))
                        <div class="card card-custom gutter-b">
                            <div class="card-body py-10 text-center text-muted">
                                <i class="fas fa-user-circle fa-3x mb-3 text-muted"></i>
                                <p>Enter a username on the left to load the user's instalment plan.</p>
                            </div>
                        </div>

                    @elseif(!$selectedUser)
                        <div class="alert alert-warning">
                            No saving-plan user found with username <strong>{{ request('username') }}</strong>.
                        </div>

                    @else
                        {{-- Current plan overview --}}
                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-4">
                                <h3 class="card-title font-weight-bolder">
                                    Current Plan — {{ $selectedUser->name }}
                                    <small class="text-muted font-size-sm ml-2">(@{{ $selectedUser->username }})</small>
                                </h3>
                                <div class="card-toolbar">
                                    <a href="{{ route('admin.saving.show', $selectedUser) }}" class="btn btn-sm btn-light-primary">
                                        <i class="fas fa-external-link-alt mr-1"></i> View Profile
                                    </a>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row mb-5">
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-light-success text-center">
                                            <div class="font-size-h4 font-weight-bold text-success">${{ number_format($selectedUser->saving_total_deposited, 2) }}</div>
                                            <div class="text-muted font-size-sm">Total Deposited</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-light-primary text-center">
                                            <div class="font-size-h4 font-weight-bold text-primary">${{ number_format($selectedUser->roi_eligible_investment_amount, 2) }}</div>
                                            <div class="text-muted font-size-sm">ROI Eligible Amount</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-light-warning text-center">
                                            <div class="font-size-h4 font-weight-bold text-warning">{{ $confirmedCount }}</div>
                                            <div class="text-muted font-size-sm">Confirmed Instalments</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Instalment table --}}
                                <div class="table-responsive" style="max-height:260px;overflow-y:auto;">
                                    <table class="table table-sm table-head-custom table-vertical-center">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Confirmed At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($instalments as $inst)
                                            <tr class="{{ in_array($inst->instalment_number, [1,2]) && $inst->status === 'confirmed' ? 'bg-light-success' : '' }}">
                                                <td class="font-weight-bold">#{{ $inst->instalment_number }}</td>
                                                <td>${{ number_format($inst->amount, 2) }}</td>
                                                <td>
                                                    @if($inst->status === 'confirmed')
                                                        <span class="badge badge-success">Confirmed</span>
                                                    @elseif($inst->status === 'pending')
                                                        <span class="badge badge-warning">Pending</span>
                                                    @elseif($inst->status === 'missed')
                                                        <span class="badge badge-danger">Missed</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ ucfirst($inst->status) }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-muted font-size-sm">
                                                    {{ $inst->confirmed_at ? \Carbon\Carbon::parse($inst->confirmed_at)->format('d M Y') : '—' }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Adjustment form --}}
                        @php
                            $inst1 = $instalments->firstWhere('instalment_number', 1);
                            $inst2 = $instalments->firstWhere('instalment_number', 2);
                            $canAdjust = $inst1?->status === 'confirmed' && $inst2?->status === 'confirmed';
                        @endphp

                        @if(!$canAdjust)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Instalments #1 and #2 must both be <strong>confirmed</strong> before an adjustment can be made.
                                <br>
                                Status — Inst #1: <strong>{{ ucfirst($inst1?->status ?? 'not found') }}</strong>,
                                Inst #2: <strong>{{ ucfirst($inst2?->status ?? 'not found') }}</strong>
                            </div>
                        @else
                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-4">
                                <h3 class="card-title font-weight-bolder text-primary">
                                    <i class="fas fa-edit text-primary mr-2"></i> Step 2 — Enter Adjustment
                                </h3>
                            </div>
                            <div class="card-body pt-0">
                                <form method="POST" action="{{ route('admin.saving.adjust-plan.apply') }}" id="adjustForm">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">
                                                    Add to Instalment #1
                                                    <small class="text-muted">(current: ${{ number_format($inst1->amount, 2) }})</small>
                                                </label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text">+$</span></div>
                                                    <input type="number" name="add_to_inst1" id="addInst1"
                                                        class="form-control form-control-solid @error('add_to_inst1') is-invalid @enderror"
                                                        step="0.01" min="0" value="{{ old('add_to_inst1', 0) }}"
                                                        placeholder="0.00">
                                                </div>
                                                @error('add_to_inst1')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                <small class="text-muted">New inst #1 amount: <span id="newInst1">${{ number_format($inst1->amount, 2) }}</span></small>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">
                                                    Add to Instalment #2
                                                    <small class="text-muted">(current: ${{ number_format($inst2->amount, 2) }})</small>
                                                </label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text">+$</span></div>
                                                    <input type="number" name="add_to_inst2" id="addInst2"
                                                        class="form-control form-control-solid @error('add_to_inst2') is-invalid @enderror"
                                                        step="0.01" min="0" value="{{ old('add_to_inst2', 0) }}"
                                                        placeholder="0.00">
                                                </div>
                                                @error('add_to_inst2')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                                <small class="text-muted">New inst #2 amount: <span id="newInst2">${{ number_format($inst2->amount, 2) }}</span></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold">Admin Notes <span class="text-muted font-weight-normal">(optional)</span></label>
                                        <input type="text" name="admin_notes" class="form-control form-control-solid"
                                            placeholder="Reason for adjustment…"
                                            value="{{ old('admin_notes') }}" maxlength="500">
                                    </div>

                                    {{-- Live preview --}}
                                    <div class="alert alert-light-primary border border-primary mt-4" id="previewBox">
                                        <h6 class="font-weight-bold mb-3"><i class="fas fa-calculator mr-1"></i> Preview</h6>
                                        <div class="row text-center">
                                            <div class="col">
                                                <div class="font-size-sm text-muted">New Inst #1</div>
                                                <div class="font-size-h5 font-weight-bold text-primary" id="prevInst1">
                                                    ${{ number_format($inst1->amount, 2) }}
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="font-size-sm text-muted">New Inst #2</div>
                                                <div class="font-size-h5 font-weight-bold text-primary" id="prevInst2">
                                                    ${{ number_format($inst2->amount, 2) }}
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="font-size-sm text-muted">New Monthly Rate (3–25)</div>
                                                <div class="font-size-h5 font-weight-bold text-success" id="prevMonthly">
                                                    ${{ number_format($inst2->amount, 2) }}
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="font-size-sm text-muted">Total Additional Credit</div>
                                                <div class="font-size-h5 font-weight-bold text-warning" id="prevTotal">
                                                    $0.00
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="font-size-sm text-muted">New Total Deposited</div>
                                                <div class="font-size-h5 font-weight-bold text-dark" id="prevNewDeposited">
                                                    ${{ number_format($selectedUser->saving_total_deposited, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-5">
                                        <a href="{{ route('admin.saving.adjust-plan') }}" class="btn btn-light mr-3">
                                            Cancel
                                        </a>
                                        <button type="submit" class="btn btn-danger font-weight-bold" id="submitBtn">
                                            <i class="fas fa-check mr-1"></i> Apply Adjustment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script>
(function () {
    var inst1Base = {{ $inst1?->amount ?? 0 }};
    var inst2Base = {{ $inst2?->amount ?? 0 }};
    var currentDeposited = {{ $selectedUser?->saving_total_deposited ?? 0 }};

    function fmt(n) { return '$' + n.toFixed(2); }

    function update() {
        var add1 = parseFloat(document.getElementById('addInst1')?.value) || 0;
        var add2 = parseFloat(document.getElementById('addInst2')?.value) || 0;

        var newInst1   = inst1Base + add1;
        var newInst2   = inst2Base + add2;
        var totalExtra = add1 + add2;

        if (document.getElementById('prevInst1'))   document.getElementById('prevInst1').textContent   = fmt(newInst1);
        if (document.getElementById('prevInst2'))   document.getElementById('prevInst2').textContent   = fmt(newInst2);
        if (document.getElementById('prevMonthly')) document.getElementById('prevMonthly').textContent = fmt(newInst2);
        if (document.getElementById('prevTotal'))   document.getElementById('prevTotal').textContent   = fmt(totalExtra);
        if (document.getElementById('prevNewDeposited')) {
            document.getElementById('prevNewDeposited').textContent = fmt(currentDeposited + totalExtra);
        }
        if (document.getElementById('newInst1')) document.getElementById('newInst1').textContent = fmt(newInst1);
        if (document.getElementById('newInst2')) document.getElementById('newInst2').textContent = fmt(newInst2);
    }

    document.getElementById('addInst1')?.addEventListener('input', update);
    document.getElementById('addInst2')?.addEventListener('input', update);

    // Confirm before submit
    document.getElementById('adjustForm')?.addEventListener('submit', function (e) {
        var add1 = parseFloat(document.getElementById('addInst1')?.value) || 0;
        var add2 = parseFloat(document.getElementById('addInst2')?.value) || 0;
        if (add1 <= 0 && add2 <= 0) {
            e.preventDefault();
            alert('Please enter at least one adjustment amount greater than zero.');
            return;
        }
        var msg = 'Are you sure you want to apply this adjustment?\n\n' +
            'Add to Inst #1: $' + add1.toFixed(2) + '\n' +
            'Add to Inst #2: $' + add2.toFixed(2) + '\n' +
            'Total additional credit: $' + (add1 + add2).toFixed(2) + '\n\n' +
            'This operation CANNOT be undone.';
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });
})();
</script>
@endsection
