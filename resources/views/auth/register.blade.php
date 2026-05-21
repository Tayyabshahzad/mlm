@extends('demo.layout.guest')
@section('title', 'Register')

@section('nav_bar')
    <span>Already have an account?</span>
    <a href="{{ route('login') }}" class="auth-nav-link">Sign In</a>
@endsection

@section('page_css')
<style>
    .auth-content-inner { padding-top: 1rem; padding-bottom: 2rem; }

    .auth-card { max-width: 560px; }

    /* Two-column row */
    .field-row { display: flex; gap: 1rem; }
    .field-row .field-group { flex: 1; }

    /* Payment method radio cards */
    .payment-methods { display: grid; grid-template-columns: 1fr 1fr; gap: 0.6rem; }

    .payment-card {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.6rem 0.85rem;
        border: 1.5px solid #e8edf0;
        border-radius: 10px;
        background: #f8fafc;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 500;
        color: #374151;
        transition: all 0.2s;
    }

    .payment-card:has(input:checked) {
        border-color: #4f46e5;
        background: rgba(79,70,229,0.06);
        color: #4f46e5;
    }

    .payment-card input[type="radio"] {
        accent-color: #4f46e5;
        width: 15px; height: 15px;
    }

    /* Section divider */
    .section-divider {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 1.25rem 0 1rem;
    }

    .section-divider::before,
    .section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e8edf0;
    }

    .wallet-address-box {
        display: flex;
        align-items: center;
        border: 1.5px solid #e8edf0;
        border-radius: 10px;
        overflow: hidden;
        background: #f8fafc;
    }

    .wallet-address-text {
        flex: 1;
        padding: 0.65rem 0.85rem;
        font-size: 0.83rem;
        color: #374151;
        word-break: break-all;
    }

    .wallet-copy-btn {
        padding: 0.65rem 0.85rem;
        background: rgba(79,70,229,0.08);
        border: none;
        color: #4f46e5;
        cursor: pointer;
        font-size: 0.82rem;
        font-weight: 600;
        transition: background 0.2s;
        white-space: nowrap;
    }

    .wallet-copy-btn:hover { background: rgba(79,70,229,0.15); }

    .qr-img {
        width: 100%;
        max-width: 220px;
        border-radius: 12px;
        border: 2px solid #e8edf0;
        display: block;
        margin: 0 auto 0.75rem;
    }

    /* phone input group */
    .phone-wrap { display: flex; gap: 0; }
    .phone-wrap select,
    .phone-wrap #countryCode {
        border-radius: 10px 0 0 10px !important;
        border-right: none !important;
        border: 1.5px solid #e8edf0 !important;
        background: #f8fafc !important;
        font-size: 0.88rem !important;
        width: auto !important;
        min-width: 90px;
        padding: 0.7rem 0.5rem 0.7rem 0.75rem !important;
        height: auto !important;
        outline: none !important;
        box-shadow: none !important;
    }
    .phone-wrap input {
        border-radius: 0 10px 10px 0 !important;
        padding-left: 1rem !important;
        border-left: none !important;
    }
