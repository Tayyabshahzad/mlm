@extends('demo.layout.auth')
@section('title', 'Sign In')

@section('nav_bar')
    <span>Don't have an account?</span>
    <a href="{{ route('register') }}">Sign Up</a>
@endsection

@section('content')
<div class="al-card" style="max-width:460px;">

    <div class="al-card-header">
        <div class="al-brand-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="al-card-title">Welcome Back</div>
        <div class="al-card-sub">Sign in to your GVI account to continue</div>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="f-group">
            <label class="f-label">Email or Username</label>
            <div class="f-wrap">
                <div class="f-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        <polyline points="22,6 12,13 2,6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <input class="f-input" type="text" name="login"
                       placeholder="your@email.com or username"
                       value="{{ old('login') }}" autocomplete="off" required />
            </div>
            @error('login') <div class="f-error">{{ $message }}</div> @enderror
        </div>

        <div class="f-group">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.4rem;">
                <label class="f-label" style="margin-bottom:0;">Password</label>
            </div>
            <div class="f-wrap">
                <div class="f-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <input class="f-input" type="password" name="password" id="pw"
                       placeholder="Enter your password" autocomplete="off" required />
                <button type="button" onclick="togglePw()" tabindex="-1"
                        style="position:absolute;right:13px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;display:flex;">
                    <svg id="eye-icon" width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </button>
            </div>
            @error('password') <div class="f-error">{{ $message }}</div> @enderror
        </div>

        <div style="margin-top:1.6rem;">
            <button type="submit" class="f-btn">
                Sign In
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-left:.5rem;vertical-align:middle;">
                    <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </form>

    <p style="text-align:center; margin-top:1.35rem; font-size:.83rem; color:#94a3b8;">
        New to GVI?
        <a href="{{ route('register') }}" style="color:var(--primary);font-weight:600;text-decoration:none;">Create a free account →</a>
    </p>

</div>
@endsection

@section('page_js')
<script>
/* ── Password toggle ────────────────────────────────── */
function togglePw() {
    var pw = document.getElementById('pw');
    var ic = document.getElementById('eye-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        ic.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>';
    } else {
        pw.type = 'password';
        ic.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>';
    }
}
</script>
@endsection
