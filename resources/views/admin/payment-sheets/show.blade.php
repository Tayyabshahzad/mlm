@extends('demo.layout.app')
@section('title', 'Payment Sheet #' . $batch->id)

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Payment Sheet #{{ $batch->id }}</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('payment-sheets.index') }}" class="text-muted">Payment Sheets</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">#{{ $batch->id }}</a></li>
                </ul>
            </div>
            <div>
                @if($batch->stored_path)
                    <a href="{{ route('payment-sheets.download', $batch) }}" class="btn btn-light-primary btn-sm font-weight-bold">Download Original File</a>
                @endif
                @role('admin')
                    <a href="{{ route('admin.saving.pending') }}" class="btn btn-primary btn-sm font-weight-bold ml-2">Go to Pending Confirmations</a>
                @endrole
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">
            <div class="card card-custom gutter-b">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><span class="text-muted d-block">File</span><span class="font-weight-bold">{{ $batch->original_file_name }}</span></div>
                        <div class="col-md-3"><span class="text-muted d-block">Uploaded by</span><span class="font-weight-bold">{{ $batch->uploader?->name }} (@ {{ $batch->uploader?->username }})</span></div>
                        <div class="col-md-2"><span class="text-muted d-block">Date</span><span class="font-weight-bold">{{ $batch->created_at->format('d M Y H:i') }}</span></div>
                        <div class="col-md-2"><span class="text-muted d-block">Payments</span><span class="font-weight-bold">{{ $batch->rows_count }}</span></div>
                        <div class="col-md-2"><span class="text-muted d-block">Total</span><span class="font-weight-bolder text-primary">${{ number_format($batch->total_amount, 2) }}</span></div>
                    </div>
                </div>
            </div>

            <div class="card card-custom gutter-b">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead><tr><th>Row</th><th>Member</th><th>Email</th><th>Txn ID</th><th>Amount</th><th>Instalment</th><th>Current Status</th></tr></thead>
                            <tbody>
                            @foreach($batch->rows as $r)
                                <tr>
                                    <td>{{ $r->row_number }}</td>
                                    <td><span class="font-weight-bold">{{ $r->user?->name }}</span><br><span class="text-muted small">@ {{ $r->username }}</span></td>
                                    <td class="small">{{ $r->email ?? '—' }}</td>
                                    <td class="small">{{ $r->txn_id ?? '—' }}</td>
                                    <td class="font-weight-bolder">${{ number_format($r->amount, 2) }}</td>
                                    <td>#{{ $r->instalment?->instalment_number }}</td>
                                    <td>
                                        @switch($r->processingStatus())
                                            @case('confirmed') <span class="badge badge-light-success">Confirmed</span> @break
                                            @case('awaiting') <span class="badge badge-light-warning">Awaiting admin</span> @break
                                            @default <span class="badge badge-light-danger">Rejected by admin</span>
                                        @endswitch
                                        @if($r->rejected_reason)
                                            <div class="small text-danger mt-1">{{ $r->rejected_reason }}</div>
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
