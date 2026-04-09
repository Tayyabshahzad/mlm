@extends('demo.layout.app')
@section('title', 'Saving Account Settings')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Saving Account Settings</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('setting.basic') }}" class="text-muted">Settings</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Saving Account</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container">
            <div class="d-flex flex-row">
                @include('setting.side-bar')

                <div class="flex-row-fluid ml-lg-8">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('setting.saving.update') }}">
                        @csrf

                        {{-- Registration & Plan --}}
                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title font-weight-bolder text-dark">Registration & Plan</h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    <div class="col-md-3 mb-4">
                                        <label class="font-weight-bold">Registration Fee ($)</label>
                                        <input type="number" step="0.01" min="0" name="saving_registration_fee"
                                               class="form-control @error('saving_registration_fee') is-invalid @enderror"
                                               value="{{ old('saving_registration_fee', $setting->saving_registration_fee ?? 5) }}">
                                        @error('saving_registration_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="text-muted">Minimum fee to register a saving account</small>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label class="font-weight-bold">Min. First Deposit ($)</label>
                                        <input type="number" step="0.01" min="0" name="saving_min_deposit"
                                               class="form-control @error('saving_min_deposit') is-invalid @enderror"
                                               value="{{ old('saving_min_deposit', $setting->saving_min_deposit ?? 19) }}">
                                        @error('saving_min_deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="text-muted">Instalment #1 amount to activate ROI</small>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label class="font-weight-bold">Monthly Instalment ($)</label>
                                        <input type="number" step="0.01" min="0" name="saving_monthly_instalment"
                                               class="form-control @error('saving_monthly_instalment') is-invalid @enderror"
                                               value="{{ old('saving_monthly_instalment', $setting->saving_monthly_instalment ?? 19) }}">
                                        @error('saving_monthly_instalment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="text-muted">Instalments #2–25</small>
                                    </div>
                                    <div class="col-md-3 mb-4">
                                        <label class="font-weight-bold">Plan Duration (Months)</label>
                                        <input type="number" min="1" name="saving_plan_months"
                                               class="form-control @error('saving_plan_months') is-invalid @enderror"
                                               value="{{ old('saving_plan_months', $setting->saving_plan_months ?? 25) }}">
                                        @error('saving_plan_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="text-muted">Total number of instalments</small>
                                    </div>
                                </div>

                                <div class="alert alert-info mt-2 mb-0 font-size-sm">
                                    <strong>Max registration amount</strong> = Registration Fee + Min. First Deposit
                                    = <strong>${{ ($setting->saving_registration_fee ?? 5) + ($setting->saving_min_deposit ?? 19) }}</strong>
                                    (enforced on the registration form)
                                </div>
                            </div>
                        </div>

                        {{-- Daily ROI --}}
                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title font-weight-bolder text-dark">Daily ROI Rate</h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row align-items-end">
                                    <div class="col-md-4 mb-4">
                                        <label class="font-weight-bold">Daily ROI % <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" max="100" name="saving_roi_daily_rate"
                                                   class="form-control @error('saving_roi_daily_rate') is-invalid @enderror"
                                                   value="{{ old('saving_roi_daily_rate', $setting->saving_roi_daily_rate ?? 0.1) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                            @error('saving_roi_daily_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <small class="text-muted">Applied to user's <code>saving_total_deposited</code> balance daily (Mon–Thu, Sat, Sun)</small>
                                    </div>
                                    <div class="col-md-8 mb-4">
                                        <div class="alert alert-warning mb-0 font-size-sm">
                                            <strong>Cron schedule:</strong> Daily at <strong>23:59</strong> (PKT), skipping <strong>Fridays</strong>.<br>
                                            ROI only fires for users with <code>can_login = true</code>, <code>saving_registration_completed = true</code>, and <code>saving_total_deposited &gt; 0</code>.
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $rate    = $setting->saving_roi_daily_rate ?? 0.1;
                                    $example = 19;
                                @endphp
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered text-center" style="max-width:400px;">
                                        <thead class="thead-light">
                                            <tr><th>Deposit Base</th><th>Daily ROI @ {{ $rate }}%</th><th>Monthly (~30 days)</th></tr>
                                        </thead>
                                        <tbody>
                                            @foreach([19, 38, 57, 100, 190] as $dep)
                                            <tr>
                                                <td>${{ $dep }}</td>
                                                <td>${{ number_format($dep * $rate / 100, 4) }}</td>
                                                <td>${{ number_format($dep * $rate / 100 * 30, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Commission Levels --}}
                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title font-weight-bolder text-dark">Referral Commission Levels (7 Levels)</h3>
                            </div>
                            <div class="card-body pt-0">
                                <p class="text-muted font-size-sm mb-4">
                                    These percentages are applied to the first instalment deposit amount when a saving account user's instalment #1 is confirmed.
                                </p>
                                <div class="row">
                                    @php
                                        $defaults = [1 => 7.0, 2 => 2.0, 3 => 1.0, 4 => 1.0, 5 => 1.0, 6 => 1.0, 7 => 1.0];
                                        $labels   = [1 => 'Direct', 2 => 'Indirect L2', 3 => 'L3', 4 => 'L4', 5 => 'L5', 6 => 'L6', 7 => 'L7'];
                                    @endphp
                                    @for($level = 1; $level <= 7; $level++)
                                    <div class="col-md-3 mb-4">
                                        <label class="font-weight-bold">
                                            Level {{ $level }}
                                            <span class="badge badge-light-{{ $level === 1 ? 'primary' : 'secondary' }} ml-1">{{ $labels[$level] }}</span>
                                        </label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" max="100"
                                                   name="saving_commission_l{{ $level }}"
                                                   class="form-control @error('saving_commission_l'.$level) is-invalid @enderror"
                                                   value="{{ old('saving_commission_l'.$level, $setting->{'saving_commission_l'.$level} ?? $defaults[$level]) }}">
                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                        </div>
                                        @error('saving_commission_l'.$level)
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endfor
                                </div>

                                <div class="alert alert-light-primary mt-2 mb-0 font-size-sm">
                                    <strong>Example:</strong> If instalment #1 = $19 and Level 1 = 7%,
                                    the direct sponsor earns <strong>${{ number_format(19 * ($setting->saving_commission_l1 ?? 7) / 100, 2) }}</strong>.
                                </div>
                            </div>
                        </div>

                        <div class="text-right mb-8">
                            <button type="submit" class="btn btn-primary font-weight-bold px-8">
                                Save Saving Account Settings
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
