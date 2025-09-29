@extends('demo.layout.app')
@section('title', 'ROI Submission Monitoring')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">ROI Submission Monitoring</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">ROI Submission</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <!-- Alert Banner -->
            <div class="mb-4 card bg-light-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger"></i>
                        </div>
                        <div>
                            <h4 class="mb-2 text-danger">⚠️ ROI Submission Alert</h4>
                            <p class="mb-0">The following users did not receive their ROI for the selected date range. Review and take action.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="mb-4 card">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search User</label>
                            <input type="text" name="search" class="form-control" placeholder="Name, username, email, or ID" value="{{ $search }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3>Users Missing ROI ({{ $users->total() }} found)</h3>
                    </div>
                </div>
                <div class="p-0 card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>User Info</th>
                                    <th>Investment</th>
                                    <th>Last ROI Payment</th>
                                    <th>ROI Status</th>
                                    <th>2X Progress</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            <br>
                                            <small class="text-muted"> @ {{ $user->username }}</small>
                                            <br>
                                            <small class="text-muted">ID: {{ $user->id }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>${{ number_format($user->roi_eligible_investment_amount, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            2X Limit: ${{ number_format($user->roi_eligible_investment_amount * 2, 2) }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($user->last_roi_payment_date)
                                            <span class="badge badge-warning">
                                                {{ \Carbon\Carbon::parse($user->last_roi_payment_date)->format('M d, Y') }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($user->last_roi_payment_date)->diffForHumans() }}
                                            </small>
                                        @else
                                            <span class="badge badge-danger">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = $user->roi_status === 'active' ? 'badge-success' : 'badge-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
                                            {{ ucfirst($user->roi_status ?? 'N/A') }}
                                        </span>
                                        @if($user->stop_reason)
                                            <br>
                                            <small class="text-muted">{{ $user->stop_reason }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $totalPaid = $user->roi_stats['total_roi_paid'] ?? 0;
                                            $twoXLimit = ($user->roi_eligible_investment_amount * 2);
                                            $percentage = $twoXLimit > 0 ? ($totalPaid / $twoXLimit) * 100 : 0;
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $percentage >= 100 ? 'bg-danger' : 'bg-success' }}"
                                                style="width: {{ min(100, $percentage) }}%">
                                                {{ number_format($percentage, 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            ${{ number_format($totalPaid, 2) }} / ${{ number_format($twoXLimit, 2) }}
                                        </small>
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-sm btn-success"
                                            onclick="showGenerateModal({{ $user->id }}, '{{ addslashes($user->username) }}', '{{ $dateFrom }}')">
                                            <i class="fas fa-play"></i> Generate ROI
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center">
                                        <i class="mb-3 fas fa-check-circle fa-3x text-success"></i>
                                        <br>
                                        <span class="text-muted">All eligible users have received their ROI for the selected date range</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate ROI Modal -->
<div class="modal fade" id="generateRoiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="generateRoiForm" method="POST">
                @csrf
                <div class="modal-header bg-light-success">
                    <h5 class="modal-title">
                        <i class="fas fa-dollar-sign me-2"></i> Generate ROI Manually
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        You are about to manually generate ROI for user: <strong id="generateUsername"></strong>
                    </div>

                    <div class="form-group">
                        <label>ROI Date</label>
                        <input type="date" name="date" class="form-control" id="roiDate" max="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> This will generate ROI based on the current week percentage and user's investment amount. The system will automatically check 2X limits.
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="confirmGenerate" required>
                        <label class="form-check-label" for="confirmGenerate">
                            I confirm that I want to manually generate ROI for this user
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Generate ROI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('page_js')
<script>
function showGenerateModal(userId, username, defaultDate) {
    document.getElementById('generateUsername').textContent = username;
    document.getElementById('roiDate').value = defaultDate;
    const form = document.getElementById('generateRoiForm');
    form.action = `/roi-submission/generate/${userId}`;
    $('#generateRoiModal').modal('show');
}
</script>
@endsection