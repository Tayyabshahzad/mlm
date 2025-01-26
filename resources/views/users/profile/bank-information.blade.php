@extends('demo.layout.app')
@section('title','Bank Account Information')
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
                            <a href="" class="text-muted">Bank Account Information</a>
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
                                <h3 class="card-label font-weight-bolder text-dark">Bank Account  Information</h3>
                                <span class="text-muted font-weight-bold font-size-sm mt-1">Update your Bank Account information</span>
                            </div> 
                        </div>
                        <!--end::Header-->
                        <!--begin::Form-->
                        <form class="form" action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">


                             
                            
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="step" value="4">
                            <div class="card-body">
                                <div class="row">
                                    <label class="col-xl-3"></label>
                                    <div class="col-lg-9 col-xl-6">
                                        <h5 class="font-weight-bold mb-6">Bank Account</h5>
                                    </div>
                                </div> 
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Account Title <span class="text-danger">*</span> </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input class="form-control form-control-lg form-control-solid"  
                                        name="account_title"
                                        required
                                        value="{{ old('account_title', $profile->account_title ?? '') }}" 
                                         />
                                         @error('account_title')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                   
                                </div>  
                                {{-- <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Account Number <span class="text-danger">*</span>  </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid"> 
                                            <input type="text" class="form-control form-control-lg form-control-solid" 
                                            name="account_number"
                                            required
                                            value="{{ old('account_number', $profile->account_number ?? '') }}" />
                                        </div>
                                        @error('account_number')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>    --}}
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">IBN Number <span class="text-danger">*</span> </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid"> 
                                            <input type="text" class="form-control form-control-lg form-control-solid"
                                            required
                                             name="ibn_number"
                                            value="{{ old('ibn_number', $profile->ibn_number ?? '') }}"/>
                                        </div>
                                        @error('ibn_number')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div> 
  

                                {{-- <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Branch Name <span class="text-danger">*</span> </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid"> 
                                            <input type="text" class="form-control form-control-lg form-control-solid" 
                                            required
                                            name="branch_name"
                                            value="{{ old('branch_name', $profile->branch_name ?? '') }}" />
                                        </div>
                                        @error('branch_name')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>  --}}

                                {{-- <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Branch Code <span class="text-danger">*</span> </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid"> 
                                            <input type="text" class="form-control form-control-lg form-control-solid" 
                                            required
                                            name="branch_code"
                                            value="{{ old('branch_code', $profile->branch_code ?? '') }}" />
                                        </div>
                                        @error('branch_code')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>   --}}


                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Bank Name <span class="text-danger">*</span> </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid"> 
                                            <input type="text" class="form-control form-control-lg form-control-solid"
                                            required
                                             name="bank_name"
                                            value="{{ old('bank_name', $profile->bank_name ?? '') }}" />
                                        </div>
                                        @error('bank_name')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>  

                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">USDT Address <span class="text-danger">*</span> </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid"> 
                                            <input type="text" class="form-control form-control-lg form-control-solid"
                                            required
                                             name="usdt_address"
                                            value="{{ old('usdt_address', $profile->account_number ?? '') }}"/>
                                        </div>
                                        @error('usdt_address')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div> 

                                <div class="card-toolbar">
                                    <button type="submit" class="btn btn-success mr-2 rounded-0">Update Changes</button> 
                                </div>
                            </div>
                            <!--end::Body-->
                        </form>
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
    <script>
        "use strict";

// Class definition
var KTProfile = function () {
	// Elements
	var avatar;
	var offcanvas;

	// Private functions
	var _initAside = function () {
		// Mobile offcanvas for mobile mode
		offcanvas = new KTOffcanvas('kt_profile_aside', {
            overlay: true,
            baseClass: 'offcanvas-mobile',
            //closeBy: 'kt_user_profile_aside_close',
            toggleBy: 'kt_subheader_mobile_toggle'
        });
	}

	var _initForm = function() {
		avatar = new KTImageInput('kt_profile_avatar');
	}

	return {
		// public functions
		init: function() {
			_initAside();
			_initForm();
		}
	};
}();

jQuery(document).ready(function() {
	KTProfile.init();
});

    </script>
    
@endsection