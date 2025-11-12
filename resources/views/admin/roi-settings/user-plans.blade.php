@extends('demo.layout.app')
@section('title', 'User Plan Assignment - VIP & Standard')

@push('styles')
<style>
    /* Pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        border-radius: 0.42rem;
        margin: 0 0.25rem;
        border: 1px solid #E4E6EF;
        color: #7E8299;
        font-weight: 500;
    }
    .pagination .page-item.active .page-link {
        background-color: #3699FF;
        border-color: #3699FF;
        color: #ffffff;
    }
    .pagination .page-link:hover {
        background-color: #F3F6F9;
        color: #3699FF;
    }
    .pagination .page-item.disabled .page-link {
        background-color: #F3F6F9;
        border-color: #E4E6EF;
        color: #B5B5C3;
    }
</style>
@endpush

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">User Plan Assignment</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.roi-settings.index') }}" class="text-muted">ROI Settings</a>
                        </li>
                        <li class="breadcrumb-item text-muted">User Plans</li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="mr-2 btn btn-success font-weight-bolder" data-toggle="modal" data-target="#bulkMigrateModal">
                    <i class="la la-sync"></i> Auto-Migrate All Users
                </button>
                <button type="button" class="mr-2 btn btn-primary font-weight-bolder" data-toggle="modal" data-target="#assignAllUsersModal">
                    <i class="la la-users"></i> Assign Plan to All Users
                </button>
                <a href="{{ route('admin.roi-settings.index') }}" class="btn btn-light font-weight-bolder">
                    <i class="la la-arrow-left"></i> Back to ROI Settings
                </a>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="flaticon2-check-mark"></i></div>
                    <div class="alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="flaticon-warning"></i></div>
                    <div class="alert-text">{{ session('error') }}</div>
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Stats -->
            <div class="mb-6 row">
                <div class="col-xl-4">
                    <div class="card card-custom bg-light-info">
                        <div class="card-body">
                            <div class="mt-2 text-info font-weight-bold font-size-h2">
                                {{ $totalUsers }}
                            </div>
                            <div class="mt-2 font-weight-bold text-muted">Total Users with Investment</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card card-custom bg-light-primary">
                        <div class="card-body">
                            <div class="mt-2 text-primary font-weight-bold font-size-h2">
                                {{ $standardCount }}
                            </div>
                            <div class="mt-2 font-weight-bold text-muted">Standard Plan Users</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card card-custom bg-light-warning">
                        <div class="card-body">
                            <div class="mt-2 text-warning font-weight-bold font-size-h2">
                                {{ $vipCount }}
                            </div>
                            <div class="mt-2 font-weight-bold text-muted">VIP Plan Users</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Alert -->
            <div class="mb-6 alert alert-custom alert-light-info">
                <div class="alert-icon"><i class="flaticon-information"></i></div>
                <div class="alert-text">
                    <strong>Bulk Migration:</strong> Click "Auto-Migrate All Users" to automatically assign packages to all users based on their investment amount and the configured package ranges in settings.
                    Users with investment below the VIP threshold will be assigned Standard, and those at or above will be assigned VIP.
                </div>
            </div>

            <!-- User List -->
            <div class="card card-custom">
                <div class="py-5 border-0 card-header">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label font-weight-bolder text-dark">User Plans</span>
                        <span class="mt-3 text-muted font-weight-bold font-size-sm">Assign VIP or Standard plan to users</span>
                    </h3>
                    <div class="card-toolbar">
                        <div id="bulkActions" style="display:none;">
                            <span class="mr-3 text-muted"><span id="selectedCount">0</span> users selected</span>
                            <button type="button" class="mr-2 btn btn-sm btn-light-primary font-weight-bold" onclick="assignSelectedUsers('standard')">
                                <i class="la la-user"></i> Set Standard
                            </button>
                            <button type="button" class="btn btn-sm btn-light-warning font-weight-bold" onclick="assignSelectedUsers('vip')">
                                <i class="la la-star"></i> Set VIP
                            </button>
                        </div>
                    </div>
                </div>
                <div class="pt-0 pb-3 card-body">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center table-head-bg table-borderless">
                            <thead>
                                <tr class="text-left text-uppercase">
                                    <th style="min-width: 40px">
                                        <label class="checkbox checkbox-single">
                                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"/>
                                            <span></span>
                                        </label>
                                    </th>
                                    <th style="min-width: 50px"><span class="text-dark-75">ID</span></th>
                                    <th style="min-width: 200px"><span class="text-dark-75">User</span></th>
                                    <th style="min-width: 150px"><span class="text-dark-75">Email</span></th>
                                    <th style="min-width: 120px"><span class="text-dark-75">Investment</span></th>
                                    <th style="min-width: 120px" class="text-center"><span class="text-dark-75">Current Plan</span></th>
                                    <th style="min-width: 150px" class="text-center"><span class="text-dark-75">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>
                                        <label class="checkbox checkbox-single">
                                            <input type="checkbox" class="user-checkbox" value="{{ $user->id }}" onchange="updateBulkActions()"/>
                                            <span></span>
                                        </label>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">
                                            #{{ $user->id }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 symbol symbol-40 symbol-light-success">
                                                <span class="symbol-label font-size-h4 font-weight-bold">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <span class="text-dark-75 font-weight-bolder d-block font-size-lg">
                                                    {{ $user->name }}
                                                </span>
                                                <span class="text-muted font-weight-bold">
                                                    @ {{ $user->username }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted font-weight-500">
                                            {{ $user->email }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark-75 font-weight-bolder d-block font-size-lg">
                                            ${{ number_format($user->roi_eligible_investment_amount, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($user->user_plan === 'vip')
                                            <span class="label label-lg label-light-warning label-inline font-weight-bold">
                                                <i class="la la-star"></i> VIP
                                            </span>
                                        @else
                                            <span class="label label-lg label-light-primary label-inline font-weight-bold">
                                                <i class="la la-user"></i> Standard
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            @if($user->user_plan !== 'standard')
                                                <button type="button"
                                                        class="btn btn-sm btn-light-primary font-weight-bold"
                                                        onclick="updateUserPlan({{ $user->id }}, 'standard', '{{ $user->name }}')">
                                                    <i class="la la-user"></i> Set Standard
                                                </button>
                                            @endif
                                            @if($user->user_plan !== 'vip')
                                                <button type="button"
                                                        class="btn btn-sm btn-light-warning font-weight-bold"
                                                        onclick="updateUserPlan({{ $user->id }}, 'vip', '{{ $user->name }}')">
                                                    <i class="la la-star"></i> Set VIP
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center">
                                        <div class="text-muted font-weight-bold font-size-h5">
                                            <i class="la la-info-circle"></i> No users with investments found
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center flex-wrap pt-5">
                            <div class="d-flex flex-wrap py-2 mr-3">
                                <span class="text-muted font-weight-bold">
                                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                                </span>
                            </div>
                            <div class="d-flex flex-wrap">
                                {{ $users->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Plan Form (Hidden) -->
<form id="updatePlanForm" action="{{ route('admin.roi-settings.update-user-plan') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="user_id" id="update_user_id">
    <input type="hidden" name="user_plan" id="update_user_plan">
</form>

<!-- Bulk Update Selected Users Form (Hidden) -->
<form id="bulkUpdateForm" action="{{ route('admin.roi-settings.bulk-assign-selected') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="user_ids" id="bulk_user_ids">
    <input type="hidden" name="plan" id="bulk_plan">
</form>

<!-- Assign Plan to All Users Modal -->
<div class="modal fade" id="assignAllUsersModal" tabindex="-1" role="dialog" aria-labelledby="assignAllUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignAllUsersModalLabel">
                    <i class="la la-users text-primary"></i> Assign Plan to All Users
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.roi-settings.assign-all-users') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <div class="alert-text">
                            <strong>Warning:</strong> This action will assign the selected plan to ALL users with investments, regardless of their investment amount.
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Select Plan to Assign:</label>
                        <div class="radio-list">
                            <label class="radio radio-lg">
                                <input type="radio" name="plan" value="standard" checked/>
                                <span></span>
                                <span class="ml-2">
                                    <i class="la la-user text-primary"></i> Standard Plan
                                </span>
                            </label>
                            <label class="radio radio-lg">
                                <input type="radio" name="plan" value="vip"/>
                                <span></span>
                                <span class="ml-2">
                                    <i class="la la-star text-warning"></i> VIP Plan
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <div class="alert-text">
                            <i class="la la-info-circle"></i> This will override all existing plan assignments.
                            Total users to be updated: <strong>{{ $totalUsers }}</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">
                        <i class="la la-close"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="la la-check"></i> Assign Plan to All
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Migrate Modal -->
<div class="modal fade" id="bulkMigrateModal" tabindex="-1" role="dialog" aria-labelledby="bulkMigrateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkMigrateModalLabel">
                    <i class="la la-sync text-success"></i> Auto-Migrate All Users
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <div class="alert-text">
                        <strong>Warning:</strong> This action will update the package assignment for all users based on their current investment amount.
                    </div>
                </div>

                <h6 class="mb-3 font-weight-bold">Current Package Range Settings:</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Package</th>
                                <th>Investment Range</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="label label-primary">Standard</span></td>
                                <td>${{ number_format(\App\Models\Setting::first()->standard_package_min ?? 50, 2) }} - ${{ number_format(\App\Models\Setting::first()->standard_package_max ?? 344, 2) }}</td>
                            </tr>
                            <tr>
                                <td><span class="label label-warning">VIP</span></td>
                                <td>${{ number_format(\App\Models\Setting::first()->vip_package_min ?? 345, 2) }} and above</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 alert alert-info">
                    <div class="alert-text">
                        <i class="la la-info-circle"></i> Users will be automatically assigned packages based on their <strong>roi_eligible_investment_amount</strong>.
                        <br>This migration will process all users with investment amounts.
                    </div>
                </div>

                <p class="mt-4 text-muted">
                    <strong>Note:</strong> This action is reversible. You can always manually change individual user plans after migration.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-danger font-weight-bold" data-dismiss="modal">
                    <i class="la la-close"></i> Cancel
                </button>
                <form action="{{ route('admin.roi-settings.bulk-migrate-users') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success font-weight-bold">
                        <i class="la la-sync"></i> Yes, Migrate All Users
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page_js')
<script>
function updateUserPlan(userId, plan, userName) {
    const planLabel = plan === 'vip' ? 'VIP' : 'Standard';
    const confirmMessage = `Are you sure you want to assign ${planLabel} plan to ${userName}?`;

    if (confirm(confirmMessage)) {
        document.getElementById('update_user_id').value = userId;
        document.getElementById('update_user_plan').value = plan;
        document.getElementById('updatePlanForm').submit();
    }
}

// Toggle select all checkboxes
function toggleSelectAll(checkbox) {
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    userCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

// Update bulk actions visibility and count
function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const count = checkedBoxes.length;
    const bulkActionsDiv = document.getElementById('bulkActions');
    const selectedCountSpan = document.getElementById('selectedCount');

    if (count > 0) {
        bulkActionsDiv.style.display = 'block';
        selectedCountSpan.textContent = count;
    } else {
        bulkActionsDiv.style.display = 'none';
        document.getElementById('selectAll').checked = false;
    }
}

// Assign plan to selected users
function assignSelectedUsers(plan) {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (userIds.length === 0) {
        alert('Please select at least one user');
        return;
    }

    const planLabel = plan === 'vip' ? 'VIP' : 'Standard';
    const confirmMessage = `Are you sure you want to assign ${planLabel} plan to ${userIds.length} selected user(s)?`;

    if (confirm(confirmMessage)) {
        document.getElementById('bulk_user_ids').value = JSON.stringify(userIds);
        document.getElementById('bulk_plan').value = plan;
        document.getElementById('bulkUpdateForm').submit();
    }
}
</script>
@endsection
