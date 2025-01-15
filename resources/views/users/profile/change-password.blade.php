@extends('demo.layout.app')
@section('title','Account Information')
@section('content')
 <!--begin::Content-->
 <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Subheader-->
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <!--begin::Info-->
            <div class="d-flex align-items-center flex-wrap mr-1">
                <!--begin::Mobile Toggle-->
                <button class="burger-icon burger-icon-left mr-4 d-inline-block d-lg-none" id="kt_subheader_mobile_toggle">
                    <span></span>
                </button>
                <!--end::Mobile Toggle-->
                <!--begin::Page Heading-->
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <!--begin::Page Title-->
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Profile</h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{  route('profile.edit') }}" class="text-muted">Profile</a>
                        </li>  
                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Change Password</a>
                        </li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page Heading-->
            </div>
             
        </div>
    </div>
    <!--end::Subheader-->
    <!--begin::Entry-->
    <div class="d-flex flex-column-fluid">
        <!--begin::Container-->
        <div class="container">
            <!--begin::Profile Personal Information-->
            <div class="d-flex flex-row">
                <!--begin::Aside-->
                @include('users.profile.side-bar') 
                <!--end::Aside-->
                <!--begin::Content-->
                <div class="flex-row-fluid ml-lg-8">
                    <!--begin::Card-->
                    <div class="card card-custom card-stretch">
                        <!--begin::Header-->
                        <div class="card-header py-3">
                            <div class="card-title align-items-start flex-column">
                                <h3 class="card-label font-weight-bolder text-dark">Change Password</h3>
                                <span class="text-muted font-weight-bold font-size-sm mt-1"> Change Password for : {{ $user->name }} </span>
                            </div>
                           
                        </div>
                        <!--end::Header-->
                        <!--begin::Form-->
                        <!--begin::Form-->
<form action="{{ route('update.password',$id) }}" method="POST" class="form">
    @csrf
    @method('PUT')

    <div class="card-body">

        <!-- Current Password -->
        @if(!$id) 
        
        <div class="form-group row">
            <label class="col-xl-3 col-lg-3 col-form-label text-alert">Current Password</label>
            <div class="col-lg-9 col-xl-6">
                <input type="password" class="form-control form-control-lg form-control-solid mb-2" 
                       name="current_password" 
                       placeholder="Current password" 
                       required 
                       value="{{ old('current_password') }}" /> 
                    @error('current_password')
                        <div class="text-danger mt-2">
                            <small>{{ $message }}</small>
                        </div>
                    @enderror
            </div>
        </div> 
        @endif   

        <!-- New Password -->
        <div class="form-group row">
            <label class="col-xl-3 col-lg-3 col-form-label text-alert">New Password</label>
            <div class="col-lg-9 col-xl-6">
                <input type="password" class="form-control form-control-lg form-control-solid"
                       name="new_password" 
                       required 
                       placeholder="New password" 
                       value="{{ old('new_password') }}" />
                @error('new_password')
                    <div class="text-danger mt-2">
                        <small>{{ $message }}</small>
                    </div>
                @enderror
            </div>
        </div>

        <!-- Confirm New Password -->
        <div class="form-group row">
            <label class="col-xl-3 col-lg-3 col-form-label text-alert">Verify Password</label>
            <div class="col-lg-9 col-xl-6">
                <input type="password" class="form-control form-control-lg form-control-solid"
                       name="new_password_confirmation" 
                       required 
                       placeholder="Verify password" 
                       value="{{ old('new_password_confirmation') }}" />
            </div>
        </div>

        <!-- Submit Button -->
        <div class="card-toolbar">
            <button type="submit" class="btn btn-success mr-2 rounded-0">Update Password</button>
        </div>

    </div>
</form>
<!--end::Form-->

                        <!--end::Form-->
                    </div>
                </div>
                <!--end::Content-->
            </div>
            <!--end::Profile Personal Information-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Entry-->
</div>
<!--end::Content-->
@endsection
@section('page_js')
    
    
@endsection