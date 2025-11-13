@extends('demo.layout.app')
@section('title', 'Commission & Profit Sharing Bonuses')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Commission & Profit Sharing Bonuses</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.roi-settings.index') }}" class="text-muted">ROI Settings</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Commission & Bonuses</li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.roi-settings.index') }}" class="btn btn-light font-weight-bolder">
                    <i class="la la-arrow-left"></i> Back to ROI Settings
                </a>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="flaticon2-check-mark"></i>
                        <span class="alert-text">{{ session('success') }}</span>
                    </div>
                    
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="flaticon-warning"></i></div>
                    <div class="alert-text">{{ session('error') }}</div>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <form action="{{ route('admin.roi-settings.update-commission-bonuses') }}" method="POST">
                @csrf

                <!-- Profit Sharing Multipliers -->
                <div class="mb-6 card card-custom">
                    <div class="py-5 border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-dark">
                            <i class="mr-2 la la-money-bill-wave text-success"></i>
                            Profit Sharing Multipliers
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-5 form-group">
                                    <label class="font-weight-bold">Standard Plan Multiplier</label>
                                    <input type="number" step="0.01" name="standard_profit_multiplier"
                                           class="form-control form-control-lg"
                                           value="{{ old('standard_profit_multiplier', $setting->standard_profit_multiplier ?? 1.00) }}"
                                           required min="0" max="10">
                                    <span class="form-text text-muted">e.g., 1.00 = 100%, 1.25 = 125%</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-5 form-group">
                                    <label class="font-weight-bold">VIP Plan Multiplier</label>
                                    <input type="number" step="0.01" name="vip_profit_multiplier"
                                           class="form-control form-control-lg"
                                           value="{{ old('vip_profit_multiplier', $setting->vip_profit_multiplier ?? 1.50) }}"
                                           required min="0" max="10">
                                    <span class="form-text text-muted">e.g., 1.50 = 150%, 2.0 = 200%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commission Bonuses -->
                <div class="mb-6 card card-custom">
                    <div class="py-5 border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-dark">
                            <i class="mr-2 la la-percent text-primary"></i>
                            7-Level Commission Bonuses (%)
                        </h3>
                    </div>
                    <div class="card-body">
                        <!-- Standard Package -->
                        <div class="mb-8">
                            <h4 class="mb-5 font-weight-bold text-primary">
                                <i class="mr-2 la la-user"></i>Standard Package
                            </h4>
                            <div class="row">
                                @for($i = 1; $i <= 7; $i++)
                                    <div class="mb-5 col-md-3">
                                        <label class="font-weight-bold">Level {{ $i }}</label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" step="0.01"
                                                   name="standard_commission_l{{ $i }}"
                                                   class="form-control"
                                                   value="{{ old('standard_commission_l'.$i, $setting->{'standard_commission_l'.$i} ?? [7,6,5,4,3,2,1][$i-1]) }}"
                                                   required min="0" max="100">
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <hr class="my-8">

                        <!-- VIP Package -->
                        <div>
                            <h4 class="mb-5 font-weight-bold text-warning">
                                <i class="mr-2 la la-star"></i>VIP Package
                            </h4>
                            <div class="row">
                                @for($i = 1; $i <= 7; $i++)
                                    <div class="mb-5 col-md-3">
                                        <label class="font-weight-bold">Level {{ $i }}</label>
                                        <div class="input-group input-group-lg">
                                            <input type="number" step="0.01"
                                                   name="vip_commission_l{{ $i }}"
                                                   class="form-control"
                                                   value="{{ old('vip_commission_l'.$i, $setting->{'vip_commission_l'.$i} ?? [3.5,3,2.5,2,1.5,1,0.5][$i-1]) }}"
                                                   required min="0" max="100">
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="mt-8 alert alert-custom alert-light-info">
                            <div class="alert-icon"><i class="flaticon-information"></i></div>
                            <div class="alert-text">
                                <strong>How it works:</strong> When a user makes an investment, their upline receives commissions based on their own plan (Standard or VIP). Each commission percentage is applied to the investment amount.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="card card-custom">
                    <div class="card-body">
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-15">
                                <i class="la la-save"></i>
                                Save All Bonuses
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
