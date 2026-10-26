@extends('demo.layout.app')
@section('title', 'Dashboard')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Data Entry Dashboard</h5>
                <span class="text-muted font-weight-bold">Your payment sheets</span>
            </div>
            <div>
                <a href="{{ route('payment-sheets.template') }}" class="btn btn-light-primary btn-sm font-weight-bold mr-2">Download Template</a>
                <a href="{{ route('payment-sheets.index') }}" class="btn btn-primary btn-sm font-weight-bold">Upload Payment Sheet</a>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            {{-- Headline figures --}}
            <div class="row">
                <div class="col-6 col-lg-3">
                    <div class="card card-custom bg-light-primary gutter-b">
                        <div class="card-body py-6">
                            <span class="text-primary font-weight-bold d-block">Sheets Uploaded</span>
                            <span class="font-weight-bolder font-size-h2 text-dark">{{ number_format($stats['sheets']) }}</span>
                            <span class="text-muted small d-block">{{ $stats['sheets_today'] }} today</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card card-custom bg-light-info gutter-b">
                        <div class="card-body py-6">
                            <span class="text-info font-weight-bold d-block">Payment Records</span>
                            <span class="font-weight-bolder font-size-h2 text-dark">{{ number_format($stats['records']) }}</span>
                            <span class="text-muted small d-block">${{ number_format($stats['amount'], 2) }} entered</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card card-custom bg-light-success gutter-b">
                        <div class="card-body py-6">
                            <span class="text-success font-weight-bold d-block">Confirmed by Admin</span>
                            <span class="font-weight-bolder font-size-h2 text-dark">{{ number_format($stats['confirmed']) }}</span>
                            <span class="text-muted small d-block">${{ number_format($stats['confirmed_amount'], 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card card-custom bg-light-warning gutter-b">
                        <div class="card-body py-6">
                            <span class="text-warning font-weight-bold d-block">Awaiting Admin</span>
                            <span class="font-weight-bolder font-size-h2 text-dark">{{ number_format($stats['awaiting']) }}</span>
                            <span class="text-muted small d-block">${{ number_format($stats['awaiting_amount'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6 col-lg-3">
                    <div class="card card-custom gutter-b">
                        <div class="card-body py-5">
                            <span class="text-danger font-weight-bold d-block">Rejected by Admin</span>
                            <span class="font-weight-bolder font-size-h4">{{ number_format($stats['rejected']) }}</span>
                            <span class="text-muted small d-block">${{ number_format($stats['rejected_amount'], 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card card-custom gutter-b">
                        <div class="card-body py-5">
                            <span class="text-dark font-weight-bold d-block">Uploads Rejected</span>
                            <span class="font-weight-bolder font-size-h4">{{ number_format($stats['rejected_uploads']) }}</span>
                            <span class="text-muted small d-block">sheets with errors (nothing saved)</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card card-custom gutter-b">
                        <div class="card-body py-5">
                            <span class="text-dark font-weight-bold d-block">This Month</span>
                            <span class="font-weight-bolder font-size-h4">{{ number_format($stats['month_records']) }}</span>
                            <span class="text-muted small d-block">${{ number_format($stats['month_amount'], 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="card card-custom gutter-b">
                        <div class="card-body py-5">
                            <span class="text-dark font-weight-bold d-block">Success Rate</span>
                            <span class="font-weight-bolder font-size-h4">{{ $stats['success_rate'] }}%</span>
                            <span class="text-muted small d-block">of your records confirmed</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Recent sheets --}}
                <div class="col-xl-7">
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title font-weight-bolder">Recent Sheets</h3>
                            <div class="card-toolbar"><a href="{{ route('payment-sheets.index') }}" class="btn btn-sm btn-light">View all</a></div>
                        </div>
                        <div class="card-body pt-2">
                            <table class="table table-hover table-vertical-center">
                                <thead><tr class="text-muted"><th>#</th><th>File</th><th>Payments</th><th>Total</th><th>Progress</th><th></th></tr></thead>
                                <tbody>
                                @forelse($recentSheets as $b)
                                    <tr>
                                        <td class="font-weight-bold">#{{ $b->id }}</td>
                                        <td class="small">{{ \Illuminate\Support\Str::limit($b->original_file_name, 26) }}<br><span class="text-muted">{{ $b->created_at->format('d M Y H:i') }}</span></td>
                                        <td>{{ $b->rows_count }}</td>
                                        <td class="font-weight-bolder">${{ number_format($b->total_amount, 2) }}</td>
                                        <td class="small text-nowrap">
                                            <span class="badge badge-light-success">{{ $b->rows->filter(fn ($r) => $r->processingStatus() === 'confirmed')->count() }} confirmed</span>
                                            <span class="badge badge-light-warning">{{ $b->rows->filter(fn ($r) => $r->processingStatus() === 'awaiting')->count() }} awaiting</span>
                                            @if(($rej = $b->rows->filter(fn ($r) => $r->processingStatus() === 'rejected')->count()) > 0)
                                                <span class="badge badge-light-danger">{{ $rej }} rejected</span>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('payment-sheets.show', $b) }}" class="btn btn-xs btn-light-primary">View</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-6">
                                        No sheets yet. <a href="{{ route('payment-sheets.index') }}">Upload your first payment sheet</a>.
                                    </td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    {{-- Payments the admin rejected --}}
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder">Rejected by Admin</h3></div>
                        <div class="card-body pt-2">
                            @forelse($rejectedRows as $r)
                                <div class="d-flex justify-content-between border-bottom py-3">
                                    <div class="pr-3">
                                        <span class="font-weight-bold">{{ $r->user?->name }}</span>
                                        <span class="text-muted small">@ {{ $r->username }}</span><br>
                                        <span class="text-muted small">Sheet #{{ $r->batch_id }}, row {{ $r->row_number }} · inst #{{ $r->instalment?->instalment_number }}</span>
                                        @if($r->rejected_reason)
                                            <div class="small text-danger">{{ \Illuminate\Support\Str::limit($r->rejected_reason, 90) }}
                                                <span class="text-muted">· {{ $r->rejected_at?->format('d M Y') }}</span></div>
                                        @endif
                                    </div>
                                    <span class="font-weight-bolder text-nowrap">${{ number_format($r->amount, 2) }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">None — no payments from your sheets were rejected.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Sheets that failed validation --}}
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder">Rejected Uploads</h3></div>
                        <div class="card-body pt-2">
                            @forelse($rejectedUploads as $a)
                                <div class="border-bottom py-3">
                                    <span class="font-weight-bold small">{{ $a->details['file'] ?? 'Sheet' }}</span>
                                    <span class="text-muted small">· {{ $a->created_at->format('d M Y H:i') }}</span><br>
                                    <span class="small text-danger">{{ count($a->details['errors'] ?? []) }} problem(s){{ isset($a->details['errors'][0]['message']) ? ' — e.g. row ' . $a->details['errors'][0]['row'] . ': ' . \Illuminate\Support\Str::limit($a->details['errors'][0]['message'], 70) : '' }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">None.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
