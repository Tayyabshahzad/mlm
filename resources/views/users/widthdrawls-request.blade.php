@extends('demo.layout.app')
@section('title','Withdrawal Requests')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Withdrawal Requests</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">User Info</a></li>
                        <li class="breadcrumb-item"><a href="" class="text-muted">Withdrawal Requests</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            {{-- Alerts --}}
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

            {{-- Filter Card --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <h3 class="card-title font-weight-bolder text-dark">
                        <i class="fas fa-filter text-primary mr-2"></i> Filter Requests
                    </h3>
                    @php $hasFilter = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty(); @endphp
                    @if($hasFilter)
                        <div class="card-toolbar">
                            <a href="{{ route('withdrawals.requests') }}" class="btn btn-sm btn-light-danger">
                                <i class="fas fa-times mr-1"></i> Clear Filters
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body pt-0">
                    <form method="GET" action="{{ route('withdrawals.requests') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold font-size-sm">Username / Name</label>
                                <input type="text" name="username" class="form-control form-control-sm"
                                    placeholder="Search by username or name..."
                                    value="{{ $filters['username'] ?? '' }}">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="font-weight-bold font-size-sm">From Date</label>
                                <input type="date" name="from_date" class="form-control form-control-sm"
                                    value="{{ $filters['from_date'] ?? '' }}">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="font-weight-bold font-size-sm">To Date</label>
                                <input type="date" name="to_date" class="form-control form-control-sm"
                                    value="{{ $filters['to_date'] ?? '' }}">
                            </div>
                            <div class="col-md-1 mb-3">
                                <label class="font-weight-bold font-size-sm">Min Amount</label>
                                <input type="number" name="min_amount" class="form-control form-control-sm"
                                    placeholder="0" step="0.01" min="0"
                                    value="{{ $filters['min_amount'] ?? '' }}">
                            </div>
                            <div class="col-md-1 mb-3">
                                <label class="font-weight-bold font-size-sm">Max Amount</label>
                                <input type="number" name="max_amount" class="form-control form-control-sm"
                                    placeholder="Any" step="0.01" min="0"
                                    value="{{ $filters['max_amount'] ?? '' }}">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="font-weight-bold font-size-sm">Status</label>
                                <select name="status" class="form-control form-control-sm">
                                    <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                                    <option value="pending"  {{ ($filters['status'] ?? '') === 'pending'  ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-1 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>

                        {{-- Quick date shortcuts --}}
                        <div class="d-flex flex-wrap" style="gap:0.4rem;">
                            <span class="text-muted font-size-sm mr-1 align-self-center">Quick:</span>
                            @php
                                $now = \Carbon\Carbon::now();
                                $shortcuts = [
                                    'Today'        => [$now->format('Y-m-d'), $now->format('Y-m-d')],
                                    'This Week'    => [$now->copy()->startOfWeek()->format('Y-m-d'), $now->copy()->endOfWeek()->format('Y-m-d')],
                                    'This Month'   => [$now->copy()->startOfMonth()->format('Y-m-d'), $now->copy()->endOfMonth()->format('Y-m-d')],
                                    'Last Month'   => [$now->copy()->subMonth()->startOfMonth()->format('Y-m-d'), $now->copy()->subMonth()->endOfMonth()->format('Y-m-d')],
                                    'Last 7 Days'  => [$now->copy()->subDays(6)->format('Y-m-d'), $now->format('Y-m-d')],
                                    'Last 30 Days' => [$now->copy()->subDays(29)->format('Y-m-d'), $now->format('Y-m-d')],
                                ];
                            @endphp
                            @foreach($shortcuts as $label => [$from, $to])
                                <a href="{{ request()->fullUrlWithQuery(['from_date' => $from, 'to_date' => $to]) }}"
                                   class="btn btn-sm btn-light {{ ($filters['from_date'] ?? '') === $from && ($filters['to_date'] ?? '') === $to ? 'btn-primary text-white' : '' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </form>
                </div>
            </div>

            {{-- Main Table Card --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bolder text-dark">
                            Total: {{ $withDrawsRequests->count() }} requests
                            @if($hasFilter)<small class="text-muted ml-1">(filtered)</small>@endif
                        </span>
                        <span class="text-muted mt-1 font-size-sm">
                            <span class="text-success font-weight-bold">{{ $withDrawsRequests->where('status','approved')->count() }} Approved</span>
                            &nbsp;|&nbsp;
                            <span class="text-warning font-weight-bold">{{ $withDrawsRequests->where('status','pending')->count() }} Pending</span>
                            &nbsp;|&nbsp;
                            <span class="text-danger font-weight-bold">{{ $withDrawsRequests->where('status','rejected')->count() }} Rejected</span>
                            &nbsp;|&nbsp;
                            <span class="font-weight-bold">Total Amount: ${{ number_format($withDrawsRequests->sum('amount'), 2) }}</span>
                        </span>
                    </h3>
                    <div class="card-toolbar">
                        {{-- Export Button --}}
                        <form method="GET" action="{{ route('withdrawals.export') }}" id="exportForm">
                            <input type="hidden" name="from_date"  value="{{ $filters['from_date']  ?? '' }}">
                            <input type="hidden" name="to_date"    value="{{ $filters['to_date']    ?? '' }}">
                            <input type="hidden" name="username"   value="{{ $filters['username']   ?? '' }}">
                            <input type="hidden" name="min_amount" value="{{ $filters['min_amount'] ?? '' }}">
                            <input type="hidden" name="max_amount" value="{{ $filters['max_amount'] ?? '' }}">
                            <input type="hidden" name="status"     value="{{ $filters['status']     ?? '' }}">
                            <button type="submit" class="btn btn-success font-weight-bolder font-size-sm">
                                <i class="fas fa-file-excel mr-1"></i> Export Excel
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body py-0">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr class="text-left">
                                    <th class="pl-0">#</th>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withDrawsRequests as $req)
                                <tr class="text-left">
                                    <td class="pl-0">{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="font-weight-bold">{{ $req->user->username ?? '—' }}</span>
                                    </td>
                                    <td>{{ $req->user->name ?? '—' }}</td>
                                    <td class="font-weight-bold">${{ number_format($req->amount, 2) }}</td>
                                    <td>
                                        <span class="btn btn-sm
                                            @if($req->status === 'pending') btn-outline-warning
                                            @elseif($req->status === 'rejected') btn-outline-danger
                                            @else btn-outline-success
                                            @endif">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                    </td>
                                    <td>{{ ucwords($req->request_type) }}</td>
                                    <td>{{ $req->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <button data-id="{{ $req->id }}" data-toggle="modal" data-target="#WithdrawModel"
                                            class="view-details-btn btn btn-sm btn-outline-info">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-6">
                                        No withdrawal requests found{{ $hasFilter ? ' matching your filters.' : '.' }}
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Withdrawal Detail Modal --}}
<div class="modal fade" id="WithdrawModel" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('withdraw.request.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Withdrawal Request</h5>
                    <button type="button" class="close" data-dismiss="modal"><i aria-hidden="true" class="ki ki-close"></i></button>
                </div>
                <div class="modal-body" id="modal-content"></div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-danger btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('page_js')
<script>
$(document).ready(function () {
    $('.view-details-btn').on('click', function () {
        var requestId = $(this).data('id');
        var url = "{{ route('withdraw.request.details', ':id') }}".replace(':id', requestId);
        $('#modal-content').html('<p class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</p>');

        $.ajax({
            url: url,
            method: 'GET',
            success: function (r) {
                $('#request_status').val(r.status);
                var content = `
                    <input type="hidden" name="request_id" value="${r.id}"/>
                    <table class="table table-head-custom table-vertical-center">
                        <tr>
                            <th>Username</th><th>Amount</th><th>Status</th>
                            <th>Request Type</th><th>Transfer Fee</th><th>Created At</th>
                        </tr>
                        <tr class="bg-warning">
                            <td>${r.username}</td><td>${r.amount}</td><td>${r.status}</td>
                            <td>${r.request_type}</td><td>${r.transfer_fee}</td><td>${r.created_at}</td>
                        </tr>
                    </table>
                    <div class="row">
                        <div class="col-lg-8">
                            <table class="table table-bordered">
                                <tr><th colspan="4" class="text-center">Bank Details</th></tr>
                                <tr><th colspan="2">Bank Name</th><td colspan="2">${r.bank_name}</td></tr>
                                <tr><th colspan="2">Account Title</th><td colspan="2">${r.account_title}</td></tr>
                                <tr><th colspan="2">Account Number</th><td colspan="2">${r.ibn_number}</td></tr>
                                <tr><th colspan="2">USDT Address</th><td colspan="2">${r.account_number}</td></tr>
                                <tr>
                                    <th colspan="2">Update Status</th>
                                    <td colspan="2">
                                        <select class="form-control" name="status" required id="request_status">
                                            <option value="" selected disabled>Update Request Status</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Reject</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">Screenshot</th>
                                    <td colspan="2"><input type="file" name="screenshot"/></td>
                                </tr>
                            </table>
                            <table class="table table-bordered">
                                <tr><th class="text-center">Current USD Rate</th></tr>
                                <tr><td class="text-center">${r.current_usd}</td></tr>
                            </table>
                        </div>
                        <div class="col-lg-4">
                            <table class="table table-bordered">
                                <tr><th colspan="2" class="text-center">Payable Amount</th></tr>
                                <tr><td class="text-center text-danger" colspan="2"><b>${r.payable_amount} PKR</b></td></tr>
                                <tr><th colspan="2" class="text-center">Transaction Screenshot</th></tr>
                                <tr><td colspan="2" class="text-center">
                                    <img src="${r.withdraw_screenshot}" class="img img-thumbnail" width="400"/>
                                </td></tr>
                            </table>
                        </div>
                    </div>`;
                $('#modal-content').html(content);
            },
            error: function () {
                $('#modal-content').html('<p class="text-danger text-center">Error fetching details.</p>');
            }
        });
    });
});
</script>
@endsection
