extends('demo.layout.app')
@section('title','Members')
@section('custom_css')
<style>
    .table td, .table th { vertical-align: middle; }
    .nav-tabs .nav-link { font-weight: 600; font-size: 0.95rem; padding: 0.75rem 1.5rem; }
    .nav-tabs .nav-link.active { border-bottom: 3px solid #3699FF; color: #3699FF; }
    .tab-badge { font-size: 0.72rem; padding: 2px 7px; border-radius: 10px; margin-left: 5px; }
</style>
@endsection

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Members</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="" class="text-muted">Members</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="flex-row d-flex">
                <div class="flex-row-fluid ml-lg-8">
                    <div class="card card-custom gutter-b">
                        <div class="p-0 card-body">

                            {{-- ── Tabs ─────────────────────────────────── --}}
                            <ul class="px-8 pt-5 nav nav-tabs nav-tabs-line" id="memberTabs">
                                <li class="nav-item">
                                    <a class="nav-link {{ $tab === 'standard' ? 'active' : '' }}"
                                       href="{{ route('users.index', array_merge(request()->query(), ['tab' => 'standard'])) }}">
                                        Standard Investment
                                        <span class="tab-badge badge badge-primary">{{ $totalMembers }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $tab === 'saving' ? 'active' : '' }}"
                                       href="{{ route('users.index', array_merge(request()->query(), ['tab' => 'saving'])) }}">
                                        Saving Accounts
                                        <span class="tab-badge badge badge-success">{{ $totalSavingMembers }}</span>
                                        @if($totalInactiveSaving > 0)
                                            <span class="tab-badge badge badge-warning">{{ $totalInactiveSaving }} pending</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>

                            <div class="px-8 py-6">

                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show">
                                        {{ session('success') }}
                                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show">
                                        {{ session('error') }}
                                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                    </div>
                                @endif

                                {{-- ════════════════════════════════════════
                                     TAB: STANDARD INVESTMENT
                                ════════════════════════════════════════ --}}
                                @if($tab === 'standard')

                                    {{-- Stats --}}
                                    <div class="flex-wrap mb-4 btn-group" role="group">
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-primary">Total: {{ $totalMembers }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-success">Active: {{ $totalActiveMembers }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-warning">Inactive: {{ $totalInActiveMembers }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-danger">Blocked: {{ $totalBlockedMembers }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-info">Frozen: {{ $totalfreezeMembers }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-success" data-toggle="modal" data-target="#downloadModal">
                                            Download Contacts
                                        </button>
                                    </div>

                                    {{-- Search --}}
                                    <form method="GET" action="{{ route('users.index') }}" class="mb-4">
                                        <input type="hidden" name="tab" value="standard">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control rounded-0"
                                                   placeholder="Search by username, name, or email"
                                                   value="{{ $tab === 'standard' ? ($search ?? '') : '' }}">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-sm rounded-0 btn-info">Search</button>
                                                <a href="{{ route('users.index', ['tab' => 'standard']) }}" class="rounded-0 btn btn-success">Clear</a>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="table-responsive-sm">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Username</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Payment Method</th>
                                                    <th>User Plan</th>
                                                    <th>Actions</th>
                                                    <th>Created At</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($teamMembers as $teamMember)
                                                <tr class="
                                                    @if($teamMember->blocked) text-danger
                                                    @elseif($teamMember->can_login) text-success
                                                    @elseif($teamMember->freez_wallet) text-info
                                                    @else text-warning @endif">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $teamMember->username }}</td>
                                                    <td>{{ $teamMember->name }}</td>
                                                    <td>{{ $teamMember->email }}</td>
                                                    <td>{{ ucfirst($teamMember->payment_method) }}</td>
                                                    <td>
                                                        @if(($teamMember->user_plan ?? 'standard') === 'vip')
                                                            <span class="badge badge-warning">VIP Gold</span>
                                                        @else
                                                            <span class="badge badge-primary">VIP Silver</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-info rounded-0 dropdown-toggle" type="button" data-toggle="dropdown">
                                                                More Actions
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item text-info user-details-btn" data-id="{{ $teamMember->id }}" data-target="#userDetails" data-toggle="modal" href="#">Info</a>
                                                                <a class="dropdown-item text-warning"
                                                                    @if(!$teamMember->can_login) data-toggle="modal" data-target="#changeStatus" @endif
                                                                    data-id="{{ $teamMember->id }}" href="#">
                                                                    {{ $teamMember->can_login ? 'Activated' : 'Activate' }}
                                                                </a>
                                                                <a class="dropdown-item text-primary" href="{{ route('user.info', $teamMember->id) }}">Details</a>
                                                                <a class="dropdown-item text-success" href="{{ route('admin.user.wallets', $teamMember->id) }}">Wallet Overview</a>
                                                                <a class="dropdown-item text-danger" data-toggle="modal" data-target="#deleteUser" data-id="{{ $teamMember->id }}" href="#">Delete</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $teamMember->created_at->format('d M Y') }}</td>
                                                    <td><a href="{{ route('admin.user.team', $teamMember->id) }}">View Team</a></td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @include('users._pagination', ['paginator' => $teamMembers])
                                    </div>

                                @endif

                                {{-- ════════════════════════════════════════
                                     TAB: SAVING ACCOUNTS
                                ════════════════════════════════════════ --}}
                                @if($tab === 'saving')

                                    {{-- Stats --}}
                                    <div class="flex-wrap mb-4 btn-group" role="group">
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-primary">Total: {{ $totalSavingMembers }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-success">Admin Activated: {{ $totalActiveSaving }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-warning">Pending Activation: {{ $totalInactiveSaving }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-info">Deposit Complete: {{ $totalActivatedSaving }}</button>
                                        <a href="{{ route('admin.saving.pending') }}" class="mb-2 mr-2 btn btn-danger">View Pending Instalments</a>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-dark"
                                                data-toggle="modal" data-target="#dueExportModal">
                                            <i class="mr-1 fas fa-file-excel"></i> Due Instalment Sheet
                                        </button>
                                    </div>

                                    {{-- Search --}}
                                    <form method="GET" action="{{ route('users.index') }}" class="mb-4">
                                        <input type="hidden" name="tab" value="saving">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control rounded-0"
                                                   placeholder="Search saving account users..."
                                                   value="{{ $tab === 'saving' ? ($search ?? '') : '' }}">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-sm rounded-0 btn-info">Search</button>
                                                <a href="{{ route('users.index', ['tab' => 'saving']) }}" class="rounded-0 btn btn-success">Clear</a>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="table-responsive-sm">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Username</th>
                                                    <th>Name</th>
                                                    <th>Phone</th>
                                                    <th>Sponsor</th>
                                                    <th>Deposit Status</th>
                                                    <th>Admin Status</th>
                                                    <th>Options</th>
                                                    <th>Instalments</th>
                                                    <th>Actions</th>
                                                    <th>Joined</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($savingMembers as $member)
                                                @php
                                                    $confirmedInst  = $member->savingInstalments->where('status','confirmed')->count();
                                                    $totalInst      = $member->savingInstalments->count();
                                                    $submittedInst  = $member->savingInstalments->where('status','submitted')->count();
                                                    $isEnrolled     = $member->saving_enrolled && $member->account_type !== 'saving';
                                                    $savingActive   = $isEnrolled ? $member->saving_enrollment_activated : $member->can_login;
                                                @endphp
                                                <tr class="{{ !$savingActive ? 'text-warning' : 'text-success' }}">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        {{ $member->username }}
                                                        @if($isEnrolled)
                                                            <span class="ml-1 badge badge-light-primary" style="font-size:0.7rem;">Enrolled</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $member->name }}</td>
                                                    <td>{{ $member->phone_number }}</td>
                                                    <td>{{ optional($member->savingSponsor->first())->username ?? '—' }}</td>
                                                    <td>
                                                        @if($member->saving_registration_completed)
                                                            <span class="badge badge-success">Deposit Done</span>
                                                        @else
                                                            <span class="badge badge-warning">Fee Only ($5)</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($savingActive)
                                                            <span class="badge badge-success">Activated</span>
                                                        @else
                                                            <span class="badge badge-danger">Not Activated</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($member->adb_option)
                                                            <span class="badge badge-light-primary">ADB</span>
                                                        @endif
                                                        @if($member->fisp_option)
                                                            <span class="badge badge-light-success">FISP</span>
                                                        @endif
                                                        @if(!$member->adb_option && !$member->fisp_option)
                                                            <span class="text-muted" style="font-size:.8rem;">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $confirmedInst }}/{{ $totalInst }}
                                                        @if($submittedInst > 0)
                                                            <span class="ml-1 badge badge-warning">{{ $submittedInst }} pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-info rounded-0 dropdown-toggle" type="button" data-toggle="dropdown">
                                                                Actions
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                @if(!$savingActive)
                                                                    <a class="dropdown-item text-success"
                                                                       href="{{ route('admin.saving.show', $member) }}">
                                                                       Activate Savings
                                                                    </a>
                                                                @else
                                                                    <span class="dropdown-item text-muted">Already Activated</span>
                                                                @endif
                                                                <a class="dropdown-item text-warning user-details-btn"
                                                                   data-toggle="modal" data-target="#userDetails"
                                                                   data-id="{{ $member->id }}" href="#">Signup Details</a>
                                                                <a class="dropdown-item text-success" href="{{ route('admin.user.wallets', $member->id) }}">Wallet Overview</a>
                                                                <a class="dropdown-item text-primary" href="{{ route('admin.saving.show', $member) }}">Instalment Details</a>
                                                                <a class="dropdown-item text-info" href="{{ route('user.info', $member->id) }}">User Info</a>
                                                                <a class="dropdown-item text-danger" data-toggle="modal" data-target="#deleteUser" data-id="{{ $member->id }}" data-saving="true" href="#">Remove Saving Data</a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $member->created_at->format('d M Y') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @include('users._pagination', ['paginator' => $savingMembers])
                                    </div>

                                @endif

                            </div>{{-- /px-8 py-6 --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Shared Modals ────────────────────────────────────────── --}}

