@extends('demo.layout.app')
@section('title', 'Saving Account — ' . $savingUser->name)

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">{{ $savingUser->name }} — Saving Plan</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.saving.index') }}" class="text-muted">Saving Accounts</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">{{ $savingUser->username }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- User Profile + Summary --}}
            <div class="row mb-6">
                <div class="col-md-4">
                    <div class="card card-custom">
                        <div class="card-body text-center py-8">
                            <div class="symbol symbol-80 symbol-light-success mb-4 mx-auto">
                                @if($savingUser->getFirstMedia('profile_photos'))
                                    <img src="{{ $savingUser->getFirstMedia('profile_photos')->getUrl() }}"
                                         class="symbol-label" style="object-fit:cover;width:80px;height:80px;border-radius:50%;">
                                @else
                                    <span class="symbol-label font-size-h3 font-weight-bold" style="font-size:2rem;">
                                        {{ strtoupper(substr($savingUser->name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                            <h4 class="font-weight-bolder mb-1"> {{ $savingUser->name }} </h4>
                            <div class="text-muted mb-2">@ {{ $savingUser->username }}</div>
                            <div class="text-muted mb-1">{{ $savingUser->email }}</div>
                            <div class="text-muted mb-3">{{ $savingUser->phone_number }}</div>

                            {{-- Saving Plan Parent (independent from standard plan sponsor) --}}
                            @php
                                $savingParent = $savingUser->savingSponsor->first();
                            @endphp
                            <div class="mb-4 p-3" style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; font-size:0.82rem;">
                                <div class="text-muted font-size-xs font-weight-bold text-uppercase mb-1">Saving Plan Parent</div>
                                @if($savingParent)
                                    <span class="font-weight-bold text-dark">{{ $savingParent->name }}</span>
                                    <span class="text-muted ml-1">@ {{ $savingParent->username }}</span>
                                @else
                                    <span class="text-muted">— (root / no parent)</span>
                                @endif
                            </div>

                            @php
                                $isEnrolled    = $savingUser->saving_enrolled && $savingUser->account_type !== 'saving';
                                $isFullyActive = $isEnrolled
                                    ? $savingUser->saving_enrollment_activated
                                    : ($savingUser->saving_registration_completed && $savingUser->can_login);

                                // inst #1 fully confirmed = ROI + commissions are eligible
                                $inst1Confirmed = $instalments->where('instalment_number', 1)->where('status', 'confirmed')->isNotEmpty();

                                // Signup net credit (what they already paid toward saving at registration)
                                $regNet = max(0, (float)($savingUser->saving_initial_payment ?? 0) - (float)($savingUser->saving_initial_fee ?? 0));
                            @endphp

                            <div>
                                @if($isEnrolled)
                                    <span class="badge badge-light-primary px-4 py-2 d-block mb-1">Enrolled Member</span>
                                @endif

                                @if($isFullyActive)
                                    <span class="badge badge-success px-4 py-2">Savings Program Active</span>
                                @elseif($savingUser->saving_registration_completed)
                                    <span class="badge badge-info px-4 py-2">Registered — Activation Pending</span>
                                @else
                                    <span class="badge badge-warning px-4 py-2">Pending Activation</span>
                                @endif
                            </div>
                            <div class="mt-3 text-muted font-size-sm">
                                Plan start: {{ $savingUser->saving_plan_start_date ? \Carbon\Carbon::parse($savingUser->saving_plan_start_date)->format('d M Y') : '—' }}
                            </div>

                            @if(!$inst1Confirmed && ($savingUser->saving_initial_payment ?? 0) > 0)
                                @php
                                    $setting      = $setting ?? \App\Models\Setting::first();
                                    $inst1Amount  = (float)($setting->saving_min_deposit ?? 19);
                                    $inst1Still   = max(0, $inst1Amount - $regNet);
                                @endphp
                                <div class="mt-3 px-3 py-2" style="background:#fef3c7; border:1px solid #f59e0b; border-radius:8px; font-size:0.8rem; color:#92400e;">
                                    <strong>First instalment incomplete.</strong>
                                    Paid at signup: ${{ number_format($regNet, 2) }} of ${{ number_format($inst1Amount, 2) }}.
                                    Still owed: <strong>${{ number_format($inst1Still, 2) }}</strong>.<br>
                                    ROI and commissions will <strong>not</strong> start until the full ${{ number_format($inst1Amount, 2) }} first instalment is confirmed.
                                </div>
                            @endif

                            @if(!$isFullyActive)
                                <div class="mt-4">
                                    @php
                                        $confirmMsg = $inst1Confirmed
                                            ? 'Activate account for ' . addslashes($savingUser->name) . '? First instalment is confirmed — ROI and commissions will start immediately.'
                                            : 'Activate login access for ' . addslashes($savingUser->name) . '? NOTE: ROI and commissions will NOT start until the first instalment ($' . number_format($inst1Amount ?? 19, 2) . ') is fully confirmed.';
                                    @endphp
                                    <form method="POST" action="{{ route('admin.saving.activate', $savingUser) }}"
                                          onsubmit="return confirm('{{ $confirmMsg }}')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm font-weight-bold px-5 {{ $inst1Confirmed ? 'btn-success' : 'btn-warning' }}">
                                            <i class="fas fa-check mr-1"></i>
                                            {{ $isEnrolled ? 'Activate Savings Enrollment' : 'Activate Account' }}
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-6 mb-4">
                            <div class="card card-custom bg-primary text-white">
                                <div class="card-body py-5 text-center">
                                    @php
                                        $regNet = max(0, (float)($savingUser->saving_initial_payment ?? 0) - (float)($savingUser->saving_initial_fee ?? 0));
                                        $displayPaid = $paid_amount > 0 ? $paid_amount : $regNet;
                                    @endphp
                                    <div style="font-size:1.5rem; font-weight:700;">${{ number_format($displayPaid, 2) }}</div>
                                    <div class="font-size-sm mt-1">
                                        {{ $paid_amount > 0 ? 'Total Confirmed Deposited' : 'Paid at Registration (net)' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-4">
                            <div class="card card-custom bg-warning text-white">
                                <div class="card-body py-5 text-center">
                                    <div style="font-size:1.5rem; font-weight:700;">${{ number_format($remaining, 2) }}</div>
                                    <div class="font-size-sm mt-1">Remaining</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card card-custom bg-success text-white">
                                <div class="card-body py-5 text-center">
                                    <div style="font-size:1.5rem; font-weight:700;">{{ $paid_count }} / {{ $total_count }}</div>
                                    <div class="font-size-sm mt-1">Instalments Confirmed</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card card-custom bg-info text-white">
                                <div class="card-body py-5 text-center">
                                    @if($next_due)
                                        <div style="font-size:1rem; font-weight:700;">
                                            #{{ $next_due->instalment_number }}<br>
                                            {{ $next_due->due_date->format('d M Y') }}
                                        </div>
                                        <div class="font-size-sm mt-1">Next Due</div>
                                    @else
                                        <div style="font-size:1rem;">{{ $plan_complete ? 'Plan Complete!' : '—' }}</div>
                                        <div class="font-size-sm mt-1">Next Due</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Registration Payment Breakdown --}}
            @php
                $regTotalPaid  = (float) ($savingUser->saving_initial_payment ?? 0);
                $regFee        = (float) ($savingUser->saving_initial_fee ?? 0);
                $regNetCredit  = max(0, $regTotalPaid - $regFee);
                // Enrolled standard users: saving proof is in 'saving_enrollment_proof' (separate from standard plan).
                // Dedicated saving users: proof is in 'user_amount_source' (their only proof).
                $proof = ($savingUser->saving_enrolled && $savingUser->account_type !== 'saving')
                    ? $savingUser->getFirstMedia('saving_enrollment_proof')
                    : $savingUser->getFirstMedia('user_amount_source');
            @endphp
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Registration Payment</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between border-bottom py-3">
                                <span class="text-muted">Total Paid at Registration</span>
                                <span class="font-weight-bold text-primary">${{ number_format($regTotalPaid, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-3">
                                <span class="text-muted">Registration Fee (deducted)</span>
                                <span class="font-weight-bold text-danger">− ${{ number_format($regFee, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-3">
                                <span class="text-muted">Net Credited Toward Saving</span>
                                <span class="font-weight-bold text-success">${{ number_format($regNetCredit, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-3">
                                <span class="text-muted">Instalment #1 Full Amount</span>
                                @php $inst1 = $instalments->where('instalment_number', 1)->first(); @endphp
                                <span class="font-weight-bold">${{ $inst1 ? number_format($inst1->amount, 2) : '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-3">
                                <span class="text-muted">Still Owed for Instalment #1</span>
                                @php $stillOwed = $inst1 ? max(0, $inst1->amount - $regNetCredit) : 0; @endphp
                                <span class="font-weight-bold {{ $stillOwed > 0 ? 'text-warning' : 'text-success' }}">
                                    @if($stillOwed > 0)
                                        ${{ number_format($stillOwed, 2) }}
                                    @else
                                        <span class="badge badge-success">Fully Covered</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between border-bottom py-3">
                                <span class="text-muted">Payment Method</span>
                                <span class="font-weight-bold">{{ ucfirst($savingUser->payment_method ?? '—') }}</span>
                            </div>
                            <div class="d-flex justify-content-between border-bottom py-3">
                                <span class="text-muted">Transaction ID</span>
                                <span class="font-weight-bold font-size-sm">{{ $savingUser->transaction_id ?? '—' }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-3">
                                <span class="text-muted">Instalment #1 Status</span>
                                @if($inst1)
                                    @switch($inst1->status)
                                        @case('confirmed') <span class="badge badge-success">Confirmed</span> @break
                                        @case('submitted') <span class="badge badge-warning">Awaiting Confirmation</span> @break
                                        @default          <span class="badge badge-secondary">Pending</span>
                                    @endswitch
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            @if($proof)
                                <p class="text-muted mb-2 font-size-sm">Payment Proof</p>
                                <a href="{{ $proof->getUrl() }}" target="_blank">
                                    <img src="{{ $proof->getUrl() }}" class="img-thumbnail" style="max-width:100%;max-height:200px;object-fit:contain;">
                                </a>
                            @else
                                <div class="text-muted mt-4">No payment proof uploaded.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Instalment List with Confirm / Reject Actions --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5 d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bolder text-dark">Instalment Schedule</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        @php $hasOpts = $savingUser->adb_option || $savingUser->fisp_option; @endphp
                        <table class="table table-hover table-head-custom table-vertical-center" style="font-size:.88rem;">
                            <thead>
                                <tr>
                                    <th style="width:36px;">#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Submitted</th>
                                    <th>Txn ID</th>
                                    <th>Status</th>
                                    <th>Submitted On</th>
                                    <th>Proof</th>
                                    <th>Deposited</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($instalments as $inst)
                                @php
                                    $adbC   = $savingUser->adb_option  ? round($inst->amount * 0.003, 2) : 0;
                                    $fispC  = $savingUser->fisp_option ? round($inst->amount * 0.004, 2) : 0;
                                    $totalP = $inst->amount + $adbC + $fispC;
                                @endphp
                                <tr class="{{ $inst->status === 'submitted' ? 'table-warning' : '' }}">
                                    <td><strong>{{ $inst->instalment_number }}</strong></td>
                                    <td class="text-nowrap">{{ $inst->due_date->format('d M Y') }}</td>

                                    {{-- Amount cell: base + ADB/FISP breakdown inline --}}
                                    <td>
                                        <strong>${{ number_format($totalP, 2) }}</strong>
                                        @if($hasOpts)
                                        <div style="font-size:.75rem; line-height:1.4; margin-top:2px; color:#6b7280;">
                                            <span>Base: ${{ number_format($inst->amount, 2) }}</span>
                                            @if($adbC > 0)<span class="ml-1 text-danger">+ADB ${{ number_format($adbC, 2) }}</span>@endif
                                            @if($fispC > 0)<span class="ml-1 text-warning">+FISP ${{ number_format($fispC, 2) }}</span>@endif
                                        </div>
                                        @endif
                                    </td>

                                    <td>
                                        @if($inst->submitted_amount)
                                            <span class="{{ $inst->submitted_amount >= $totalP ? 'text-success' : 'text-danger' }} font-weight-bold">
                                                ${{ number_format($inst->submitted_amount, 2) }}
                                            </span>
                                            @if($inst->submitted_amount < $totalP)
                                                <div style="font-size:.72rem;" class="text-danger">Short by ${{ number_format($totalP - $inst->submitted_amount, 2) }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="text-muted" style="font-size:.8rem;">{{ $inst->transaction_id ?? '—' }}</td>

                                    <td>
                                        @switch($inst->status)
                                            @case('confirmed')
                                                <span class="badge badge-light-success">Confirmed</span>
                                                @if($inst->is_late)<span class="badge badge-light-warning ml-1">Late</span>@endif
                                                @break
                                            @case('submitted')
                                                <span class="badge badge-light-warning">Awaiting</span>
                                                @break
                                            @case('missed')
                                                <span class="badge badge-light-danger">Missed</span>
                                                @break
                                            @default
                                                @if($inst->isOverdue())
                                                    <span class="badge badge-light-danger">Overdue</span>
                                                @else
                                                    <span class="badge badge-light-secondary">Pending</span>
                                                @endif
                                        @endswitch
                                    </td>

                                    <td class="text-nowrap" style="font-size:.8rem;">{{ $inst->submitted_at?->format('d M Y H:i') ?? '—' }}</td>

                                    <td>
                                        @if($inst->proofScreenshot())
                                            <a href="{{ $inst->proofScreenshot() }}" target="_blank" class="btn btn-xs btn-light-primary">View</a>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap" style="font-size:.8rem;">
                                        @if($inst->deposited_at)
                                            {{ $inst->deposited_at->format('d M Y') }}
                                        @elseif($inst->deposit_deferred && $inst->next_cycle_date)
                                            <span class="text-warning">Deferred<br>{{ $inst->next_cycle_date->format('d M Y') }}</span>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($inst->status === 'submitted')
                                            <form method="POST" action="{{ route('admin.saving.confirm', $inst) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-0"
                                                    onclick="return confirm('Confirm #{{ $inst->instalment_number }}?\nSubmitted: ${{ number_format($inst->submitted_amount ?? 0, 2) }}\nRequired: ${{ number_format($totalP, 2) }}')">
                                                    Confirm &amp; Deposit
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-danger rounded-0 mt-1" data-toggle="modal" data-target="#rejectModal{{ $inst->id }}">Reject</button>

                                            <div class="modal fade" id="rejectModal{{ $inst->id }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('admin.saving.reject', $inst) }}">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Reject Instalment #{{ $inst->instalment_number }}</h5>
                                                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <label>Reason</label>
                                                                <textarea name="notes" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">Reject</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