</style>
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
        <h2 class="auth-card-title">Create Account</h2>
        <p class="auth-card-subtitle">Join Global Visioners International today</p>
    </div>

    @if(session('error'))
        <div style="background:#fef2f2; border-left:4px solid #ef4444; border-radius:6px; padding:.75rem 1rem; margin-bottom:1rem; font-size:.84rem; color:#b91c1c;">
            <strong>âš </strong> {{ session('error') }}
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('register.user') }}" enctype="multipart/form-data">
        @csrf

        {{-- Hidden flag so the controller knows which path to take --}}
        <input type="hidden" name="user_type" id="user_type" value="{{ old('user_type', 'new') }}" />

        <!-- â”€â”€ Account Type â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
        <div class="field-group">
            @if($isSaving)
                {{-- Savings Program link: account type is fixed --}}
                <input type="hidden" name="account_type" value="saving" />
                <div style="background:rgba(16,185,129,0.06); border:1.5px solid #6ee7b7; border-radius:10px; padding:0.9rem 1rem; font-size:0.82rem; color:#065f46; margin-bottom:0.5rem;">
                    <strong>Savings Program â€” 25-Month Plan</strong><br>
                    Registration fee: <strong>${{ $setting->saving_registration_fee ?? 5 }}</strong> &nbsp;|&nbsp;
                    Initial deposit: <strong>${{ $setting->saving_min_deposit ?? 19 }}</strong> (optional at signup)<br>
                    Monthly instalment: <strong>${{ $setting->saving_monthly_instalment ?? 19 }}</strong> over 25 months<br>
                    <span style="color:#b45309; font-weight:600;">Minimum to join: ${{ $setting->saving_registration_fee ?? 5 }} (fee only). Pay ${{ ($setting->saving_registration_fee ?? 5) + ($setting->saving_min_deposit ?? 19) }} to activate immediately.</span>
                </div>
            @else
                <label class="auth-label">Account Type</label>
                <div class="auth-input-wrap">
                    <div class="auth-input-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <rect x="2" y="3" width="20" height="14" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
                            <path d="M8 21h8M12 17v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <select class="auth-input auth-select" name="account_type" id="account_type" onchange="updateAccountBadge(this.value)">
                        <option value="standard_investment" {{ old('account_type', 'standard_investment') == 'standard_investment' ? 'selected' : '' }}>Standard Investment</option>
                        <option value="saving" {{ old('account_type') == 'saving' ? 'selected' : '' }}>Saving Account</option>
                    </select>
                    <div class="auth-select-arrow">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
                <div style="margin-top:0.4rem;">
                    <span id="account-badge" class="account-badge badge-investment">One-time full investment upfront</span>
                </div>
                <!-- Saving account info panel (standard form toggle) -->
                <div id="saving-info-panel" class="d-none" style="margin-top:0.75rem; background:rgba(16,185,129,0.06); border:1.5px solid #6ee7b7; border-radius:10px; padding:0.9rem 1rem; font-size:0.82rem; color:#065f46;">
                    <strong>Saving Account â€” 25-Month Plan</strong><br>
                    Registration fee: <strong>${{ $setting->saving_registration_fee ?? 5 }}</strong> &nbsp;|&nbsp;
                    Initial deposit: <strong>${{ $setting->saving_min_deposit ?? 19 }}</strong> (optional at signup)<br>
                    Monthly instalment: <strong>${{ $setting->saving_monthly_instalment ?? 19 }}</strong> over 25 months<br>
                    <span style="color:#b45309; font-weight:600;">Minimum to register: ${{ $setting->saving_registration_fee ?? 5 }} (fee only). Pay ${{ ($setting->saving_registration_fee ?? 5) + ($setting->saving_min_deposit ?? 19) }} to activate immediately.</span>
                </div>
            @endif
        </div>

        @if($isSaving)
        <!-- â”€â”€ Are you a new or existing user? (Savings Program only) â”€â”€ -->
        <div class="field-group" style="margin-top:0.75rem;">
            <label class="auth-label">Are you a new or existing member? <span style="color:#ef4444;">*</span></label>
            <div style="display:flex; gap:0.75rem; margin-top:0.3rem;">
                <label style="flex:1; display:flex; align-items:center; gap:0.5rem; padding:0.65rem 1rem; border:1.5px solid #e8edf0; border-radius:10px; cursor:pointer; font-size:0.88rem; font-weight:500; color:#374151; transition:all 0.2s;" id="label-new-user">
                    <input type="radio" name="user_type_toggle" value="new" checked onchange="switchUserType('new')" style="accent-color:#4f46e5;" />
                    New Member
                </label>
                <label style="flex:1; display:flex; align-items:center; gap:0.5rem; padding:0.65rem 1rem; border:1.5px solid #e8edf0; border-radius:10px; cursor:pointer; font-size:0.88rem; font-weight:500; color:#374151; transition:all 0.2s;" id="label-existing-user">
                    <input type="radio" name="user_type_toggle" value="existing" onchange="switchUserType('existing')" style="accent-color:#4f46e5;" />
                    Existing Member
                </label>
            </div>
        </div>
        @endif

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             NEW USER FIELDS (full registration form)
             â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div id="new-user-fields">

        <div class="section-divider">Personal Details</div>

        <!-- Name + Username -->
        <div class="field-row">
            <div class="field-group">
                <label class="auth-label">Full Name <span style="color:#ef4444;">*</span></label>
                <div class="auth-input-wrap">
                    <div class="auth-input-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                            <circle cx="12" cy="7" r="4" stroke="#94a3b8" stroke-width="1.8"/>
                        </svg>
                    </div>
                    <input class="auth-input" type="text" name="name" placeholder="Your name" value="{{ old('name') }}" autocomplete="off" />
                </div>
                @error('name') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="field-group">
                <label class="auth-label">Username <span style="color:#ef4444;">*</span></label>
                <div class="auth-input-wrap">
                    <div class="auth-input-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="#94a3b8" stroke-width="1.8"/>
                            <path d="M8 12a4 4 0 1 0 8 0 4 4 0 0 0-8 0" stroke="#94a3b8" stroke-width="1.8"/>
                        </svg>
                    </div>
                    <input class="auth-input" type="text" name="username" placeholder="username" value="{{ old('username') }}" autocomplete="off" />
                </div>
                @error('username') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Email -->
        <div class="field-group">
            <label class="auth-label">Email Address <span style="color:#ef4444;">*</span></label>
            <div class="auth-input-wrap">
                <div class="auth-input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        <polyline points="22,6 12,13 2,6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <input class="auth-input" type="email" name="email" placeholder="your@email.com" value="{{ old('email') }}" autocomplete="off" />
            </div>
            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <!-- Password + Confirm -->
        <div class="field-row">
            <div class="field-group">
                <label class="auth-label">Password <span style="color:#ef4444;">*</span></label>
                <div class="auth-input-wrap">
                    <div class="auth-input-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <input class="auth-input" type="password" name="password" placeholder="Password" autocomplete="off" />
                </div>
                @error('password') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="field-group">
                <label class="auth-label">Confirm <span style="color:#ef4444;">*</span></label>
                <div class="auth-input-wrap">
                    <div class="auth-input-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M9 16l2 2 4-4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <input class="auth-input" type="password" name="password_confirmation" placeholder="Confirm" autocomplete="off" />
                </div>
            </div>
        </div>

        <!-- Admission Fee -->
        <div class="field-group">
            <label class="auth-label">Admission Fee For <span style="color:#ef4444;">*</span></label>
            <div class="auth-input-wrap">
                <div class="auth-input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <select class="auth-input auth-select" required>
                    <option value="Global Visioners Educational System">Global Visioners Educational System</option>
                </select>
                <div class="auth-select-arrow">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M6 9l6 6 6-6" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Mobile -->
        <div class="field-group">
            <label class="auth-label">Mobile Number <span style="color:#ef4444;">*</span></label>
            <div class="phone-wrap">
                @include('auth.country-code')
                <input
                    id="phone"
                    name="phone_number"
                    value="{{ old('phone_number') }}"
                    type="tel"
                    class="auth-input"
                    placeholder="Phone number"
                    style="padding-left:1rem !important;"
                />
            </div>
            @error('phone_number') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        </div>{{-- end #new-user-fields --}}

        <!-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             EXISTING USER FIELDS (savings enrolment only)
             â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div id="existing-user-fields" class="d-none">

        <div class="section-divider">Identify Your Account</div>
        <div style="font-size:0.82rem; color:#64748b; margin-bottom:0.75rem;">
            Enter your existing username or email address. Your account details remain unchanged â€” only your Savings Program membership is added.
        </div>

        <div class="field-group">
            <label class="auth-label">Username or Email <span style="color:#ef4444;">*</span></label>
            <div class="auth-input-wrap">
                <div class="auth-input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="#94a3b8" stroke-width="1.8"/>
                        <path d="M8 12a4 4 0 1 0 8 0 4 4 0 0 0-8 0" stroke="#94a3b8" stroke-width="1.8"/>
                    </svg>
                </div>
                <input class="auth-input" type="text" name="identifier" id="identifier" placeholder="Your username or email" value="{{ old('identifier') }}" autocomplete="off" />
            </div>
            @error('identifier') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        </div>{{-- end #existing-user-fields --}}

        <!-- Referral Link -->
        <div class="field-group">
            <label class="auth-label">Referral Code <span style="color:#ef4444;">*</span></label>
            <div class="auth-input-wrap">
                <div class="auth-input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <input class="auth-input" type="text" name="referral_link" value="{{ $ref ?? old('referral_link') }}" placeholder="Referral code" required autocomplete="off" />
            </div>
            @error('referral_link') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        @if($isSaving)
        {{-- ADB / FISP options â€” only for saving plan registrations --}}
        <div class="section-divider">Additional Options</div>
        <div class="field-group">
            <label class="auth-label" style="margin-bottom:.5rem;">Select Optional Covers</label>
            <div style="display:flex; flex-direction:column; gap:.6rem;">
                <label style="display:flex; align-items:flex-start; gap:.6rem; cursor:pointer; background:rgba(79,70,229,0.04); border:1.5px solid #e0e7ff; border-radius:9px; padding:.65rem .85rem;">
                    <input type="checkbox" name="adb_option" value="1" id="reg_adb"
                           {{ old('adb_option') ? 'checked' : '' }}
                           style="accent-color:#4f46e5; width:16px; height:16px; margin-top:2px; flex-shrink:0;">
                    <div>
                        <div style="font-size:.86rem; font-weight:600; color:#111827;">ADB Option</div>
                        <div style="font-size:.75rem; color:#6b7280; margin-top:2px;">Accidental Death Benefit â€” Rs. 3 per Rs. 1,000 invested &nbsp;(charged every month)</div>
                    </div>
                </label>
                <label style="display:flex; align-items:flex-start; gap:.6rem; cursor:pointer; background:rgba(79,70,229,0.04); border:1.5px solid #e0e7ff; border-radius:9px; padding:.65rem .85rem;">
                    <input type="checkbox" name="fisp_option" value="1" id="reg_fisp"
                           {{ old('fisp_option') ? 'checked' : '' }}
                           style="accent-color:#4f46e5; width:16px; height:16px; margin-top:2px; flex-shrink:0;">
                    <div>
                        <div style="font-size:.86rem; font-weight:600; color:#111827;">FISP Option</div>
                        <div style="font-size:.75rem; color:#6b7280; margin-top:2px;">Family Income Support Plan â€” Rs. 4 per Rs. 1,000 invested &nbsp;(charged every month)</div>
                    </div>
                </label>
            </div>
        </div>
        @endif

        <div class="section-divider">Payment Details</div>

        <!-- Payment Method -->
        <div class="field-group">
            <label class="auth-label">Choose Payment Method <span style="color:#ef4444;">*</span></label>
            <div class="payment-methods">
                <label class="payment-card">
                    <input type="radio" name="payment_method" value="bank" onclick="toggleReferralLink('bank')" {{ old('payment_method') == 'bank' ? 'checked' : '' }} required />
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.8"/><polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="1.8"/></svg>
                    Bank
                </label>
                <label class="payment-card">
                    <input type="radio" name="payment_method" value="usdt" onclick="toggleReferralLink('usdt')" {{ old('payment_method') == 'usdt' ? 'checked' : '' }} />
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    USDT
                </label>
                <label class="payment-card">
                    <input type="radio" name="payment_method" value="cash_slip" onclick="toggleReferralLink('cash')" {{ old('payment_method') == 'cash_slip' ? 'checked' : '' }} />
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/></svg>
                    Cash Slip
                </label>
                <label class="payment-card">
                    <input type="radio" name="payment_method" value="activation_code" onclick="toggleReferralLink('activation_code')" {{ old('payment_method') == 'activation_code' ? 'checked' : '' }} />
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 2H3v6l9 6 9-6V2z" stroke="currentColor" stroke-width="1.8"/><path d="M3 8v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8" stroke="currentColor" stroke-width="1.8"/></svg>
                    Activation Code
                </label>
            </div>
        </div>
        <!-- USDT QR Panel -->
        <div id="referral-link-container" class="d-none">
            <div style="background:rgba(79,70,229,0.04); border:1.5px solid #e0e7ff; border-radius:12px; padding:1rem; margin-bottom:1rem;">
                <p style="font-size:0.8rem; color:#d97706; font-weight:600; text-align:center; margin-bottom:0.75rem;">
                    Exchange rate: 1 USD = {{ $setting->usd }} PKR (as of {{ \Carbon\Carbon::today()->format('F j, Y') }})
                </p>
                <img src="{{ asset('assets/custom-images/amount-qr.jpeg') }}" alt="QR Code" class="qr-img" />
                <label class="auth-label">Binance Wallet Address</label>
                <div class="wallet-address-box">
                    <div class="wallet-address-text" id="walletAddress">TJaz7ykL6nnpDaVnPYJRNauKXLNtgLUYJP</div>
                    <button type="button" class="wallet-copy-btn" onclick="copyAddressToClipboard()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="margin-right:3px; vertical-align:middle;">
                            <rect x="9" y="9" width="13" height="13" rx="2" stroke="currentColor" stroke-width="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        Copy
                    </button>
                </div>
            </div>
        </div>

        <!-- Activation Code Panel -->
        <div id="activation-code-container" class="d-none field-group">
            <label class="auth-label">Activation Code <span style="color:#ef4444;">*</span></label>
            <div class="auth-input-wrap">
                <div class="auth-input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M21 2H3v6l9 6 9-6V2z" stroke="#94a3b8" stroke-width="1.8"/>
                        <path d="M3 8v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8" stroke="#94a3b8" stroke-width="1.8"/>
                    </svg>
                </div>
                <input class="auth-input" id="activation_code" type="text" name="activation_code" value="{{ old('activation_code') }}" placeholder="Enter activation code" autocomplete="off" />
            </div>
            @error('activation_code') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <!-- Transaction ID -->
        <div class="field-group">
            <label class="auth-label">Transaction ID <span style="color:#ef4444;">*</span></label>
            <div class="auth-input-wrap">
                <div class="auth-input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" stroke="#94a3b8" stroke-width="1.8"/>
                        <rect x="9" y="3" width="6" height="4" rx="1" stroke="#94a3b8" stroke-width="1.8"/>
                    </svg>
                </div>
                <input class="auth-input" type="text" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="Enter transaction ID" autocomplete="off" required />
            </div>
            @error('transaction_id') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <!-- Amount + USDT row -->
        <div class="field-row">
            <div class="field-group">
                <label class="auth-label">Amount Transferred <span style="color:#ef4444;">*</span></label>
                <div class="auth-input-wrap">
                    <div class="auth-input-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <line x1="12" y1="1" x2="12" y2="23" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <input class="auth-input" type="text" name="transferred_amount" id="transferred_amount" value="{{ old('transferred_amount') }}" placeholder="PKR amount" autocomplete="off" required />
                </div>
                <small id="usdInfo" style="font-size:0.75rem; color:#d97706;"></small>
                @error('transferred_amount') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="field-group">
                <label class="auth-label">Equivalent in USDT <span style="font-size:0.68rem; color:#ef4444;">(Min. 60 USD)</span></label>
                <div class="auth-input-wrap">
                    <div class="auth-input-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="#94a3b8" stroke-width="1.8"/>
                            <path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <input class="auth-input" type="text" name="usdt_amount" id="usdt_amount" min="{{ $setting->saving_registration_fee ?? 5 }}" placeholder="Auto-calculated" readonly required />
                </div>
                @error('usdt_amount') <div class="text-danger">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Transaction Proof -->
        <div class="field-group" id="transaction-proof-container">
            <label class="auth-label">Transaction Proof <span style="color:#ef4444;">*</span></label>
            <label class="auth-file-label" id="file-label">
                <input type="file" name="amount_src" id="amount_src" class="d-none" accept="image/*" onchange="updateFileLabel(this)" />
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <polyline points="17,8 12,3 7,8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <span id="file-label-text">Click to upload payment screenshot</span>
            </label>
            @error('amount_src') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <!-- Submit -->
        <div style="margin-top: 1.5rem; margin-bottom: 0.5rem;">
            <button type="submit" class="auth-btn" id="submit-btn">
                @if($isSaving) Join Savings Program @else Create Account @endif
            </button>
        </div>

    </form>

    <p style="text-align:center; margin-top:1rem; margin-bottom:0; font-size:0.85rem; color:#94a3b8;">
        Already registered?
        <a href="{{ route('login') }}" style="color:#4f46e5; font-weight:600; text-decoration:none;">Sign in here</a>
    </p>

</div>
@endsection

@section('page_js')
<script>
    const savingFee     = {{ $setting->saving_registration_fee ?? 5 }};
    const savingDeposit = {{ $setting->saving_min_deposit ?? 19 }};
    const savingFull    = savingFee + savingDeposit;
    const isSavingPage  = {{ $isSaving ? 'true' : 'false' }};

    // â”€â”€ Savings Program: toggle between new / existing member â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    function switchUserType(type) {
        const newFields      = document.getElementById('new-user-fields');
        const existingFields = document.getElementById('existing-user-fields');
        const userTypeInput  = document.getElementById('user_type');
        const submitBtn      = document.getElementById('submit-btn');

        // Update labels styling
        const labelNew      = document.getElementById('label-new-user');
        const labelExisting = document.getElementById('label-existing-user');

        if (type === 'existing') {
            newFields.classList.add('d-none');
            existingFields.classList.remove('d-none');
            userTypeInput.value = 'existing';
            if (submitBtn) submitBtn.textContent = 'Enroll in Savings Program';
            if (labelNew)      labelNew.style.borderColor      = '#e8edf0';
            if (labelExisting) labelExisting.style.borderColor = '#4f46e5';
        } else {
            newFields.classList.remove('d-none');
            existingFields.classList.add('d-none');
            userTypeInput.value = 'new';
            if (submitBtn) submitBtn.textContent = 'Join Savings Program';
            if (labelNew)      labelNew.style.borderColor      = '#4f46e5';
            if (labelExisting) labelExisting.style.borderColor = '#e8edf0';
        }
    }

    function updateAccountBadge(value) {
        const badge      = document.getElementById('account-badge');
        const infoPanel  = document.getElementById('saving-info-panel');
        const usdtInput  = document.getElementById('usdt_amount');
        const usdtLabel  = usdtInput ? usdtInput.closest('.field-group').querySelector('.auth-label') : null;

        if (value === 'standard_investment') {
            if (badge) {
                badge.className = 'account-badge badge-investment';
                badge.textContent = 'One-time full investment upfront';
            }
            if (infoPanel) infoPanel.classList.add('d-none');
            if (usdtInput) { usdtInput.min = 60; usdtInput.removeAttribute('max'); }
            if (usdtLabel) usdtLabel.innerHTML = 'Equivalent in USDT <span style="font-size:0.68rem; color:#ef4444;">(Min. 60 USD)</span>';
        } else {
            if (badge) {
                badge.className = 'account-badge badge-installment';
                badge.textContent = 'Pay in scheduled monthly instalments â€” 25 months';
            }
            if (infoPanel) infoPanel.classList.remove('d-none');
            if (usdtInput) { usdtInput.min = savingFee; usdtInput.removeAttribute('max'); }
            if (usdtLabel) usdtLabel.innerHTML = `Equivalent in USDT <span style="font-size:0.68rem; color:#ef4444;">(Min. $${savingFee})</span>`;
        }
    }

    // Payment method toggle
    function toggleReferralLink(method) {
        const qrPanel        = document.getElementById('referral-link-container');
        const codePanel      = document.getElementById('activation-code-container');
        const codeInput      = document.getElementById('activation_code');
        const proofContainer = document.getElementById('transaction-proof-container');

        if (qrPanel)   qrPanel.classList.add('d-none');
        if (codePanel) codePanel.classList.add('d-none');
        if (codeInput) codeInput.removeAttribute('required');

        if (method === 'activation_code') {
            if (codePanel) codePanel.classList.remove('d-none');
            if (codeInput) codeInput.setAttribute('required', 'required');
            if (proofContainer) proofContainer.classList.add('d-none');
        } else {
            if (proofContainer) proofContainer.classList.remove('d-none');
            if (method === 'usdt' && qrPanel) qrPanel.classList.remove('d-none');
        }
    }

    // File label update
    function updateFileLabel(input) {
        const label = document.getElementById('file-label-text');
        if (input.files && input.files[0]) {
            label.textContent = input.files[0].name;
        }
    }

    // Copy wallet address
    function copyAddressToClipboard() {
        const text = document.getElementById('walletAddress').textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
            toastr.info('Wallet address copied!');
        });
    }

    // PKR â†’ USDT conversion
    document.addEventListener('DOMContentLoaded', function () {
        const rate      = {{ $setting->usd ?? 281.10 }};
        const pkrInput  = document.getElementById('transferred_amount');
        const usdOutput = document.getElementById('usdt_amount');
        const usdInfo   = document.getElementById('usdInfo');

        function calcConversion() {
            const pkr = parseFloat(pkrInput.value);
            if (!isNaN(pkr) && pkr > 0) {
                let usd = parseFloat((pkr / rate).toFixed(2));
                if (usdInfo) usdInfo.textContent = `1 USD = PKR ${rate}  â†’  PKR ${pkr.toLocaleString()} â‰ˆ $${usd} USD`;
                usdOutput.value = usd;
            } else {
                usdOutput.value = '';
                if (usdInfo) usdInfo.textContent = '';
            }
        }

        if (pkrInput) {
            pkrInput.addEventListener('input', calcConversion);
            pkrInput.addEventListener('blur',  calcConversion);
        }

        // Restore old payment method on page reload
        const oldMethod = "{{ old('payment_method') }}";
        if (oldMethod) toggleReferralLink(oldMethod);

        if (isSavingPage) {
            // Restore user type toggle on validation error reload
            const oldUserType = "{{ old('user_type', 'new') }}";
            switchUserType(oldUserType);
            const radios = document.querySelectorAll('input[name="user_type_toggle"]');
            radios.forEach(r => { if (r.value === oldUserType) r.checked = true; });
            if (usdOutput) { usdOutput.min = savingFee; usdOutput.removeAttribute('max'); }
        } else {
            // Restore account type badge (standard form)
            const accountTypeEl = document.getElementById('account_type');
            if (accountTypeEl) {
                updateAccountBadge(accountTypeEl.value);
                accountTypeEl.addEventListener('change', function () {
                    updateAccountBadge(this.value);
                });
            }
        }
    });

    // Refresh CSRF token before every form submit to prevent 419
    (function () {
        var form = document.querySelector('form[action="{{ route('register.user') }}"]');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var self = this;

            fetch('{{ route('csrf.refresh') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                // Update ALL _token fields in the form
                form.querySelectorAll('input[name="_token"]').forEach(function (el) {
                    el.value = data.token;
                });
                self.submit();
            })
            .catch(function () {
                // If fetch fails, submit anyway
                self.submit();
            });
        }, true); // capture phase so it runs before other submit handlers
    })();
</script>
@endsection
