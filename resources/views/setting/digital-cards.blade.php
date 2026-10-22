@extends('demo.layout.app')
@section('title', 'Card Settings')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Card Settings</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('setting.basic') }}" class="text-muted">Settings</a></li>
                        <li class="breadcrumb-item"><a href="#" class="text-muted">Card Settings</a></li>
                    </ul>
                </div>
            </div>
            <a href="{{ route('admin.cards.index') }}" class="btn btn-light-primary btn-sm font-weight-bold">Manage Digital Cards</a>
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

                    <form method="POST" action="{{ route('admin.cards.settings.update') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Generation --}}
                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title font-weight-bolder text-dark">Card Generation</h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="form-group">
                                    <span class="switch switch-sm switch-icon">
                                        <label>
                                            <input type="checkbox" name="digital_card_enabled" value="1"
                                                   @checked(old('digital_card_enabled', $setting->digital_card_enabled ?? true))>
                                            <span></span>
                                        </label>
                                        <span class="ml-3 font-weight-bold">Allow members to generate digital cards</span>
                                    </span>
                                    <small class="text-muted d-block mt-1">When off, the "Generate Digital Card" button is hidden. Existing cards can still be shared and used.</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-4">
                                        <label class="font-weight-bold">Minimum Card Amount ($)</label>
                                        <input type="number" step="0.01" min="0.01" name="digital_card_min_amount"
                                               class="form-control @error('digital_card_min_amount') is-invalid @enderror"
                                               value="{{ old('digital_card_min_amount', $setting->digital_card_min_amount ?? 1) }}">
                                        @error('digital_card_min_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label class="font-weight-bold">Maximum Card Amount ($)</label>
                                        <input type="number" step="0.01" min="0.01" name="digital_card_max_amount"
                                               class="form-control @error('digital_card_max_amount') is-invalid @enderror"
                                               value="{{ old('digital_card_max_amount', $setting->digital_card_max_amount ?? 10000) }}">
                                        @error('digital_card_max_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <label class="font-weight-bold">Card Validity (days)</label>
                                        <input type="number" step="1" min="1" max="3650" name="digital_card_expiry_days"
                                               class="form-control @error('digital_card_expiry_days') is-invalid @enderror"
                                               value="{{ old('digital_card_expiry_days', $setting->digital_card_expiry_days ?? 365) }}">
                                        @error('digital_card_expiry_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="text-muted">365 = 1 year. Applies to <strong>newly generated</strong> cards only.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Security --}}
                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title font-weight-bolder text-dark">PIN Security</h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <label class="font-weight-bold">Wrong PIN Attempts Allowed</label>
                                        <input type="number" step="1" min="1" max="50" name="digital_card_max_failed_pin_attempts"
                                               class="form-control @error('digital_card_max_failed_pin_attempts') is-invalid @enderror"
                                               value="{{ old('digital_card_max_failed_pin_attempts', $setting->digital_card_max_failed_pin_attempts ?? 5) }}">
                                        @error('digital_card_max_failed_pin_attempts')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="text-muted">After this many wrong PINs, the card is temporarily locked for that member.</small>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="font-weight-bold">Lock Duration (minutes)</label>
                                        <input type="number" step="1" min="1" max="10080" name="digital_card_pin_lock_minutes"
                                               class="form-control @error('digital_card_pin_lock_minutes') is-invalid @enderror"
                                               value="{{ old('digital_card_pin_lock_minutes', $setting->digital_card_pin_lock_minutes ?? 60) }}">
                                        @error('digital_card_pin_lock_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="text-muted">Wrong attempts within this window are counted.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Appearance --}}
                        <div class="card card-custom gutter-b">
                            <div class="card-header border-0 py-5">
                                <h3 class="card-title font-weight-bolder text-dark">Card Logo</h3>
                            </div>
                            <div class="card-body pt-0">
                                <div class="mb-4 p-5 rounded text-center" style="background:#141414;">
                                    <img src="{{ asset($logoPath) }}?v={{ is_file(public_path($logoPath)) ? filemtime(public_path($logoPath)) : 0 }}" alt="Card logo" style="max-width:100%;max-height:90px;">
                                </div>
                                <div class="row align-items-end">
                                    <div class="col-md-8 mb-4">
                                        <label class="font-weight-bold">Upload New Logo (PNG)</label>
                                        <input type="file" name="logo" accept="image/png"
                                               class="form-control-file @error('logo') is-invalid @enderror">
                                        @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        <small class="text-muted">Transparent PNG, wide format (about 6:1), max 2 MB. Shown top-right on every card, including cards already issued.</small>
                                    </div>
                                    @if($hasCustomLogo)
                                        <div class="col-md-4 mb-4">
                                            <label class="checkbox">
                                                <input type="checkbox" name="remove_logo" value="1"><span></span>
                                                &nbsp;Restore default logo
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="text-right mb-8">
                            <button type="submit" class="btn btn-primary font-weight-bold px-8">Save Card Settings</button>
                        </div>
                    </form>

                    {{-- PIN-locked cards --}}
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 py-5">
                            <h3 class="card-title font-weight-bolder text-dark">
                                Currently PIN-Locked Cards
                                @if($lockedCards->isNotEmpty())
                                    <span class="badge badge-warning ml-2">{{ $lockedCards->count() }}</span>
                                @endif
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <p class="text-muted small">
                                These members entered too many wrong PINs and are seeing "Too many incorrect PIN attempts".
                                Resetting clears the lock immediately; the wrong attempts stay in the card history.
                            </p>
                            <table class="table table-sm table-vertical-center">
                                <thead><tr class="text-muted"><th>Card</th><th>Recipient</th><th>Wrong PINs</th><th>Locked Until</th><th></th></tr></thead>
                                <tbody>
                                    @forelse($lockedCards as $row)
                                        <tr>
                                            <td><a href="{{ route('admin.cards.show', $row['card']) }}">{{ $row['card']->maskedNumber() }}</a></td>
                                            <td class="small">{{ $row['card']->recipient?->name }} <span class="text-muted">@ {{ $row['card']->recipient?->username }}</span></td>
                                            <td>{{ $row['lock']['attempts'] }}</td>
                                            <td class="small text-nowrap">{{ $row['lock']['unlocks_at']->format('d M Y H:i') }}</td>
                                            <td class="text-right">
                                                <form method="POST" action="{{ route('admin.cards.reset-pin-lock', $row['card']) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="reason" value="Reset from Card Settings">
                                                    <button class="btn btn-sm btn-warning" onclick="return confirm('Reset the PIN lock for this card?')">Reset Lock</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">No cards are PIN-locked right now.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Audit --}}
                    <div class="card card-custom gutter-b">
                        <div class="card-header border-0 py-5">
                            <h3 class="card-title font-weight-bolder text-dark">Recent Changes</h3>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-sm">
                                <thead><tr class="text-muted"><th>When</th><th>By</th><th>Changes</th></tr></thead>
                                <tbody>
                                    @forelse($recentChanges as $ev)
                                        <tr>
                                            <td class="text-nowrap small">{{ $ev->created_at->format('d M Y H:i') }}</td>
                                            <td class="small">{{ $ev->actor?->name }} <span class="text-muted">@ {{ $ev->actor?->username }}</span></td>
                                            <td class="small">
                                                @foreach(($ev->meta ?? []) as $key => $change)
                                                    <div>
                                                        <span class="text-muted">{{ str_replace(['digital_card_', '_'], ['', ' '], $key) }}:</span>
                                                        {{ is_bool($change['from'] ?? null) ? (($change['from'] ?? false) ? 'on' : 'off') : ($change['from'] ?? '—') }}
                                                        →
                                                        <strong>{{ is_bool($change['to'] ?? null) ? (($change['to'] ?? false) ? 'on' : 'off') : ($change['to'] ?? '—') }}</strong>
                                                    </div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">No changes recorded yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
