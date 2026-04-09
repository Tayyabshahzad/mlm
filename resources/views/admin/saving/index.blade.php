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
                            <div class="font-size-sm mt-1">Activated (Full $24 Paid)</div>
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
                                    <th>Sponsor</th>
                                    <th>Registered</th>
                                    <th>Status</th>
                                    <th>Paid</th>
                                    <th>Remaining</th>
                                    <th>Instalments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                @php
                                    $confirmed = $user->savingInstalments->where('status', 'confirmed')->count();
                                    $total     = $user->savingInstalments->count();
                                    $paidAmt   = $user->savingInstalments->where('status', 'confirmed')->sum('amount');
                                    $totalAmt  = $user->savingInstalments->sum('amount');
                                    $submitted = $user->savingInstalments->where('status', 'submitted')->count();
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
                                        @if($user->sponsor_id)
                                            {{ optional($user->parent)->name ?? '—' }}
                                        @else —
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($user->saving_registration_completed)
                                            <span class="badge badge-light-success">Activated</span>
                                        @else
                                            <span class="badge badge-light-warning">Fee Only</span>
                                        @endif
                                        @if($submitted > 0)
                                            <span class="badge badge-light-info ml-1">{{ $submitted }} pending</span>
                                        @endif
                                    </td>
                                    <td class="text-success font-weight-bold">${{ number_format($paidAmt, 2) }}</td>
                                    <td class="text-warning font-weight-bold">${{ number_format($totalAmt - $paidAmt, 2) }}</td>
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
@endsection