<div class="modal fade" id="changeStatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activate Member</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
            </div>
            <form action="{{ route('users.status.update') }}" id="updateUserForm" method="POST">
                @method('put')
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to activate this member?</p>
                    <p class="text-muted small">For Saving Account users, this will enable ROI and release any held referral commissions.</p>
                    <input type="hidden" name="member_id" id="member_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary font-weight-bold rounded-0" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary font-weight-bold rounded-0" id="updateUser">Activate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="userDetails" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Member Details</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
            </div>
            <div class="modal-body">
                <div id="loading-spinner" style="display:none; text-align:center;"><p>Loading...</p></div>
                <div id="user-details-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete User</h5>
                <button type="button" class="close" data-dismiss="modal"><i class="ki ki-close"></i></button>
            </div>
            <form action="" id="deleteUserForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <p class="text-center text-danger" id="deleteModalMessage">Are you sure you want to delete this member?<br>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary btn-sm font-weight-bold rounded-0" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold rounded-0">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="downloadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Select Date Range</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('download.contacts') }}" method="GET">
                <div class="modal-body">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                    <label class="mt-3">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Download</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Due Instalment Filter Modal ──────────────────────────────────── --}}
<div class="modal fade" id="dueExportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:480px;">
        <div class="modal-content" style="border:none; border-radius:12px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-2" style="background:linear-gradient(135deg,#1e2a38 0%,#2d3e50 100%); padding:1.25rem 1.5rem;">
                <div>
                    <h5 class="modal-title text-white font-weight-bold mb-0" style="font-size:1rem; letter-spacing:.3px;">
                        <i class="fas fa-file-excel mr-2" style="color:#5bc65b;"></i> Due Instalment Sheet
                    </h5>
                    <p class="mb-0 mt-1" style="font-size:0.75rem; color:#8fa8c8;">Filter by date range &amp; member</p>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:.7;"><span>&times;</span></button>
            </div>
            <div class="modal-body" style="padding:1.5rem; background:#f8fafc;">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group mb-3">
                            <label style="font-size:.75rem; font-weight:700; color:#4a5568; text-transform:uppercase; letter-spacing:.5px;">From Date</label>
                            <input type="date" id="dueFrom" class="form-control" value="{{ date('Y-m-01') }}"
                                   style="border-radius:8px; border:1.5px solid #d1dbe6; font-size:.875rem; height:38px;">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-3">
                            <label style="font-size:.75rem; font-weight:700; color:#4a5568; text-transform:uppercase; letter-spacing:.5px;">To Date</label>
                            <input type="date" id="dueTo" class="form-control" value="{{ date('Y-m-d') }}"
                                   style="border-radius:8px; border:1.5px solid #d1dbe6; font-size:.875rem; height:38px;">
                        </div>
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label style="font-size:.75rem; font-weight:700; color:#4a5568; text-transform:uppercase; letter-spacing:.5px;">Member</label>
                    <select id="dueUserId" class="form-control" style="border-radius:8px; border:1.5px solid #d1dbe6; font-size:.875rem; height:38px;">
                        <option value="">— All Members —</option>
                        @foreach($savingUsers as $su)
                            <option value="{{ $su->id }}">{{ $su->name }} (@{{ $su->username }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-3 p-2" style="background:#fff3cd; border-radius:8px; border-left:3px solid #f59e0b; font-size:.78rem; color:#7c5700;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Also includes <strong>overdue instalments</strong> from before the start date.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0" style="padding:1rem 1.5rem 1.25rem; background:#f8fafc; gap:.5rem;">
                <button type="button" class="btn btn-light btn-sm" data-dismiss="modal" style="border-radius:8px; padding:.45rem 1rem;">Cancel</button>
                <button type="button" class="btn btn-sm" id="duePreviewBtn"
                        style="border-radius:8px; padding:.45rem 1.1rem; background:#2563eb; color:#fff; border:none;">
                    <i class="fas fa-eye mr-1"></i> Preview
                </button>
                <a href="#" id="dueDownloadBtn" class="btn btn-sm"
                   style="border-radius:8px; padding:.45rem 1.1rem; background:#1e2a38; color:#fff; border:none;">
                    <i class="fas fa-download mr-1"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ── Due Instalment Preview Modal ─────────────────────────────────── --}}
