@extends('demo.layout.app')
@section('title', 'Upload Payment Sheet')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Upload Payment Sheet</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Payment Sheets</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            <div class="row">
                <div class="col-lg-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title font-weight-bolder">Upload Sheet</h3>
                        </div>
                        <div class="card-body pt-2">
                            <form method="POST" action="{{ route('payment-sheets.upload') }}" enctype="multipart/form-data" id="sheetForm">
                                @csrf
                                <div class="form-group">
                                    <label class="font-weight-bold">Excel or CSV file <span class="text-danger">*</span></label>
                                    <input type="file" name="sheet" accept=".xlsx,.xls,.csv" class="form-control-file" required>
                                    @error('sheet')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <button type="submit" class="btn btn-primary font-weight-bold" id="sheetSubmit"
                                        onclick="return confirm('Upload this sheet? Every listed member\'s next instalment will be submitted for admin confirmation.')">
                                    Upload &amp; Submit
                                </button>
                                <a href="{{ route('payment-sheets.template') }}" class="btn btn-light-primary font-weight-bold ml-2">Download Template</a>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card card-custom gutter-b bg-light-primary">
                        <div class="card-body">
                            <h5 class="font-weight-bolder mb-3">Sheet format</h5>
                            <table class="table table-sm table-bordered bg-white mb-3">
                                <thead><tr><th>Username</th><th>Email</th><th>Txn ID</th><th>Amount</th></tr></thead>
                                <tbody><tr><td>ali123</td><td>ali@mail.com</td><td>HBL-889900</td><td>19</td></tr></tbody>
                            </table>
                            <ul class="small mb-0 pl-4">
                                <li><strong>Username</strong> and <strong>Amount</strong> are required; Email and Txn ID are optional.</li>
                                <li>Amount must <strong>exactly</strong> match the member's next instalment (including ADB/FISP).</li>
                                <li>If Email is given it must match the member; a Txn ID can be used only once.</li>
                                <li>Each username only once per sheet. Members with a payment already awaiting confirmation are not allowed.</li>
                                <li><strong>If any row has a problem, the whole sheet is rejected and nothing is saved.</strong></li>
                                <li>Accepted payments go to <em>Pending Confirmations</em>; the admin confirms them.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row errors from the last rejected upload --}}
            @if(session('sheet_errors'))
                <div class="card card-custom gutter-b border border-danger">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title font-weight-bolder text-danger">
                            Sheet rejected — {{ count(session('sheet_errors')) }} problem(s), nothing was saved
                        </h3>
                    </div>
                    <div class="card-body pt-2">
                        <table class="table table-sm table-hover">
                            <thead><tr class="text-muted"><th style="width:80px">Row</th><th style="width:220px">Username</th><th>Problem</th></tr></thead>
                            <tbody>
                            @foreach(session('sheet_errors') as $err)
                                <tr>
                                    <td>{{ $err['row'] ?: '—' }}</td>
                                    <td class="font-weight-bold">{{ $err['username'] !== '' ? $err['username'] : '—' }}</td>
                                    <td class="text-danger">{{ $err['message'] }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">Uploaded Sheets</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead><tr><th>#</th><th>File</th><th>Uploaded By</th><th>Payments</th><th>Total</th><th>Confirmed by Admin</th><th>Date</th><th></th></tr></thead>
                            <tbody>
                            @forelse($batches as $b)
                                <tr>
                                    <td class="font-weight-bold">#{{ $b->id }}</td>
                                    <td class="small">{{ $b->original_file_name }}</td>
                                    <td class="small">{{ $b->uploader?->name }}<br><span class="text-muted">@ {{ $b->uploader?->username }}</span></td>
                                    <td>{{ $b->rows_count }}</td>
                                    <td class="font-weight-bolder">${{ number_format($b->total_amount, 2) }}</td>
                                    <td>
                                        @if(($confirmedCount = $b->rows->filter(fn ($r) => $r->processingStatus() === 'confirmed')->count()) === $b->rows_count)
                                            <span class="badge badge-light-success">{{ $confirmedCount }} / {{ $b->rows_count }}</span>
                                        @else
                                            <span class="badge badge-light-warning">{{ $confirmedCount }} / {{ $b->rows_count }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-nowrap">{{ $b->created_at->format('d M Y H:i') }}</td>
                                    <td><a href="{{ route('payment-sheets.show', $b) }}" class="btn btn-sm btn-light-primary">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-6">No sheets uploaded yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $batches->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('page_js')
<script>
    $('#sheetForm').on('submit', function () { $('#sheetSubmit').prop('disabled', true).text('Uploading…'); });
</script>
@endsection
