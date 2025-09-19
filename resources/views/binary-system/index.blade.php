@extends('demo.layout.app')

@section('title', 'Binary System (2x/7x)')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Binary System Management</h3>
                </div>
                <div class="card-body">
                    <!-- User Rank Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5>Your Current Rank</h5>
                                    <h3>{{ $status['user_rank']['rank_name'] ?? 'No Rank' }}</h3>
                                    <p>Level: {{ $status['user_rank']['current_level'] ?? 0 }}</p>

                                    @if($status['user_rank']['eligible_2x'])
                                        <span class="badge badge-success">✅ 2x Eligible</span>
                                    @else
                                        <span class="badge badge-danger">❌ 2x Not Eligible</span>
                                    @endif

                                    @if($status['user_rank']['eligible_7x'])
                                        <span class="badge badge-success">✅ 7x Eligible</span>
                                    @else
                                        <span class="badge badge-danger">❌ 7x Not Eligible</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5>Total Binary Earnings</h5>
                                    <h3>${{ number_format($status['total_binary_earnings'] ?? 0, 2) }}</h3>
                                    <button class="btn btn-warning btn-sm" onclick="upgradeRank()">
                                        🚀 Check Rank Upgrade
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2x System -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h4>2x Binary System</h4>
                                </div>
                                <div class="card-body">
                                    @if($status['binary_2x'])
                                        <div class="mb-3">
                                            <strong>Current Level:</strong> {{ $status['binary_2x']['current_level'] }}<br>
                                            <strong>Earned:</strong> ${{ number_format($status['binary_2x']['total_earned'], 2) }}<br>
                                            <strong>Limit:</strong> ${{ number_format($status['binary_2x']['current_limit'], 2) }}<br>
                                            <strong>Progress:</strong> {{ number_format($status['binary_2x']['progress_percentage'], 1) }}%
                                        </div>

                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-success"
                                                 style="width: {{ $status['binary_2x']['progress_percentage'] }}%">
                                            </div>
                                        </div>

                                        @if($status['binary_2x']['can_progress'])
                                            <button class="btn btn-success btn-sm"
                                                    onclick="progressLevel('{{ $status['binary_2x']['system_id'] ?? '' }}')">
                                                📈 Progress to Next Level
                                            </button>
                                        @endif
                                    @else
                                        @if($status['user_rank']['eligible_2x'])
                                            <p>You are eligible for 2x system!</p>
                                            <button class="btn btn-primary" onclick="initializeSystem('2x')">
                                                🚀 Start 2x System
                                            </button>
                                        @else
                                            <p class="text-muted">Upgrade to Silver rank or higher to access 2x system.</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 7x System -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-warning text-white">
                                    <h4>7x Binary System</h4>
                                </div>
                                <div class="card-body">
                                    @if($status['binary_7x'])
                                        <div class="mb-3">
                                            <strong>Current Level:</strong> {{ $status['binary_7x']['current_level'] }}<br>
                                            <strong>Earned:</strong> ${{ number_format($status['binary_7x']['total_earned'], 2) }}<br>
                                            <strong>Limit:</strong> ${{ number_format($status['binary_7x']['current_limit'], 2) }}<br>
                                            <strong>Progress:</strong> {{ number_format($status['binary_7x']['progress_percentage'], 1) }}%
                                        </div>

                                        <div class="progress mb-3">
                                            <div class="progress-bar bg-warning"
                                                 style="width: {{ $status['binary_7x']['progress_percentage'] }}%">
                                            </div>
                                        </div>

                                        @if($status['binary_7x']['can_progress'])
                                            <button class="btn btn-warning btn-sm"
                                                    onclick="progressLevel('{{ $status['binary_7x']['system_id'] ?? '' }}')">
                                                📈 Progress to Next Level
                                            </button>
                                        @endif
                                    @else
                                        @if($status['user_rank']['eligible_7x'])
                                            <p>You are eligible for 7x system!</p>
                                            <button class="btn btn-primary" onclick="initializeSystem('7x')">
                                                🚀 Start 7x System
                                            </button>
                                        @else
                                            <p class="text-muted">Upgrade to Gold rank or higher to access 7x system.</p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Level Information Tables -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>2x System Levels</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Level</th>
                                                <th>Investment</th>
                                                <th>Limit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($levels['2x'] as $level => $data)
                                            <tr>
                                                <td>{{ $level }}</td>
                                                <td>${{ number_format($data['investment']) }}</td>
                                                <td>${{ number_format($data['limit']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5>7x System Levels</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Level</th>
                                                <th>Investment</th>
                                                <th>Limit</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($levels['7x'] as $level => $data)
                                            <tr>
                                                <td>{{ $level }}</td>
                                                <td>${{ number_format($data['investment']) }}</td>
                                                <td>${{ number_format($data['limit']) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rank Requirements -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Rank Requirements</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Investment Required</th>
                                                <th>Direct Referrals</th>
                                                <th>Team Size</th>
                                                <th>2x Access</th>
                                                <th>7x Access</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($ranks as $level => $rank)
                                            <tr>
                                                <td>{{ $rank['name'] }}</td>
                                                <td>${{ number_format($rank['investment']) }}</td>
                                                <td>{{ $rank['direct_referrals'] }}</td>
                                                <td>{{ $rank['team_size'] }}</td>
                                                <td>
                                                    @if($rank['eligible_2x'])
                                                        <span class="badge badge-success">✅</span>
                                                    @else
                                                        <span class="badge badge-danger">❌</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($rank['eligible_7x'])
                                                        <span class="badge badge-success">✅</span>
                                                    @else
                                                        <span class="badge badge-danger">❌</span>
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

                    <!-- Utility Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>System Utilities</h5>
                                </div>
                                <div class="card-body">
                                    <button class="btn btn-info me-2" onclick="fixOnlineIncome()">
                                        🔧 Fix Online Income Issues
                                    </button>
                                    <button class="btn btn-secondary" onclick="location.reload()">
                                        🔄 Refresh Status
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Initialize System Modal -->
<div class="modal fade" id="initializeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Initialize Binary System</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="initializeForm">
                    <input type="hidden" id="systemType" name="system_type">
                    <div class="mb-3">
                        <label for="investmentAmount" class="form-label">Investment Amount ($)</label>
                        <input type="number" class="form-control" id="investmentAmount"
                               name="investment_amount" min="100" step="0.01" required>
                        <div class="form-text">Minimum investment: $100</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitInitialize()">Initialize</button>
            </div>
        </div>
    </div>
</div>

<script>
function initializeSystem(type) {
    document.getElementById('systemType').value = type;
    document.getElementById('investmentAmount').value = type === '2x' ? 100 : 100;
    new bootstrap.Modal(document.getElementById('initializeModal')).show();
}

function submitInitialize() {
    const form = document.getElementById('initializeForm');
    const formData = new FormData(form);

    fetch('{{ route("binary-system.initialize") }}', {
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

    bootstrap.Modal.getInstance(document.getElementById('initializeModal')).hide();
}

function progressLevel(systemId) {
    if (!confirm('Are you sure you want to progress to the next level?')) return;

    fetch('{{ route("binary-system.progress") }}', {
        method: 'POST',
        body: JSON.stringify({system_id: systemId}),
        headers: {
            'Content-Type': 'application/json',
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
    });
}

function upgradeRank() {
    fetch('{{ route("binary-system.upgrade-rank") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('🎉 ' + data.message);
            location.reload();
        } else {
            alert('ℹ️ ' + data.message);
        }
    });
}

function fixOnlineIncome() {
    if (!confirm('This will disconnect online income from binary systems. Continue?')) return;

    fetch('{{ route("binary-system.fix-online-income") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.success ? '✅ ' + data.message : '❌ ' + data.message);
        if (data.success) location.reload();
    });
}
</script>
@endsection