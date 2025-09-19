@extends('demo.layout.app')

@section('title', 'Admin - Binary Systems')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>Active 2x Systems</h5>
                            <h3>{{ $stats['total_2x_systems'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5>Active 7x Systems</h5>
                            <h3>{{ $stats['total_7x_systems'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>Completed Systems</h5>
                            <h3>{{ $stats['completed_systems'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5>Total Earnings</h5>
                            <h3>${{ number_format($stats['total_earnings'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Binary Systems Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Binary Systems Management</h3>
                    <div class="card-tools">
                        <button class="btn btn-success btn-sm" onclick="processEarnings()">
                            💰 Process Earnings
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>System Type</th>
                                <th>Level</th>
                                <th>Investment</th>
                                <th>Earned</th>
                                <th>Limit</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($systems as $system)
                            <tr>
                                <td>{{ $system->id }}</td>
                                <td>
                                    <strong>{{ $system->user->name }}</strong><br>
                                    <small class="text-muted">{{ $system->user->username }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $system->system_type === '2x' ? 'success' : 'warning' }}">
                                        {{ $system->system_type }}
                                    </span>
                                </td>
                                <td>{{ $system->current_level }}</td>
                                <td>${{ number_format($system->investment_amount, 2) }}</td>
                                <td>${{ number_format($system->total_earned, 2) }}</td>
                                <td>${{ number_format($system->current_limit, 2) }}</td>
                                <td>
                                    @php
                                        $progress = ($system->total_earned / $system->current_limit) * 100;
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : 'primary' }}"
                                             style="width: {{ min($progress, 100) }}%">
                                            {{ number_format($progress, 1) }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($system->completed_at)
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($system->is_active)
                                        <span class="badge badge-primary">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $system->created_at->format('M d, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.binary-system.show', $system) }}"
                                       class="btn btn-sm btn-info">
                                        👁️ View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $systems->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Process Earnings Modal -->
<div class="modal fade" id="processEarningsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Binary Earnings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="processEarningsForm">
                    <div class="mb-3">
                        <label for="userId" class="form-label">User ID</label>
                        <input type="number" class="form-control" id="userId" name="user_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount ($)</label>
                        <input type="number" class="form-control" id="amount" name="amount"
                               min="0.01" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="source" class="form-label">Source</label>
                        <select class="form-control" id="source" name="source" required>
                            <option value="commission">Commission</option>
                            <option value="bonus">Bonus</option>
                            <option value="referral">Referral</option>
                            <option value="manual">Manual Addition</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitProcessEarnings()">
                    Process Earnings
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function processEarnings() {
    new bootstrap.Modal(document.getElementById('processEarningsModal')).show();
}

function submitProcessEarnings() {
    const form = document.getElementById('processEarningsForm');
    const formData = new FormData(form);

    fetch('{{ route("admin.binary-system.process-earnings") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    });

    bootstrap.Modal.getInstance(document.getElementById('processEarningsModal')).hide();
}
</script>
@endsection