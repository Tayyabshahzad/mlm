@extends('demo.layout.app')
@section('title', 'ROI Settings - VIP Gold & VIP Silver Plans')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">ROI Percentage Settings</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('setting.basic') }}" class="text-muted">Settings</a>
                        </li>
                        <li class="breadcrumb-item text-muted">ROI Settings</li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.roi-settings.profit-share-settings') }}" class="btn btn-light-success font-weight-bolder mr-3">
                    <i class="la la-percentage"></i> Profit Share Settings
                </a>
                <a href="{{ route('admin.roi-settings.commission-bonuses') }}" class="btn btn-light-warning font-weight-bolder mr-3">
                    <i class="la la-money-bill-wave"></i> Commission Settings
                </a>
                <a href="{{ route('admin.roi-settings.user-plans') }}" class="btn btn-light-primary font-weight-bolder">
                    <i class="la la-users"></i> Manage User Plans
                </a>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="flaticon2-check-mark"></i></div>
                    <div class="alert-text">{{ session('success') }}</div>
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="alert-icon"><i class="flaticon-warning"></i></div>
                    <div class="alert-text">{{ session('error') }}</div>
                    <button type="button" class="close" data-dismiss="alert">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="row mb-6">
                <div class="col-xl-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-body d-flex align-items-center py-5 py-lg-10">
                            <div class="d-flex flex-column flex-grow-1 py-2 py-lg-5">
                                <span class="card-title font-weight-bolder text-dark-75 font-size-h5 mb-2 text-hover-primary">
                                    VIP Silver
                                </span>
                                <span class="font-weight-bold text-muted font-size-lg">
                                    Daily ROI Percentage for Regular Users
                                </span>
                                <div class="font-weight-boldest font-size-h1 text-primary mt-3">
                                    {{ number_format($week->standard_percentage ?? 0, 2) }}%
                                </div>
                            </div>
                            <div class="symbol symbol-circle symbol-80 flex-shrink-0">
                                <div class="symbol-label" style="background-color: #E1F0FF">
                                    <i class="la la-percentage icon-4x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-body d-flex align-items-center py-5 py-lg-10">
                            <div class="d-flex flex-column flex-grow-1 py-2 py-lg-5">
                                <span class="card-title font-weight-bolder text-dark-75 font-size-h5 mb-2 text-hover-primary">
                                    VIP Gold
                                </span>
                                <span class="font-weight-bold text-muted font-size-lg">
                                    Daily ROI Percentage for Premium Users
                                </span>
                                <div class="font-weight-boldest font-size-h1 text-warning mt-3">
                                    {{ number_format($week->vip_percentage ?? 0, 2) }}%
                                </div>
                            </div>
                            <div class="symbol symbol-circle symbol-80 flex-shrink-0">
                                <div class="symbol-label" style="background-color: #FFF4DE">
                                    <i class="la la-star icon-4x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update Form -->
            <div class="card card-custom">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-label">
                            <i class="la la-edit text-primary"></i> Update ROI Percentages
                        </h3>
                    </div>
                </div>
                <form action="{{ route('admin.roi-settings.update') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="alert alert-custom alert-light-info">
                            <div class="alert-icon"><i class="flaticon-information"></i></div>
                            <div class="alert-text">
                                <strong>Note:</strong> These percentages will be applied to all users based on their assigned plan (VIP or Standard).
                                Changes will take effect from the next ROI generation cycle.
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-3 col-form-label font-weight-bold">VIP Silver Percentage</label>
                            <div class="col-9">
                                <div class="input-group">
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           name="standard_percentage"
                                           class="form-control @error('standard_percentage') is-invalid @enderror"
                                           value="{{ old('standard_percentage', $week->standard_percentage ?? 0) }}"
                                           placeholder="Enter percentage (e.g., 3.00)">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                @error('standard_percentage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <span class="form-text text-muted">Daily ROI percentage for users on the VIP Silver plan</span>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-3 col-form-label font-weight-bold">VIP Gold Percentage</label>
                            <div class="col-9">
                                <div class="input-group">
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           name="vip_percentage"
                                           class="form-control @error('vip_percentage') is-invalid @enderror"
                                           value="{{ old('vip_percentage', $week->vip_percentage ?? 0) }}"
                                           placeholder="Enter percentage (e.g., 5.00)">
                                    <div class="input-group-append">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                @error('vip_percentage')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <span class="form-text text-muted">Daily ROI percentage for users on the VIP Gold plan</span>
                            </div>
                        </div>

                        <div class="separator separator-dashed my-8"></div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-custom bg-light-primary">
                                    <div class="card-body p-5">
                                        <h5 class="font-weight-bolder text-primary mb-3">
                                            <i class="la la-lightbulb"></i> VIP Silver Example
                                        </h5>
                                        <p class="text-dark-75 mb-2">
                                            If a user invests <strong>$1,000</strong> with <strong>{{ number_format($week->standard_percentage ?? 0, 2) }}%</strong> daily ROI:
                                        </p>
                                        <ul class="text-dark-75">
                                            <li>Daily ROI: <strong>${{ number_format(1000 * (($week->standard_percentage ?? 0) / 100), 2) }}</strong></li>
                                            <li>Days to 2X: <strong>{{ ($week->standard_percentage ?? 0) > 0 ? round(200 / ($week->standard_percentage ?? 1)) : 'N/A' }} days</strong></li>
                                            <li>Total return: <strong>$2,000</strong> (2X commitment)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-custom bg-light-warning">
                                    <div class="card-body p-5">
                                        <h5 class="font-weight-bolder text-warning mb-3">
                                            <i class="la la-star"></i> VIP Gold Example
                                        </h5>
                                        <p class="text-dark-75 mb-2">
                                            If a user invests <strong>$1,000</strong> with <strong>{{ number_format($week->vip_percentage ?? 0, 2) }}%</strong> daily ROI:
                                        </p>
                                        <ul class="text-dark-75">
                                            <li>Daily ROI: <strong>${{ number_format(1000 * (($week->vip_percentage ?? 0) / 100), 2) }}</strong></li>
                                            <li>Days to 2X: <strong>{{ ($week->vip_percentage ?? 0) > 0 ? round(200 / ($week->vip_percentage ?? 1)) : 'N/A' }} days</strong></li>
                                            <li>Total return: <strong>$2,000</strong> (2X commitment)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary font-weight-bold px-9 py-4">
                            <i class="la la-save"></i> Update Percentages
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
