@extends('demo.layout.app')

@section('title', 'User Reward Review - Admin')

@section('content')
<div class="container-xxl">
    <!--begin::Page title-->
    <div class="flex-wrap mb-5 page-title d-flex flex-column justify-content-center me-3">
        <!--begin::Title-->
        <h1 class="my-0 text-gray-900 page-heading d-flex fw-bold fs-3 flex-column justify-content-center">
            Reward Review: {{ $user->name }}
            <small class="text-muted fs-6 fw-normal ms-1">ID: {{ $user->id }} • {{ $user->email }}</small>
        </h1>
        <!--end::Title-->
        <!--begin::Breadcrumb-->
        <ul class="pt-1 breadcrumb breadcrumb-separatorless fw-semibold fs-7">
            <li class="breadcrumb-item text-muted">
                <a href="{{ route('admin.reward-review.index') }}" class="text-muted text-hover-primary">Reward Review</a>
            </li>
            <li class="breadcrumb-item">
                <span class="bg-gray-500 bullet w-5px h-2px"></span>
            </li>
            <li class="breadcrumb-item text-muted">User Details</li>
        </ul>
        <!--end::Breadcrumb-->
    </div>
    <!--end::Page title-->

    <!--begin::Issues Alert-->
    @if(count($issues) > 0)
    <div class="p-5 mb-10 alert alert-danger d-flex align-items-center">
        <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-danger">{{ count($issues) }} Issue(s) Detected</h4>
            <span>This user's reward assignments may need review and potential correction.</span>
        </div>
    </div>
    @endif
    <!--end::Issues Alert-->

    <div class="row g-5 g-xl-8">
        <!--begin::User Info-->
        <div class="col-xl-4">
            <div class="mb-5 card card-xl-stretch">
                <div class="mt-4 border-0 card-header align-items-center">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="mb-2 text-gray-900 fw-bold">User Information</span>
                    </h3>
                </div>
                <div class="pt-5 card-body">
                    <div class="pb-5 d-flex flex-column">
                        <div class="mb-3 d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <div class="text-gray-800 fs-6 fw-bold">{{ $user->name }}</div>
                                <div class="text-muted">{{ $user->email }}</div>
                            </div>
                        </div>
                        
                        <div class="my-3 separator separator-dashed"></div>
                        
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">User ID:</span>
                            <span class="fw-bold">{{ $user->id }}</span>
                        </div>
                        
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Status:</span>
                            <span class="badge badge-{{ $user->blocked ? 'danger' : 'success' }}">
                                {{ $user->blocked ? 'Blocked' : 'Active' }}
                            </span>
                        </div>
                        
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Can Login:</span>
                            <span class="badge badge-{{ $user->can_login ? 'success' : 'warning' }}">
                                {{ $user->can_login ? 'Yes' : 'No' }}
                            </span>
                        </div>
                        
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Sponsor ID:</span>
                            <span class="fw-bold">{{ $user->sponsor_id ?? 'None' }}</span>
                        </div>
                        
                        <div class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Joined:</span>
                            <span class="text-muted">{{ $user->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::User Info-->
        
        <!--begin::Reward Analysis-->
        <div class="col-xl-8">
            <div class="mb-5 card card-xl-stretch">
                <div class="mt-4 border-0 card-header align-items-center">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="mb-2 text-gray-900 fw-bold">Reward & Team Analysis</span>
                    </h3>
                </div>
                <div class="pt-5 card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th>Level</th>
                                    <th>Required Team</th>
                                    <th>Current Team</th>
                                    <th>Has Reward</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @foreach($teamAnalysis as $level => $analysis)
                                <tr class="{{ !$analysis['meets_requirement'] && $analysis['has_reward'] ? 'bg-light-danger' : '' }}">
                                    <td>
                                        <span class="fw-bold">Level {{ $analysis['level'] }}</span>
                                    </td>
                                    <td>{{ $analysis['required_count'] }}</td>
                                    <td>
                                        <span class="{{ $analysis['meets_requirement'] ? 'text-success' : 'text-danger' }} fw-bold">
                                            {{ $analysis['current_count'] }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($analysis['has_reward'])
                                        <span class="badge badge-success">✓ Yes</span>
                                        @else
                                        <span class="badge badge-light-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($analysis['has_reward'] && !$analysis['meets_requirement'])
                                        <span class="badge badge-danger">⚠️ Over-rewarded</span>
                                        @elseif(!$analysis['has_reward'] && $analysis['meets_requirement'])
                                        <span class="badge badge-warning">Missing Reward</span>
                                        @elseif($analysis['has_reward'] && $analysis['meets_requirement'])
                                        <span class="badge badge-success">✓ Correct</span>
                                        @else
                                        <span class="badge badge-light">Not Eligible</span>
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
        <!--end::Reward Analysis-->
    </div>

    <!--begin::Current Rewards-->
    <div class="mb-5 card">
        <div class="mt-4 border-0 card-header align-items-center">
            <h3 class="card-title align-items-start flex-column">
                <span class="mb-2 text-gray-900 fw-bold">Current Reward Assignments</span>
                <span class="mt-1 text-muted fw-semibold fs-7">Rewards currently in user's wallet</span>
            </h3>
        </div>
        <div class="pt-5 card-body">
            @if($rewardWallets->count() > 0)
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Level</th>
                            <th>Reward Amount</th>
                            <th>Received Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($rewardWallets as $wallet)
                        <tr>
                            <td>
                                <span class="fw-bold">Level {{ $wallet->level }}</span>
                            </td>
                            <td>
                                <span class="text-success fw-bold">${{ number_format($wallet->balance) }}</span>
                            </td>
                            <td>{{ $wallet->created_at->format('M j, Y g:i A') }}</td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" 
                                        onclick="showReverseModal({{ $user->id }}, {{ $wallet->level }}, {{ $wallet->balance }})">
                                    Reverse Reward
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="py-10 text-center">
                <div class="text-gray-400">No reward assignments found for this user</div>
            </div>
            @endif
        </div>
    </div>
    <!--end::Current Rewards-->

    <!--begin::Pending Rewards-->
    @if($pendingRewards->count() > 0)
    <div class="mb-5 card">
        <div class="mt-4 border-0 card-header align-items-center">
            <h3 class="card-title align-items-start flex-column">
                <span class="mb-2 text-gray-900 fw-bold">Pending Rewards</span>
                <span class="mt-1 text-muted fw-semibold fs-7">Rewards awaiting approval</span>
            </h3>
        </div>
        <div class="pt-5 card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Level</th>
                            <th>Reward Amount</th>
                            <th>Status</th>
                            <th>Submitted Date</th>
                            <th>Approved By</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($pendingRewards as $pending)
                        <tr>
                            <td>
                                <span class="fw-bold">Level {{ $pending->level }}</span>
                            </td>
                            <td>
                                <span class="text-primary fw-bold">${{ number_format($pending->reward_amount) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $pending->status === 'approved' ? 'success' : ($pending->status === 'denied' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($pending->status) }}
                                </span>
                            </td>
                            <td>{{ $pending->created_at->format('M j, Y g:i A') }}</td>
                            <td>
                                @if($pending->approvedBy)
                                {{ $pending->approvedBy->name }}
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    <!--end::Pending Rewards-->

    <!--begin::Issues Found-->
    @if(count($issues) > 0)
    <div class="mb-5 card">
        <div class="mt-4 border-0 card-header align-items-center">
            <h3 class="card-title align-items-start flex-column">
                <span class="mb-2 fw-bold text-danger">Issues Detected</span>
                <span class="mt-1 text-muted fw-semibold fs-7">Potential problems with reward assignments</span>
            </h3>
        </div>
        <div class="pt-5 card-body">
            @foreach($issues as $issue)
            <div class="alert alert-{{ $issue['severity'] === 'high' ? 'danger' : 'warning' }} d-flex align-items-center p-5 mb-5">
                <i class="ki-duotone ki-information-5 fs-2hx text-{{ $issue['severity'] === 'high' ? 'danger' : 'warning' }} me-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="d-flex flex-column">
                    <h4 class="mb-1 text-{{ $issue['severity'] === 'high' ? 'danger' : 'warning' }}">
                        {{ ucfirst(str_replace('_', ' ', $issue['type'])) }}
                    </h4>
                    <span>{{ $issue['message'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    <!--end::Issues Found-->

    <!--begin::Transaction History-->
    @if($rewardTransactions->count() > 0)
    <div class="mb-5 card">
        <div class="mt-4 border-0 card-header align-items-center">
            <h3 class="card-title align-items-start flex-column">
                <span class="mb-2 text-gray-900 fw-bold">Reward Transaction History</span>
                <span class="mt-1 text-muted fw-semibold fs-7">Complete audit trail of all reward activities</span>
            </h3>
        </div>
        <div class="pt-5 card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Level</th>
                            <th>Amount</th>
                            <th>Balance Change</th>
                            <th>Processed By</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @foreach($rewardTransactions as $transaction)
                        <tr class="{{ $transaction->transaction_type === 'reward_reversed' ? 'bg-light-danger' : 'bg-light-success' }}">
                            <td>
                                <span class="fw-bold font-monospace">{{ $transaction->reference_number }}</span>
                            </td>
                            <td>{{ $transaction->created_at->format('M j, Y g:i A') }}</td>
                            <td>
                                <span class="badge badge-{{ $transaction->status_color }}">
                                    {{ $transaction->formatted_type }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold">Level {{ $transaction->level }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-{{ $transaction->transaction_type === 'reward_reversed' ? 'danger' : 'success' }}">
                                    {{ $transaction->transaction_type === 'reward_reversed' ? '-' : '+' }}${{ number_format($transaction->amount) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <small class="text-muted">From: ${{ number_format($transaction->previous_balance) }}</small>
                                    <small class="text-muted">To: ${{ number_format($transaction->new_balance) }}</small>
                                </div>
                            </td>
                            <td>
                                @if($transaction->processedBy)
                                <div class="d-flex flex-column">
                                    <span>{{ $transaction->processedBy->name }}</span>
                                    <small class="text-muted">{{ $transaction->processedBy->email }}</small>
                                </div>
                                @else
                                <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->reason)
                                <span class="text-wrap" style="max-width: 200px;">{{ Str::limit($transaction->reason, 50) }}</span>
                                @if(strlen($transaction->reason) > 50)
                                <br><small class="cursor-pointer text-primary" onclick="showFullReason('{{ addslashes($transaction->reason) }}')">Show full reason</small>
                                @endif
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    <!--end::Transaction History-->
</div>

<!-- Reverse Reward Modal -->
<div class="modal fade" id="reverseRewardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Reverse Reward Assignment</h2>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <form id="reverseRewardForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action will remove the reward from the user's wallet and cannot be easily undone.
                    </div>
                    
                    <input type="hidden" id="reverse_user_id" name="user_id">
                    <input type="hidden" id="reverse_level" name="level">
                    
                    <div class="mb-3">
                        <label class="form-label">User:</label>
                        <div class="fw-bold">{{ $user->name }} (ID: {{ $user->id }})</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reward Details:</label>
                        <div id="reward_details" class="fw-bold text-danger"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label required">Reason for Reversal:</label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Explain why this reward is being reversed..." required minlength="10" maxlength="500"></textarea>
                        <div class="form-text">Minimum 10 characters required. Be specific about the issue.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reverse Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showReverseModal(userId, level, amount) {
    // Clear previous form data
    document.getElementById('reverseRewardForm').reset();

    // Set form values
    document.getElementById('reverse_user_id').value = userId;
    document.getElementById('reverse_level').value = level;
    document.getElementById('reward_details').innerHTML = `Level ${level} - $${new Intl.NumberFormat().format(amount)}`;

    // Reset submit button state
    const submitBtn = document.querySelector('#reverseRewardForm button[type="submit"]');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Reverse Reward';

    // Show modal using multiple approaches for compatibility
    try {
        // Try jQuery first (most common in Laravel projects)
        if (typeof $ !== 'undefined') {
            $('#reverseRewardModal').modal('show');
        } else {
            // Fallback to Bootstrap 5 method
            const modalElement = document.getElementById('reverseRewardModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        }
    } catch (e) {
        // Final fallback - show modal manually
        const modalElement = document.getElementById('reverseRewardModal');
        modalElement.style.display = 'block';
        modalElement.classList.add('show');
        document.body.classList.add('modal-open');

        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(backdrop);
    }
}

document.getElementById('reverseRewardForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;

    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';

    fetch('{{ route("admin.reward-review.reverse") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal using multiple approaches for compatibility
            try {
                // Try jQuery first (most common in Laravel projects)
                if (typeof $ !== 'undefined') {
                    $('#reverseRewardModal').modal('hide');
                } else {
                    // Fallback to Bootstrap 5 method
                    const modalElement = document.getElementById('reverseRewardModal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modal.hide();
                }
            } catch (e) {
                // Final fallback - just remove modal backdrop and hide modal
                const modalElement = document.getElementById('reverseRewardModal');
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');

                // Remove backdrop if exists
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }

            // Show success message with transaction reference
            let message = data.message;
            if (data.transaction_reference) {
                message += '\nTransaction Reference: ' + data.transaction_reference;
            }
            alert(message);

            // Small delay before reload to ensure modal closes
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert('Error: ' + data.message);
            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Function to show full reason in modal/alert
function showFullReason(reason) {
    alert('Full Reason:\n\n' + reason);
}
</script>
@endsection