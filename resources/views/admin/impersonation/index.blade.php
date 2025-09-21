@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">👤 User Impersonation (Admin Login as User)</h3>

                    @if(session()->has('impersonating_user_id'))
                        <div class="alert alert-warning mb-0">
                            <strong>⚠️ IMPERSONATION ACTIVE</strong>
                            <form action="{{ route('admin.impersonation.stop') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger ms-2">
                                    <i class="fas fa-stop"></i> Stop Impersonation
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.impersonation.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">🔍 Search Users:</label>
                                    <input type="text"
                                           class="form-control"
                                           id="search"
                                           name="search"
                                           value="{{ request('search') }}"
                                           placeholder="Name, Email, Username, or ID">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status">📊 Status Filter:</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Users</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                            Active Users
                                        </option>
                                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>
                                            Blocked Users
                                        </option>
                                        <option value="cannot_login" {{ request('status') == 'cannot_login' ? 'selected' : '' }}>
                                            Cannot Login
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <a href="{{ route('admin.impersonation.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-refresh"></i> Clear Filters
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Users Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>👤 Name</th>
                                    <th>📧 Email/Username</th>
                                    <th>📊 Status</th>
                                    <th>💰 Investment</th>
                                    <th>🎭 Roles</th>
                                    <th>📅 Joined</th>
                                    <th>🔧 Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <strong>{{ $user->id }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                @if($user->username)
                                                    <br><small class="text-muted">{{ $user->username }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $user->email }}</div>
                                        </td>
                                        <td>
                                            @if($user->blocked)
                                                <span class="badge bg-danger">🚫 Blocked</span>
                                            @elseif(!$user->can_login)
                                                <span class="badge bg-warning">⛔ Cannot Login</span>
                                            @else
                                                <span class="badge bg-success">✅ Active</span>
                                            @endif

                                            @if($user->freez_wallet ?? false)
                                                <br><span class="badge bg-info">🧊 Wallet Frozen</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->roi_eligible_investment_amount)
                                                <strong>${{ number_format($user->roi_eligible_investment_amount, 2) }}</strong>
                                            @else
                                                <span class="text-muted">$0.00</span>
                                            @endif
                                        </td>
                                        <td>
                                            @forelse($user->roles as $role)
                                                <span class="badge bg-primary">{{ $role->name }}</span>
                                            @empty
                                                <span class="text-muted">No roles</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            <small>{{ $user->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            @if(!$user->hasRole('admin') && !$user->hasRole('super-admin'))
                                                <form action="{{ route('admin.impersonation.start', $user->id) }}"
                                                      method="POST"
                                                      style="display: inline;"
                                                      onsubmit="return confirm('Are you sure you want to login as {{ $user->name }}?')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-warning"
                                                            {{ session()->has('impersonating_user_id') ? 'disabled' : '' }}>
                                                        <i class="fas fa-user-secret"></i>
                                                        Login as User
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Cannot impersonate admin</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                                <h5>No users found</h5>
                                                <p class="text-muted">Try adjusting your search criteria</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <p class="text-muted">
                                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }}
                                of {{ $users->total() }} users
                            </p>
                        </div>
                        <div>
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Security Warning Modal -->
@if(session()->has('impersonating_user_id'))
<div class="modal fade" id="impersonationWarning" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">⚠️ Impersonation Active</h5>
            </div>
            <div class="modal-body">
                <p><strong>You are currently logged in as a user!</strong></p>
                <p>All actions will be performed as that user. Be careful with what you do.</p>
                <p>To return to your admin account, click "Stop Impersonation" at any time.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">I Understand</button>
                <form action="{{ route('admin.impersonation.stop') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger">Stop Impersonation Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session()->has('impersonating_user_id'))
        // Show warning modal if impersonating
        var modal = new bootstrap.Modal(document.getElementById('impersonationWarning'));
        modal.show();
    @endif
});
</script>
@endif

@endsection

@push('styles')
<style>
.badge {
    font-size: 0.75rem;
}

.table td {
    vertical-align: middle;
}

.alert-warning {
    border-left: 4px solid #ff9800;
}

.impersonation-header {
    background: linear-gradient(45deg, #ff9800, #ffc107);
    color: white;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
}
</style>
@endpush