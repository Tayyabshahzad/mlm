@extends('demo.layout.app')
@section('title', 'Staff Roles')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Staff Roles</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Staff Roles</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @if($errors->any())
                <div class="alert alert-light-danger mb-6">
                    @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                </div>
            @endif

            <div class="row">
                <div class="col-lg-5">
                    {{-- Create a new staff login --}}
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder">Create New Staff Account</h3></div>
                        <div class="card-body pt-2">
                            <form method="POST" action="{{ route('admin.office-admins.create') }}" autocomplete="off">
                                @csrf
                                <div class="form-group">
                                    <label class="font-weight-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-6">
                                        <label class="font-weight-bold">Username <span class="text-danger">*</span></label>
                                        <input type="text" name="username" class="form-control" required value="{{ old('username') }}" placeholder="letters, numbers, - _">
                                    </div>
                                    <div class="form-group col-6">
                                        <label class="font-weight-bold">Phone</label>
                                        <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-6">
                                        <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                                    </div>
                                    <div class="form-group col-6">
                                        <label class="font-weight-bold">Confirm <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Role <span class="text-danger">*</span></label>
                                    <select name="role" class="form-control" required>
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}" @selected(old('role') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="btn btn-primary font-weight-bold btn-block">Create Account</button>
                                <small class="text-muted d-block mt-2">The account can log in immediately. It is not part of the MLM: no sponsor, referral link, wallets or instalments.</small>
                            </form>
                        </div>
                    </div>

                    {{-- Give a role to an existing account --}}
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder">Give Role to Existing Account</h3></div>
                        <div class="card-body pt-2">
                            <form method="POST" action="{{ route('admin.office-admins.assign') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="font-weight-bold">Username or Email</label>
                                    <input type="text" name="account" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-bold">Role</label>
                                    <select name="role" class="form-control">
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="btn btn-light-primary font-weight-bold">Give Access</button>
                            </form>
                            <div class="separator separator-dashed my-6"></div>
                            <p class="text-muted small mb-2"><strong>Office Admin</strong> — Bank Reconciliation only: verify bank payments, record payments seen in the bank, reject payments not received.</p>
                            <p class="text-muted small mb-0"><strong>Data Entry Operator</strong> — Upload Payment Sheet only: submits members' next instalments from an Excel sheet; the admin still confirms them.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder">Current Staff</h3></div>
                        <div class="card-body pt-2">
                            <div class="table-responsive">
                            <table class="table table-vertical-center">
                                <thead><tr class="text-muted"><th>Name</th><th>Role</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                                <tbody>
                                @forelse($staff as $u)
                                    @if($u->roles->whereIn('name', array_keys($roles))->isEmpty())
                                        {{-- Staff account whose role was removed: no access until a role is given again --}}
                                        <tr style="background:#fff8f0; border-left:3px solid #ffa800;">
                                            <td>
                                                <span class="font-weight-bold text-dark-75">{{ $u->name }}</span><br>
                                                <span class="text-dark-50 small">@ {{ $u->username }}</span><br>
                                                <span class="text-dark-50 small">{{ $u->email }}</span>
                                            </td>
                                            <td>
                                                <span class="label label-inline label-light-warning font-weight-bold text-nowrap">No role</span>
                                                <div class="small text-dark-50 mt-1 text-nowrap">No access</div>
                                            </td>
                                            <td>
                                                @if($u->can_login)
                                                    <span class="label label-inline label-light-success font-weight-bold">Active</span>
                                                @else
                                                    <span class="label label-inline label-light-danger font-weight-bold">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-inline">
                                                    <button type="button" class="btn btn-sm btn-light-primary font-weight-bold dropdown-toggle"
                                                            data-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">Actions</button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <h6 class="dropdown-header">Restore access</h6>
                                                        @foreach($roles as $key => $label)
                                                            <form method="POST" action="{{ route('admin.office-admins.assign') }}">
                                                                @csrf
                                                                <input type="hidden" name="account" value="{{ $u->username }}">
                                                                <input type="hidden" name="role" value="{{ $key }}">
                                                                <button type="submit" class="dropdown-item"
                                                                        onclick="return confirm('Give {{ $label }} role to {{ $u->name }}? The account will be re-activated.')">
                                                                    <i class="la la-user-check mr-2 text-success"></i> Give {{ $label }} role
                                                                </button>
                                                            </form>
                                                        @endforeach
                                                        <div class="dropdown-divider"></div>
                                                        <button type="button" class="dropdown-item js-change-password"
                                                                data-action="{{ route('admin.office-admins.password', $u) }}"
                                                                data-name="{{ $u->name }} (@ {{ $u->username }})">
                                                            <i class="la la-key mr-2 text-primary"></i> Change Password
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                    @foreach($u->roles->whereIn('name', array_keys($roles)) as $r)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $u->name }}</span><br>
                                                <span class="text-muted small">@ {{ $u->username }}</span><br>
                                                <span class="text-muted small">{{ $u->email }} {{ $u->phone_number ? '· ' . $u->phone_number : '' }}</span>
                                            </td>
                                            <td><span class="label label-inline font-weight-bold text-nowrap {{ $r->name === 'operator' ? 'label-light-info' : 'label-light-primary' }}">{{ $roles[$r->name] }}</span></td>
                                            <td>
                                                @if($u->can_login)
                                                    <span class="label label-inline label-light-success font-weight-bold">Active</span>
                                                @else
                                                    <span class="label label-inline label-light-danger font-weight-bold">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-inline">
                                                    <button type="button" class="btn btn-sm btn-light-primary font-weight-bold dropdown-toggle"
                                                            data-toggle="dropdown" data-boundary="window" aria-haspopup="true" aria-expanded="false">Actions</button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        @if(!$u->hasRole('admin') && $u->id !== auth()->id())
                                                            <form method="POST" action="{{ route('admin.office-admins.toggle', $u) }}">
                                                                @csrf
                                                                @if($u->can_login)
                                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Deactivate {{ $u->name }}? They will not be able to use the portal.')">
                                                                        <i class="la la-pause-circle mr-2 text-warning"></i> Deactivate
                                                                    </button>
                                                                @else
                                                                    <button type="submit" class="dropdown-item" onclick="return confirm('Activate {{ $u->name }}?')">
                                                                        <i class="la la-play-circle mr-2 text-success"></i> Activate
                                                                    </button>
                                                                @endif
                                                            </form>
                                                            <button type="button" class="dropdown-item js-change-password"
                                                                    data-action="{{ route('admin.office-admins.password', $u) }}"
                                                                    data-name="{{ $u->name }} (@ {{ $u->username }})">
                                                                <i class="la la-key mr-2 text-primary"></i> Change Password
                                                            </button>
                                                            <div class="dropdown-divider"></div>
                                                        @endif
                                                        <form method="POST" action="{{ route('admin.office-admins.remove', $u) }}">
                                                            @csrf @method('DELETE')
                                                            <input type="hidden" name="role" value="{{ $r->name }}">
                                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Remove {{ $roles[$r->name] }} role from {{ $u->name }}?')">
                                                                <i class="la la-user-times mr-2 text-danger"></i> Remove Role
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-6">No staff accounts yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>

                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder">Access Changes</h3></div>
                        <div class="card-body pt-2">
                            <table class="table table-sm">
                                <thead><tr class="text-muted"><th>When</th><th>Change</th><th>By</th></tr></thead>
                                <tbody>
                                @forelse($history as $h)
                                    <tr>
                                        <td class="small text-nowrap">{{ $h->created_at->format('d M Y H:i') }}</td>
                                        <td class="small">{{ $h->description }}</td>
                                        <td class="small">{{ $h->actor?->name }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No changes yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Change password modal --}}
<div class="modal fade" id="passwordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="passwordForm" autocomplete="off">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Change Password — <span id="passwordName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal"><i aria-hidden="true" class="ki ki-close"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">New Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary font-weight-bold">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script>
$(function () {
    $(document).on('click', '.js-change-password', function () {
        $('#passwordForm').attr('action', $(this).data('action'))[0].reset();
        $('#passwordName').text($(this).data('name'));
        $('#passwordModal').modal('show');
    });
});
</script>
@endsection
