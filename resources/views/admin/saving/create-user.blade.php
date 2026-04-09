@extends('demo.layout.app')
@section('title', 'Create Saving Account User')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Create Saving Account User</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.saving.index') }}" class="text-muted">Saving Accounts</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Create User</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="card card-custom gutter-b" style="max-width:680px; margin:0 auto;">
                <div class="card-header border-0 py-5">
                    <h3 class="card-title font-weight-bolder text-dark">New Saving Account User</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.saving.create-user.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="font-weight-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="font-weight-bold">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="font-weight-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="font-weight-bold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="font-weight-bold">Referral Code (optional)</label>
                            <input type="text" name="referral_link" class="form-control" value="{{ old('referral_link') }}"
                                   placeholder="Leave empty to place under the default Saving parent user">
                            <small class="text-muted">If left blank, the user will be placed under the configured Saving parent user.</small>
                        </div>

                        @if(!$setting->saving_parent_user_id)
                            <div class="mb-5">
                                <div class="checkbox-inline">
                                    <label class="checkbox checkbox-primary">
                                        <input type="checkbox" name="is_saving_parent" value="1" {{ old('is_saving_parent') ? 'checked' : '' }}>
                                        <span></span>
                                        Set this user as the <strong>Saving Account Parent (Root)</strong>
                                    </label>
                                </div>
                                <small class="text-muted">Check this box to designate this user as the root of the Saving Account referral tree. All saving users without a referral will be placed under them.</small>
                            </div>
                        @else
                            @php $parentUser = \App\Models\User::find($setting->saving_parent_user_id); @endphp
                            <div class="alert alert-info mb-5">
                                Current Saving parent: <strong>{{ $parentUser?->name }}</strong> (@ {{ $parentUser?->username }})
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-3">
                            <a href="{{ route('admin.saving.index') }}" class="btn btn-light mr-3">Cancel</a>
                            <button type="submit" class="btn btn-primary">Create User</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
