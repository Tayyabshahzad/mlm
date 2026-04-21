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
                                                            <span class="badge badge-light-primary ml-1" style="font-size:0.7rem;">Enrolled</span>
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
                        var netCredited = Math.max(0, parseFloat((totalPaid - fee).toFixed(2)));

                        var inst1Due      = d.signup_instalment ? parseFloat(d.signup_instalment.amount || 0) : 0;
                        var inst1Status   = d.signup_instalment ? d.signup_instalment.status : 'pending';
                        var inst1TxId     = (d.saving_transaction_id) || (d.signup_instalment && d.signup_instalment.transaction_id) || '—';
                        var inst1DueDate  = d.signup_instalment ? d.signup_instalment.due_date : '—';
                        var stillOwed     = Math.max(0, inst1Due - netCredited);

                        var statusMap = {
                            confirmed: '<span class="badge badge-success">Confirmed</span>',
                            submitted: '<span class="badge badge-warning">Awaiting Admin Confirmation</span>',
                            missed:    '<span class="badge badge-danger">Missed</span>',
                            pending:   '<span class="badge badge-secondary">Pending</span>',
                        };
                        var inst1Badge = statusMap[inst1Status] || '<span class="badge badge-secondary">' + inst1Status + '</span>';

                        html += '<table class="table table-bordered table-sm">'
                            + '<tr class="bg-light-success"><td colspan="2" class="text-center font-weight-bold">Saving Plan — Signup Payment</td></tr>'
                            + '<tr><td width="50%"><strong>Instalment #1 Total Due</strong></td>'
                            +     '<td><strong>$' + inst1Due.toFixed(2) + '</strong> &nbsp;' + inst1Badge + '</td></tr>'
                            + '<tr><td>Amount Paid at Signup</td>'
                            +     '<td class="text-primary font-weight-bold">$' + totalPaid.toFixed(2) + '</td></tr>'
                            + '<tr><td>Registration Fee (deducted)</td>'
                            +     '<td class="text-danger">− $' + fee.toFixed(2) + '</td></tr>'
                            + '<tr><td>Net Credited Toward Saving</td>'
                            +     '<td class="text-success font-weight-bold">$' + netCredited.toFixed(2) + '</td></tr>'
                            + '<tr><td>Still Owed for Instalment #1</td>'
                            +     '<td class="' + (stillOwed > 0 ? 'text-warning font-weight-bold' : 'text-success') + '">'
                            +     (stillOwed > 0 ? '$' + stillOwed.toFixed(2) : '<span class="badge badge-success">Fully Covered</span>')
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
                            html += '<div class="alert alert-warning mt-2">No payment proof uploaded.</div>';
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
                            html += '<div class="alert alert-warning mt-2">No transaction proof uploaded.</div>';
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
</script>
@endsection
