@extends('demo.layout.app')
@section('title', 'My Saving Plan')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Saving Plan — Instalment Tracker</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Saving Plan</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Summary Cards --}}
            <div class="row mb-6">
                <div class="col-md-3">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem; font-weight:700;">${{ number_format($paid_amount, 2) }}</div>
                            <div class="font-size-sm mt-1">Total Deposited</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom bg-warning text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem; font-weight:700;">${{ number_format($remaining, 2) }}</div>
                            <div class="font-size-sm mt-1">Remaining</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.6rem; font-weight:700;">{{ $paid_count }} / {{ $total_count }}</div>
                            <div class="font-size-sm mt-1">Instalments Paid</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom {{ $plan_complete ? 'bg-success' : 'bg-info' }} text-white">
                        <div class="card-body py-5 text-center">
                            @if($plan_complete)
                                <div style="font-size:1.6rem; font-weight:700;">&#10003; Done</div>
                                <div class="font-size-sm mt-1">Plan Complete!</div>
                            @elseif($next_due)
                                <div style="font-size:1.1rem; font-weight:700;">
                                    #{{ $next_due->instalment_number }}<br>
                                    {{ $next_due->due_date->format('d M Y') }}
                                </div>
                                <div class="font-size-sm mt-1">Next Due Date</div>
                            @else
                                <div style="font-size:1rem;">—</div>
                                <div class="font-size-sm mt-1">No pending</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Registration Payment Breakdown --}}
            <div class="card card-custom gutter-b border-left-primary">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">Registration Payment Summary</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="svg-icon svg-icon-primary mr-3">
                                    <i class="fas fa-money-bill-wave text-primary" style="font-size:1.4rem;"></i>
                                </span>
                                <div>
                                    <div class="text-muted font-size-sm">Amount Paid at Registration</div>
                                    <div class="font-weight-bolder font-size-h5">${{ number_format($paidAtReg, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="svg-icon svg-icon-warning mr-3">
                                    <i class="fas fa-receipt text-warning" style="font-size:1.4rem;"></i>
                                </span>
                                <div>
                                    <div class="text-muted font-size-sm">Registration Fee</div>
                                    <div class="font-weight-bolder font-size-h5">${{ number_format($regFee, 2) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-3">
                                <span class="svg-icon svg-icon-success mr-3">
                                    <i class="fas fa-piggy-bank text-success" style="font-size:1.4rem;"></i>
                                </span>
                                <div>
                                    <div class="text-muted font-size-sm">Deposited Toward Instalment #1</div>
                                    <div class="font-weight-bolder font-size-h5">${{ number_format($partialDeposit, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!auth()->user()->saving_registration_completed)
                        <div class="alert alert-warning mt-2 mb-0">
                            <strong>Registration Incomplete.</strong>
                            You paid ${{ number_format($paidAtReg, 2) }} at registration (${{ number_format($regFee, 2) }} fee + ${{ number_format($partialDeposit, 2) }} toward deposit).
                            You still need to deposit <strong>${{ number_format($inst1Remaining, 2) }}</strong> to complete Instalment #1 and activate your account.
                        </div>
                    @else
                        <div class="alert alert-success mt-2 mb-0">
                            <strong>Registration Complete.</strong> Your saving account is fully activated.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Early payment notice --}}
            @if($next_due && $next_due->status === 'pending' && $next_due->instalment_number > 1 && \Carbon\Carbon::today()->lt($next_due->due_date))
                <div class="alert alert-info mb-6">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Early Payment:</strong> Instalment #{{ $next_due->instalment_number }} is scheduled for
                    <strong>{{ $next_due->due_date->format('d M Y') }}</strong>.
                    You can pay now — ROI for this instalment will begin from its scheduled date.
                </div>
            @endif

            {{-- Submit Next Instalment — shown for all pending instalments --}}
            @if($next_due && $next_due->status === 'pending')
                <div class="card card-custom gutter-b">
                    <div class="card-header border-0 py-5">
                        <h3 class="card-title font-weight-bolder text-dark">
                            Pay Instalment #{{ $next_due->instalment_number }}
                            @if($next_due->instalment_number === 1 && $partialDeposit > 0)
                                <span class="badge badge-light-success ml-2">${{ number_format($partialDeposit, 2) }} already paid</span>
                                <span class="badge badge-light-primary ml-1">${{ number_format($inst1Remaining, 2) }} remaining</span>
                            @else
                                <span class="badge badge-light-primary ml-2">${{ number_format($next_due->amount, 2) }}</span>
                            @endif
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-sm table-borderless">
                                    <tr><td class="text-muted" width="160">Due Date</td><td class="font-weight-bold">{{ $next_due->due_date->format('d M Y') }}</td></tr>
                                    @if($next_due->instalment_number === 1 && $partialDeposit > 0)
                                        <tr><td class="text-muted">Paid at Registration</td><td class="font-weight-bold text-success">${{ number_format($partialDeposit, 2) }}</td></tr>
                                        <tr><td class="text-muted">Remaining to Pay</td><td class="font-weight-bold text-primary">${{ number_format($inst1Remaining, 2) }}</td></tr>
                                    @else
                                        <tr><td class="text-muted">Amount Due</td><td class="font-weight-bold text-primary">${{ number_format($next_due->amount, 2) }}</td></tr>
                                    @endif
                                    <tr>
                                        <td class="text-muted">Status</td>
                                        <td>
                                            @if($next_due->isOverdue())
                                                <span class="badge badge-light-danger">Overdue</span>
                                                <small class="text-danger ml-1">(payment will be late — next cycle applies)</small>
                                            @else
                                                <span class="badge badge-light-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('saving.user.submit') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-bold">Payment Method</label>
                                    <select name="payment_method" class="form-control" required>
                                        <option value="">Select method</option>
                                        <option value="bank">Bank Transfer</option>
                                        <option value="usdt">USDT</option>
                                        <option value="cash_slip">Cash Slip</option>
                                    </select>
                                    @error('payment_method')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-bold">Transaction ID</label>
                                    <input type="text" name="transaction_id" class="form-control" placeholder="Your transaction ID" required value="{{ old('transaction_id') }}">
                                    @error('transaction_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4 mb-4">
                                    <label class="font-weight-bold">Amount Submitted ($)</label>
                                    @php
                                        $defaultAmount = ($next_due->instalment_number === 1 && $inst1Remaining > 0)
                                            ? $inst1Remaining
                                            : $next_due->amount;
                                    @endphp
                                    <input type="number" step="0.01" name="submitted_amount" class="form-control"
                                           placeholder="{{ number_format($defaultAmount, 2) }}"
                                           value="{{ old('submitted_amount', $defaultAmount) }}" required>
                                    @error('submitted_amount')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label class="font-weight-bold">Payment Proof Screenshot</label>
                                    <input type="file" name="proof" class="form-control-file" accept="image/*" required>
                                    @error('proof')<div class="text-danger small">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Payment Proof</button>
                        </form>
                    </div>
                </div>
            @elseif($next_due && $next_due->status === 'submitted')
                <div class="alert alert-info mb-6">
                    <strong>Instalment #{{ $next_due->instalment_number }} submitted</strong> on {{ $next_due->submitted_at?->format('d M Y H:i') }}.
                    Awaiting admin confirmation.
                </div>
            @endif

            {{-- Full Instalment History --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Instalment Schedule (25 Months)</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Paid On</th>
                                    <th>Deposited</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($instalments as $inst)
                                <tr>
                                    <td><strong>{{ $inst->instalment_number }}</strong></td>
                                    <td>{{ $inst->due_date->format('d M Y') }}</td>
                                    <td>${{ number_format($inst->amount, 2) }}</td>
                                    <td>
                                        @switch($inst->status)
                                            @case('confirmed')
                                                <span class="badge badge-light-success">Confirmed</span>
                                                @if($inst->is_late) <span class="badge badge-light-warning ml-1">Late</span> @endif
                                                @break
                                            @case('submitted')
                                                <span class="badge badge-light-info">Awaiting Confirmation</span>
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
                                    <td>{{ $inst->confirmed_at?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @if($inst->deposited_at)
                                            {{ $inst->deposited_at->format('d M Y') }}
                                        @elseif($inst->deposit_deferred && $inst->next_cycle_date)
                                            <span class="text-warning">Deferred to {{ $inst->next_cycle_date->format('d M Y') }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $inst->notes ?? '' }}</td>
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
