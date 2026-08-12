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

                    @if(session('level_success'))
                        <div class="alert alert-info alert-dismissible fade show">
                            <i class="fas fa-check-circle mr-2"></i>{{ session('level_success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    {{-- ── Campaign Active Banner ──────────────────────────────── --}}
                    @if($setting->saving_campaign_enabled)
                    <div class="mb-6" style="background:#f64e60;border-radius:8px;padding:1.2rem 1.5rem;">
                        <div class="d-flex align-items-center">
                            <span style="font-size:2rem;margin-right:1rem;">⚠️</span>
                            <div style="color:#fff!important;">
                                <strong class="d-block font-size-h6" style="color:#fff!important;">Campaign / Bonus Commission is ACTIVE!</strong>
                                <span class="font-size-sm" style="color:#fff!important;">
                                    Instalment payments are currently using <strong style="color:#fff!important;text-decoration:underline;">Campaign rates</strong>,
                                    not the default rates. Review the current campaign rates in the Campaign section below.
                                    &nbsp;—&nbsp;
                                    To revert to default rates, turn the toggle <strong style="color:#fff!important;">OFF</strong> in the Campaign section.
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ── Form 1: Registration & ROI ───────────────────────── --}}
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

                        <div class="text-right mb-6">
                            <button type="submit" class="btn btn-primary font-weight-bold px-8">
                                Save Registration &amp; ROI Settings
                            </button>
                        </div>

                    </form>
                    {{-- ── End Form 1 ──────────────────────────────────────── --}}

                    {{-- ── Commission Levels (separate per-level forms) ──────── --}}
                    @php
                        $levelLabels   = [1 => 'Direct', 2 => 'L2', 3 => 'L3', 4 => 'L4', 5 => 'L5', 6 => 'L6', 7 => 'L7'];
                        $levelDefaults = [1 => 7.0, 2 => 2.0, 3 => 1.0, 4 => 1.0, 5 => 1.0, 6 => 1.0, 7 => 1.0];
                    @endphp

                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title font-weight-bolder text-dark">Referral Commission Levels (7 Levels)</h3>
                            </div>
                            <div class="card-body pt-0">
                                <p class="text-muted font-size-sm mb-4">
                                    Har level alag save hota hai — sirf jo level update karo wahi change hoga, baqi same rahenge.
                                </p>

                                @if(session('level_success'))
                                    <div class="alert alert-success alert-dismissible fade show mb-4">
                                        {{ session('level_success') }}
                                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                    </div>
                                @endif

                                <div class="table-responsive">
                                    <table class="table table-bordered table-vertical-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:120px;">Level</th>
                                                <th>Current Rate</th>
                                                <th style="width:240px;">New Value</th>
                                                <th style="width:100px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for($level = 1; $level <= 7; $level++)
                                            @php $currentVal = $setting->{'saving_commission_l'.$level} ?? $levelDefaults[$level]; @endphp
                                            <tr>
                                                <td>
                                                    <span class="font-weight-bold">Level {{ $level }}</span>
                                                    <span class="badge badge-light-{{ $level === 1 ? 'primary' : 'secondary' }} ml-1">{{ $levelLabels[$level] }}</span>
                                                </td>
                                                <td>
                                                    <span class="font-weight-bold text-primary font-size-lg">{{ $currentVal }}%</span>
                                                    <small class="text-muted ml-2">($19 × {{ $currentVal }}% = ${{ number_format(19 * $currentVal / 100, 2) }})</small>
                                                </td>
                                                <td>
                                                    <form method="POST" action="{{ route('setting.saving.update-level') }}" class="d-flex align-items-center gap-2">
                                                        @csrf
                                                        <input type="hidden" name="type" value="default">
                                                        <input type="hidden" name="level" value="{{ $level }}">
                                                        <div class="input-group input-group-sm" style="max-width:160px;">
                                                            <input type="number" step="0.01" min="0" max="100"
                                                                   name="value"
                                                                   class="form-control"
                                                                   placeholder="{{ $currentVal }}"
                                                                   value="">
                                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                        </div>
                                                </td>
                                                <td>
                                                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold">Save</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Campaign / Bonus Commission --}}
                        <div class="card card-custom gutter-b border-warning">
                            <div class="card-header border-0 py-5" style="background:linear-gradient(135deg,#fff8e1,#fffde7);">
                                <h3 class="card-title font-weight-bolder text-dark">
                                    <i class="fas fa-star text-warning mr-2"></i> Campaign / Bonus Commission
                                </h3>
                                <div class="card-toolbar">
                                    @if($setting->saving_campaign_enabled)
                                        <span class="badge badge-success px-4 py-2 font-size-sm">
                                            <i class="fas fa-circle mr-1" style="font-size:8px;"></i> Campaign LIVE
                                        </span>
                                    @else
                                        <span class="badge badge-secondary px-4 py-2 font-size-sm">Campaign OFF</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body pt-2">
                                <p class="text-muted font-size-sm mb-4">
                                    When campaign is <strong>ON</strong>, these rates will be used for commissions. When <strong>OFF</strong>, the default rates above will apply.
                                </p>

                                {{-- Enable toggle --}}
                                <form method="POST" action="{{ route('setting.saving.toggle-campaign') }}" class="mb-5" id="campaignToggleForm">
                                    @csrf
                                    <div class="d-flex align-items-center">
                                        <span class="switch switch-outline switch-icon switch-success mr-3">
                                            <label>
                                                <input type="checkbox" name="saving_campaign_enabled" value="1"
                                                    onchange="this.form.submit()"
                                                    {{ $setting->saving_campaign_enabled ? 'checked' : '' }}>
                                                <span></span>
                                            </label>
                                        </span>
                                        <span class="font-weight-bold">Enable Campaign Rates</span>
                                        <small class="text-muted ml-3">Toggle karte hi save ho jayega</small>
                                    </div>
                                </form>

                                {{-- Campaign per-level forms --}}
                                <div class="table-responsive">
                                    <table class="table table-bordered table-vertical-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:120px;">Level</th>
                                                <th>Default Rate</th>
                                                <th>Campaign Rate</th>
                                                <th style="width:240px;">New Value</th>
                                                <th style="width:100px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for($level = 1; $level <= 7; $level++)
                                            @php
                                                $defaultVal  = $setting->{'saving_commission_l'.$level}  ?? $levelDefaults[$level];
                                                $campaignVal = $setting->{'saving_campaign_l'.$level};
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="font-weight-bold">Level {{ $level }}</span>
                                                    <span class="badge badge-light-{{ $level === 1 ? 'warning' : 'secondary' }} ml-1">{{ $levelLabels[$level] }}</span>
                                                </td>
                                                <td><span class="text-muted">{{ $defaultVal }}%</span></td>
                                                <td>
                                                    @if($campaignVal !== null)
                                                        <span class="font-weight-bold text-warning font-size-lg">{{ $campaignVal }}%</span>
                                                        <small class="text-muted ml-1">($19 = ${{ number_format(19 * $campaignVal / 100, 2) }})</small>
                                                    @else
                                                        <span class="text-muted font-italic">same as default</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <form method="POST" action="{{ route('setting.saving.update-level') }}" class="d-flex align-items-center">
                                                        @csrf
                                                        <input type="hidden" name="type" value="campaign">
                                                        <input type="hidden" name="level" value="{{ $level }}">
                                                        <div class="input-group input-group-sm" style="max-width:160px;">
                                                            <input type="number" step="0.01" min="0" max="100"
                                                                   name="value"
                                                                   class="form-control"
                                                                   placeholder="{{ $campaignVal ?? $defaultVal }}"
                                                                   value="">
                                                            <div class="input-group-append"><span class="input-group-text">%</span></div>
                                                        </div>
                                                </td>
                                                <td>
                                                        <button type="submit" class="btn btn-sm btn-warning font-weight-bold">Save</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
