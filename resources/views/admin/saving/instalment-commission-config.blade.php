@extends('demo.layout.app')
@section('title', 'Instalment Commission Rates')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <h5 class="text-dark font-weight-bold my-1 mr-5">Instalment Commission Rates</h5>
                <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('setting.saving') }}" class="text-muted">Saving Settings</a></li>
                    <li class="breadcrumb-item text-muted">Commission Rates</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4 py-3">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            @php
                $isEnabled       = $setting?->saving_instalment_config_enabled ?? false;
                $configuredCount = $configs->count();
                $levelLabels     = [1=>'L1 Direct', 2=>'L2', 3=>'L3', 4=>'L4', 5=>'L5', 6=>'L6', 7=>'L7'];
            @endphp

            {{-- ── Status + Toggle Card ──────────────────────────────────────── --}}
            <div class="card card-custom gutter-b shadow-sm"
                 style="border-left: 5px solid {{ $isEnabled ? '#f64e60' : '#E4E6EF' }};">
                <div class="card-body py-5 px-6">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:16px;">

                        {{-- Left: Icon + Info --}}
                        <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center justify-content-center rounded-lg mr-4 flex-shrink-0"
                                 style="width:54px;height:54px;background:{{ $isEnabled ? '#FFE2E5' : '#F5F5F5' }};">
                                <i class="fas fa-sliders-h fa-lg" style="color:{{ $isEnabled ? '#F64E60' : '#B5B5C3' }};"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center mb-2 flex-wrap" style="gap:8px;">
                                    <span class="font-weight-bolder text-dark" style="font-size:16px;">
                                        Custom Instalment Commission Rates
                                    </span>
                                    @if($isEnabled)
                                        <span class="badge badge-danger px-3 py-1" style="font-size:11px;">● ACTIVE</span>
                                    @else
                                        <span class="badge badge-secondary px-3 py-1" style="font-size:11px;">○ INACTIVE</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center flex-wrap text-muted font-size-sm" style="gap:10px;">
                                    <span>
                                        <i class="fas fa-table mr-1" style="font-size:10px;"></i>
                                        <strong class="{{ $configuredCount > 0 ? 'text-primary' : '' }}">{{ $configuredCount }}</strong>/25 instalments configured
                                    </span>
                                    <span>·</span>
                                    <strong class="{{ $totalCustom > 0 ? 'text-primary' : '' }}">{{ $totalCustom }}</strong> custom cells
                                    <span>·</span>
                                    <span>
                                        @if($isEnabled)
                                            Custom rates are <strong class="text-danger">overriding</strong> defaults.
                                        @else
                                            Default / campaign rates are in use.
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Toggle Form --}}
                        <form method="POST" action="{{ route('admin.saving.commission-config.toggle') }}">
                            @csrf
                            @if($isEnabled)
                                <button type="submit" class="btn font-weight-bold px-7 py-3"
                                        style="background:#FFF5F8;color:#F64E60;border:2px solid #F64E60;">
                                    <i class="fas fa-toggle-on fa-lg mr-2"></i> Disable Custom Rates
                                </button>
                            @else
                                <input type="hidden" name="saving_instalment_config_enabled" value="1">
                                <button type="submit" class="btn btn-primary font-weight-bold px-7 py-3">
                                    <i class="fas fa-toggle-off fa-lg mr-2"></i> Enable Custom Rates
                                </button>
                            @endif
                        </form>

                    </div>
                </div>
            </div>

            {{-- ── Danger Banner (when active) ───────────────────────────────── --}}
            @if($isEnabled)
            <div class="d-flex align-items-start rounded mb-5 px-5 py-4" style="background:#F64E60;">
                <i class="fas fa-exclamation-triangle fa-lg mr-3 flex-shrink-0" style="color:#fff;margin-top:2px;"></i>
                <div>
                    <div style="color:#fff;font-weight:700;font-size:15px;">
                        ⚠️ Custom Instalment Commission Rates are ACTIVE
                    </div>
                    <div style="color:#FFD6D9;font-size:13px;margin-top:3px;">
                        The rates configured in the matrix below are currently overriding default commission rates.
                        Click <strong style="color:#fff;">Disable Custom Rates</strong> above to revert to default / campaign rates.
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Priority Info ─────────────────────────────────────────────── --}}
            <div class="d-flex align-items-center rounded px-5 py-3 mb-5 font-size-sm"
                 style="background:#F3F6F9;border-left:4px solid #3699FF;">
                <i class="fas fa-info-circle text-primary mr-3 flex-shrink-0"></i>
                <div class="text-muted">
                    <strong class="text-dark">Priority when custom rates are enabled:</strong>
                    &nbsp;
                    <span class="badge badge-primary">1st</span> This page
                    <span class="text-muted mx-1">→</span>
                    <span class="badge badge-warning">2nd</span> Campaign (if active)
                    <span class="text-muted mx-1">→</span>
                    <span class="badge badge-secondary">3rd</span> Default (Saving Settings)
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <span style="color:#3699FF;font-weight:600;">Blue cell</span> = custom value
                    &nbsp;·&nbsp;
                    Grey = using default
                    &nbsp;·&nbsp;
                    Click <i class="fas fa-check" style="font-size:9px;"></i> to save &nbsp;·&nbsp;
                    Click <i class="fas fa-times" style="font-size:9px;color:#f64e60;"></i> to reset a custom cell
                </div>
            </div>

            {{-- ── Commission Rate Matrix ─────────────────────────────────────── --}}
            <div class="card card-custom gutter-b">
                <div class="card-header border-0 py-5 px-6">
                    <div class="d-flex align-items-center">
                        <h3 class="card-title font-weight-bolder text-dark mb-0 mr-3">Commission Rate Matrix</h3>
                        <span class="text-muted font-size-sm">25 instalments × 7 levels</span>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('setting.saving') }}" class="btn btn-sm btn-light font-weight-bold">
                            <i class="fas fa-cog mr-1"></i> Default Rates
                        </a>
                    </div>
                </div>
                <div class="card-body p-0 pb-4">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" style="min-width:870px;">

                            {{-- Header --}}
                            <thead>
                                <tr style="background:#F3F6F9;">
                                    <th class="text-center align-middle py-4 px-3"
                                        style="width:56px;min-width:56px;border-right:2px solid #dee2e6;font-size:12px;color:#B5B5C3;font-weight:600;letter-spacing:.5px;">
                                        #
                                    </th>
                                    @foreach($levelLabels as $lv => $lbl)
                                    <th class="text-center align-middle py-4 px-2" style="min-width:118px;">
                                        <span class="font-weight-bolder text-dark d-block" style="font-size:12px;">{{ $lbl }}</span>
                                        <span class="text-muted" style="font-size:10px;font-weight:500;">default {{ $defaults[$lv] }}%</span>
                                    </th>
                                    @endforeach
                                    <th class="text-center align-middle py-4"
                                        style="width:56px;min-width:56px;font-size:11px;color:#B5B5C3;font-weight:600;">
                                        CLEAR
                                    </th>
                                </tr>
                            </thead>

                            {{-- Rows --}}
                            <tbody>
                                @for($inst = 1; $inst <= 25; $inst++)
                                @php
                                    $instConfigs = $configs->get($inst, collect());
                                    $hasCustom   = $instConfigs->isNotEmpty();
                                @endphp
                                <tr>
                                    {{-- Instalment # --}}
                                    <td class="text-center align-middle p-2"
                                        style="border-right:2px solid #dee2e6;background:{{ $hasCustom ? '#EEF1FF' : '#FAFAFA' }};">
                                        <span class="font-weight-bolder {{ $hasCustom ? 'text-primary' : 'text-muted' }}"
                                              style="font-size:13px;">#{{ $inst }}</span>
                                        @if($hasCustom)
                                            <div style="font-size:9px;color:#3699FF;margin-top:1px;font-weight:600;letter-spacing:.3px;">CUSTOM</div>
                                        @endif
                                    </td>

                                    {{-- Level cells --}}
                                    @foreach($levelLabels as $lv => $lbl)
                                    @php $cfg = $instConfigs->get($lv); @endphp
                                    <td class="p-1 align-middle"
                                        style="{{ $cfg ? 'background:#EEF6FF!important;' : '' }}">
                                        <form method="POST"
                                              action="{{ route('admin.saving.commission-config.update-level') }}"
                                              class="d-flex justify-content-center">
                                            @csrf
                                            <input type="hidden" name="instalment_number" value="{{ $inst }}">
                                            <input type="hidden" name="level" value="{{ $lv }}">
                                            <div class="input-group input-group-sm" style="max-width:{{ $cfg ? '112px' : '100px' }};">
                                                <input type="number" name="percentage" step="0.01" min="0" max="100"
                                                       value="{{ $cfg ? $cfg->percentage : '' }}"
                                                       placeholder="{{ $defaults[$lv] }}"
                                                       class="form-control form-control-sm text-center"
                                                       style="font-size:11px;padding:3px 4px;{{ $cfg ? 'border-color:#3699FF!important;color:#3699FF;font-weight:700;' : 'color:#6c757d;' }}">
                                                <div class="input-group-append">
                                                    <button type="submit" name="action" value="save"
                                                            class="btn btn-sm {{ $cfg ? 'btn-primary' : 'btn-outline-secondary' }} px-2"
                                                            title="Save" style="font-size:9px;padding:3px 6px;">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    @if($cfg)
                                                    <button type="submit" name="action" value="reset"
                                                            class="btn btn-sm btn-outline-danger px-2"
                                                            title="Reset to default" style="font-size:9px;padding:3px 6px;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                    @endforeach

                                    {{-- Clear row --}}
                                    <td class="text-center align-middle p-1">
                                        @if($hasCustom)
                                        <form method="POST"
                                              action="{{ route('admin.saving.commission-config.delete-instalment', $inst) }}"
                                              onsubmit="return confirm('Clear all custom rates for Instalment #{{ $inst }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-sm btn-light-danger"
                                                    title="Clear all custom rates for #{{ $inst }}"
                                                    style="width:28px;height:28px;">
                                                <i class="fas fa-trash-alt" style="font-size:10px;"></i>
                                            </button>
                                        </form>
                                        @else
                                            <span class="text-muted" style="font-size:13px;">—</span>
                                        @endif
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
@endsection
