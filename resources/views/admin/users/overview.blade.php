@extends('demo.layout.app')
@section('title', 'User Overview — ' . $user->username)

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">

    {{-- Subheader --}}
    <div class="py-2 subheader py-lg-6 subheader-solid" id="kt_subheader">
        <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
            <div class="d-flex align-items-baseline">
                <h5 class="my-1 mr-5 text-dark font-weight-bold">User Overview</h5>
                <ul class="p-0 my-2 breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold font-size-sm">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-muted">Users</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('user.info', $user->id) }}" class="text-muted">{{ $user->username }}</a></li>
                    <li class="breadcrumb-item text-muted">Overview</li>
                </ul>
            </div>
            <div class="d-flex" style="gap:.5rem;">
                <a href="{{ route('user.info', $user->id) }}" class="btn btn-sm btn-light-primary">
                    <i class="mr-1 fas fa-user-edit"></i> Edit Profile
                </a>
                <a href="{{ route('admin.user.wallets', $user->id) }}" class="btn btn-sm btn-light-info">
                    <i class="mr-1 fas fa-wallet"></i> Wallet
                </a>
                <a href="{{ route('admin.user.team', $user->id) }}" class="btn btn-sm btn-light-success">
                    <i class="mr-1 fas fa-sitemap"></i> Team Tree
                </a>
                @if($user->account_type === 'saving' || $user->saving_enrolled)
                <a href="{{ route('admin.saving.show', $user->id) }}" class="btn btn-sm btn-light-warning">
                    <i class="mr-1 fas fa-piggy-bank"></i> Saving Account
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="flex-column-fluid">
        <div class="container">

            {{-- ── User Header Card ─────────────────────────────────────────── --}}
            <div class="mb-6 card card-custom">
                <div class="py-5 card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-4 symbol symbol-60 symbol-circle">
                            <img src="{{ $user->getFirstMediaUrl('user_profile_images', 'thumb') ?: asset('assets/custom-images/fav-icon.png') }}"
                                 alt="{{ $user->name }}" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="flex-wrap d-flex align-items-center" style="gap:.4rem;">
                                <h4 class="mb-0 font-weight-bold text-dark">{{ $user->name }}</h4>
                                <span class="text-muted font-size-sm"> @ {{ $user->username }}</span>
                                @if($user->account_type === 'saving')
                                    <span class="badge badge-info">Saving Plan</span>
                                @else
                                    <span class="badge badge-primary">Standard Investment</span>
                                @endif
                                @if($user->saving_enrolled)
                                    <span class="badge badge-warning text-dark">Enrolled (Saving)</span>
                                @endif
                                @if($user->can_login && !$user->blocked)
                                    <span class="badge badge-success">Active</span>
                                @elseif($user->blocked)
                                    <span class="badge badge-danger">Blocked</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                                @if($currentRank)
                                    <span class="badge badge-dark">{{ $currentRank->rank_name }}</span>
                                @endif
                            </div>
                            <div class="flex-wrap mt-1 text-muted font-size-sm d-flex" style="gap:1rem;">
                                <span><i class="mr-1 fas fa-envelope"></i>{{ $user->email }}</span>
                                @if($user->phone_number)
                                    <span><i class="mr-1 fas fa-phone"></i>{{ $user->phone_number }}</span>
                                @endif
                                <span><i class="mr-1 fas fa-calendar"></i>Joined {{ $user->created_at->format('d M Y') }}</span>
                                <span><i class="mr-1 fas fa-user-friends"></i>Sponsor: <strong>{{ optional($user->parent)->username ?? '—' }}</strong></span>
                                <span><i class="mr-1 fas fa-hashtag"></i>ID: {{ $user->id }}</span>
                            </div>
                        </div>
                        <div class="ml-4 text-right">
                            <div class="font-size-h4 font-weight-bold text-success">
                                ${{ number_format($walletSummary['total_earnings'], 2) }}
                            </div>
                            <div class="text-muted font-size-sm">Total Earnings</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Summary Tiles ─────────────────────────────────────────────── --}}
            <div class="mb-6 row">
                @php
                    $tiles = [
                        ['Online Wallet',       $walletSummary['online'],          'bg-success',  'fas fa-wallet'],
                        ['Commissions',         $walletSummary['direct_indirect'],  'bg-primary',  'fas fa-hand-holding-usd'],
                        ['ROI Earned',          $walletSummary['roi'],              'bg-warning',  'fas fa-chart-line'],
                        ['Team Size (Standard)',$standardTotalTeam,                'bg-info',     'fas fa-users', true],
                    ];
                @endphp
                @foreach($tiles as $tile)
                <div class="mb-4 col-md-3">
                    <div class="card card-custom {{ $tile[2] }} text-white h-100">
                        <div class="py-5 card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="mb-1 font-size-sm">{{ $tile[0] }}</div>
                                <div style="font-size:1.6rem;font-weight:700;">
                                    @if(!isset($tile[4]))$@endif{{ number_format($tile[1], isset($tile[4]) ? 0 : 2) }}
                                </div>
                            </div>
                            <i class="{{ $tile[3] }}" style="font-size:2rem;opacity:.6;"></i>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── Tabs ──────────────────────────────────────────────────────── --}}
            <div class="card card-custom">
                <div class="pt-5 border-0 card-header">
                    <ul class="nav nav-tabs nav-bold nav-tabs-line" id="overviewTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-network">
                                <i class="mr-2 fas fa-sitemap"></i>Network
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-wallet">
                                <i class="mr-2 fas fa-wallet"></i>Wallet & Earnings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-commissions">
                                <i class="mr-2 fas fa-hand-holding-usd"></i>Commissions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-investments">
                                <i class="mr-2 fas fa-chart-bar"></i>Investments
                            </a>
                        </li>
                        @if($savingSummary)
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-saving">
                                <i class="mr-2 fas fa-dollar-sign"></i>Saving Plan
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-withdrawals">
                                <i class="mr-2 fas fa-money-bill-wave"></i>Withdrawals
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-activity">
                                <i class="mr-2 fas fa-history"></i>Activity
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="pt-4 card-body">
                    <div class="tab-content">

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- TAB: Network                                               --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade show active" id="tab-network">

                            <div class="mb-6 row">
                                <div class="col-md-6">
                                    <h6 class="pb-2 mb-4 font-weight-bold text-dark border-bottom">
                                        <i class="mr-2 fas fa-network-wired text-primary"></i>Standard Plan Network
                                    </h6>
                                    <div class="mb-4 row">
                                        <div class="col-6">
                                            <div class="p-4 text-center rounded bg-light">
                                                <div class="text-black font-size-h3 font-weight-bold">{{ $standardTotalTeam }}</div>
                                                <div class="text-white font-size-sm">Total Team</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-4 text-center rounded bg-light">
                                                <div class="text-black font-size-h3 font-weight-bold">{{ $standardActiveCount }}</div>
                                                <div class="text-white font-size-sm">Active Members</div>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-sm table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Level</th>
                                                <th class="text-center">Members</th>
                                                <th>Bar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for($lvl = 1; $lvl <= 7; $lvl++)
                                            @php $cnt = $standardTree->get($lvl, 0); @endphp
                                            <tr>
                                                <td><span class="badge badge-primary">L{{ $lvl }}</span></td>
                                                <td class="text-center font-weight-bold">{{ $cnt }}</td>
                                                <td style="width:40%">
                                                    @if($cnt > 0)
                                                    <div class="progress" style="height:8px;">
                                                        <div class="progress-bar bg-primary" style="width:{{ min(100, $cnt * 5) }}%"></div>
                                                    </div>
                                                    @else
                                                    <span class="text-muted font-size-xs">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="pb-2 mb-4 font-weight-bold text-dark border-bottom">
                                        <i class="mr-2 fas fa-piggy-bank text-warning"></i>Saving Plan Network
                                    </h6>
                                    <div class="mb-4 row">
                                        <div class="col-6">
                                            <div class="p-4 text-center rounded bg-light">
                                                <div class="text-black font-size-h3 font-weight-bold">{{ $savingTotalTeam }}</div>
                                                <div class="text-white font-size-sm">Total Team</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-4 text-center rounded bg-light">
                                                <div class="text-black font-size-h3 font-weight-bold">{{ $savingTree->get(1, 0) }}</div>
                                                <div class="text-white font-size-sm">Direct Referrals</div>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-sm table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Level</th>
                                                <th class="text-center">Members</th>
                                                <th>Bar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for($lvl = 1; $lvl <= 7; $lvl++)
                                            @php $cnt = $savingTree->get($lvl, 0); @endphp
                                            <tr>
                                                <td><span class="badge badge-warning text-dark">L{{ $lvl }}</span></td>
                                                <td class="text-center font-weight-bold">{{ $cnt }}</td>
                                                <td style="width:40%">
                                                    @if($cnt > 0)
                                                    <div class="progress" style="height:8px;">
                                                        <div class="progress-bar bg-warning" style="width:{{ min(100, $cnt * 5) }}%"></div>
                                                    </div>
                                                    @else
                                                    <span class="text-muted font-size-xs">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>{{-- /tab-network --}}

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- TAB: Wallet & Earnings                                     --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade" id="tab-wallet">

                            <div class="mb-6 row">
                                @php
                                    $walletTiles = [
                                        ['Online / Liquid',        $walletSummary['online'],                '#1bc5bd'],
                                        ['Total Commissions',      $walletSummary['direct_indirect'],        '#3699ff'],
                                        ['   Direct Commission', $walletSummary['direct_balance'],         '#187de4'],
                                        ['   Indirect Commission',$walletSummary['indirect_balance'],      '#5f5cf1'],
                                        ['ROI Earned',             $walletSummary['roi'],                   '#f64e60'],
                                        ['Reward',                 $walletSummary['reward'],                '#ffa800'],
                                        ['Profit Share',           $walletSummary['profit_share'],          '#1bc5bd'],
                                        ['Designation Incentive',  $walletSummary['designation_incentive'],'#8950fc'],
                                    ];
                                    if ($user->account_type === 'saving' || $user->saving_enrolled) {
                                        $walletTiles[] = ['Saving Deposit',      $walletSummary['saving_deposit'],  '#0bb783'];
                                        $walletTiles[] = ['Saving ROI',          $walletSummary['saving_roi'],      '#f64e60'];
                                        $walletTiles[] = ['Saving Direct Comm',  $savingDirect,                     '#3699ff'];
                                        $walletTiles[] = ['Saving Indirect Comm',$savingIndirect,                   '#5f5cf1'];
                                    }
                                @endphp
                                @foreach($walletTiles as $tile)
                                <div class="mb-4 col-md-3">
                                    <div class="card card-custom h-100" style="border-left:4px solid {{ $tile[2] }}">
                                        <div class="py-4 card-body">
                                            <div class="mb-1 text-muted font-size-sm">{{ $tile[0] }}</div>
                                            <div class="font-size-h4 font-weight-bold" style="color:{{ $tile[2] }}">
                                                ${{ number_format($tile[1], 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            {{-- Withdrawal summary --}}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card card-custom h-100" style="border-left:4px solid #f64e60">
                                        <div class="py-4 card-body">
                                            <div class="mb-1 text-muted font-size-sm">Total Withdrawn</div>
                                            <div class="font-size-h4 font-weight-bold text-danger">
                                                ${{ number_format($withdrawalSummary['total'], 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-custom h-100" style="border-left:4px solid #ffa800">
                                        <div class="py-4 card-body">
                                            <div class="mb-1 text-muted font-size-sm">Pending Withdrawal</div>
                                            <div class="font-size-h4 font-weight-bold text-warning">
                                                ${{ number_format($withdrawalSummary['pending'], 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card card-custom h-100" style="border-left:4px solid #1bc5bd">
                                        <div class="py-4 card-body">
                                            <div class="mb-1 text-muted font-size-sm">Total Earnings (All Types)</div>
                                            <div class="font-size-h4 font-weight-bold text-success">
                                                ${{ number_format($walletSummary['total_earnings'], 2) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ROI history --}}
                            @if($roiHistory->isNotEmpty())
                            <div class="mt-6">
                                <h6 class="pb-2 mb-3 font-weight-bold text-dark border-bottom">Recent ROI Payments</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover font-size-sm">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Date</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-right">Rate</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($roiHistory as $roi)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($roi->transaction_date)->format('d M Y') }}</td>
                                                <td class="text-right font-weight-bold text-success">${{ number_format($roi->amount, 4) }}</td>
                                                <td class="text-right">{{ $roi->percentage }}%</td>
                                                <td class="text-muted">{{ $roi->description }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                        </div>{{-- /tab-wallet --}}

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- TAB: Commissions                                           --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade" id="tab-commissions">

                            <div class="mb-5 row">
                                <div class="col-md-3">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-primary">${{ number_format($walletSummary['direct_balance'], 2) }}</div>
                                        <div class="text-muted font-size-sm">Direct (L1)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-info">${{ number_format($walletSummary['indirect_balance'], 2) }}</div>
                                        <div class="text-muted font-size-sm">Indirect (L2–L7)</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-warning">${{ number_format($savingDirect + $savingIndirect, 2) }}</div>
                                        <div class="text-muted font-size-sm">Saving Plan Total</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-success">${{ number_format($walletSummary['direct_indirect'], 2) }}</div>
                                        <div class="text-muted font-size-sm">Grand Total</div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="pb-2 mb-3 font-weight-bold text-dark border-bottom">Commission History (Last 50)</h6>

                            @if($commissionHistory->isEmpty())
                                <div class="py-8 text-center text-muted">No commission records found.</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm font-size-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Source Member</th>
                                            <th class="text-center">Level</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-right">Rate</th>
                                            <th class="text-right">Amount</th>
                                            <th>Source</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($commissionHistory as $comm)
                                        <tr>
                                            <td class="text-muted">{{ $comm->created_at->format('d M Y') }}</td>
                                            <td>
                                                @if($comm->form)
                                                    <div class="font-weight-bold">{{ $comm->form->name }}</div>
                                                    <div class="text-muted small"> @ {{ $comm->form->username }}</div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $comm->level == 1 ? 'primary' : 'secondary' }}">
                                                    L{{ $comm->level }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $comm->commission_type === 'direct' ? 'success' : 'info' }}">
                                                    {{ ucfirst($comm->commission_type ?? '—') }}
                                                </span>
                                            </td>
                                            <td class="text-right">{{ $comm->percentage }}%</td>
                                            <td class="text-right font-weight-bold text-success">${{ number_format($comm->balance, 4) }}</td>
                                            <td class="text-muted font-size-xs">{{ $comm->source_type ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                        </div>{{-- /tab-commissions --}}

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- TAB: Investments                                           --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade" id="tab-investments">

                            <div class="mb-5 row">
                                <div class="col-md-3">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-primary">${{ number_format($investmentSummary['total'], 2) }}</div>
                                        <div class="text-muted font-size-sm">Total Invested</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-success">{{ $investmentSummary['active_count'] }}</div>
                                        <div class="text-muted font-size-sm">Active Investments</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-secondary">{{ $investmentSummary['completed_count'] }}</div>
                                        <div class="text-muted font-size-sm">Completed</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-warning">${{ number_format($roiTotal, 2) }}</div>
                                        <div class="text-muted font-size-sm">Total ROI Paid</div>
                                    </div>
                                </div>
                            </div>

                            @php
                                $investRows = DB::table('user_investments')->where('user_id', $user->id)->orderByDesc('created_at')->get();
                            @endphp

                            @if($investRows->isEmpty())
                                <div class="py-8 text-center text-muted">No investment records found.</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm font-size-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right">Earnings</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($investRows as $inv)
                                        <tr>
                                            <td class="text-muted">{{ \Carbon\Carbon::parse($inv->created_at)->format('d M Y') }}</td>
                                            <td class="text-right font-weight-bold">${{ number_format($inv->amount, 2) }}</td>
                                            <td class="text-right text-success">${{ number_format($inv->total_earnings, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ ucfirst($inv->type) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($inv->roi_status === 'active')
                                                    <span class="badge badge-success">Active</span>
                                                @elseif($inv->roi_status === 'completed')
                                                    <span class="badge badge-primary">Completed</span>
                                                @else
                                                    <span class="badge badge-warning text-dark">{{ ucfirst($inv->roi_status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                        </div>{{-- /tab-investments --}}

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- TAB: Saving Plan                                           --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        @if($savingSummary)
                        <div class="tab-pane fade" id="tab-saving">

                            <div class="mb-5 row">
                                @php
                                    $savTiles = [
                                        ['Total Deposited',    $savingSummary['total_deposited'],  '#0bb783', false],
                                        ['Capital (ROI Base)', $savingSummary['capital_amount'],   '#3699ff', false],
                                        ['Saving ROI Earned',  $savingSummary['roi_earned'],        '#f64e60', false],
                                        ['Instalments Paid',   $savingSummary['paid_count'],        '#ffa800', true],
                                        ['Remaining',          $savingSummary['remaining'],         '#8950fc', true],
                                        ['Pending/Submitted',  $savingSummary['pending_count'],     '#187de4', true],
                                    ];
                                @endphp
                                @foreach($savTiles as $st)
                                <div class="mb-4 col-md-2">
                                    <div class="card card-custom h-100" style="border-top:3px solid {{ $st[2] }}">
                                        <div class="py-4 text-center card-body">
                                            <div class="font-size-h4 font-weight-bold" style="color:{{ $st[2] }}">
                                                @if(!$st[3])$@endif{{ number_format($st[1], $st[3] ? 0 : 2) }}
                                            </div>
                                            <div class="mt-1 text-muted font-size-sm">{{ $st[0] }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            @if($savingSummary['next_due'])
                            <div class="mb-4 alert alert-info">
                                <strong>Next Due:</strong>
                                Instalment #{{ $savingSummary['next_due']->instalment_number }}
                                — ${{ number_format($savingSummary['next_due']->amount, 2) }}
                                due <strong>{{ \Carbon\Carbon::parse($savingSummary['next_due']->due_date)->format('d M Y') }}</strong>
                            </div>
                            @endif

                            <h6 class="pb-2 mb-3 font-weight-bold text-dark border-bottom">Instalment Schedule</h6>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm font-size-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-right">Amount</th>
                                            <th>Due Date</th>
                                            <th>Confirmed Date</th>
                                            <th>Txn Ref</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($savingInstalments as $inst)
                                        <tr>
                                            <td class="text-center font-weight-bold">#{{ $inst->instalment_number }}</td>
                                            <td class="text-right">${{ number_format($inst->submitted_amount ?? $inst->amount, 2) }}</td>
                                            <td class="text-muted">{{ $inst->due_date ? \Carbon\Carbon::parse($inst->due_date)->format('d M Y') : '—' }}</td>
                                            <td>
                                                @if($inst->confirmed_at)
                                                    <span class="text-success">{{ \Carbon\Carbon::parse($inst->confirmed_at)->format('d M Y') }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-muted font-size-xs">{{ $inst->transaction_id ?? '—' }}</td>
                                            <td class="text-center">
                                                @php
                                                    $badgeMap = [
                                                        'confirmed'  => 'success',
                                                        'submitted'  => 'warning',
                                                        'pending'    => 'secondary',
                                                        'missed'     => 'danger',
                                                        'rejected'   => 'danger',
                                                    ];
                                                    $badge = $badgeMap[$inst->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-{{ $badge }}">{{ ucfirst($inst->status) }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        </div>{{-- /tab-saving --}}
                        @endif

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- TAB: Withdrawals                                           --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade" id="tab-withdrawals">

                            <div class="mb-5 row">
                                <div class="col-md-4">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-success">${{ number_format($withdrawalSummary['total'], 2) }}</div>
                                        <div class="text-muted font-size-sm">Total Approved</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-warning">${{ number_format($withdrawalSummary['pending'], 2) }}</div>
                                        <div class="text-muted font-size-sm">Pending</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-4 text-center rounded" style="background:#f3f6f9;">
                                        <div class="font-size-h4 font-weight-bold text-danger">{{ $withdrawalSummary['rejected'] }}</div>
                                        <div class="text-muted font-size-sm">Rejected</div>
                                    </div>
                                </div>
                            </div>

                            @if($withdrawals->isEmpty())
                                <div class="py-8 text-center text-muted">No withdrawal records found.</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm font-size-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right">Fee</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-center">Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($withdrawals as $wd)
                                        <tr>
                                            <td class="text-muted">{{ \Carbon\Carbon::parse($wd->created_at)->format('d M Y') }}</td>
                                            <td class="text-right font-weight-bold">${{ number_format($wd->amount, 2) }}</td>
                                            <td class="text-right text-danger">
                                                @if($wd->transfer_fee > 0)-${{ number_format($wd->transfer_fee, 2) }}@else —@endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">{{ ucfirst($wd->request_type) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($wd->status === 'approved')
                                                    <span class="badge badge-success">Approved</span>
                                                @elseif($wd->status === 'pending')
                                                    <span class="badge badge-warning text-dark">Pending</span>
                                                @else
                                                    <span class="badge badge-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td class="text-muted font-size-xs">{{ $wd->review_notes ?? '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                        </div>{{-- /tab-withdrawals --}}

                        {{-- ══════════════════════════════════════════════════════════ --}}
                        {{-- TAB: Activity                                              --}}
                        {{-- ══════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade" id="tab-activity">

                            @if($recentActivity->isEmpty())
                                <div class="py-8 text-center text-muted">No activity records found.</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm font-size-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>From Wallet</th>
                                            <th>To Wallet</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right">Final</th>
                                            <th class="text-center">Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentActivity as $log)
                                        <tr>
                                            <td class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</td>
                                            <td>{{ $log->description ?? '—' }}</td>
                                            <td class="text-muted font-size-xs">{{ $log->from_wallet_type ?? '—' }}</td>
                                            <td class="text-muted font-size-xs">{{ $log->to_wallet_type ?? '—' }}</td>
                                            <td class="text-right">${{ number_format($log->amount, 2) }}</td>
                                            <td class="text-right font-weight-bold {{ $log->status === 'credit' ? 'text-success' : 'text-danger' }}">
                                                {{ $log->status === 'credit' ? '+' : '-' }}${{ number_format($log->final_amount, 2) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $log->status === 'credit' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif

                        </div>{{-- /tab-activity --}}

                    </div>{{-- /tab-content --}}
                </div>
            </div>{{-- /card --}}

        </div>
    </div>
</div>
@endsection
