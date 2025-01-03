@extends('demo.layout.app')
@section('title','Profile Information')
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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Profile </h5>
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
                            <a href="" class="text-muted">Personal Information</a>
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
                                <h3 class="card-label font-weight-bolder text-dark">Personal Information</h3>
                                <span class="text-muted font-weight-bold font-size-sm mt-1">Update your personal informaiton</span>
                            </div>

                            <div class="card-title align-items-start flex-column">
                                <a class="btn btn-sm btn-info rounded-0" href="{{ route('user.profile.agreement.request') }}">
                                     Request Agreement 
                                </a>  
                            </div>
                           
                        </div>
                        <!--end::Header-->
                        <!--begin::Form-->
                        <form class="form" action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                            
                            @csrf
                        @method('PUT')
                           <input type="hidden" name="step" value="1">
                            <div class="card-body">
                                <div class="row">
                                    <label class="col-xl-3"></label>
                                    <div class="col-lg-9 col-xl-6">
                                        <h5 class="font-weight-bold mb-6">Your  Information</h5>
                                    </div>
                                </div> 

                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Avatar</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="image-input image-input-outline" id="kt_profile_avatar" 
                                        style="background-image: url({{ asset('assets/media/users/blank.png') }})"> 
                                            <div class="image-input-wrapper" 
                                            style="background-image:url({{asset(Auth::user()->getFirstMediaUrl('user_profile_images')) }})"></div>
                                            <label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
                                                <i class="fa fa-pen icon-sm text-muted"></i>
                                                <input type="file" name="profile_avatar" accept=".png, .jpg, .jpeg" />
                                                <input type="hidden" name="profile_avatar_remove" />
                                            </label>
                                            <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="cancel" data-toggle="tooltip" title="Cancel avatar">
                                                <i class="ki ki-bold-close icon-xs text-muted"></i>
                                            </span>
                                            <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="remove" data-toggle="tooltip" title="Remove avatar">
                                                <i class="ki ki-bold-close icon-xs text-muted"></i>
                                            </span>
                                        </div>
                                        <span class="form-text text-muted">Allowed file types: png, jpg, jpeg.</span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">First Name</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input class="form-control form-control-lg form-control-solid" type="text" name="first_name" value="{{ old('first_name', $profile->first_name ?? '') }}" required />
                                    </div>
                                    @error('first_name')
                                        <div class="text-danger mt-2">
                                            <small>{{ $message }}</small>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Last Name</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input class="form-control form-control-lg form-control-solid" type="text" name="last_name" value="{{ old('last_name', $profile->last_name ?? '') }}" required />

                                        @error('last_name')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Gender</label>
                                    <div class="col-lg-9 col-xl-6 pl-10">
                                        <label>
                                            <input class="form-control-solid form-check-input" type="radio" value="1" required name="gender" {{ old('gender', $profile->gender ?? '') == 1 ? 'checked' : '' }} />
                                            Male
                                        </label>
                                        &nbsp;&nbsp;&nbsp;
                                        <label>
                                            <input class="form-control-solid form-check-input" type="radio" value="0" required name="gender" {{ old('gender', $profile->gender ?? '') == 0 ? 'checked' : '' }} />
                                            Female
                                        </label>
                                        @error('gender')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                    
                                </div>
                                
                                <div class="row">
                                    <label class="col-xl-3"></label>
                                    <div class="col-lg-9 col-xl-6">
                                        <h5 class="font-weight-bold mt-10 mb-6">Contact Info</h5>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Contact Phone</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="la la-phone"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control form-control-lg form-control-solid" name="phone"
                                             value="{{ old('phone', Auth::user()->phone_number ?? '') }}" required placeholder="Phone" />
                                        </div>
                                        
                                        <span class="form-text text-danger">
                                            @if(!Auth::user()->phone_verified && Auth::user()->phone_number)
                                                Please Verify Your Phone Number  
                                                <b>
                                                    <a href="#" id="sendOTPLink" data-toggle="modal" data-target="#sendOTP">
                                                        Verify Now
                                                    </a>
                                                </b>
                                            @endif
                                        </span>

                                        @error('phone')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Email Address</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">
                                                    <i class="la la-at"></i>
                                                </span>
                                            </div>
                                            <input type="text" class="form-control form-control-lg form-control-solid" value="{{ Auth::user()->email }}" placeholder="Email" />
                                        </div>
                                    </div>
                                </div> 

                               
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">CNIC Number <span class="text-danger">*</span> </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="input-group input-group-lg input-group-solid"> 
                                            <input type="text" class="form-control form-control-lg form-control-solid" 
                                             name="cnic"
                                             required
                                             value="{{ old('cnic', $profile->cnic ?? '') }}"  
                                             placeholder="CNIC Number" />
                                        </div>

                                        @error('cnic')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror

                                    </div>
                                </div> 

                                <div class="form-group row">
                                    <div class="col-lg-6">
                                        CNIC  - Front  <span class="text-danger">*</span>
                                    </div>
                                    <label class="col-xl-3 col-lg-3 col-form-label"> CNIC  - Back <span class="text-danger">*</span> </label>
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="image-input image-input-outline" id="kt_cnic_front" 
                                        style="background-image: url({{ asset('assets/custom-images/dummy-card.jpg') }})"> 
                                            <div class="image-input-wrapper" 
                                            style="background-image:url({{asset(Auth::user()->getFirstMediaUrl('user_document_cnic_front')) }})"></div>
                                            <label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
                                                <i class="fa fa-pen icon-sm text-muted"></i>
                                                <input type="file" name="cnic_front" accept=".png, .jpg, .jpeg" />
                                                <input type="hidden" name="cnic_front_remove" />
                                            </label>
                                            <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="cancel" data-toggle="tooltip" title="Cancel avatar">
                                                <i class="ki ki-bold-close icon-xs text-muted"></i>
                                            </span>
                                            <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="remove" data-toggle="tooltip" title="Remove avatar">
                                                <i class="ki ki-bold-close icon-xs text-muted"></i>
                                            </span>
                                        </div>
                                        <span class="form-text text-muted">Allowed file types: png, jpg, jpeg.</span>
                                        @error('cnic_front')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror


                                    </div> 
                                    <div class="col-lg-9 col-xl-6">
                                        <div class="image-input image-input-outline" id="kt_cnic_back" 
                                        style="background-image: url({{ asset('assets/custom-images/dummy-card.jpg') }})"> 
                                            <div class="image-input-wrapper" 
                                            style="background-image:url({{asset(Auth::user()->getFirstMediaUrl('user_document_cnic_back')) }})"></div>
                                            <label class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="change" data-toggle="tooltip" title="" data-original-title="Change avatar">
                                                <i class="fa fa-pen icon-sm text-muted"></i>
                                                <input type="file" name="cnic_back" accept=".png, .jpg, .jpeg" />
                                                <input type="hidden" name="cnic_back_remove" />
                                            </label>
                                            <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="cancel" data-toggle="tooltip" title="Cancel avatar">
                                                <i class="ki ki-bold-close icon-xs text-muted"></i>
                                            </span>
                                            <span class="btn btn-xs btn-icon btn-circle btn-white btn-hover-text-primary btn-shadow" data-action="remove" data-toggle="tooltip" title="Remove avatar">
                                                <i class="ki ki-bold-close icon-xs text-muted"></i>
                                            </span>
                                        </div>
                                        <span class="form-text text-muted">Allowed file types: png, jpg, jpeg.</span>
                                        @error('cnic_back')
                                        <div class="text-danger mt-2">
                                            <small>{{ $message }}</small>
                                        </div>
                                    @enderror
                                    </div>  
                                </div>


                                <div class="card-toolbar">
                                    <button type="submit" class="btn btn-success mr-2 rounded-0">Update Profile</button> 
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

 

