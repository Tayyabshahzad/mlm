@extends('demo.layout.app')
@section('title', 'Pending Instalment Confirmations')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Pending Instalment Confirmations</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.saving.index') }}" class="text-muted">Saving Accounts</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Pending</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
            @endif

            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Submissions Awaiting Confirmation</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Phone</th>
                                    <th>Instalment</th>
                                    <th>Due Date</th>
                                    <th>Submitted Amt</th>
                                    <th>Transaction ID</th>
                                    <th>Method</th>
                                    <th>Submitted On</th>
                                    <th>Proof</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($instalments as $inst)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.saving.show', $inst->user) }}" class="font-weight-bold text-dark-75">{{ $inst->user->name }}</a><br>
                                        <span class="text-muted small">@ {{ $inst->user->username }}</span>
                                    </td>
                                    <td>{{ $inst->user->phone_number }}</td>
                                    <td class="font-weight-bold">#{{ $inst->instalment_number }}</td>
                                    <td>
                                        {{ $inst->due_date->format('d M Y') }}
                                        @if(\Carbon\Carbon::today()->gt($inst->due_date))
                                            <span class="badge badge-light-danger ml-1">Late</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold text-primary">${{ number_format($inst->submitted_amount ?? $inst->amount, 2) }}</td>
                                    <td class="text-muted small">{{ $inst->transaction_id ?? '—' }}</td>
                                    <td>{{ $inst->payment_method ?? '—' }}</td>
                                    <td>{{ $inst->submitted_at?->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($inst->proofScreenshot())
                                            <a href="{{ $inst->proofScreenshot() }}" target="_blank" class="btn btn-sm btn-light-primary">View Proof</a>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.saving.confirm', $inst) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"
                                                onclick="return confirm('Confirm and deposit ${{ number_format($inst->submitted_amount ?? $inst->amount, 2) }} for {{ $inst->user->name }}?')">
                                                Confirm
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-danger ml-1" data-toggle="modal" data-target="#rejectModal{{ $inst->id }}">Reject</button>

                                        <div class="modal fade" id="rejectModal{{ $inst->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('admin.saving.reject', $inst) }}">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Reject #{{ $inst->instalment_number }} — {{ $inst->user->name }}</h5>
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
                                    </td>
                                </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center text-muted py-6">No pending submissions.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $instalments->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
