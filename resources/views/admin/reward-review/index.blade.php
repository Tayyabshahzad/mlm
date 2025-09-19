@extends('demo.layout.app')

@section('title', 'Reward Review - Admin')

@section('content')
<div class="container-xxl">
    <!--begin::Page title-->
    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3 mb-5">
        <!--begin::Title-->
        <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">
            Reward Assignment Review
            <small class="text-muted fs-6 fw-normal ms-1">Review and manage reward assignments</small>
        </h1>
        <!--end::Title-->
    </div>
    <!--end::Page title-->

    <!--begin::Alert for temporary page-->
    <div class="alert alert-warning d-flex align-items-center p-5 mb-10">
        <i class="ki-duotone ki-shield-tick fs-2hx text-warning me-4">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-warning">Temporary Review Page</h4>
            <span>This page is designed to help identify users who may have received rewards incorrectly. Use this to review and reverse incorrect reward assignments.</span>
        </div>
    </div>
    <!--end::Alert-->

    <!--begin::Statistics Cards-->
    <div class="row g-5 g-xl-8 mb-5">
        @foreach($stats as $stat)
        <div class="col-xl-3">
            <div class="card card-xl-stretch">
                <div class="card-body p-0">
                    <div class="px-9 pt-7 card-rounded h-275px w-100 bg-light-{{ $stat['potential_over_rewards'] > 0 ? 'warning' : 'success' }}">
                        <div class="d-flex flex-stack">
                            <h3 class="m-0 text-gray-900 fw-bold fs-3">Level {{ $stat['level'] }}</h3>
                            <div class="ms-auto">
                                <div class="bg-{{ $stat['potential_over_rewards'] > 0 ? 'warning' : 'success' }} bg-opacity-20 rounded-2 px-6 py-2">
                                    <span class="fw-semibold fs-6 text-{{ $stat['potential_over_rewards'] > 0 ? 'warning' : 'success' }}">
                                        ${{ number_format($stat['reward_amount']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-column text-gray-900">
                            <div class="fs-2hx fw-bold">{{ $stat['users_with_reward'] }}</div>
                            <div class="mb-0 lh-1">
                                <span class="text-gray-700 fs-6">users have received reward</span>
                            </div>
                            <div class="fs-6 fw-semibold text-gray-500 mt-3">
                                Requires: {{ $stat['users_required'] }} team members<br>
                                Currently eligible: {{ $stat['users_currently_eligible'] }} users
                                @if($stat['potential_over_rewards'] > 0)
                                <br><span class="text-warning fw-bold">
                                    ⚠️ {{ $stat['potential_over_rewards'] }} potentially over-rewarded
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <!--end::Statistics Cards-->

    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <h3>Users with Reward Assignments</h3>
            </div>
            <!--end::Card title-->
            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <!--begin::Filter-->
                    <div class="w-150px me-3">
                        <select class="form-select form-select-solid" data-control="select2" 
                                data-placeholder="Filter by level" onchange="filterByLevel(this.value)">
                            <option value="">All Levels</option>
                            @foreach($rewardLevels as $level)
                            <option value="{{ $level->level }}" 
                                    {{ request('level') == $level->level ? 'selected' : '' }}>
                                Level {{ $level->level }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <!--end::Filter-->
                    <!--begin::Search-->
                    <div class="w-250px me-3">
                        <div class="position-relative">
                            <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-3 top-50 translate-middle-y">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <input type="text" class="form-control form-control-solid ps-10" 
                                   placeholder="Search users..." value="{{ request('search') }}" 
                                   onchange="searchUsers(this.value)">
                        </div>
                    </div>
                    <!--end::Search-->
                    <!--begin::Export-->
                    <a href="{{ route('admin.reward-review.export') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-exit-down fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>Export CSV
                    </a>
                    <!--end::Export-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body py-4">
            <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-125px">User</th>
                        <th class="min-w-125px">Reward Levels</th>
                        <th class="min-w-125px">Total Rewards</th>
                        <th class="min-w-125px">Team Analysis</th>
                        <th class="min-w-100px">Status</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                    @forelse($users as $user)
                    @php
                        $userRewards = $user->wallets()
                            ->where('wallet_type', 'reward')
                            ->where('commission_type', 'reward')
                            ->where('balance', '>', 0)
                            ->orderBy('level')
                            ->get();
                        
                        $totalRewards = $userRewards->sum('balance');
                        $maxLevel = $userRewards->max('level');
                        
                        // Quick team count check for highest level
                        $currentTeamCount = 0;
                        $requiredTeamCount = 0;
                        if ($maxLevel) {
                            $rewardLevel = $rewardLevels->firstWhere('level', $maxLevel);
                            if ($rewardLevel) {
                                $requiredTeamCount = $rewardLevel->users_required;
                                // Simple team count - you might want to use RewardService here
                                $currentTeamCount = \Illuminate\Support\Facades\DB::table('referral_trees')
                                    ->join('users as descendants', 'referral_trees.descendant_id', '=', 'descendants.id')
                                    ->where('referral_trees.ancestor_id', $user->id)
                                    ->where('referral_trees.level', $maxLevel)
                                    ->where('descendants.blocked', false)
                                    ->where('descendants.can_login', 1)
                                    ->count();
                            }
                        }
                        
                        $hasIssue = $maxLevel && $currentTeamCount < $requiredTeamCount;
                    @endphp
                    <tr>
                        <td class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <a href="{{ route('admin.reward-review.show', $user) }}" 
                                   class="text-gray-800 text-hover-primary mb-1">{{ $user->name }}</a>
                                <span class="text-muted">ID: {{ $user->id }} • {{ $user->email }}</span>
                            </div>
                        </td>
                        <td>
                            @foreach($userRewards as $reward)
                            <span class="badge badge-light-primary me-1">
                                L{{ $reward->level }}: ${{ number_format($reward->balance) }}
                            </span>
                            @endforeach
                        </td>
                        <td>
                            <span class="fw-bold text-success">${{ number_format($totalRewards) }}</span>
                        </td>
                        <td>
                            @if($maxLevel)
                            <div class="text-sm">
                                <div>Max Level: {{ $maxLevel }}</div>
                                <div class="{{ $hasIssue ? 'text-warning' : 'text-success' }}">
                                    {{ $currentTeamCount }}/{{ $requiredTeamCount }} team
                                </div>
                            </div>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($hasIssue)
                            <span class="badge badge-light-warning">⚠️ Review Needed</span>
                            @else
                            <span class="badge badge-light-success">✓ Looks Good</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.reward-review.show', $user) }}" 
                               class="btn btn-light btn-active-light-primary btn-sm">
                                Review Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10">
                            <div class="text-gray-400">No users found with reward assignments</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <!--end::Table-->

            <!--begin::Pagination-->
            <div class="d-flex justify-content-center">
                {{ $users->withQueryString()->links() }}
            </div>
            <!--end::Pagination-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>

<script>
function filterByLevel(level) {
    const url = new URL(window.location);
    if (level) {
        url.searchParams.set('level', level);
    } else {
        url.searchParams.delete('level');
    }
    window.location = url.toString();
}

function searchUsers(search) {
    const url = new URL(window.location);
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    window.location = url.toString();
}
</script>
@endsection