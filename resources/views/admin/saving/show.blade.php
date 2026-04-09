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
                            <div class="text-muted mb-4">{{ $savingUser->phone_number }}</div>
                            <div>
                                @if($savingUser->saving_registration_completed && $savingUser->can_login)
                                    <span class="badge badge-success px-4 py-2">Activated</span>
                                @elseif($savingUser->saving_registration_completed && !$savingUser->can_login)
                                    <span class="badge badge-info px-4 py-2">Registered — Login Pending</span>
                                @else
                                    <span class="badge badge-warning px-4 py-2">Fee Only — Not Activated</span>
                                @endif
                            </div>
                            <div class="mt-3 text-muted font-size-sm">
                                Plan start: {{ $savingUser->saving_plan_start_date ? \Carbon\Carbon::parse($savingUser->saving_plan_start_date)->format('d M Y') : '—' }}
                            </div>

                            @if(!$savingUser->saving_registration_completed || !$savingUser->can_login)
                                <div class="mt-4">
                                    <form method="POST" action="{{ route('admin.saving.activate', $savingUser) }}"
                                          onsubmit="return confirm('Activate {{ $savingUser->name }}? This will mark their registration complete and allow login.')">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm font-weight-bold px-5">
                                            <i class="fas fa-check mr-1"></i> Activate Account
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
                                    <div style="font-size:1.5rem; font-weight:700;">${{ number_format($paid_amount, 2) }}</div>
                                    <div class="font-size-sm mt-1">Total Deposited</div>
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

            {{-- Instalment List with Confirm / Reject Actions --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5 d-flex justify-content-between align-items-center">
                    <h3 class="card-title font-weight-bolder text-dark">Instalment Schedule</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Submitted Amt</th>
                                    <th>Transaction ID</th>
                                    <th>Status</th>
                                    <th>Submitted On</th>
                                    <th>Proof</th>
                                    <th>Deposited</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($instalments as $inst)
                                <tr class="{{ $inst->status === 'submitted' ? 'table-warning' : '' }}">
                                    <td><strong>{{ $inst->instalment_number }}</strong></td>
                                    <td>{{ $inst->due_date->format('d M Y') }}</td>
                                    <td>${{ number_format($inst->amount, 2) }}</td>
                                    <td>
                                        @if($inst->submitted_amount)
                                            ${{ number_format($inst->submitted_amount, 2) }}
                                        @else —
                                        @endif
                                    </td>
                                    <td class="font-size-sm text-muted">{{ $inst->transaction_id ?? '—' }}</td>
                                    <td>
                                        @switch($inst->status)
                                            @case('confirmed')
                                                <span class="badge badge-light-success">Confirmed</span>
                                                @if($inst->is_late) <span class="badge badge-light-warning ml-1">Late</span> @endif
                                                @break
                                            @case('submitted')
                                                <span class="badge badge-light-warning">Awaiting Confirmation</span>
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
                                    <td>{{ $inst->submitted_at?->format('d M Y H:i') ?? '—' }}</td>
                                    <td>
                                        @if($inst->proofScreenshot())
                                            <a href="{{ $inst->proofScreenshot() }}" target="_blank" class="btn btn-sm btn-light-primary">View</a>
                                        @else —
                                        @endif
                                    </td>
                                    <td>
                                        @if($inst->deposited_at)
                                            {{ $inst->deposited_at->format('d M Y') }}
                                        @elseif($inst->deposit_deferred && $inst->next_cycle_date)
                                            <span class="text-warning small">Deferred: {{ $inst->next_cycle_date->format('d M Y') }}</span>
                                        @else —
                                        @endif
                                    </td>
                                    <td>
                                        @if($inst->status === 'submitted')
                                            {{-- Confirm form --}}
                                            <form method="POST" action="{{ route('admin.saving.confirm', $inst) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Confirm instalment #{{ $inst->instalment_number }} and deposit ${{ number_format($inst->submitted_amount ?? $inst->amount, 2) }}?')">
                                                    Confirm &amp; Deposit
                                                </button>
                                            </form>
                                            {{-- Reject form --}}
                                            <button class="btn btn-sm btn-danger ml-1" data-toggle="modal" data-target="#rejectModal{{ $inst->id }}">Reject</button>

                                            {{-- Reject Modal --}}
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
                                                                <label>Reason for rejection</label>
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
