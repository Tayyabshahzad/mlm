@extends('demo.layout.app')
@section('title', 'Profile Information')
@section('content')
    <!--begin::Content-->
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Subheader-->
        <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
            <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
                <!--begin::Info-->
                <div class="d-flex align-items-center flex-wrap mr-1">
                    <!--begin::Mobile Toggle-->
                    <button class="burger-icon burger-icon-left mr-4 d-inline-block d-lg-none"
                        id="kt_subheader_mobile_toggle">
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
                                <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}" class="text-muted">Setting</a>
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
                                    <h3 class="card-label font-weight-bolder text-dark">Last Updated:
                                        {{ $setting->updated_at }} </h3>
                                </div>
                            </div>
                            <!--end::Header-->
                            <!--begin::Form--> 

                            <form class="form" action="{{ route('setting.update') }}" method="POST"
                                enctype="multipart/form-data">

                                @csrf
                                <input type="hidden" name="id" value="{{ $setting->id }}">
                                <div class="card-body">
                                    <div class="row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <h5 class="font-weight-bold mb-6">System Information</h5>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Site Name</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="text"
                                                name="site_name" value="{{ old('site_name', $setting->site_name ?? '') }}"
                                                required />
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
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                name="pv_amount" value="{{ old('pv_amount', $setting->pv_amount ?? '') }}"
                                                required />

                                            @error('pv_amount')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Registration Fee</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                name="registration_fee" value="{{ old('registration_fee', $setting->registration_fee ?? '') }}"
                                                required />

                                            @error('registration_fee')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <h5 class="font-weight-bold mb-6 mt-6">Package Range Settings</h5>
                                            <p class="text-muted mb-4">Configure investment amount ranges for package reference (Note: Package assignment is manual only)</p>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Standard Package Min ($)</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="standard_package_min"
                                                value="{{ old('standard_package_min', $setting->standard_package_min ?? 50) }}"
                                                required />
                                            <span class="form-text text-muted">Minimum investment amount for Standard package</span>
                                            @error('standard_package_min')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Standard Package Max ($)</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="standard_package_max"
                                                value="{{ old('standard_package_max', $setting->standard_package_max ?? 344) }}"
                                                required />
                                            <span class="form-text text-muted">Maximum investment amount for Standard package</span>
                                            @error('standard_package_max')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">VIP Package Min ($)</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="vip_package_min"
                                                value="{{ old('vip_package_min', $setting->vip_package_min ?? 345) }}"
                                                required />
                                            <span class="form-text text-muted">Minimum investment amount for VIP package (amounts equal or greater get VIP)</span>
                                            @error('vip_package_min')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <h5 class="font-weight-bold mt-10 mb-6">Profit Sharing Bonus Multipliers</h5>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Standard Plan Multiplier</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="standard_profit_multiplier"
                                                value="{{ old('standard_profit_multiplier', $setting->standard_profit_multiplier ?? 1.00) }}"
                                                required min="0.01" max="10" />
                                            <span class="form-text text-muted">Profit sharing multiplier for Standard users (e.g., 1.00 = 100%, 1.25 = 125%)</span>
                                            @error('standard_profit_multiplier')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">VIP Plan Multiplier</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="vip_profit_multiplier"
                                                value="{{ old('vip_profit_multiplier', $setting->vip_profit_multiplier ?? 1.50) }}"
                                                required min="0.01" max="10" />
                                            <span class="form-text text-muted">Profit sharing multiplier for VIP users (e.g., 1.50 = 150%, 2.0 = 200%)</span>
                                            @error('vip_profit_multiplier')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <div class="alert alert-custom alert-light-info">
                                                <div class="alert-icon"><i class="flaticon-information"></i></div>
                                                <div class="alert-text">
                                                    <strong>Example:</strong> If you distribute $1000 profit and a user's share is $10:
                                                    <ul class="mt-2 mb-0">
                                                        <li>Standard user (1.0x): Gets $10</li>
                                                        <li>VIP user (1.5x): Gets $15 (50% bonus)</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <div class="alert alert-custom alert-light-warning">
                                                <div class="alert-icon"><i class="flaticon-warning"></i></div>
                                                <div class="alert-text">
                                                    <strong>Important:</strong> Package ranges are for reference. Package assignment (Standard/VIP) is done automatically at registration or manually through User Plans page.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 7-Level Commission Bonuses Section -->
                                    <div class="row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <h5 class="font-weight-bold mt-10 mb-6">7-Level Commission Bonuses (%)</h5>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label font-weight-bold text-primary">Standard Package</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <div class="row">
                                                @for($i = 1; $i <= 7; $i++)
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold">Level {{$i}}</label>
                                                        <input class="form-control form-control-lg form-control-solid" type="number"
                                                            step="0.01" name="standard_commission_l{{$i}}"
                                                            value="{{ old('standard_commission_l'.$i, $setting->{'standard_commission_l'.$i} ?? [7,6,5,4,3,2,1][$i-1]) }}"
                                                            required min="0" max="100" />
                                                    </div>
                                                @endfor
                                            </div>
                                            <span class="form-text text-muted">Commission percentage for each level (Standard users)</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label font-weight-bold text-warning">VIP Package</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <div class="row">
                                                @for($i = 1; $i <= 7; $i++)
                                                    <div class="col-md-4 mb-3">
                                                        <label class="font-weight-bold">Level {{$i}}</label>
                                                        <input class="form-control form-control-lg form-control-solid" type="number"
                                                            step="0.01" name="vip_commission_l{{$i}}"
                                                            value="{{ old('vip_commission_l'.$i, $setting->{'vip_commission_l'.$i} ?? [3.5,3,2.5,2,1.5,1,0.5][$i-1]) }}"
                                                            required min="0" max="100" />
                                                    </div>
                                                @endfor
                                            </div>
                                            <span class="form-text text-muted">Commission percentage for each level (VIP users)</span>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <div class="alert alert-custom alert-light-success">
                                                <div class="alert-icon"><i class="flaticon-information"></i></div>
                                                <div class="alert-text">
                                                    <strong>How it works:</strong> When a user makes an investment/topup, their upline receives commissions based on their user plan:
                                                    <ul class="mt-2 mb-0">
                                                        <li><strong>Standard User</strong> (Level 1 sponsor): Gets 7% commission</li>
                                                        <li><strong>VIP User</strong> (Level 1 sponsor): Gets 3.5% commission</li>
                                                    </ul>
                                                    Each user receives commission based on their own plan, not the investor's plan.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <h5 class="font-weight-bold mb-6 mt-6">Company Information</h5>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">About Company</label>
                                        <div class="col-lg-9 col-xl-6  ">
                                            <textarea type="text" class="form-control form-control-lg form-control-solid" name="description" required
                                                placeholder="Description">{{ $setting->description ?? '' }}</textarea>

                                        </div>
                                    </div>

                                    

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Latest USDT </label>
                                        <div class="col-lg-9 col-xl-6  ">
                                            <div class="input-group input-group-lg input-group-solid">
                                                <input type="number"
                                                    class="form-control form-control-lg form-control-solid"
                                                    placeholder="USDT Amount" name="usd"
                                                    value="{{ old('usd', $setting->usd ?? '') }}">

                                                    
                                                </button>

                                                <div class="input-group-append"    
                                                  data-toggle="tooltip"
                                                  title="Want to Get Rate Manually?"
                                                  style="cursor: pointer;">
                                                    <span class="input-group-text" 
                                                    data-toggle="modal"
                                                    data-target="#getlatestUSDT" 
                                                    class="">  
                                                        <i class="la la-info" data-bs-toggle="modal"
                                                            data-bs-target="#updateRateModal"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            @if (session('status'))
                                                <small class="text-warning">
                                                    {{ session('status') }}
                                                </small>
                                            @endif
                                        </div>

                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label"> Activation Code Amount </label>
                                        <div class="col-lg-9 col-xl-6  ">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                name="activation_code"
                                                value="{{ old('activation_code', $setting->activation_code) }}" />
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Withdraw Block</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input type="radio" name="withdraw_block" value="1"
                                                {{ $setting->withdraw_block == 1 ? 'checked' : '' }}> Yes
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="radio" name="withdraw_block" value="0"
                                                {{ $setting->withdraw_block == 0 ? 'checked' : '' }}> No
                                        </div>
                                    </div>

                                    <div class="row">
                                        <label class="col-xl-3"></label>
                                        <div class="col-lg-9 col-xl-6">
                                            <h5 class="font-weight-bold mb-6 mt-6">Withdrawal & Transfer Settings</h5>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Minimum Withdrawal Limit ($)</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="min_withdrawal_limit"
                                                value="{{ old('min_withdrawal_limit', $setting->min_withdrawal_limit ?? 25) }}"
                                                required />
                                            <span class="form-text text-muted">Minimum amount a user can withdraw</span>
                                            @error('min_withdrawal_limit')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Minimum Member Transfer ($)</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="min_member_transfer"
                                                value="{{ old('min_member_transfer', $setting->min_member_transfer ?? 7) }}"
                                                required />
                                            <span class="form-text text-muted">Minimum amount for member-to-member transfers</span>
                                            @error('min_member_transfer')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Bank Withdrawal Fee (%)</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="bank_withdrawal_fee_percent"
                                                value="{{ old('bank_withdrawal_fee_percent', $setting->bank_withdrawal_fee_percent ?? 2) }}"
                                                required min="0" max="100" />
                                            <span class="form-text text-muted">Additional fee percentage for bank withdrawals</span>
                                            @error('bank_withdrawal_fee_percent')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Cash Withdrawal Fee (%)</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="cash_withdrawal_fee_percent"
                                                value="{{ old('cash_withdrawal_fee_percent', $setting->cash_withdrawal_fee_percent ?? 0) }}"
                                                required min="0" max="100" />
                                            <span class="form-text text-muted">Fee percentage for cash withdrawals</span>
                                            @error('cash_withdrawal_fee_percent')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">USDT Withdrawal Discount (%)</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <input class="form-control form-control-lg form-control-solid" type="number"
                                                step="0.01" name="usdt_withdrawal_discount_percent"
                                                value="{{ old('usdt_withdrawal_discount_percent', $setting->usdt_withdrawal_discount_percent ?? 2) }}"
                                                required min="0" max="100" />
                                            <span class="form-text text-muted">Discount percentage (incentive) for USDT withdrawals</span>
                                            @error('usdt_withdrawal_discount_percent')
                                                <div class="text-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-xl-3 col-lg-3 col-form-label">Block Wallet</label>
                                        <div class="col-lg-9 col-xl-6">
                                            <div class="row">
                                                <div class="col-lg-3 mb-3">
                                                    <input type="checkbox" name="block_wallet[online]" value="1" {{ isset($walletSettings['online']) && $walletSettings['online'] ? 'checked' : '' }}> Online
                                                </div>
                                                <div class="col-lg-4 mb-2">
                                                    <input type="checkbox" name="block_wallet[investment]" value="1" {{ isset($walletSettings['investment']) && $walletSettings['investment'] ? 'checked' : '' }}> Investment
                                                </div>
                                                <div class="col-lg-4 mb-2">
                                                    <input type="checkbox" name="block_wallet[direct_indirect]" value="1" {{ isset($walletSettings['direct_indirect']) && $walletSettings['direct_indirect'] ? 'checked' : '' }}> Direct/Indirect
                                                </div>
                                            </div>
                                    
                                            <div class="row mb-3">
                                                <div class="col-lg-3 mb-2">
                                                    <input type="checkbox" name="block_wallet[reward]" value="1" {{ isset($walletSettings['reward']) && $walletSettings['reward'] ? 'checked' : '' }}> Reward
                                                </div>
                                    
                                                <div class="col-lg-3 mb-2">
                                                    <input type="checkbox" name="block_wallet[roi]" value="1" {{ isset($walletSettings['roi']) && $walletSettings['roi'] ? 'checked' : '' }}> ROI
                                                </div>
                                    
                                                <div class="col-lg-4 mb-2 text-right">
                                                    <input type="checkbox" name="block_wallet[profit_share]" value="1" {{ isset($walletSettings['profit_share']) && $walletSettings['profit_share'] ? 'checked' : '' }}> Profit Share
                                                </div>
                                    
                                                <div class="col-lg-3 mb-2">
                                                    <input type="checkbox" name="block_wallet[rank]" value="1" {{ isset($walletSettings['rank']) && $walletSettings['rank'] ? 'checked' : '' }}> Rank
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    
                                    


                                    <div class="card-toolbar">
                                        <button type="submit" class="btn btn-success mr-2 rounded-0">Update
                                            Setting</button>
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
    <div class="modal fade" id="sendOTP" tabindex="-1" role="dialog" aria-labelledby="sendOTPModalLabel"
        aria-hidden="true">
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
                            <input type="text" name="otp" required id="otpField" class="form-control"
                                placeholder="Enter the OTP">
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">
                            Update Phone Number</button>
                        <button type="submit" id="submitOtpBtn" class="btn btn-primary font-weight-bold">
                            Submit OTP</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="getlatestUSDT" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document"> 
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">  Manual Rate Update </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div> 
                    <div class="modal-body">
                        <div id="loading-spinner">
                            <p>Current USDT to PKR rate is: <strong>₨ {{ $setting->usd ?? 'N/A' }}</strong></p>
                            <p>Do you want to fetch the latest updated rate?</p>
                        </div>  
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-primary font-weight-bold" data-dismiss="modal">Close</button> 
                        <form action="{{ route('rate.manual.update') }}" method="GET"> 
                            <button type="submit" class="btn btn-light-info font-weight-bold" >Update</button>
                          </form>
                    </div> 
            </div> 
        </div>
    </div>

    <!--end::Content-->
@endsection
@section('page_js')

    
    <script>
        var avatar = new KTImageInput('kt_profile_avatar');
        new KTImageInput('kt_cnic_front');
        new KTImageInput('kt_cnic_back');
        document.getElementById('sendOTPLink').addEventListener('click', function(e) {

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

        $(document).ready(function() {
            $('#request_agreement').on('click', function() {
                $(this).prop('disabled', true);
                $(this).text('Sending Email ...');
            });


        });

        $(function() {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>

@endsection
