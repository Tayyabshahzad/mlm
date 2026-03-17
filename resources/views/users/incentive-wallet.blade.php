@extends('demo.layout.app')
@section('title', 'Designation Incentive Wallet')
@section('custom_css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single { height: 38px; line-height: 36px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
@endsection
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Designation Incentive Wallet</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Users</a></li>
                        <li class="breadcrumb-item"><a href="" class="text-muted">Designation Incentive Wallet</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--end::Subheader-->

    <div class="flex-column-fluid">
        <div class="container">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-0">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-0">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <!-- Send Incentive Card -->
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title">
                        <span class="card-label font-weight-bolder text-dark">Send Designation Incentive to User</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('admin.incentive.send') }}" method="POST">
                        @csrf
                        <input type="hidden" name="wallet_type" value="designation_incentive">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Select User <span class="text-danger">*</span></label>
                                    <select name="user_id" class="form-control select2" required>
                                        <option value="">-- Select User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->username }} ({{ $user->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="font-weight-bold">Amount ($) <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" class="form-control rounded-0"
                                        min="5" step="0.01" placeholder="Min $5"
                                        value="{{ old('amount') }}" required>
                                    @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="font-weight-bold">Description</label>
                                    <input type="text" name="description" class="form-control rounded-0"
                                        placeholder="Optional note..."
                                        value="{{ old('description') }}" maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-warning btn-sm rounded-0 w-100 font-weight-bold">
                                        <i class="la la-paper-plane"></i> Send
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- History Table -->
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title">
                        <span class="card-label font-weight-bolder text-dark">Incentive History</span>
                        <span class="text-muted mt-3 font-weight-bold font-size-sm ml-2">Total: {{ $incentives->total() }} records</span>
                    </h3>
                    <div class="card-toolbar">
                        <form method="GET" action="{{ route('admin.incentive.wallet') }}" class="d-flex align-items-center">
                            <input type="text" name="search" class="form-control form-control-sm rounded-0 mr-2"
                                placeholder="Search username..." value="{{ $search ?? '' }}" style="width:200px;">
                            <button type="submit" class="btn btn-primary btn-sm rounded-0 mr-1">
                                <i class="fas fa-search"></i> Search
                            </button>
                            @if(!empty($search))
                                <a href="{{ route('admin.incentive.wallet') }}" class="btn btn-secondary btn-sm rounded-0">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="card-body py-0">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr class="text-left">
                                    <th>S#</th>
                                    <th>Username</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incentives as $entry)
                                    <tr class="text-left">
                                        <td>{{ $incentives->firstItem() + $loop->index }}</td>
                                        <td>
                                            <span class="font-weight-bold">{{ $entry->user->username ?? '--' }}</span><br>
                                            <small class="text-muted">{{ $entry->user->name ?? '' }}</small>
                                        </td>
                                        <td><strong class="text-success">${{ number_format($entry->balance, 2) }}</strong></td>
                                        <td>{{ $entry->description ?? '--' }}</td>
                                        <td>{{ $entry->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4 mb-3">
                        {{ $incentives->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
@section('page_js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2({ placeholder: 'Search user...', allowClear: true });
    });
</script>
@endsection
