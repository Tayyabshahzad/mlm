@extends('demo.layout.app')
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
                            <ul class="nav nav-tabs nav-tabs-line px-8 pt-5" id="memberTabs">
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
                                    <div class="btn-group flex-wrap mb-4" role="group">
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
                                                <button type="submit" class="btn btn-sm rounded-0 btn btn-info">Search</button>
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
                                    <div class="btn-group flex-wrap mb-4" role="group">
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-primary">Total: {{ $totalSavingMembers }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-success">Admin Activated: {{ $totalActiveSaving }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-warning">Pending Activation: {{ $totalInactiveSaving }}</button>
                                        <button type="button" class="mb-2 mr-2 rounded-0 btn btn-info">Deposit Complete: {{ $totalActivatedSaving }}</button>
                                        <a href="{{ route('admin.saving.pending') }}" class="mb-2 mr-2 btn btn-danger">View Pending Instalments</a>
                                    </div>

                                    {{-- Search --}}
                                    <form method="GET" action="{{ route('users.index') }}" class="mb-4">
                                        <input type="hidden" name="tab" value="saving">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control rounded-0"
                                                   placeholder="Search saving account users..."
                                                   value="{{ $tab === 'saving' ? ($search ?? '') : '' }}">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-sm rounded-0 btn btn-info">Search</button>
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
                                                    <th>Instalments</th>
                                                    <th>Actions</th>
                                                    <th>Joined</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($savingMembers as $member)
                                                @php
                                                    $confirmedInst = $member->savingInstalments->where('status','confirmed')->count();
                                                    $totalInst     = $member->savingInstalments->count();
                                                    $submittedInst = $member->savingInstalments->where('status','submitted')->count();
                                                @endphp
                                                <tr class="{{ !$member->can_login ? 'text-warning' : 'text-success' }}">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $member->username }}</td>
                                                    <td>{{ $member->name }}</td>
                                                    <td>{{ $member->phone_number }}</td>
                                                    <td>{{ optional($member->parent)->username ?? '—' }}</td>
                                                    <td>
                                                        @if($member->saving_registration_completed)
                                                            <span class="badge badge-success">Deposit Done</span>
                                                        @else
                                                            <span class="badge badge-warning">Fee Only ($5)</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($member->can_login)
                                                            <span class="badge badge-success">Activated</span>
                                                        @else
                                                            <span class="badge badge-danger">Not Activated</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $confirmedInst }}/{{ $totalInst }}
                                                        @if($submittedInst > 0)
                                                            <span class="badge badge-warning ml-1">{{ $submittedInst }} pending</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-outline-info rounded-0 dropdown-toggle" type="button" data-toggle="dropdown">
                                                                Actions
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                @if(!$member->can_login)
                                                                    <a class="dropdown-item text-success"
                                                                       data-toggle="modal" data-target="#changeStatus"
                                                                       data-id="{{ $member->id }}" href="#">
                                                                       Activate Account
                                                                    </a>
                                                                @else
                                                                    <span class="dropdown-item text-muted">Already Activated</span>
                                                                @endif
                                                                <a class="dropdown-item text-primary" href="{{ route('admin.saving.show', $member) }}">Instalment Details</a>
                                                                <a class="dropdown-item text-info" href="{{ route('user.info', $member->id) }}">User Info</a>
                                                                <a class="dropdown-item text-danger" data-toggle="modal" data-target="#deleteUser" data-id="{{ $member->id }}" href="#">Delete</a>
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
    <div class="modal-dialog">
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
            <form action="{{ route('user.delete') }}" id="deleteUserForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="delete_id" id="delete_id">
                    <p class="text-center text-danger">Are you sure you want to delete this member?<br>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary btn-sm font-weight-bold rounded-0" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold rounded-0">Delete</button>
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

@endsection

@section('page_js')
<script>
$(document).ready(function () {

    // Activate modal — set member_id
    $('[data-target="#changeStatus"]').click(function () {
        $('#member_id').val($(this).data('id'));
    });

    // Delete modal — set delete_id
    $('[data-target="#deleteUser"]').click(function () {
        $('#delete_id').val($(this).data('id'));
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
                    var html = `<table class='table table-bordered'>
                        <tr><td>Name</td><td>${d.name}</td></tr>
                        <tr><td>Email</td><td>${d.email}</td></tr>
                        <tr><td>Joined</td><td>${d.created_at}</td></tr>
                        <tr><td>Status</td><td>${d.status}</td></tr>
                        <tr><td>Transaction ID</td><td>${d.transaction_id}</td></tr>
                        <tr><td>Payment Method</td><td>${d.payment_method}</td></tr>
                        <tr><td>Activation Code</td><td>${d.activationCode.code}</td></tr>
                        <tr><td>Code Generated By</td><td>${d.activationCode.generated_by}</td></tr>
                        <tr><td>Referred By</td><td><a href="/users/info/${d.referBy.id}">${d.referBy.username}</a></td></tr>
                        <tr class="bg-light-danger"><td colspan="2" align="center">Payment Information</td></tr>
                        <tr><td>Transferred (PKR)</td><td>${d.transferred_amount}</td></tr>
                        <tr><td>Total USDT Paid</td><td>${d.converted_usdt_amount}</td></tr>
                        <tr><td>Fee Deducted</td><td>${d.fee_deducted}</td></tr>
                        <tr><td>Net Invested USDT</td><td>${d.net_invested_usdt_amount}</td></tr>
                        <tr><td>USDT Rate</td><td>${d.usdt_rate}</td></tr>
                        <tr><td>Created At</td><td>${d.created_at}</td></tr>
                    </table>`;
                    if (d.amount_proof) {
                        html += `<table class='table table-bordered'>
                            <tr><td>Amount Proof</td></tr>
                            <tr><td><img src="${d.amount_proof}" class="img img-thumbnail" style="max-width:100%;height:auto;"/></td></tr>
                        </table>`;
                    }
                    $('#user-details-content').html(html);
                } else {
                    $('#user-details-content').html('<p>Error fetching user details.</p>');
                }
            },
            error: function () {
                $('#loading-spinner').hide();
                $('#user-details-content').html('<p>Unable to fetch user details.</p>');
            }
        });
    });
});
</script>
@endsection
