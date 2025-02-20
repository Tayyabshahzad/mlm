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

                                        <div id="other_bank_div" class="mt-2" style="display: none;">
                                            <input type="text" name="bank_name" id="other_bank_name" 
                                            class="form-control" placeholder="Enter Bank Name"  >
                                        </div>


                                        <div class="input-group input-group-lg input-group-solid">
                                            <select name="bank_name" id="bank_name" class="form-control form-control-lg form-control-solid">
                                                <option value="" selected disabled>Select Bank</option>
                                                <option value="Allied Bank Limited (ABL)" {{ old('bank_name', $profile->bank_name ?? '') == 'Allied Bank Limited (ABL)' ? 'selected' : '' }}>Allied Bank Limited (ABL)</option>
                                                <option value="Askari Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Askari Bank Limited' ? 'selected' : '' }}>Askari Bank Limited</option>
                                                <option value="Bank Alfalah Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Bank Alfalah Limited' ? 'selected' : '' }}>Bank Alfalah Limited</option>
                                                <option value="Bank Al-Habib Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Bank Al-Habib Limited' ? 'selected' : '' }}>Bank Al-Habib Limited</option>
                                                <option value="Bank Islami Pakistan Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Bank Islami Pakistan Limited' ? 'selected' : '' }}>Bank Islami Pakistan Limited</option>
                                                <option value="Bank of Khyber (BOK)" {{ old('bank_name', $profile->bank_name ?? '') == 'Bank of Khyber (BOK)' ? 'selected' : '' }}>Bank of Khyber (BOK)</option>
                                                <option value="Bank of Punjab (BOP)" {{ old('bank_name', $profile->bank_name ?? '') == 'Bank of Punjab (BOP)' ? 'selected' : '' }}>Bank of Punjab (BOP)</option>
                                                <option value="Dubai Islamic Bank Pakistan Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Dubai Islamic Bank Pakistan Limited' ? 'selected' : '' }}>Dubai Islamic Bank Pakistan Limited</option>
                                                <option value="Faysal Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Faysal Bank Limited' ? 'selected' : '' }}>Faysal Bank Limited</option>
                                                <option value="First Women Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'First Women Bank Limited' ? 'selected' : '' }}>First Women Bank Limited</option>
                                                <option value="Habib Bank Limited (HBL)" {{ old('bank_name', $profile->bank_name ?? '') == 'Habib Bank Limited (HBL)' ? 'selected' : '' }}>Habib Bank Limited (HBL)</option>
                                                <option value="Habib Metropolitan Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Habib Metropolitan Bank Limited' ? 'selected' : '' }}>Habib Metropolitan Bank Limited</option>
                                                <option value="JS Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'JS Bank Limited' ? 'selected' : '' }}>JS Bank Limited</option>
                                                <option value="MCB Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'MCB Bank Limited' ? 'selected' : '' }}>MCB Bank Limited</option>
                                                <option value="Meezan Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Meezan Bank Limited' ? 'selected' : '' }}>Meezan Bank Limited</option>
                                                <option value="National Bank of Pakistan (NBP)" {{ old('bank_name', $profile->bank_name ?? '') == 'National Bank of Pakistan (NBP)' ? 'selected' : '' }}>National Bank of Pakistan (NBP)</option>
                                                <option value="Sindh Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Sindh Bank Limited' ? 'selected' : '' }}>Sindh Bank Limited</option>
                                                <option value="Soneri Bank Limited" {{ old('bank_name', $profile->bank_name ?? '') == 'Soneri Bank Limited' ? 'selected' : '' }}>Soneri Bank Limited</option>
                                                <option value="Standard Chartered Bank Pakistan" {{ old('bank_name', $profile->bank_name ?? '') == 'Standard Chartered Bank Pakistan' ? 'selected' : '' }}>Standard Chartered Bank Pakistan</option>
                                                <option value="United Bank Limited (UBL)" {{ old('bank_name', $profile->bank_name ?? '') == 'United Bank Limited (UBL)' ? 'selected' : '' }}>United Bank Limited (UBL)</option>
                                                <option value="Zarai Taraqiati Bank Limited (ZTBL)" {{ old('bank_name', $profile->bank_name ?? '') == 'Zarai Taraqiati Bank Limited (ZTBL)' ? 'selected' : '' }}>Zarai Taraqiati Bank Limited (ZTBL)</option>
                                                <option value="Easypaisa" {{ old('bank_name', $profile->bank_name ?? '') == 'Easypaisa' ? 'selected' : '' }}>Easypaisa</option>
                                                <option value="Jazzcash" {{ old('bank_name', $profile->bank_name ?? '') == 'Jazzcash' ? 'selected' : '' }}>Jazzcash</option>
                                            </select>
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
                                             name="account_number"
                                            value="{{ old('account_number', $profile->account_number ?? '') }}"/>
                                        </div>
                                        @error('account_number')
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


<script>
    document.addEventListener('DOMContentLoaded', function() {
        let bankSelect = document.getElementById('bank_name');
        let otherBankDiv = document.getElementById('other_bank_div');
        let otherBankInput = document.getElementById('other_bank_name');

        function toggleOtherBank() {
            if (bankSelect.value === 'Other') {
                otherBankDiv.style.display = 'block';
                otherBankInput.required = true;
            } else {
                otherBankDiv.style.display = 'none';
                otherBankInput.required = false;
            }
        }

        bankSelect.addEventListener('change', toggleOtherBank);

        // Check if "Other" was previously selected and show the input field
        if (bankSelect.value === 'Other') {
            otherBankDiv.style.display = 'block';
        }
    });
</script>
    
@endsection