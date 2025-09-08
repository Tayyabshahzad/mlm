@extends('demo.layout.app')
@section('title', 'Create New Reward Level')
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="subheader py-2 py-lg-6 subheader-solid" id="kt_subheader">
        <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap flex-sm-nowrap">
            <div class="d-flex align-items-center flex-wrap mr-1">
                <div class="d-flex align-items-baseline flex-wrap mr-5">
                    <h5 class="text-dark font-weight-bold my-1 mr-5">Create New Reward Level</h5>
                    <ul class="breadcrumb breadcrumb-transparent breadcrumb-dot font-weight-bold p-0 my-2 font-size-sm">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-muted">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.reward-settings.index') }}" class="text-muted">Reward Settings</a>
                        </li>
                        <li class="breadcrumb-item text-muted">Create Level</li>
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
                            <h3 class="card-title">New Reward Level Configuration</h3>
                        </div>
                        <form method="POST" action="{{ route('admin.reward-settings.store') }}">
                            @csrf
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Level Number <span class="text-danger">*</span></label>
                                    <div class="col-9">
                                        <input type="number" class="form-control @error('level') is-invalid @enderror" 
                                               name="level" value="{{ old('level') }}" 
                                               min="1" max="10" required>
                                        @error('level')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="form-text text-muted">Unique level number (1-10)</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Reward Amount ($) <span class="text-danger">*</span></label>
                                    <div class="col-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">$</span>
                                            </div>
                                            <input type="number" step="0.01" class="form-control @error('reward_amount') is-invalid @enderror" 
                                                   name="reward_amount" value="{{ old('reward_amount') }}" 
                                                   min="0" required>
                                        </div>
                                        @error('reward_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <span class="form-text text-muted">Amount users receive for reaching this level</span>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-3 col-form-label">Users Required <span class="text-danger">*</span></label>
                                    <div class="col-9">
                                        <input type="number" class="form-control @error('users_required') is-invalid @enderror" 
                                               name="users_required" value="{{ old('users_required') }}" 
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
                                                  name="description" rows="3" placeholder="e.g., Bronze Level - First milestone reward">{{ old('description') }}</textarea>
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
                                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                                <span></span>
                                                Active (users can receive this reward)
                                            </label>
                                        </div>
                                        <span class="form-text text-muted">New levels are active by default</span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-3"></div>
                                    <div class="col-9">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="la la-save"></i> Create Reward Level
                                        </button>
                                        <a href="{{ route('admin.reward-settings.index') }}" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Existing Levels Info -->
                    <div class="card card-custom mb-5">
                        <div class="card-header">
                            <h3 class="card-title">Existing Levels</h3>
                        </div>
                        <div class="card-body">
                            @php
                                $existingLevels = \App\Models\RewardSetting::orderBy('level')->get();
                            @endphp
                            @if($existingLevels->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Level</th>
                                                <th>Users Required</th>
                                                <th>Reward</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($existingLevels as $level)
                                                <tr class="{{ !$level->is_active ? 'text-muted' : '' }}">
                                                    <td>{{ $level->level }}</td>
                                                    <td>{{ number_format($level->users_required) }}</td>
                                                    <td>${{ number_format($level->reward_amount, 0) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No existing levels configured</p>
                            @endif
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div class="card card-custom">
                        <div class="card-header">
                            <h3 class="card-title">Level Guidelines</h3>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light-primary">
                                <h6>Best Practices:</h6>
                                <ul class="mb-0">
                                    <li>Use sequential level numbers (1, 2, 3...)</li>
                                    <li>Increase user requirements progressively</li>
                                    <li>Higher levels should have higher rewards</li>
                                    <li>Keep descriptions clear and motivating</li>
                                </ul>
                            </div>
                            
                            <div class="separator separator-dashed my-3"></div>
                            
                            <div class="alert alert-light-info">
                                <h6>Suggested Progression:</h6>
                                <ul class="mb-0 text-sm">
                                    <li><strong>Level 1:</strong> 10 users → $130</li>
                                    <li><strong>Level 2:</strong> 20 users → $350</li>
                                    <li><strong>Level 3:</strong> 30 users → $1,050</li>
                                    <li><strong>Level 4:</strong> 40 users → $3,450</li>
                                    <li><strong>Level 5:</strong> 50 users → $8,650</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection