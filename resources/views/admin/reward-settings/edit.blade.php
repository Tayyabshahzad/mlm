@extends('demo.layout.app')
@section('title', 'Edit Reward Level')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Edit Reward Level {{ $rewardSetting->level }}</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.reward-settings.index') }}" class="text-muted">Reward Settings</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Edit Level</li>
                    </ul>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.reward-settings.index') }}" class="btn btn-light-primary font-weight-bolder">
                    <i class="la la-arrow-left"></i> Back to Settings
                </a>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column-fluid">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h3 class="card-title">Level {{ $rewardSetting->level }} Settings</h3>
                        </div>
                        <form method="POST" action="{{ route('admin.reward-settings.update', $rewardSetting) }}">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Level Number</label>
                                    <div class="col-9">
                                        <input type="number" class="form-control @error('level') is-invalid @enderror" 
                                               name="level" value="{{ old('level', $rewardSetting->level) }}" 
                                               min="1" max="10" required>
                                        @error('level')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="form-text text-muted">Unique level number (1-10)</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Reward Amount ($)</label>
                                    <div class="col-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" step="0.01" class="form-control @error('reward_amount') is-invalid @enderror" 
                                                   name="reward_amount" value="{{ old('reward_amount', $rewardSetting->reward_amount) }}" 
                                                   min="0" required>
                                        </div>
                                        @error('reward_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="form-text text-muted">Amount users receive for reaching this level</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Users Required</label>
                                    <div class="col-9">
                                        <input type="number" class="form-control @error('users_required') is-invalid @enderror" 
                                               name="users_required" value="{{ old('users_required', $rewardSetting->users_required) }}" 
                                               min="1" required>
                                        @error('users_required')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="form-text text-muted">Number of team members required at this level</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Description</label>
                                    <div class="col-9">
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  name="description" rows="3">{{ old('description', $rewardSetting->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="form-text text-muted">Optional description for this reward level</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Status</label>
                                    <div class="col-9">
                                        <div class="checkbox-inline">
                                            <label class="checkbox">
                                                <input type="checkbox" name="is_active" value="1" 
                                                       {{ old('is_active', $rewardSetting->is_active) ? 'checked' : '' }}>
                                                <span></span>
                                                Active (users can receive this reward)
                                            </label>
                                        </div>
                                        <span class="form-text text-muted">Inactive levels won't generate new rewards</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-3"></div>
                                    <div class="col-9">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="la la-save"></i> Update Level Settings
                                        </button>
                                        <a href="{{ route('admin.reward-settings.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Current Statistics -->
                    <div class="card card-custom mb-5">
                        <div class="card-header">
                            <h3 class="card-title">Current Level Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Users Eligible:</span>
                                <span class="font-weight-bold">
                                    @php
                                        $eligibleCount = DB::table('referral_trees')
                                            ->join('users', 'referral_trees.descendant_id', '=', 'users.id')
                                            ->where('referral_trees.level', $rewardSetting->level)
                                            ->where('users.blocked', false)
                                            ->where('users.can_login', 1)
                                            ->distinct('referral_trees.ancestor_id')
                                            ->count('referral_trees.ancestor_id');
                                    @endphp
                                    {{ number_format($eligibleCount) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Rewards Paid:</span>
                                <span class="font-weight-bold text-success">
                                    @php
                                        $paidCount = DB::table('wallets')
                                            ->where('wallet_type', 'reward')
                                            ->where('level', $rewardSetting->level)
                                            ->where('balance', '>', 0)
                                            ->count();
                                    @endphp
                                    {{ number_format($paidCount) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Total Amount Paid:</span>
                                <span class="font-weight-bold text-success">
                                    @php
                                        $totalPaid = DB::table('wallets')
                                            ->where('wallet_type', 'reward')
                                            ->where('level', $rewardSetting->level)
                                            ->sum('balance');
                                    @endphp
                                    ${{ number_format($totalPaid, 2) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Pending Rewards:</span>
                                <span class="font-weight-bold text-warning">
                                    @php
                                        $pendingCount = DB::table('pending_rewards')
                                            ->where('level', $rewardSetting->level)
                                            ->where('status', 'pending')
                                            ->count();
                                    @endphp
                                    {{ number_format($pendingCount) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Level Requirements Preview -->
                    <div class="card card-custom">
                        <div class="card-header">
                            <h3 class="card-title">Level Requirements</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light-info">
                                <h6>Level {{ $rewardSetting->level }} Requirements:</h6>
                                <ul class="mb-0">
                                    <li>{{ number_format($rewardSetting->users_required) }} team members at level {{ $rewardSetting->level }}</li>
                                    @if($rewardSetting->level > 1)
                                        <li>Must have all previous level rewards (1-{{ $rewardSetting->level - 1 }})</li>
                                    @endif
                                    <li>User must be active and not blocked</li>
                                </ul>
                            </div>
                            
                            @if($rewardSetting->level > 1)
                                <div class="separator separator-dashed my-3"></div>
                                <div class="text-muted">
                                    <small>Sequential requirement: Users must receive rewards in order (Level 1 → 2 → 3, etc.)</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection