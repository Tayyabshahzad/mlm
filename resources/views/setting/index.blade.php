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
                    <h5 class="text-dark font-weight-bold my-1 mr-5">System Setting </h5>
                    <!--end::Page Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Setting</a>
                        </li>  
                        <li class="breadcrumb-item">
                            <a href="" class="text-muted">Basic Setting</a>
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
                @include('setting.side-bar')
                <!--end::Aside-->
                <!--begin::Content-->
                <div class="flex-row-fluid ml-lg-8">
                    <!--begin::Card-->
                    <div class="card card-custom card-stretch">
                        <!--begin::Header-->
                        <div class="card-header py-3">
                            <div class="card-title align-items-start flex-column">
                                <h3 class="card-label font-weight-bolder text-dark">System Setting</h3> 
                            </div> 
                            <div class="card-title align-items-start flex-column">
                                <h3 class="card-label font-weight-bolder text-dark">Last Updated: {{ $setting->updated_at }} </h3> 
                            </div> 
                        </div>
                        <!--end::Header-->
                        <!--begin::Form-->
                        <form class="form" action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data">
                            
                            @csrf 
                           <input type="hidden" name="id" value="{{ $setting->id }}">
                            <div class="card-body">
                                <div class="row">
                                    <label class="col-xl-3"></label>
                                    <div class="col-lg-9 col-xl-6">
                                        <h5 class="font-weight-bold mb-6">System  Information</h5>
                                    </div>
                                </div>   
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Site Name</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input class="form-control form-control-lg form-control-solid" type="text" name="site_name" value="{{ old('site_name', $setting->site_name ?? '') }}" required />
                                    </div>
                                    @error('site_name')
                                        <div class="text-danger mt-2">
                                            <small>{{ $message }}</small>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">PV Amount</label>
                                    <div class="col-lg-9 col-xl-6">
                                        <input class="form-control form-control-lg form-control-solid" type="number" name="pv_amount" value="{{ old('pv_amount', $setting->pv_amount ?? '') }}" required />

                                        @error('pv_amount')
                                            <div class="text-danger mt-2">
                                                <small>{{ $message }}</small>
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">About Company</label>
                                    <div class="col-lg-9 col-xl-6  "> 
                                        <textarea type="text" class="form-control form-control-lg form-control-solid" name="description"
                                              required placeholder="Description">{{  $setting->description ?? '' }}</textarea> 
                                        
                                    </div> 
                                </div> 

                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label">Latest USD </label>
                                    <div class="col-lg-9 col-xl-6  "> 
                                        <input class="form-control form-control-lg form-control-solid" 
                                        type="number" name="usd" value="{{ old('usd', $setting->usd ?? '') }}"  />  
                                    </div> 
                                </div> 

                                <div class="form-group row">
                                    <label class="col-xl-3 col-lg-3 col-form-label"> Activation Code Amount </label>
                                    <div class="col-lg-9 col-xl-6  "> 
                                        <input class="form-control form-control-lg form-control-solid" 
                                        type="number" name="activation_code" value="{{ old('activation_code', $setting->activation_code ) }}"  />  
                                    </div> 
                                </div> 


                                <div class="card-toolbar">
                                    <button type="submit" class="btn btn-success mr-2 rounded-0">Update Setting</button> 
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
    
    $(document).ready(function () {
        $('#request_agreement').on('click', function () {  
            $(this).prop('disabled', true); 
            $(this).text('Sending Email ...');
        });

        
    });



    </script>
    
@endsection