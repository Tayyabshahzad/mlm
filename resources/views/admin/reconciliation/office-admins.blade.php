@extends('demo.layout.app')
@section('title', 'Office Admins')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Office Admins</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('reconciliation.index') }}" class="text-muted">Bank Reconciliation</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-muted">Office Admins</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">
            <div class="row">
                <div class="col-lg-5">
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder">Add Office Admin</h3></div>
                        <div class="card-body pt-2">
                            <p class="text-muted small">
                                Office Admins can open <strong>Bank Reconciliation</strong> only: view saving members' instalments,
                                verify bank payments, record payments seen in the bank, and reject payments not received.
                                They cannot open any other admin page.
                            </p>
                            <form method="POST" action="{{ route('admin.office-admins.assign') }}">
                                @csrf
                                <div class="form-group">
                                    <label class="font-weight-bold">Username or Email of an existing account</label>
                                    <input type="text" name="account" class="form-control" required value="{{ old('account') }}">
                                    <small class="text-muted">The account must be able to log in.</small>
                                </div>
                                <button class="btn btn-primary font-weight-bold">Give Office Admin Access</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 pt-5"><h3 class="card-title font-weight-bolder">Current Office Admins</h3></div>
                        <div class="card-body pt-2">
                            <table class="table table-vertical-center">
                                <thead><tr class="text-muted"><th>Name</th><th>Email / Phone</th><th>Login</th><th></th></tr></thead>
                                <tbody>
                                @forelse($officeAdmins as $u)
                                    <tr>
                                        <td><span class="font-weight-bold">{{ $u->name }}</span><br><span class="text-muted small">@ {{ $u->username }}</span></td>
                                        <td class="small">{{ $u->email }}<br>{{ $u->phone_number }}</td>
                                        <td>
                                            @if($u->can_login)
                                                <span class="badge badge-light-success">Enabled</span>
                                            @else
                                                <span class="badge badge-light-danger">Disabled</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <form method="POST" action="{{ route('admin.office-admins.remove', $u) }}">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-light-danger" onclick="return confirm('Remove Office Admin access from {{ $u->name }}?')">Remove</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-6">No Office Admins yet.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
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
@endsection
