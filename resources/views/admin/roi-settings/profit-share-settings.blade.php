@extends('demo.layout.app')
@section('title', 'Profit Share Settings')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="flex-wrap mr-1 d-flex align-items-center">
                <div class="flex-wrap mr-5 d-flex align-items-baseline">
                    <h5 class="my-1 mr-5 text-dark font-weight-bold">Profit Share Settings</h5>
                    <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.roi-settings.index') }}" class="text-muted">ROI Settings</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Profit Share Settings</li>
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

            <!-- Info Alert -->
            <div class="mb-6 alert alert-custom alert-light-primary">
                <div class="alert-icon"><i class="flaticon-info"></i></div>
                <div class="alert-text">
                    <h4 class="mb-3 text-primary"><strong>Profit Share Settings</strong></h4>
                    <p class="mb-2"><strong>What is Profit Share?</strong> Profit sharing is the commission users earn when their downline receives ROI payments. This is different from direct investment commissions.</p>
                    <p class="mb-2"><strong>Standard Plan:</strong> Gets FULL profit share percentages (higher ROI commission)</p>
                    <p class="mb-0"><strong>VIP Plan:</strong> Gets HALF profit share percentages (VIP users already get higher ROI on their own investments)</p>
                </div>
            </div>

            <form action="{{ route('admin.roi-settings.update-profit-share') }}" method="POST">
                @csrf

                <!-- Standard Package Profit Share -->
                <div class="mb-6 card card-custom">
                    <div class="py-5 border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-primary">
                            <i class="mr-2 la la-user"></i>
                            Standard Package - Profit Share (%)
                        </h3>
                        <div class="card-toolbar">
                            <span class="badge badge-primary badge-lg">Full Profit Share</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @for($i = 1; $i <= 7; $i++)
                                <div class="mb-5 col-md-3">
                                    <label class="font-weight-bold">Level {{ $i }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.01"
                                               name="standard_profit_l{{ $i }}"
                                               class="form-control"
                                               value="{{ old('standard_profit_l'.$i, $setting->{'standard_profit_l'.$i} ?? [7,6,5,4,3,2,1][$i-1]) }}"
                                               required min="0" max="100">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    @php
                                        $requiredUsers = [10, 50, 150, 400, 1000, 2000, 4000][$i-1];
                                    @endphp
                                    <span class="form-text text-muted">Required: {{ $requiredUsers }} users at level {{ $i }}</span>
                                </div>
                            @endfor
                        </div>
                        <div class="mt-5 alert alert-custom alert-light-info">
                            <div class="alert-text">
                                <strong>Default Values:</strong> L1: 7%, L2: 6%, L3: 5%, L4: 4%, L5: 3%, L6: 2%, L7: 1%
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-8">

                <!-- VIP Package Profit Share -->
                <div class="mb-6 card card-custom">
                    <div class="py-5 border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-warning">
                            <i class="mr-2 la la-star"></i>
                            VIP Package - Profit Share (%)
                        </h3>
                        <div class="card-toolbar">
                            <span class="badge badge-warning badge-lg">Half Profit Share</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @for($i = 1; $i <= 7; $i++)
                                <div class="mb-5 col-md-3">
                                    <label class="font-weight-bold">Level {{ $i }}</label>
                                    <div class="input-group input-group-lg">
                                        <input type="number" step="0.01"
                                               name="vip_profit_l{{ $i }}"
                                               class="form-control"
                                               value="{{ old('vip_profit_l'.$i, $setting->{'vip_profit_l'.$i} ?? [3.5,3,2.5,2,1.5,1,0.5][$i-1]) }}"
                                               required min="0" max="100">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    @php
                                        $requiredUsers = [10, 50, 150, 400, 1000, 2000, 4000][$i-1];
                                    @endphp
                                    <span class="form-text text-muted">Required: {{ $requiredUsers }} users at level {{ $i }}</span>
                                </div>
                            @endfor
                        </div>
                        <div class="mt-5 alert alert-custom alert-light-warning">
                            <div class="alert-text">
                                <strong>Default Values:</strong> L1: 3.5%, L2: 3%, L3: 2.5%, L4: 2%, L5: 1.5%, L6: 1%, L7: 0.5%
                                <br>
                                <strong>Note:</strong> VIP gets exactly half of Standard profit share (VIP users already benefit from higher ROI percentages)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Comparison Table -->
                <div class="mb-6 card card-custom">
                    <div class="py-5 border-0 card-header">
                        <h3 class="card-title font-weight-bolder text-dark">
                            <i class="mr-2 la la-table"></i>
                            Comparison Table
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">Level</th>
                                        <th class="text-center">Required Users</th>
                                        <th class="text-center text-primary">Standard Profit Share</th>
                                        <th class="text-center text-warning">VIP Profit Share</th>
                                        <th class="text-center">Difference</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $requiredUsersArray = [10, 50, 150, 400, 1000, 2000, 4000];
                                        $standardDefaults = [7, 6, 5, 4, 3, 2, 1];
                                        $vipDefaults = [3.5, 3, 2.5, 2, 1.5, 1, 0.5];
                                    @endphp
                                    @for($i = 1; $i <= 7; $i++)
                                        <tr>
                                            <td class="font-weight-bold text-center">Level {{ $i }}</td>
                                            <td class="text-center">{{ $requiredUsersArray[$i-1] }}</td>
                                            <td class="text-center text-primary">
                                                <strong>{{ $setting->{'standard_profit_l'.$i} ?? $standardDefaults[$i-1] }}%</strong>
                                            </td>
                                            <td class="text-center text-warning">
                                                <strong>{{ $setting->{'vip_profit_l'.$i} ?? $vipDefaults[$i-1] }}%</strong>
                                            </td>
                                            <td class="text-center">
                                                @php
                                                    $standard = $setting->{'standard_profit_l'.$i} ?? $standardDefaults[$i-1];
                                                    $vip = $setting->{'vip_profit_l'.$i} ?? $vipDefaults[$i-1];
                                                    $diff = $standard - $vip;
                                                @endphp
                                                <span class="badge badge-info">{{ $diff }}%</span>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="card card-custom">
                    <div class="card-body">
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-15">
                                <i class="la la-save"></i>
                                Save Profit Share Settings
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
