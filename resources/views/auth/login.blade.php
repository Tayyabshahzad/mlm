@extends('demo.layout.guest')
@section('title', 'Login')

@section('nav_bar')
    <span>Don't have an account?</span>
    <a href="{{ route('register') }}" class="auth-nav-link">Sign Up</a>
@endsection

@section('content')
<div class="auth-card">

    <!-- Header -->
    <div class="auth-card-header">
        <div class="auth-brand-icon">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h2 class="auth-card-title">Welcome Back Oracle</h2>
        <p class="auth-card-subtitle">Sign in to your GVI account</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email / Username -->
        <div class="field-group">
            <label class="auth-label">Email / Username</label>
            <div class="auth-input-wrap">
                <div class="auth-input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        <polyline points="22,6 12,13 2,6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <input
                    class="auth-input"
                    type="text"
                    name="login"
                    placeholder="Enter email or username"
                    autocomplete="off"
                    value="{{ old('login') }}"
                    required
                />
            </div>
            @error('login')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="field-group">
            <label class="auth-label">Password</label>
            <div class="auth-input-wrap">
                <div class="auth-input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <input
                    class="auth-input"
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="off"
                    required
                />
            </div>
            @error('password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit -->
        <div style="margin-top: 1.5rem;">
            <button type="submit" class="auth-btn">Sign In</button>
        </div>

    </form>

    <p style="text-align:center; margin-top:1.25rem; margin-bottom:0; font-size:0.85rem; color:#94a3b8;">
        New to GVI?
        <a href="{{ route('register') }}" style="color:#4f46e5; font-weight:600; text-decoration:none;">Create an account</a>
    </p>

</div>
@endsection

@section('page_js')
@endsection
