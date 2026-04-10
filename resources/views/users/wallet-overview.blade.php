@extends('demo.layout.app')
@section('title', 'Wallet Overview — ' . $user->username)

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between">
            <div class="mr-5 d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">Wallet Overview</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-muted">Users</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.info', $user->id) }}" class="text-muted">{{ $user->username }}</a></li>
                    <li class="breadcrumb-item text-muted">Wallet Overview</li>
                </ul>
            </div>
            <div>
                <a href="{{ route('user.info', $user->id) }}" class="mr-2 btn btn-sm btn-light-primary rounded-0">
                    <i class="mr-1 fas fa-user"></i> User Info
                </a>
                <a href="{{ route('admin.user.team', $user->id) }}" class="btn btn-sm btn-light-success rounded-0">
                    <i class="mr-1 fas fa-sitemap"></i> View Team
                </a>
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            {{-- User Header --}}
            <div class="mb-6 card card-custom">
                <div class="py-4 card-body d-flex align-items-center">
                    <div class="mr-4">
                        <img src="{{ $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png') }}"
                             class="rounded-circle" width="55" height="55" style="object-fit:cover;">
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-0 font-weight-bold">{{ $user->name }}
                            <span class="ml-2 font-size-sm text-muted">@ {{ $user->username }}</span>
                            @if($user->account_type === 'saving')
                                <span class="ml-2 badge badge-info">Saving Account</span>
                            @else
                                <span class="ml-2 badge badge-primary">Standard Investment</span>
                            @endif
                        </h4>
                        <div class="mt-1 text-muted font-size-sm">
                            {{ $user->email }} &bull; {{ $user->phone_number }}
                            &bull; Joined {{ $user->created_at->format('d M Y') }}
                            &bull; Sponsor: <strong>{{ optional($user->parent)->username ?? '—' }}</strong>
                        </div>
                    </div>
                    <div class="text-right">
                        @if($user->can_login)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-danger">Not Activated</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($user->account_type === 'saving')
            {{-- ── Saving Account Wallet Summary ─────────────────────────────── --}}
            <div class="mb-4 font-weight-bold text-uppercase text-muted font-size-sm">Saving Account Wallets</div>
            <div class="mb-6 row">
                <div class="mb-4 col-md-3">
                    <div class="text-white card card-custom bg-success h-100">
                        <div class="py-5 text-center card-body">
                            <div class="mb-1 font-size-sm">Saving Deposit Balance</div>
                            <div style="font-size:1.5rem;font-weight:700;">${{ number_format($savingSummary['saving_deposit'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="mb-4 col-md-3">
                    <div class="text-white card card-custom bg-warning h-100">
                        <div class="py-5 text-center card-body">
                            <div class="mb-1 font-size-sm">Saving ROI Balance</div>
                            <div style="font-size:1.5rem;font-weight:700;">${{ number_format($savingSummary['saving_roi'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="mb-4 col-md-3">
                    <div class="text-white card card-custom bg-primary h-100">
                        <div class="py-5 text-center card-body">
                            <div class="mb-1 font-size-sm">Direct Commissions</div>
                            <div style="font-size:1.5rem;font-weight:700;">${{ number_format($savingSummary['saving_direct'], 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="mb-4 col-md-3">
                    <div class="text-white card card-custom bg-info h-100">
                        <div class="py-5 text-center card-body">
                            <div class="mb-1 font-size-sm">Indirect Commissions</div>
                            <div style="font-size:1.5rem;font-weight:700;">${{ number_format($savingSummary['saving_indirect'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ── General Wallet Summary ─────────────────────────────────────── --}}
            <div class="mb-4 font-weight-bold text-uppercase text-muted font-size-sm">
                {{ $user->account_type === 'saving' ? 'Other Wallets' : 'Wallet Balances' }}
            </div>
            <div class="mb-6 row">
                @php
                    $tiles = [
                        ['label' => 'Online Wallet',          'value' => $summary['online'],                'color' => '#1bc5bd'],
                        ['label' => 'Direct / Indirect',      'value' => $summary['direct_indirect'],       'color' => '#3699ff'],
                        ['label' => '↳ Direct',               'value' => $summary['direct_balance'],        'color' => '#0073e6'],
                        ['label' => '↳ Indirect',             'value' => $summary['indirect_balance'],      'color' => '#8950fc'],
                        ['label' => 'ROI Wallet',             'value' => $summary['roi'],                   'color' => '#f64e60'],
                        ['label' => 'Reward Wallet',          'value' => $summary['reward'],                'color' => '#ffa800'],
                        ['label' => 'Profit Share',           'value' => $summary['profit_share'],          'color' => '#1e1e2d'],
                        ['label' => 'Designation Incentive',  'value' => $summary['designation_incentive'], 'color' => '#187de4'],
                    ];
                @endphp
                @foreach($tiles as $tile)
                <div class="mb-4 col-6 col-md-3">
                    <div class="card card-custom h-100" style="border-top: 3px solid {{ $tile['color'] }}">
                        <div class="py-4 text-center card-body">
                            <div class="mb-1 text-muted font-size-xs">{{ $tile['label'] }}</div>
                            <div class="font-weight-bold" style="font-size:1.25rem;color:{{ $tile['color'] }}">
                                ${{ number_format($tile['value'], 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── Recent ROI ─────────────────────────────────────────────────── --}}
            <div class="mb-6 card card-custom gutter-b">
                <div class="py-5 border-0 card-header">
                    <h3 class="card-title font-weight-bolder text-dark">
                        {{ $user->account_type === 'saving' ? 'Saving ROI Payments' : 'ROI Payments' }}
                        <span class="ml-2 text-muted font-size-sm font-weight-normal">(last 20)</span>
                    </h3>
                </div>
                <div class="pt-0 card-body">
                    @if($recentRoi->isEmpty())
                        <p class="text-muted">No ROI payments found.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRoi as $entry)
                                <tr>
                                    <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                    <td class="font-weight-bold text-success">${{ number_format($entry->balance, 2) }}</td>
                                    <td class="text-muted">{{ $entry->description ?: '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Recent Commissions ──────────────────────────────────────────── --}}
            <div class="mb-6 card card-custom gutter-b">
                <div class="py-5 border-0 card-header">
                    <h3 class="card-title font-weight-bolder text-dark">
                        {{ $user->account_type === 'saving' ? 'Saving Commissions' : 'Direct / Indirect Commissions' }}
                        <span class="ml-2 text-muted font-size-sm font-weight-normal">(last 20)</span>
                    </h3>
                </div>
                <div class="pt-0 card-body">
                    @if($recentCommissions->isEmpty())
                        <p class="text-muted">No commission entries found.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Level</th>
                                    <th>Amount</th>
                                    <th>From User</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCommissions as $entry)
                                <tr>
                                    <td>{{ $entry->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($entry->commission_type === 'direct')
                                            <span class="badge badge-light-primary">Direct</span>
                                        @else
                                            <span class="badge badge-light-info">Indirect</span>
                                        @endif
                                    </td>
                                    <td>{{ $entry->level !== '-' ? 'Level '.$entry->level : '—' }}</td>
                                    <td class="font-weight-bold text-primary">${{ number_format($entry->balance, 2) }}</td>
                                    <td class="text-muted">
                                        {{ optional(\App\Models\User::find($entry->wallet_from))->username ?? '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
