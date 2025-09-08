@extends('demo.layout.app')
@section('title', 'Reward Settings Management')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Reward Settings Management</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('setting.basic') }}" class="text-muted">Settings</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Reward Settings</li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.reward-settings.create') }}" class="btn btn-primary font-weight-bolder mr-2">
                    <i class="la la-plus"></i> Add New Level
                </a>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-toggle="dropdown">
                        <i class="la la-cog"></i> Actions
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="{{ route('admin.reward-settings.export') }}">
                            <i class="la la-download"></i> Export Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-warning" href="#" onclick="confirmReset()">
                            <i class="la la-refresh"></i> Reset to Defaults
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Overview Statistics -->
            <div class="row mb-6">
                <div class="col-lg-3 col-6">
                    <div class="card card-custom">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45 symbol-light-success mr-5">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-layer-group text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-h4">{{ $activelevelsCount }}</div>
                                    <div class="text-muted font-weight-bold">Active Levels</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="card card-custom">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45 symbol-light-primary mr-5">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-dollar-sign text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-h4">${{ number_format($totalRewardsPaid, 0) }}</div>
                                    <div class="text-muted font-weight-bold">Total Paid</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="card card-custom">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45 symbol-light-info mr-5">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-users text-info"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-h4">{{ number_format($totalUsersRewarded) }}</div>
                                    <div class="text-muted font-weight-bold">Users Rewarded</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="card card-custom">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45 symbol-light-warning mr-5">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-clock text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-h4">{{ $totalPendingRewards }}</div>
                                    <div class="text-muted font-weight-bold">Pending Rewards</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reward Levels Table -->
            <div class="card">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title">Reward Levels Configuration</h3>
                </div>
                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Description</th>
                                    <th>Users Required</th>
                                    <th>Reward Amount</th>
                                    <th>Status</th>
                                    <th>Statistics</th>
                                    <th>Performance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rewardLevels as $level)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-dark-75 font-weight-bold font-size-lg">
                                                    Level {{ $level->level }}
                                                </span>
                                                @if(!$level->is_active)
                                                    <span class="text-muted text-sm">Inactive</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark-75">{{ $level->description ?? 'No description' }}</span>
                                        </td>
                                        <td>
                                            <span class="label label-lg label-light-info label-inline">
                                                {{ number_format($level->users_required) }} users
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-success font-weight-bold font-size-lg">
                                                ${{ number_format($level->reward_amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($level->is_active)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-dark-75 font-weight-bold">{{ number_format($level->rewards_paid_count) }} rewarded</span>
                                                <span class="text-muted text-sm">${{ number_format($level->total_amount_paid, 0) }} paid</span>
                                                @if($level->pending_count > 0)
                                                    <span class="text-warning text-sm">{{ $level->pending_count }} pending</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-info font-weight-bold">{{ number_format($level->total_users_eligible) }} eligible</span>
                                                @if($level->total_users_eligible > 0 && $level->rewards_paid_count > 0)
                                                    <span class="text-muted text-sm">
                                                        {{ number_format(($level->rewards_paid_count / $level->total_users_eligible) * 100, 1) }}% conversion
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td nowrap="nowrap">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.reward-settings.edit', $level) }}" 
                                                   class="btn btn-sm btn-light">
                                                    <i class="la la-edit"></i> Edit
                                                </a>
                                                
                                                <button class="btn btn-sm btn-{{ $level->is_active ? 'warning' : 'success' }}" 
                                                        onclick="toggleStatus({{ $level->id }}, {{ $level->is_active ? 'false' : 'true' }})">
                                                    <i class="la la-{{ $level->is_active ? 'pause' : 'play' }}"></i>
                                                    {{ $level->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                                
                                                @if($level->rewards_paid_count == 0 && $level->pending_count == 0)
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="confirmDelete({{ $level->id }}, {{ $level->level }})">
                                                        <i class="la la-trash"></i> Delete
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">No reward levels configured</div>
                                            <a href="{{ route('admin.reward-settings.create') }}" class="btn btn-primary mt-2">
                                                Create First Level
                                            </a>
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

<!-- Reset Confirmation Modal -->
<div class="modal fade" id="resetModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset to Default Settings</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reset all reward settings to default values?</p>
                <p class="text-warning"><strong>Warning:</strong> This will overwrite all current settings!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="{{ route('admin.reward-settings.reset') }}" class="btn btn-warning">
                    Reset to Defaults
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Reward Level</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete Level <span id="deleteLevel"></span>?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Level</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom_js')
<script>
function confirmReset() {
    $('#resetModal').modal('show');
}

function confirmDelete(levelId, levelNumber) {
    $('#deleteLevel').text(levelNumber);
    $('#deleteForm').attr('action', `{{ url('/reward-settings') }}/${levelId}`);
    $('#deleteModal').modal('show');
}

function toggleStatus(levelId, newStatus) {
    if (confirm('Are you sure you want to change the status of this reward level?')) {
        window.location.href = `{{ url('/reward-settings') }}/${levelId}/toggle`;
    }
}
</script>
@endsection