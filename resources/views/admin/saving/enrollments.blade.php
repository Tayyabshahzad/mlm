@extends('demo.layout.app')
@section('title', 'Savings Program Enrollments')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Savings Program Enrollments</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.saving.index') }}" class="text-muted">Saving Accounts</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Enrollments</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @foreach(['success','error','info'] as $msg)
                @if(session($msg))
                    <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }} alert-dismissible fade show mb-4">
                        {{ session($msg) }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
            @endforeach

            {{-- Summary Cards --}}
            <div class="row mb-6">
                <div class="col-md-4">
                    <div class="card card-custom bg-primary text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem;font-weight:700;">{{ $totalEnrolled }}</div>
                            <div class="font-size-sm mt-1">Total Enrolled Members</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-success text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem;font-weight:700;">{{ $totalActivated }}</div>
                            <div class="font-size-sm mt-1">Activated</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-custom bg-warning text-white">
                        <div class="card-body py-5 text-center">
                            <div style="font-size:1.8rem;font-weight:700;">{{ $pendingActivation }}</div>
                            <div class="font-size-sm mt-1">Pending Activation</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter / Search --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-4">
                    <form method="GET" class="d-flex align-items-center gap-3 w-100">
                        <input type="text" name="search" class="form-control" style="max-width:280px;"
                               placeholder="Search name, username, email..." value="{{ request('search') }}">
                        <select name="status" class="form-control" style="max-width:180px;">
                            <option value="">All Status</option>
                            <option value="activated" {{ request('status') == 'activated' ? 'selected' : '' }}>Activated</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Activation</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.saving.enrollments') }}" class="btn btn-light">Reset</a>
                    </form>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Saving Plan Parent</th>
                                    <th>Enrolled On</th>
                                    <th>Plan Status</th>
                                    <th>Signup Payment</th>
                                    <th>Total Confirmed</th>
                                    <th>Instalments</th>
                                    <th>Payment Proof</th>
                                    <th>Activation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                @php
                                    $inst1          = $user->savingInstalments->where('instalment_number', 1)->first();
                                    $confirmed      = $user->savingInstalments->where('status', 'confirmed')->count();
                                    $total          = $user->savingInstalments->count();
                                    $submitted      = $user->savingInstalments->where('status', 'submitted')->count();
                                    // Enrolled standard users upload saving proof to 'saving_enrollment_proof'
                                    $proof          = $user->getFirstMedia('saving_enrollment_proof');
                                    $signupPayment  = (float)($user->saving_initial_payment ?? 0);
                                    $signupFee      = (float)($user->saving_initial_fee ?? 0);
                                    $signupNet      = max(0, $signupPayment - $signupFee);
                                    $totalPaid      = $user->savingInstalments->where('status', 'confirmed')->sum('amount');
                                    $savingParent   = $user->savingSponsor->first();
                                @endphp
                                <tr>
                                    <td>
                                        <div>
                                            <a href="{{ route('users.show', $user) }}" class="font-weight-bolder text-dark-75 d-block">{{ $user->name }}</a>
                                            <span class="text-muted font-size-sm">@{{ $user->username }} &bull; {{ $user->email }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($savingParent)
                                            <span class="font-weight-bold">{{ $savingParent->name }}</span>
                                            <div class="text-muted font-size-xs">@ {{ $savingParent->username }}</div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->saving_plan_start_date ? \Carbon\Carbon::parse($user->saving_plan_start_date)->format('d M Y') : '—' }}</td>
                                    <td>
                                        @if($user->saving_registration_completed)
                                            <span class="badge badge-light-success">Plan Active</span>
                                        @else
                                            <span class="badge badge-light-warning">Pending Activation</span>
                                        @endif
                                        @if($submitted > 0)
                                            <span class="badge badge-light-info ml-1">{{ $submitted }} pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-primary">${{ number_format($signupPayment, 2) }}</span>
                                        @if($signupPayment > 0)
                                            <div class="text-muted font-size-xs">Fee: ${{ number_format($signupFee, 2) }} / Net: ${{ number_format($signupNet, 2) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-weight-bold text-success">${{ number_format($totalPaid, 2) }}</span>
                                        @if($submitted > 0)
                                            <div class="text-muted font-size-xs">({{ $submitted }} pending confirm)</div>
                                        @endif
                                    </td>
                                    <td>{{ $confirmed }} / {{ $total }}</td>
                                    <td>
                                        @if($proof)
                                            <a href="{{ $proof->getUrl() }}" target="_blank" class="btn btn-xs btn-light-primary">
                                                <i class="la la-image"></i> View Proof
                                            </a>
                                        @elseif($inst1 && $inst1->proofScreenshot())
                                            <a href="{{ $inst1->proofScreenshot() }}" target="_blank" class="btn btn-xs btn-light-primary">
                                                <i class="la la-image"></i> Inst #1 Proof
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->saving_enrollment_activated)
                                            <span class="badge badge-light-success">Activated</span>
                                            <div class="text-muted font-size-xs mt-1">
                                                {{ $user->saving_enrollment_activated_at?->format('d M Y H:i') }}
                                            </div>
                                        @else
                                            <span class="badge badge-light-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex" style="gap:0.5rem;">
                                            @if(!$user->saving_enrollment_activated)
                                                <form method="POST" action="{{ route('admin.saving.enrollments.activate', $user) }}"
                                                      onsubmit="return confirm('Activate Savings Program enrollment for {{ addslashes($user->name) }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        <i class="la la-check"></i> Activate
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('genealogy.my.saving.tree') }}?user={{ $user->id }}" class="btn btn-sm btn-light-primary">
                                                <i class="la la-sitemap"></i> Saving Tree
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center text-muted py-6">No enrolled members found.</td></tr>
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