<!-- Modal-->
<div class="modal fade" id="sendOTP" tabindex="-1" role="dialog" aria-labelledby="sendOTPModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">

        <form action="{{ route('verify.otp') }}" method="post">
            @csrf
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="sendOTPModalLabel">Verify Phone Number</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div> 
                <div class="modal-body"> 
                    <div class="alert alert-info">
                        An OTP has been sent to your phone number: <strong id="currentPhoneNumber">
                            {{ Auth::user()->phone_number }}
                    </strong>.
                        
                    </div> 
                    <div class="form-group">
                        <label for="otpField">Enter OTP</label>
                        <input type="text" name="otp" required id="otpField" class="form-control" placeholder="Enter the OTP">
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal"> Update Phone Number</button>
                    <button type="submit" id="submitOtpBtn" class="btn btn-primary font-weight-bold">
                        Submit OTP</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!--end::Content-->
@endsection
@section('page_js')
    <script>
         var avatar = new KTImageInput('kt_profile_avatar'); 
     new KTImageInput('kt_cnic_front'); 
     new KTImageInput('kt_cnic_back'); 
         document.getElementById('sendOTPLink').addEventListener('click', function (e) {
    
    e.preventDefault();  
    
    const link = this;
    link.innerText = "Sending OTP...";
    link.style.pointerEvents = "none"; 

    fetch('/generate/otp', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    })
    .then((response) => response.json())
    .then((data) => {
        if (data.status) { 
            $('#sendOTP').modal('show');
        } else {
            toastr.error(data.message); 
        }
    })
    .catch((error) => {
        console.error('Error:', error);
        alert('An error occurred while sending OTP.');
    })
    .finally(() => {
        // Reset the link text and enable it
        link.innerText = "Verify Now";
        link.style.pointerEvents = "auto";
    });
});




    </script>
    
@endsection