<div class="modal fade" id="duePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:min(98vw,1400px); width:98vw; margin:auto;">
        <div class="modal-content" style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,0.10);">

            {{-- Header --}}
            <div class="modal-header" style="background:#fff; border-bottom:1px solid #f0f0f0; padding:1rem 1.4rem;">
                <div>
                    <h6 class="modal-title mb-0" style="font-size:.9rem; font-weight:700; color:#111827;">
                        Due Instalments
                    </h6>
                    <p class="mb-0 mt-1" id="duePreviewTitle" style="font-size:.78rem; color:#6b7280;"></p>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#9ca3af; opacity:1; font-size:1.2rem;"><span>&times;</span></button>
            </div>

            {{-- Summary strip --}}
            <div id="duePreviewSummary" style="display:none; background:#fafafa; border-bottom:1px solid #f0f0f0; padding:.6rem 1.4rem;">
                <div style="display:flex; gap:2rem; flex-wrap:wrap; align-items:center;">
                    <span style="font-size:.78rem; color:#374151;">
                        <strong id="sumMembers" style="color:#111827; font-size:.92rem;">0</strong>
                        <span style="color:#9ca3af; margin-left:3px;">Members</span>
                    </span>
                    <span style="font-size:.78rem; color:#374151;">
                        Total Due: <strong id="sumTotal" style="color:#111827; font-size:.92rem;">$0.00</strong>
                    </span>
                    <span style="font-size:.78rem; color:#374151;">
                        Overdue: <strong id="sumOverdue" style="color:#dc2626; font-size:.92rem;">0</strong>
                    </span>
                    <span style="font-size:.78rem; color:#374151;">
                        In-range only: <strong id="sumInRange" style="color:#16a34a; font-size:.92rem;">0</strong>
                    </span>
                </div>
            </div>

            {{-- Body --}}
            <div class="modal-body p-0" style="background:#fff;">

                {{-- Loading --}}
                <div id="duePreviewLoading" class="text-center" style="padding:3rem 1rem;">
                    <div class="spinner-border" role="status" style="color:#d1d5db; width:1.8rem; height:1.8rem; border-width:2px;"></div>
                    <div style="margin-top:.75rem; font-size:.8rem; color:#9ca3af;">Loading…</div>
                </div>

                {{-- Empty --}}
                <div id="duePreviewEmpty" style="display:none; text-align:center; padding:3rem 1rem;">
                    <div style="font-size:.88rem; color:#6b7280; font-weight:500;">No pending instalments found.</div>
                    <div style="font-size:.78rem; color:#9ca3af; margin-top:.3rem;">Try a different date range or member.</div>
                </div>

                {{-- Table --}}
                <div id="duePreviewTableWrap" style="display:none; overflow-x:auto; max-height:60vh; overflow-y:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:.93rem;">
                        <thead>
                            <tr style="background:#f9fafb; position:sticky; top:0; z-index:2;">
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; white-space:nowrap; width:36px;">#</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; white-space:nowrap;">Member</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; white-space:nowrap;">Phone</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; white-space:nowrap;">Sponsor</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#dc2626; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:center; white-space:nowrap;">Overdue</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#16a34a; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:center; white-space:nowrap;">In Range</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:right; white-space:nowrap;">Overdue ($)</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:right; white-space:nowrap;">In Range ($)</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:right; white-space:nowrap;">Total ($)</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; white-space:nowrap;">Oldest Due</th>
                                <th style="padding:.65rem 1rem; font-size:.82rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; white-space:nowrap;">Latest Due</th>
                            </tr>
                        </thead>
                        <tbody id="duePreviewBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer" style="background:#fff; border-top:1px solid #f0f0f0; padding:.75rem 1.4rem; display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                    <span style="font-size:.74rem; color:#9ca3af;">
                        <span style="display:inline-block; width:8px; height:8px; background:#fee2e2; border-radius:50%; margin-right:4px; border:1px solid #fca5a5;"></span>Overdue
                    </span>
                    <span style="font-size:.74rem; color:#9ca3af;">
                        <span style="display:inline-block; width:8px; height:8px; background:#f0fdf4; border-radius:50%; margin-right:4px; border:1px solid #bbf7d0;"></span>In range only
                    </span>
                    <span id="duePreviewFooterCount" style="font-size:.74rem; color:#9ca3af;"></span>
                </div>
                <div style="display:flex; gap:.5rem;">
                    <button type="button" data-dismiss="modal"
                            style="padding:.38rem .9rem; font-size:.8rem; border-radius:6px; border:1px solid #e5e7eb; background:#fff; color:#374151; cursor:pointer;">
                        Back
                    </button>
                    <a href="#" id="duePreviewDownloadBtn"
                       style="padding:.38rem .9rem; font-size:.8rem; border-radius:6px; border:none; background:#111827; color:#fff; text-decoration:none; display:inline-flex; align-items:center; gap:5px;">
                        <i class="fas fa-download" style="font-size:.72rem;"></i> Download Excel
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@section('page_js')
<script>
$(document).ready(function () {

    // Activate modal — set member_id
    $('[data-target="#changeStatus"]').click(function () {
        $('#member_id').val($(this).data('id'));
    });

    // Delete modal — set delete_id and correct action based on tab
    $('[data-target="#deleteUser"]').click(function () {
        var id      = $(this).data('id');
        var isSaving= $(this).data('saving') == true || $(this).data('saving') == 'true';
        $('#delete_id').val(id);
        if (isSaving) {
            $('#deleteUserForm').attr('action', '{{ route("user.saving.remove") }}');
            $('#deleteModalMessage').html('This will <strong>remove this user\'s saving plan data only</strong>.<br>Their main account will remain intact.');
        } else {
            $('#deleteUserForm').attr('action', '{{ route("user.delete") }}');
            $('#deleteModalMessage').html('Are you sure you want to delete this member?<br>This action cannot be undone.');
        }
    });

    // Disable button on submit
    $('#updateUser').on('click', function () {
        $(this).prop('disabled', true).text('Activating...');
        $('#updateUserForm').submit();
    });

    // User details AJAX
    $('.user-details-btn').on('click', function () {
        var memberId = $(this).data('id');
        $('#user-details-content').html('');
        $('#loading-spinner').show();
        $.ajax({
            url: '/users/details',
            method: 'GET',
            data: { id: memberId },
            success: function (response) {
                $('#loading-spinner').hide();
                if (response.success) {
                    var d = response.data;
                    var isSaving        = d.account_type === 'saving';
                    var isSavingRelated = isSaving || d.saving_enrolled;

                    // ── Personal info ────────────────────────────────────────────
                    // For saving-related users show ONLY the saving tree referrer.
                    // Never fall back to the standard-plan parent — they are different trees.
                    var referLabel = isSavingRelated ? 'Saving Plan Referrer' : 'Referred By';
                    var referLink  = '—';
                    if (isSavingRelated) {
                        if (d.saving_sponsor && d.saving_sponsor.id) {
                            referLink = '<a href="/users/info/' + d.saving_sponsor.id + '">' + d.saving_sponsor.username + '</a>';
                        }
                    } else if (d.referBy && d.referBy.id) {
                        referLink = '<a href="/users/info/' + d.referBy.id + '">' + d.referBy.username + '</a>';
                    }

                    var accountBadge = isSaving
                        ? '<span class="badge badge-info">Saving Account</span>'
                        : (d.saving_enrolled
                            ? '<span class="badge badge-light-info">Standard + Saving Enrolled</span>'
                            : '<span class="badge badge-primary">Standard Investment</span>');

                    var html = '<table class="table table-bordered table-sm">'
                        + '<tr class="bg-light-primary"><td colspan="2" class="text-center font-weight-bold">Personal Information</td></tr>'
                        + '<tr><td width="40%">Name</td><td>' + d.name + '</td></tr>'
                        + '<tr><td>Username</td><td>' + d.username + '</td></tr>'
                        + '<tr><td>Email</td><td>' + d.email + '</td></tr>'
                        + '<tr><td>Phone</td><td>' + (d.phone_number || '—') + '</td></tr>'
                        + '<tr><td>' + referLabel + '</td><td>' + referLink + '</td></tr>'
                        + '<tr><td>Joined</td><td>' + d.created_at + '</td></tr>'
                        + '<tr><td>Account Type</td><td>' + accountBadge + '</td></tr>'
                        + '<tr><td>Login Status</td><td>' + (d.status === 'Active'
                            ? '<span class="badge badge-success">Active</span>'
                            : '<span class="badge badge-danger">Not Activated</span>') + '</td></tr>';

                    if (isSavingRelated) {
                        var regBadge = d.saving_registration_completed
                            ? '<span class="badge badge-success">Fully Paid</span>'
                            : '<span class="badge badge-warning">Partial / Fee Only</span>';
                        html += '<tr><td>Registration Status</td><td>' + regBadge + '</td></tr>'
                             +  '<tr><td>Plan Start Date</td><td>' + (d.saving_plan_start_date || '—') + '</td></tr>'
                             +  '<tr><td>Total Confirmed Deposited</td><td><strong>$' + parseFloat(d.saving_total_deposited || 0).toFixed(2) + '</strong></td></tr>';
                    }
                    html += '</table>';

                    // ── Saving Plan — Signup Payment Breakdown ───────────────────
                    if (isSavingRelated) {
                        var totalPaid   = parseFloat(d.saving_initial_payment || 0);
                        var fee         = parseFloat(d.saving_initial_fee || 0);
                        var rate        = parseFloat(d.usdt_rate || 0);
                        var adbOn       = d.adb_option  === true;
                        var fispOn      = d.fisp_option === true;

                        // Step 1: gross after fee deduction
                        var grossAfterFee = Math.max(0, totalPaid - fee);

                        // Step 2: ADB/FISP monthly charges based on gross (0.3% and 0.4%)
                        var adbCharge  = adbOn  ? parseFloat((grossAfterFee * 0.075).toFixed(4)) : 0;
                        var fispCharge = fispOn ? parseFloat((grossAfterFee * 0.1).toFixed(4)) : 0;
                        var totalDeductions = adbCharge + fispCharge;

                        // Step 3: net credited = gross - deductions
                        var netCredited = Math.max(0, parseFloat((grossAfterFee - totalDeductions).toFixed(4)));

                        var inst1Due      = d.signup_instalment ? parseFloat(d.signup_instalment.amount || 0) : 0;
                        var inst1Status   = d.signup_instalment ? d.signup_instalment.status : 'pending';
                        var inst1TxId     = (d.saving_transaction_id) || (d.signup_instalment && d.signup_instalment.transaction_id) || '—';
                        var inst1DueDate  = d.signup_instalment ? d.signup_instalment.due_date : '—';
                        // Still Owed = based on gross paid (before ADB/FISP deductions)
                        // ADB/FISP reduce the investment credit, NOT the instalment balance
                        var stillOwed     = Math.max(0, parseFloat((inst1Due - grossAfterFee).toFixed(2)));

                        var statusMap = {
                            confirmed: '<span class="badge badge-success">Confirmed</span>',
                            submitted: '<span class="badge badge-warning">Awaiting Admin Confirmation</span>',
                            missed:    '<span class="badge badge-danger">Missed</span>',
                            pending:   '<span class="badge badge-secondary">Pending</span>',
                        };
                        var inst1Badge = statusMap[inst1Status] || '<span class="badge badge-secondary">' + inst1Status + '</span>';

                        // Helper: show USD + PKR equivalent
                        function usdPkr(usd) {
                            if (!rate || rate <= 0) return '$' + usd.toFixed(2);
                            var pkr = (usd * rate).toFixed(0);
                            return '$' + usd.toFixed(2) + ' <small class="text-muted">(PKR ' + parseInt(pkr).toLocaleString() + ')</small>';
                        }

                        html += '<table class="table table-bordered table-sm">'
                            + '<tr class="bg-light-success"><td colspan="2" class="text-center font-weight-bold">Saving Plan — Signup Payment</td></tr>';

                        // Rate info row
                        if (rate > 0) {
                            html += '<tr class="table-light"><td colspan="2" class="text-muted" style="font-size:.8rem;">Exchange rate used at registration: <strong>1 USD = PKR ' + rate + '</strong></td></tr>';
                        }

                        html += '<tr><td width="50%"><strong>Instalment #1 Total Due</strong></td>'
                            +     '<td><strong>' + usdPkr(inst1Due) + '</strong> &nbsp;' + inst1Badge + '</td></tr>'

                            + '<tr><td>Amount Paid at Signup</td>'
                            +     '<td class="text-primary font-weight-bold">' + usdPkr(totalPaid) + '</td></tr>'

                            + '<tr><td>Registration Fee (deducted)</td>'
                            +     '<td class="text-danger">− ' + usdPkr(fee) + '</td></tr>'

                            + '<tr style="background:#f8f9fa;"><td><strong>Gross After Fee</strong></td>'
                            +     '<td class="font-weight-bold">' + usdPkr(grossAfterFee) + '</td></tr>';

                        // ADB/FISP rows (only if selected)
                        if (adbOn) {
                            html += '<tr><td>ADB Option Charge (0.3% / month)</td>'
                                +   '<td class="text-danger">− ' + usdPkr(adbCharge) + ' <small class="text-muted">per month × 25</small></td></tr>';
                        }
                        if (fispOn) {
                            html += '<tr><td>FISP Option Charge (0.4% / month)</td>'
                                +   '<td class="text-danger">− ' + usdPkr(fispCharge) + ' <small class="text-muted">per month × 25</small></td></tr>';
                        }
                        if (adbOn || fispOn) {
                            html += '<tr class="table-warning"><td><strong>Total Insurance Charges</strong> <small class="text-muted d-block">(deducted from each instalment before crediting investment)</small></td>'
                                +   '<td class="text-danger font-weight-bold">− ' + usdPkr(totalDeductions) + ' / month</td></tr>';
                        }

                        html += '<tr><td>Net Credited Toward Saving</td>'
                            +     '<td class="text-success font-weight-bold">' + usdPkr(netCredited) + '</td></tr>'

                            + '<tr><td>Still Owed for Instalment #1</td>'
                            +     '<td class="' + (stillOwed > 0 ? 'text-warning font-weight-bold' : 'text-success') + '">'
                            +     (stillOwed > 0 ? usdPkr(stillOwed) : '<span class="badge badge-success">Fully Covered</span>')
                            +     '</td></tr>'

                            + '<tr><td>Transaction ID</td><td>' + inst1TxId + '</td></tr>'
                            + '<tr><td>Instalment #1 Due Date</td><td>' + inst1DueDate + '</td></tr>'
                            + '</table>';

                        var proof = (d.signup_instalment && d.signup_instalment.proof_url) || d.amount_proof || '';
                        if (proof) {
                            html += '<table class="table table-bordered table-sm">'
                                + '<tr class="bg-light-info"><td class="text-center font-weight-bold">Signup Payment Proof</td></tr>'
                                + '<tr><td><a href="' + proof + '" target="_blank">'
                                + '<img src="' + proof + '" class="img img-thumbnail" style="max-width:100%;height:auto;"/>'
                                + '</a></td></tr></table>';
                        } else {
                            html += '<div class="mt-2 alert alert-warning">No payment proof uploaded.</div>';
                        }
                    }

                    // ── Standard Plan registration info (only for non-saving users) ──
                    if (!isSavingRelated) {
                        html += '<table class="table table-bordered table-sm">'
                            + '<tr class="bg-light-danger"><td colspan="2" class="text-center font-weight-bold">Registration Payment Information</td></tr>'
                            + '<tr><td width="40%">Payment Method</td><td>' + d.payment_method + '</td></tr>'
                            + '<tr><td>Transaction ID</td><td>' + d.transaction_id + '</td></tr>'
                            + '<tr><td>Activation Code</td><td>' + d.activationCode.code + '</td></tr>'
                            + '<tr><td>Code Generated By</td><td>' + d.activationCode.generated_by + '</td></tr>'
                            + '<tr><td>Transferred (PKR)</td><td>' + d.transferred_amount + '</td></tr>'
                            + '<tr><td>Total USDT Paid</td><td>$' + d.converted_usdt_amount + '</td></tr>'
                            + '<tr><td>Fee Deducted</td><td>$' + d.fee_deducted + '</td></tr>'
                            + '<tr><td>Net Invested USDT</td><td>$' + d.net_invested_usdt_amount + '</td></tr>'
                            + '<tr><td>USDT Rate</td><td>' + d.usdt_rate + '</td></tr>'
                            + '</table>';

                        if (d.amount_proof) {
                            html += '<table class="table table-bordered table-sm">'
                                + '<tr class="bg-light-warning"><td class="text-center font-weight-bold">Transaction Proof</td></tr>'
                                + '<tr><td><a href="' + d.amount_proof + '" target="_blank">'
                                + '<img src="' + d.amount_proof + '" class="img img-thumbnail" style="max-width:100%;height:auto;"/>'
                                + '</a></td></tr></table>';
                        } else {
                            html += '<div class="mt-2 alert alert-warning">No transaction proof uploaded.</div>';
                        }
                    }

                    // For pure saving accounts also show method / transferred amount if available
                    if (isSaving && d.saving_payment_method) {
                        html += '<table class="table table-bordered table-sm">'
                            + '<tr class="bg-light-secondary"><td colspan="2" class="text-center font-weight-bold">Saving Plan Registration Details</td></tr>'
                            + '<tr><td width="40%">Payment Method</td><td>' + d.saving_payment_method + '</td></tr>'
                            + '<tr><td>Transaction ID</td><td>' + (d.saving_transaction_id || '—') + '</td></tr>'
                            + (d.saving_transferred_amount ? '<tr><td>Transferred (PKR)</td><td>' + d.saving_transferred_amount + '</td></tr>' : '')
                            + '<tr><td>Activation Code</td><td>' + d.activationCode.code + '</td></tr>'
                            + '<tr><td>Code Generated By</td><td>' + d.activationCode.generated_by + '</td></tr>'
                            + '</table>';
                    }

                    $('#user-details-content').html(html);
                } else {
                    $('#user-details-content').html('<p class="text-danger">Error fetching user details.</p>');
                }
            },
            error: function () {
                $('#loading-spinner').hide();
                $('#user-details-content').html('<p class="text-danger">Unable to fetch user details.</p>');
            }
        });
    });
});

