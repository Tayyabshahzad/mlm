@extends('demo.layout.app')
@section('title','Reward Details')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Reward Details</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.pending-rewards.index') }}" class="text-muted">Pending Rewards</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Details</li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.pending-rewards.index') }}" class="mr-2 btn btn-light-primary font-weight-bolder">
                    <i class="la la-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <!-- Basic Info -->
                <div class="col-lg-4">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h3 class="card-title">Reward Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-7">
                                <div class="mr-5 symbol symbol-45 symbol-light-primary">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-trophy text-primary"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-h4">Level {{ $pendingReward->level }}</div>
                                    <div class="text-muted font-weight-bold">Reward Level</div>
                                </div>
                            </div>

                            <div class="my-5 separator separator-dashed"></div>

                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">Reward Amount:</span>
                                <span class="font-weight-bold text-success">${{ number_format($pendingReward->reward_amount, 2) }}</span>
                            </div>
                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">Team Count:</span>
                                <span class="font-weight-bold">{{ $pendingReward->team_count }}</span>
                            </div>
                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">Required Count:</span>
                                <span class="text-muted">{{ $pendingReward->users_required }}</span>
                            </div>
                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">Status:</span>
                                <span class="label label-{{ $pendingReward->status === 'pending' ? 'warning' : ($pendingReward->status === 'approved' ? 'success' : 'danger') }}">
                                    {{ ucfirst($pendingReward->status) }}
                                </span>
                            </div>
                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">Created:</span>
                                <span class="text-muted">{{ $pendingReward->created_at->format('M j, Y g:i A') }}</span>
                            </div>

                            @if($pendingReward->status === 'pending')
                                <div class="my-5 separator separator-dashed"></div>
                                <div class="d-flex justify-content-between">
                                    <button class="mr-2 btn btn-success" onclick="approveReward({{ $pendingReward->id }})">
                                        <i class="la la-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger" onclick="denyReward({{ $pendingReward->id }})">
                                        <i class="la la-times"></i> Deny
                                    </button>
                                </div>
                            @elseif($pendingReward->approved_by)
                                <div class="my-5 separator separator-dashed"></div>
                                <div class="mb-2 d-flex justify-content-between">
                                    <span class="text-dark-75">Processed by:</span>
                                    <span class="text-muted">{{ $pendingReward->approvedBy->name ?? 'N/A' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-dark-75">Processed at:</span>
                                    <span class="text-muted">{{ $pendingReward->approved_at?->format('M j, Y g:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="col-lg-4">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h3 class="card-title">User Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-7">
                                <div class="mr-5 symbol symbol-45 symbol-light-success">
                                    <div class="symbol-label">
                                        <i class="icon-xl la la-user text-success"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="text-dark-75 font-weight-bold font-size-lg">{{ $pendingReward->user->name }}</div>
                                    <div class="text-muted font-weight-bold">{{ $pendingReward->user->email }}</div>
                                </div>
                            </div>

                            <div class="my-5 separator separator-dashed"></div>

                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">User ID:</span>
                                <span class="font-weight-bold">{{ $pendingReward->user_id }}</span>
                            </div>

                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">Username:</span>
                                <span class="font-weight-bold">{{ $pendingReward->user->username }}</span>
                            </div>

                            
                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">Status:</span>
                                <span class="label label-{{ $pendingReward->user->blocked ? 'danger' : 'success' }}">
                                    {{ $pendingReward->user->blocked ? 'Blocked' : 'Active' }}
                                </span>
                            </div>
                            <div class="mb-4 d-flex justify-content-between">
                                <span class="text-dark-75">Can Login:</span>
                                <span class="label label-{{ $pendingReward->user->can_login ? 'success' : 'danger' }}">
                                    {{ $pendingReward->user->can_login ? 'Yes' : 'No' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-dark-75">Joined:</span>
                                <span class="text-muted">{{ $pendingReward->user->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Verification -->
                <div class="col-lg-4">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h3 class="card-title">Current Verification</h3>
                            <div class="card-toolbar">
                                <button class="btn btn-sm btn-light-primary" onclick="reverifyEligibility()">
                                    <i class="la la-refresh"></i> Re-verify
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="verification-content">
                            @if(isset($pendingReward->current_verification))
                                <div class="d-flex align-items-center mb-7">
                                    <div class="symbol symbol-45 symbol-light-{{ $pendingReward->current_verification['still_eligible'] ? 'success' : 'danger' }} mr-5">
                                        <div class="symbol-label">
                                            <i class="icon-xl la la-{{ $pendingReward->current_verification['still_eligible'] ? 'check' : 'times' }} text-{{ $pendingReward->current_verification['still_eligible'] ? 'success' : 'danger' }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="text-dark-75 font-weight-bold font-size-lg">
                                            {{ $pendingReward->current_verification['still_eligible'] ? 'Still Eligible' : 'Not Eligible' }}
                                        </div>
                                        <div class="text-muted font-weight-bold">Current Status</div>
                                    </div>
                                </div>

                                <div class="my-5 separator separator-dashed"></div>

                                <div class="mb-4 d-flex justify-content-between">
                                    <span class="text-dark-75">Current Team Count:</span>
                                    <span class="font-weight-bold">{{ $pendingReward->current_verification['current_team_count'] }}</span>
                                </div>
                                <div class="mb-4 d-flex justify-content-between">
                                    <span class="text-dark-75">Original Team Count:</span>
                                    <span class="text-muted">{{ $pendingReward->team_count }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-dark-75">Verified At:</span>
                                    <span class="text-sm text-muted">{{ \Carbon\Carbon::parse($pendingReward->current_verification['verified_at'])->format('M j, Y g:i A') }}</span>
                                </div>
                            @else
                                <div class="py-4 text-center">
                                    <span class="text-muted">No verification data available</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Breakdown -->
            @if(isset($pendingReward->current_verification['team_breakdown']))
                <div class="mt-5 row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header">
                                <h3 class="card-title">Team Breakdown by Level</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-head-custom table-vertical-center">
                                        <thead>
                                            <tr>
                                                <th>Level</th>
                                                <th>Current Count</th>
                                                <th>Required for Reward</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingReward->current_verification['team_breakdown'] as $level => $data)
                                                <tr>
                                                    <td>{{ str_replace('level_', '', $level) }}</td>
                                                    <td class="font-weight-bold">{{ $data['specific_level_count'] }}</td>
                                                    <td class="text-muted">{{ $data['required_for_reward'] }}</td>
                                                    <td>
                                                        @if($data['specific_level_count'] >= $data['required_for_reward'])
                                                            <span class="label label-success">Met</span>
                                                        @else
                                                            <span class="label label-warning">{{ $data['required_for_reward'] - $data['specific_level_count'] }} needed</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Original Eligibility Data -->
            @if($pendingReward->eligibility_data)
                <div class="mt-5 row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header">
                                <h3 class="card-title">Original Eligibility Data</h3>
                            </div>
                            <div class="card-body">
                                <pre class="text-sm text-muted">{{ json_encode($pendingReward->eligibility_data, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Admin Notes -->
            @if($pendingReward->admin_notes)
                <div class="mt-5 row">
                    <div class="col-12">
                        <div class="card card-custom">
                            <div class="card-header">
                                <h3 class="card-title">Admin Notes</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-dark-75">{{ $pendingReward->admin_notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
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

@endsection

@section('page_js')
    <script>
        function approveReward(rewardId) {
            const form = document.getElementById('approveForm');
            form.action = `{{ url('/pending-rewards') }}/${rewardId}/approve`;
            $('#approveModal').modal('show');
        }

        function denyReward(rewardId) {
            const form = document.getElementById('denyForm');
            form.action = `{{ url('/pending-rewards') }}/${rewardId}/deny`;
            $('#denyModal').modal('show');
        }

        function reverifyEligibility() {
            // Make AJAX call to re-verify eligibility
            const rewardId = {{ $pendingReward->id }};
            
            fetch(`{{ url('/pending-rewards') }}/${rewardId}/reverify`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update the verification content
                updateVerificationContent(data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to re-verify eligibility. Please try again.');
            });
        }

        function updateVerificationContent(data) {
            const verification = data.current_verification;
            const validation = data.validation;
            
            const content = `
                <div class="d-flex align-items-center mb-7">
                    <div class="symbol symbol-45 symbol-light-${verification.still_eligible ? 'success' : 'danger'} mr-5">
                        <div class="symbol-label">
                            <i class="icon-xl la la-${verification.still_eligible ? 'check' : 'times'} text-${verification.still_eligible ? 'success' : 'danger'}"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-dark-75 font-weight-bold font-size-lg">
                            ${verification.still_eligible ? 'Still Eligible' : 'Not Eligible'}
                        </div>
                        <div class="text-muted font-weight-bold">Current Status</div>
                    </div>
                </div>

                <div class="my-5 separator separator-dashed"></div>

                <div class="mb-4 d-flex justify-content-between">
                    <span class="text-dark-75">Current Team Count:</span>
                    <span class="font-weight-bold">${verification.current_team_count}</span>
                </div>
                <div class="mb-4 d-flex justify-content-between">
                    <span class="text-dark-75">Original Team Count:</span>
                    <span class="text-muted">{{ $pendingReward->team_count }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-dark-75">Verified At:</span>
                    <span class="text-sm text-muted">${new Date(verification.verified_at).toLocaleString()}</span>
                </div>
                
                ${!verification.still_eligible && validation.reasons ? `
                <div class="my-5 separator separator-dashed"></div>
                <div class="alert alert-light-warning">
                    <h6>Ineligibility Reasons:</h6>
                    <ul class="mb-0">
                        ${validation.reasons.map(reason => `<li>${reason}</li>`).join('')}
                    </ul>
                </div>
                ` : ''}
            `;
            
            document.getElementById('verification-content').innerHTML = content;
        }
    </script>
@endsection