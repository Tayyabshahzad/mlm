@extends('demo.layout.app')
@section('title','User Information')
@section('content')
 
<div class="content d-flex flex-column flex-column-fluid" id="kt_content"> 
    <form action="{{ route('user.info.update',$user->id) }}" method="post" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <div class="subheader py-2 py-lg-4 subheader-solid" id="kt_subheader">
            <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
                <!--begin::Details-->
                <div class="d-flex align-items-center flex-wrap mr-2">
                    <!--begin::Title-->
                    <h5 class="text-dark font-weight-bold mt-2 mb-2 mr-5">Edit User</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{  route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#" class="text-muted">Users</a>
                        </li>   

                        <li class="breadcrumb-item">
                            <a href="" class="text-muted"> {{ $user->name }} </a>
                        </li>
                    </ul> 
                </div> 
                <div class="d-flex align-items-center">
                    <!--begin::Button-->
                    <a href="#" class="rounded-0 btn btn-default font-weight-bold btn-sm px-3 font-size-base">Back</a>
                    <!--end::Button-->
                    <!--begin::Dropdown-->
                    <div class="btn-group ml-2">
                        <button  type="submit" class="rounded-0 btn btn-primary font-weight-bold btn-sm px-3 font-size-base">
                            Save Changes
                        </button> 
                    </div>
                    <!--end::Dropdown-->
                </div>
                <!--end::Toolbar-->
            </div>
        </div> 
        <div class="d-flex flex-column-fluid">
            <!--begin::Container-->
            <div class="container">
                <!--begin::Card-->
                <div class="card card-custom">
                    <!--begin::Card header-->
                    <div class="card-header card-header-tabs-line nav-tabs-line-3x " 
                    @if($user->deleted_at)
                        style="background:rgb(231, 218, 27)"
                    @endif>
                        <!--begin::Toolbar-->
                        <div class="card-toolbar">
                            <ul class="nav nav-tabs nav-bold nav-tabs-line nav-tabs-line-3x">
                                <!--begin::Item-->
                                <li class="nav-item mr-3">
                                    <a class="nav-link active" data-toggle="tab" href="#kt_user_edit_tab_1">
                                        <span class="nav-icon">
                                            <span class="svg-icon">
                                                <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Layers.svg-->
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <polygon points="0 0 24 0 24 24 0 24" />
                                                        <path d="M12.9336061,16.072447 L19.36,10.9564761 L19.5181585,10.8312381 C20.1676248,10.3169571 20.2772143,9.3735535 19.7629333,8.72408713 C19.6917232,8.63415859 19.6104327,8.55269514 19.5206557,8.48129411 L12.9336854,3.24257445 C12.3871201,2.80788259 11.6128799,2.80788259 11.0663146,3.24257445 L4.47482784,8.48488609 C3.82645598,9.00054628 3.71887192,9.94418071 4.23453211,10.5925526 C4.30500305,10.6811601 4.38527899,10.7615046 4.47382636,10.8320511 L4.63,10.9564761 L11.0659024,16.0730648 C11.6126744,16.5077525 12.3871218,16.5074963 12.9336061,16.072447 Z" fill="#000000" fill-rule="nonzero" />
                                                        <path d="M11.0563554,18.6706981 L5.33593024,14.122919 C4.94553994,13.8125559 4.37746707,13.8774308 4.06710397,14.2678211 C4.06471678,14.2708238 4.06234874,14.2738418 4.06,14.2768747 L4.06,14.2768747 C3.75257288,14.6738539 3.82516916,15.244888 4.22214834,15.5523151 C4.22358765,15.5534297 4.2250303,15.55454 4.22647627,15.555646 L11.0872776,20.8031356 C11.6250734,21.2144692 12.371757,21.2145375 12.909628,20.8033023 L19.7677785,15.559828 C20.1693192,15.2528257 20.2459576,14.6784381 19.9389553,14.2768974 C19.9376429,14.2751809 19.9363245,14.2734691 19.935,14.2717619 L19.935,14.2717619 C19.6266937,13.8743807 19.0546209,13.8021712 18.6572397,14.1104775 C18.654352,14.112718 18.6514778,14.1149757 18.6486172,14.1172508 L12.9235044,18.6705218 C12.377022,19.1051477 11.6029199,19.1052208 11.0563554,18.6706981 Z" fill="#000000" opacity="0.3" />
                                                    </g>
                                                </svg>
                                                <!--end::Svg Icon-->
                                            </span>
                                        </span>
                                        <span class="nav-text font-size-lg">Profile</span>
                                    </a>
                                </li> 
                                
                               
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#kt_user_edit_tab_4">
                                        <span class="nav-icon">
                                            <span class="svg-icon">
                                                <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Mail-opened.svg-->
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <rect x="0" y="0" width="24" height="24" />
                                                        <path d="M6,2 L18,2 C18.5522847,2 19,2.44771525 19,3 L19,12 C19,12.5522847 18.5522847,13 18,13 L6,13 C5.44771525,13 5,12.5522847 5,12 L5,3 C5,2.44771525 5.44771525,2 6,2 Z M7.5,5 C7.22385763,5 7,5.22385763 7,5.5 C7,5.77614237 7.22385763,6 7.5,6 L13.5,6 C13.7761424,6 14,5.77614237 14,5.5 C14,5.22385763 13.7761424,5 13.5,5 L7.5,5 Z M7.5,7 C7.22385763,7 7,7.22385763 7,7.5 C7,7.77614237 7.22385763,8 7.5,8 L10.5,8 C10.7761424,8 11,7.77614237 11,7.5 C11,7.22385763 10.7761424,7 10.5,7 L7.5,7 Z" fill="#000000" opacity="0.3" />
                                                        <path d="M3.79274528,6.57253826 L12,12.5 L20.2072547,6.57253826 C20.4311176,6.4108595 20.7436609,6.46126971 20.9053396,6.68513259 C20.9668779,6.77033951 21,6.87277228 21,6.97787787 L21,17 C21,18.1045695 20.1045695,19 19,19 L5,19 C3.8954305,19 3,18.1045695 3,17 L3,6.97787787 C3,6.70173549 3.22385763,6.47787787 3.5,6.47787787 C3.60510559,6.47787787 3.70753836,6.51099993 3.79274528,6.57253826 Z" fill="#000000" />
                                                    </g>
                                                </svg>
                                                <!--end::Svg Icon-->
                                            </span>
                                        </span>
                                        <span class="nav-text font-size-lg">Settings</span>
                                    </a>
                                </li>

                                <li class="nav-item mr-3">
                                    <a class="nav-link" data-toggle="tab" href="#kt_user_edit_tab_3">
                                        <span class="nav-icon">
                                            <span class="svg-icon">
                                                <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Shield-user.svg-->
                                                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                        <rect x="0" y="0" width="24" height="24" />
                                                        <path d="M4,4 L11.6314229,2.5691082 C11.8750185,2.52343403 12.1249815,2.52343403 12.3685771,2.5691082 L20,4 L20,13.2830094 C20,16.2173861 18.4883464,18.9447835 16,20.5 L12.5299989,22.6687507 C12.2057287,22.8714196 11.7942713,22.8714196 11.4700011,22.6687507 L8,20.5 C5.51165358,18.9447835 4,16.2173861 4,13.2830094 L4,4 Z" fill="#000000" opacity="0.3" />
                                                        <path d="M12,11 C10.8954305,11 10,10.1045695 10,9 C10,7.8954305 10.8954305,7 12,7 C13.1045695,7 14,7.8954305 14,9 C14,10.1045695 13.1045695,11 12,11 Z" fill="#000000" opacity="0.3" />
                                                        <path d="M7.00036205,16.4995035 C7.21569918,13.5165724 9.36772908,12 11.9907452,12 C14.6506758,12 16.8360465,13.4332455 16.9988413,16.5 C17.0053266,16.6221713 16.9988413,17 16.5815,17 C14.5228466,17 11.463736,17 7.4041679,17 C7.26484009,17 6.98863236,16.6619875 7.00036205,16.4995035 Z" fill="#000000" opacity="0.3" />
                                                    </g>
                                                </svg>
                                                <!--end::Svg Icon-->
                                            </span>
                                        </span>
                                        <span class="nav-text font-size-lg">Change Password</span>
                                    </a>
                                </li>
                                <!--end::Item-->
                            </ul>
                        </div>
                        <!--end::Toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body">
                        <form class="form" id="kt_form">
                            <div class="tab-content">
                                <!--begin::Tab-->
                                <div class="tab-pane show active px-7" id="kt_user_edit_tab_1" role="tabpanel">
                                    <!--begin::Row-->
                                    <div class="row">
                                    
                                        <div class="col-xl-6 ">
                                            <!--begin::Row-->
                                            <div class="row">
                                                
                                                <div class="col-12">
                                                    <h6 class="text-dark font-weight-bold mb-10">{{  $user->username }}'s Info':</h6>
                                                </div>
                                            </div> 
                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">Avatar</label>
                                                <div class="col-9">
                                                    <div class="image-input image-input-outline" id="kt_profile_avatar" 
                                                    style="background-image: url({{ asset('assets/media/users/blank.png') }})"> 
                                                        <div class="image-input-wrapper" 
                                                        style="background-image:url({{asset($user->getFirstMediaUrl('user_profile_images')) }})"></div>
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
                                            <!--end::Group-->
                                            <!--begin::Group-->
                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">First Name</label>
                                                <div class="col-9">
                                                    <input class="form-control form-control-lg form-control-solid"
                                                    type="text" 
                                                    name="first_name" value="{{ old('first_name', $user->profile->first_name ?? '') }}"   />
                                                </div>
                                            </div>
                                            <!--end::Group-->
                                            <!--begin::Group-->
                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">Last Name</label>
                                                <div class="col-9">
                                                    <input class="form-control form-control-lg form-control-solid" type="text" name="last_name" value="{{ old('last_name', $user->profile->last_name ?? '') }}"   />
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">Gender</label>
                                                <div class="col-lg-9 col-xl-6 pl-10">
                                                    <label>
                                                        <input class="form-control-solid form-check-input" type="radio" value="1"   name="gender" {{ old('gender', $user->profile->gender ?? '') == 1 ? 'checked' : '' }} />
                                                        Male
                                                    </label>
                                                    &nbsp;&nbsp;&nbsp;
                                                    <label>
                                                        <input class="form-control-solid form-check-input" type="radio" value="0"   name="gender" {{ old('gender', $user->profile->gender ?? '') == 0 ? 'checked' : '' }} />
                                                        Female
                                                    </label>
                                                    @error('gender')
                                                        <div class="text-danger mt-2">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                </div>
                                                
                                            </div>
                                        </div>
                                        <div class="col-xl-6 ">
                                            <div class="row">
                                                
                                                <div class="col-lg-12 ">
                                                    <h5 class="font-weight-bold mt-10 mb-6">Contact Info</h5>
                                                </div>
                                            </div>
                                            @if($user->deleted_at)
                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">Username</label>
                                                <div class="col-lg-9">
                                                    <div class="input-group input-group-lg input-group-solid">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <i class="la la-user"></i>
                                                            </span>
                                                        </div>
                                                        <input type="text" class="form-control form-control-lg form-control-solid" name="username"
                                                        value="{{ old('username',$user->username ?? '') }}"   placeholder="username" />
                                                    </div> 
                                                    @error('username')
                                                        <div class="text-danger mt-2">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                            @endif
                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">Email Address</label>
                                                <div class="col-lg-9  ">
                                                    <div class="input-group input-group-lg input-group-solid">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <i class="la la-at"></i>
                                                            </span>
                                                        </div>
                                                        <input type="text" 
                                                        @if(!$user->deleted_at)
                                                        disabled 
                                                        @endif 
                                                        name="email"
                                                        class="form-control form-control-lg form-control-solid" value="{{$user->email }}" placeholder="Email" />
                                                    </div>
                                                </div>
                                            </div> 

                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">Contact Phone</label>
                                                <div class="col-lg-9">
                                                    <div class="input-group input-group-lg input-group-solid">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <i class="la la-phone"></i>
                                                            </span>
                                                        </div>
                                                        <input type="text" class="form-control form-control-lg form-control-solid" name="phone"
                                                        value="{{ old('phone',$user->phone_number ?? '') }}"   placeholder="Phone" />
                                                    </div> 
                                                    @error('phone')
                                                        <div class="text-danger mt-2">
                                                            <small>{{ $message }}</small>
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                           
            
                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">Address</label>
                                                <div class="col-lg-9">
                                                    <div class="input-group input-group-lg input-group-solid">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <i class="la la-map"></i>
                                                            </span>
                                                        </div>
                                                        <input type="text" class="form-control form-control-lg form-control-solid" 
                                                        name="address"
                                                        value="{{ old('address',$user->profile->address ?? '') }}"   placeholder="Address" />
                                                    </div>
                                                </div>
                                            </div> 
            
                                        
                                            <div class="form-group row">
                                                <label class="col-form-label col-3 text-lg-left text-left">CNIC Number <span class="text-danger">*</span> </label>
                                                <div class="col-lg-9 ">
                                                    <div class="input-group input-group-lg input-group-solid"> 
                                                        <input type="text" class="form-control form-control-lg form-control-solid" 
                                                        name="cnic"
                                                         
                                                        value="{{ old('cnic', $user->profile->cnic ?? '') }}"  
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
                                                <label class="col-form-label col-3 text-lg-left text-left"> CNIC  - Back <span class="text-danger">*</span> </label>
                                                <div class="col-lg-9 col-xl-6">
                                                    <div class="image-input image-input-outline" id="kt_cnic_front" 
                                                    style="background-image: url({{ asset('assets/custom-images/dummy-card.jpg') }})"> 
                                                        <div class="image-input-wrapper" 
                                                        style="background-image:url({{asset($user->getFirstMediaUrl('user_document_cnic_front')) }})"></div>
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
                                                        style="background-image:url({{asset($user->getFirstMediaUrl('user_document_cnic_back')) }})"></div>
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
                                        </div>
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Tab-->
                                <!--begin::Tab-->
                                <div class="tab-pane px-7" id="kt_user_edit_tab_2" role="tabpanel">
                                
                                    
                                </div>
                                <!--end::Tab-->
                                <!--begin::Tab-->
                                <div class="tab-pane px-7" id="kt_user_edit_tab_3" role="tabpanel">
                                    <!--begin::Body-->
                                    <div class="card-body">
                                        <!--begin::Row-->
                                        <div class="row">
                                        
                                            <div class="col-xl-7">
                                                
                                                <div class="row">
                                                    <label class="col-3"></label>
                                                    <div class="col-9">
                                                        <h6 class="text-dark font-weight-bold mb-10">Change  Password:</h6>
                                                    </div>
                                                </div>
                                                
                                                <div class="form-group row">
                                                    <label class="col-form-label col-3 text-lg-left text-left">New Password</label>
                                                    <div class="col-9">
                                                        <input class="form-control form-control-lg form-control-solid" type="password" name="password" />
                                                    </div>
                                                </div>
                                                <!--end::Group-->
                                                <!--begin::Group-->
                                                <div class="form-group row">
                                                    <label class="col-form-label col-3 text-lg-left text-left">Verify Password</label>
                                                    <div class="col-9">
                                                        <input class="form-control form-control-lg form-control-solid" type="password" name="password_confirmation" />
                                                    </div>
                                                </div>
                                                <!--end::Group-->
                                            </div>
                                        </div>
                                        <!--end::Row-->
                                    </div>
                                    <!--end::Body-->
                                    <!--begin::Footer-->
                                    <div class="card-footer pb-0">
                                        <div class="row">
                                            <div class="col-xl-2"></div>
                                            <div class="col-xl-7">
                                                <div class="row text-left">
                                                   
                                                    <div class="col-12">
                                                        <button type="submit" class="btn btn-light-primary font-weight-bold">Save changes</button> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Footer-->
                                </div>
                                <!--end::Tab-->
                                <!--begin::Tab-->
                                <div class="tab-pane px-7" id="kt_user_edit_tab_4" role="tabpanel">
                                    <div class="row"> 
                                        <div class="col-xl-8">
                                            <div class="my-2">
                                                <div class="row">
                                                    <label class="col-form-label col-3 text-lg-left text-left"></label>
                                                    <div class="col-12">
                                                        <h6 class="text-dark font-weight-bold mb-7">Account Settings:</h6> 
                                                    </div>
                                                </div>
                                                <div class="form-group row mb-2">
                                                    <label class="col-form-label col-3 text-lg-left text-left">Freeze Online Wallet</label>
                                                    <div class="col-3">
                                                        <span class="switch">
                                                            <label>
                                                                <input type="hidden" name="freez_wallet" value="0">
                                                                <input type="checkbox" name="freez_wallet"  value="1" 
                                                                 @if($user->freez_wallet)  checked @endif />
                                                                <span></span>
                                                            </label>
                                                        </span>
                                                    </div>
                                                </div>
                                                 
                                                <div class="form-group row">
                                                    <label class="col-form-label col-3 text-lg-left text-left">Block Account</label>
                                                    <div class="col-3">
                                                        <span class="switch">
                                                            <label>
                                                                <input type="hidden" name="blocked" value="0">
                                                                <input type="checkbox" name="blocked" id="blockToggle" value="1" 
                                                                 @if($user->blocked) checked @endif />
                                                                <span></span>
                                                            </label>
                                                        </span>
                                                    </div>
                                                </div> 


                                                <div class="form-group row" id="reasonBlockContainer" style="display: none;">
                                                    <label class="col-form-label col-12 text-lg-left text-left">Reason Block User</label>
                                                    <div class="col-12 form-group">
                                                        <textarea id="blockReason" class="form-control" name="reason" placeholder="Enter reason">{{ $user->reason }}</textarea>
                                                    </div>
                                                </div>



                                            </div> 
                                        </div>
                                    </div> 
                                    
                                </div>
                                <!--end::Tab-->
                            </div>
                        </form>
                    </div>
                    <!--begin::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Container-->
        </div>
    </form>
    <!--end::Entry-->
</div>
@endsection
@section('page_js')
    <script>
        
         
         var avatar = new KTImageInput('kt_profile_avatar'); 
        new KTImageInput('kt_cnic_front'); 
        new KTImageInput('kt_cnic_back'); 
        
    </script>

<script>
    // DOM elements
    const blockToggle = document.getElementById('blockToggle');
    const reasonBlockContainer = document.getElementById('reasonBlockContainer');
    const blockReason = document.getElementById('blockReason');

    // Event listener for Block Account toggle
    blockToggle.addEventListener('change', () => {
        if (blockToggle.checked) {
            reasonBlockContainer.style.display = 'block';
            blockReason.setAttribute('required', 'required');
        } else {
            reasonBlockContainer.style.display = 'none';
            blockReason.removeAttribute('required');
        }
    });

    // Initial visibility check (for page load with saved data)
    if (blockToggle.checked) {
        reasonBlockContainer.style.display = 'block';
        blockReason.setAttribute('required', 'required');
    }
</script>
    
@endsection