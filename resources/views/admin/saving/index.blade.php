@extends('demo.layout.app')
@section('title', 'Saving Accounts')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Saving Accounts</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Saving Accounts</a></li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.saving.pending') }}" class="btn btn-sm btn-warning mr-2">
                    Pending Confirmations
                    @if($pendingSubmissions > 0)
                        <span class="badge badge-pill badge-light ml-1">{{ $pendingSubmissions }}</span>
                    @endif
                </a>
                <button type="button" class="btn btn-sm btn-danger mr-2"
                        data-toggle="modal" data-target="#dueExportModal">
                    <i class="fas fa-file-excel mr-1"></i> Due Instalment Sheet
                </button>
                <a href="{{ route('admin.saving.create-user') }}" class="btn btn-sm btn-primary">
                    + Create Saving User
                </a>
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

            {{-- Summary Cards --}}
            <div class="row mb-6">
                <div class="col-md-4">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem; font-weight:700;">{{ $totalSaving }}</div>
                            <div class="font-size-sm mt-1">Total Saving Users</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem; font-weight:700;">{{ $registrationDone }}</div>
                            <div class="font-size-sm mt-1">Activated Accounts</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-warning text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem; font-weight:700;">{{ $pendingSubmissions }}</div>
                            <div class="font-size-sm mt-1">Pending Confirmations</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Saving Parent User Setting --}}
            @if(!$setting->saving_parent_user_id)
                <div class="alert alert-warning mb-6">
                    <strong>No Saving Parent User set.</strong> All saving accounts should be registered under a designated parent user.
                    <a href="{{ route('admin.saving.create-user') }}" class="ml-2 font-weight-bold">Create one now →</a>
                </div>
            @else
                @php $parentUser = \App\Models\User::find($setting->saving_parent_user_id); @endphp
                @if($parentUser)
                    <div class="alert alert-info mb-6">
                        Saving Account root: <strong> {{ $parentUser->name }}</strong> (@ {{ $parentUser->username }})
                    </div>
                @endif
            @endif

            {{-- Filter / Search --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <form method="GET" class="d-flex align-items-center gap-3 w-100">
                        <input type="text" name="search" class="form-control" style="max-width:280px;"
                               placeholder="Search name, username, email, phone..." value="{{ request('search') }}">
                        <select name="status" class="form-control" style="max-width:180px;">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Activated</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Fee Only ($5)</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.saving.index') }}" class="btn btn-light">Reset</a>
                    </form>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Phone</th>
                                    <th>Saving Plan Parent</th>
                                    <th>Registered</th>
                                    <th>Status</th>
                                    <th>Signup Payment</th>
                                    <th>Inst #1 Remaining</th>
                                    <th>Instalments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                @php
                                    $confirmed      = $user->savingInstalments->where('status', 'confirmed')->count();
                                    $total          = $user->savingInstalments->count();
                                    $paidAmt        = $user->savingInstalments->where('status', 'confirmed')->sum('amount');
                                    $submitted      = $user->savingInstalments->where('status', 'submitted')->count();
                                    $signupPayment  = (float)($user->saving_initial_payment ?? 0);
                                    $signupFee      = (float)($user->saving_initial_fee ?? 0);
                                    $signupNet      = max(0, $signupPayment - $signupFee);
                                    // Remaining for inst #1: inst1 amount minus what was already credited at signup
                                    $inst1          = $user->savingInstalments->where('instalment_number', 1)->first();
                                    $inst1Amount    = $inst1 ? (float)$inst1->amount : 0;
                                    $inst1Remaining = $inst1 && $inst1->status !== 'confirmed'
                                        ? max(0, $inst1Amount - $signupNet)
                                        : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-40 symbol-light-success mr-3">
                                                @if($user->getFirstMedia('profile_photos'))
                                                    <img src="{{ $user->getFirstMedia('profile_photos')->getUrl() }}" class="symbol-label" style="object-fit:cover;">
                                                @else
                                                    <span class="symbol-label font-size-h5 font-weight-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.saving.show', $user) }}" class="font-weight-bolder text-dark-75 d-block">{{ $user->name }}</a>
                                                <span class="text-muted font-size-sm">@ {{ $user->username }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->phone_number }}</td>
                                    <td>
                                        @php($savingParent = $user->savingSponsor->first())
                                        @if($savingParent)
                                            <span class="font-weight-bold">{{ $savingParent->name }}</span>
                                            <div class="text-muted font-size-xs">@ {{ $savingParent->username }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($user->saving_enrolled && $user->account_type !== 'saving')
                                            <span class="badge badge-light-primary d-block mb-1">Enrolled Member</span>
                                            @if($user->saving_enrollment_activated)
                                                <span class="badge badge-light-success">Active</span>
                                            @else
                                                <span class="badge badge-light-warning">Pending Activation</span>
                                            @endif
                                        @elseif($user->saving_registration_completed)
                                            <span class="badge badge-light-success">Activated</span>
                                        @else
                                            <span class="badge badge-light-warning">Fee Only / Pending</span>
                                        @endif
                                        @if($submitted > 0)
                                            <span class="badge badge-light-info ml-1">{{ $submitted }} pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold {{ $signupPayment > 0 ? 'text-primary' : 'text-muted' }}">
                                            ${{ number_format($signupPayment, 2) }}
                                        </span>
                                        @if($signupPayment > 0)
                                            <div class="text-muted font-size-xs">Fee: ${{ number_format($signupFee, 2) }} / Net: ${{ number_format($signupNet, 2) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($inst1 && $inst1->status === 'confirmed')
                                            <span class="badge badge-light-success">Paid</span>
                                        @elseif($inst1Remaining > 0)
                                            <span class="font-weight-bold text-warning">${{ number_format($inst1Remaining, 2) }}</span>
                                        @elseif($signupNet > 0 && $inst1Amount == 0)
                                            <span class="text-muted">—</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $confirmed }} / {{ $total }}</td>
                                    <td>
                                        <a href="{{ route('admin.saving.show', $user) }}" class="btn btn-sm btn-light-primary">View</a>
                                    </td>
                                </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted py-6">No saving account users found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $users->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ── Due Instalment Export Modal ──────────────────────────────────── --}}
<div class="modal fade" id="dueExportModal" tabindex="-1" role="dialog" aria-labelledby="dueExportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:#dc3545;">
                <h5 class="modal-title text-white font-weight-bold" id="dueExportModalLabel">
                    <i class="fas fa-file-excel mr-2 text-white"></i> Download Due Instalment Sheet
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form method="GET" action="{{ route('admin.saving.due.export') }}">
                <div class="modal-body">

                    <p class="text-muted font-size-sm mb-5">
                        Downloads all <strong>pending / overdue</strong> instalments within the selected date range.
                        Choose a specific member or leave on <em>All Members</em>.
                    </p>

                    {{-- Date Range --}}
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold font-size-sm">From Date</label>
                                <input type="date" name="from" class="form-control"
                                       value="{{ date('Y-m-01') }}">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label class="font-weight-bold font-size-sm">To Date</label>
                                <input type="date" name="to" class="form-control"
                                       value="{{ date('Y-m-d') }}"
                                       max="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <span class="form-text text-muted mt-n3 mb-4 d-block">Only pending instalments with due dates within this range will be included.</span>

                    {{-- Member Select --}}
                    <div class="form-group mb-0">
                        <label class="font-weight-bold font-size-sm">Member</label>
                        <select name="user_id" class="form-control" id="dueExportUserSelect">
                            <option value="">— All Members —</option>
                            @foreach($savingUsers as $su)
                                <option value="{{ $su->id }}">{{ $su->name }} (@{{ $su->username }})</option>
                            @endforeach
                        </select>
                        <span class="form-text text-muted">Select a specific member or leave blank for all.</span>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-download mr-1"></i> Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