// ── Due Instalment Sheet ──────────────────────────────────────────────────
(function () {
    var previewUrl = "{{ route('admin.saving.due.preview') }}";
    var exportUrl  = "{{ route('admin.saving.due.export') }}";

    function buildQS() {
        var from   = document.getElementById('dueFrom').value;
        var to     = document.getElementById('dueTo').value;
        var userId = document.getElementById('dueUserId').value;
        var p = new URLSearchParams({ from: from, to: to });
        if (userId) p.append('user_id', userId);
        return p.toString();
    }

    function syncDownloadBtn() {
        document.getElementById('dueDownloadBtn').href = exportUrl + '?' + buildQS();
    }
    syncDownloadBtn();
    ['dueFrom','dueTo','dueUserId'].forEach(function(id){
        document.getElementById(id).addEventListener('change', syncDownloadBtn);
    });

    document.getElementById('duePreviewBtn').addEventListener('click', function () {
        var qs   = buildQS();
        var from = document.getElementById('dueFrom').value;
        var to   = document.getElementById('dueTo').value;

        document.getElementById('duePreviewDownloadBtn').href = exportUrl + '?' + qs;
        document.getElementById('duePreviewTitle').textContent = fmtDate(from) + '  →  ' + fmtDate(to);

        $('#dueExportModal').modal('hide');
        $('#duePreviewModal').modal('show');

        // Reset
        document.getElementById('duePreviewLoading').style.display   = 'block';
        document.getElementById('duePreviewEmpty').style.display     = 'none';
        document.getElementById('duePreviewTableWrap').style.display = 'none';
        document.getElementById('duePreviewSummary').style.display   = 'none';
        document.getElementById('duePreviewBody').innerHTML          = '';
        document.getElementById('duePreviewFooterCount').textContent = '';

        fetch(previewUrl + '?' + qs, { headers: {'X-Requested-With':'XMLHttpRequest'} })
        .then(function(r){ return r.json(); })
        .then(function(res){
            document.getElementById('duePreviewLoading').style.display = 'none';

            if (!res.data || res.data.length === 0) {
                document.getElementById('duePreviewEmpty').style.display = 'block';
                return;
            }

            // Summary bar
            document.getElementById('duePreviewSummary').style.display  = 'block';
            document.getElementById('sumMembers').textContent  = res.total;
            document.getElementById('sumTotal').textContent   = '$' + res.total_due;
            document.getElementById('sumOverdue').textContent = res.overdue_users;
            document.getElementById('sumInRange').textContent = res.total - res.overdue_users;

            document.getElementById('duePreviewTableWrap').style.display = 'block';
            document.getElementById('duePreviewFooterCount').textContent =
                res.total + ' member' + (res.total !== 1 ? 's' : '') + ' — as of ' + fmtDate(to);

            var tbody = document.getElementById('duePreviewBody');
            res.data.forEach(function(row, i){
                var isOverdue = row.has_overdue;
                var rowBg     = isOverdue ? '#fffbfb' : '#fff';
                var leftBorder= isOverdue ? '3px solid #fca5a5' : '3px solid transparent';

                var overdueCell = row.overdue_count > 0
                    ? '<span style="font-size:.93rem; font-weight:600; color:#dc2626;">' + row.overdue_count + '</span>'
                    : '<span style="color:#d1d5db;">—</span>';

                var inRangeCell = row.in_range_count > 0
                    ? '<span style="font-size:.93rem; font-weight:600; color:#16a34a;">' + row.in_range_count + '</span>'
                    : '<span style="color:#d1d5db;">—</span>';

                var tr = document.createElement('tr');
                tr.style.cssText = 'background:' + rowBg + '; border-left:' + leftBorder + ';';
                tr.onmouseover = function(){ this.style.background = isOverdue ? '#fff5f5' : '#f9fafb'; };
                tr.onmouseout  = function(){ this.style.background = rowBg; };

                var td = 'padding:.75rem 1rem; border-bottom:1px solid #f3f4f6; white-space:nowrap; color:#374151; font-size:.93rem;';

                tr.innerHTML =
                    '<td style="' + td + 'color:#9ca3af; text-align:center; width:36px;">' + (i+1) + '</td>' +
                    '<td style="' + td + '">' +
                        '<div style="font-weight:600; color:#111827; font-size:.95rem;">' + esc(row.name) + '</div>' +
                        '<div style="font-size:.8rem; color:#9ca3af; margin-top:2px;">@' + esc(row.username) + '</div>' +
                    '</td>' +
                    '<td style="' + td + '">' + esc(row.phone) + '</td>' +
                    '<td style="' + td + 'color:#6b7280; max-width:180px; overflow:hidden; text-overflow:ellipsis;">' + esc(row.sponsor) + '</td>' +
                    '<td style="' + td + 'text-align:center;">' + overdueCell + '</td>' +
                    '<td style="' + td + 'text-align:center;">' + inRangeCell + '</td>' +
                    '<td style="' + td + 'text-align:right; color:' + (row.overdue_count > 0 ? '#dc2626' : '#d1d5db') + ';">' +
                        (row.overdue_count > 0 ? '$' + esc(row.overdue_amount) : '—') +
                    '</td>' +
                    '<td style="' + td + 'text-align:right; color:#374151;">$' + esc(row.in_range_amount) + '</td>' +
                    '<td style="' + td + 'text-align:right; font-weight:700; color:#111827; font-size:.97rem;">$' + esc(row.total_due) + '</td>' +
                    '<td style="' + td + 'color:' + (isOverdue ? '#dc2626' : '#6b7280') + ';">' + esc(row.oldest_due) + '</td>' +
                    '<td style="' + td + 'color:#6b7280;">' + esc(row.latest_due) + '</td>';

                tbody.appendChild(tr);
            });
        })
        .catch(function(){
            document.getElementById('duePreviewLoading').style.display = 'none';
            document.getElementById('duePreviewEmpty').style.display   = 'block';
        });
    });

    function esc(str){
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str || ''));
        return d.innerHTML;
    }
    function fmtDate(str){
        if (!str) return '';
        var d = new Date(str);
        return d.toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'});
    }
})();
</script>
@endsection

