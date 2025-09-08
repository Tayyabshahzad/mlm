@extends('demo.layout.app')
@section('title','Pending Rewards Management')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Pending Rewards Management</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Pending Rewards</li>
                    </ul>
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

            <!-- Stats Cards -->
            <div class="row mb-6">
                <div class="col-lg-4">
                    <div class="card card-custom">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45 symbol-light-warning mr-5">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-clock text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-h4">{{ $stats['pending_count'] }}</div>
                                    <div class="text-muted font-weight-bold">Pending Rewards</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-custom">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45 symbol-light-success mr-5">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-check text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-h4">{{ $stats['approved_count'] }}</div>
                                    <div class="text-muted font-weight-bold">Approved Rewards</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-custom">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45 symbol-light-danger mr-5">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-times text-danger"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-h4">{{ $stats['denied_count'] }}</div>
                                    <div class="text-muted font-weight-bold">Denied Rewards</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-6">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title">Filter Rewards</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3">
                            <select class="form-control" id="status-filter" onchange="filterByStatus()">
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="denied" {{ $status === 'denied' ? 'selected' : '' }}>Denied</option>
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Rewards Table -->
            <div class="card">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title">Reward Requests</h3>
                </div>
                <div class="card-body py-3">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Level</th>
                                    <th>Reward Amount</th>
                                    <th>Team Count</th>
                                    <th>Required</th>
                                    <th>Status</th>
                                    <th>Current Eligibility</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingRewards as $reward)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-dark-75 font-weight-bold">{{ $reward->user->name }}</span>
                                                <span class="text-muted">{{ $reward->user->email }}</span>
                                                <small class="text-muted">ID: {{ $reward->user_id }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="label label-lg label-light-primary label-inline">Level {{ $reward->level }}</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-success">${{ number_format($reward->reward_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold">{{ $reward->team_count }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $reward->users_required }}</span>
                                        </td>
                                        <td>
                                            @if($reward->status === 'pending')
                                                <span class="label label-warning">Pending</span>
                                            @elseif($reward->status === 'approved')
                                                <span class="label label-success">Approved</span>
                                            @elseif($reward->status === 'denied')
                                                <span class="label label-danger">Denied</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($reward->current_verification))
                                                @if($reward->current_verification['still_eligible'])
                                                    <span class="label label-light-success">
                                                        <i class="la la-check"></i> Eligible
                                                    </span>
                                                    <div class="text-muted text-sm">
                                                        Current: {{ $reward->current_verification['current_team_count'] }}
                                                    </div>
                                                @else
                                                    <span class="label label-light-danger">
                                                        <i class="la la-times"></i> Not Eligible
                                                    </span>
                                                    <div class="text-muted text-sm">
                                                        Current: {{ $reward->current_verification['current_team_count'] }}
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $reward->created_at->format('M j, Y') }}</span>
                                            <div class="text-muted text-sm">{{ $reward->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td nowrap="nowrap">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.pending-rewards.show', $reward) }}" class="btn btn-sm btn-light">
                                                    <i class="la la-eye"></i> View
                                                </a>
                                                @if($reward->status === 'pending')
                                                    <button class="btn btn-sm btn-success" onclick="approveReward({{ $reward->id }})">
                                                        <i class="la la-check"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="denyReward({{ $reward->id }})">
                                                        <i class="la la-times"></i> Deny
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">No pending rewards found</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($pendingRewards->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $pendingRewards->firstItem() }} to {{ $pendingRewards->lastItem() }} of {{ $pendingRewards->total() }} results
                            </div>
                            {{ $pendingRewards->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Reward</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Admin Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deny Modal -->
<div class="modal fade" id="denyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deny Reward</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="denyForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Reason for Denial (Required)</label>
                        <textarea class="form-control" name="notes" rows="3" placeholder="Please provide a reason for denying this reward..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Deny Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterByStatus() {
    const status = document.getElementById('status-filter').value;
    const url = new URL(window.location);
    url.searchParams.set('status', status);
    window.location.href = url.toString();
}

function approveReward(rewardId) {
    const form = document.getElementById('approveForm');
    form.action = '{{ url("/admin/pending-rewards") }}/' + rewardId + '/approve';
    $('#approveModal').modal('show');
}

function denyReward(rewardId) {
    const form = document.getElementById('denyForm');
    form.action = '{{ url("/admin/pending-rewards") }}/' + rewardId + '/deny';
    $('#denyModal').modal('show');
}
</script>

@endsection