@extends('demo.layout.auth')
@section('title', 'Register')

@section('nav_bar')
    <span>Already have an account?</span>
    <a href="{{ route('login') }}">Sign In</a>
@endsection

@section('content')
<div class="al-card" style="max-width:580px;">

    <div class="al-card-header">
        <div class="al-brand-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="al-card-title">Create Account</div>
        <div class="al-card-sub">Join Global Visioners International today</div>
    </div>

    @if(session('error'))
        <div class="al-alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" flex-shrink="0"><circle cx="12" cy="12" r="10" stroke="#ef4444" stroke-width="1.8"/><line x1="12" y1="8" x2="12" y2="12" stroke="#ef4444" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="16" r=".5" fill="#ef4444" stroke="#ef4444"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.user') }}" enctype="multipart/form-data">
        @csrf

        <input type="hidden" name="user_type" id="user_type" value="{{ old('user_type', 'new') }}" />

        {{-- ── Account Type ── --}}
        <div class="f-group">
            @if($isSaving)
                <input type="hidden" name="account_type" value="saving" />
                <div class="info-panel">
                    <strong>Savings Program — 25-Month Plan</strong><br>
                    Registration fee: <strong>${{ $setting->saving_registration_fee ?? 5 }}</strong> &nbsp;|&nbsp;
                    Initial deposit: <strong>${{ $setting->saving_min_deposit ?? 19 }}</strong> (optional at signup)<br>
                    Monthly instalment: <strong>${{ $setting->saving_monthly_instalment ?? 19 }}</strong> over 25 months<br>
                    <span style="color:#b45309;font-weight:600;">Minimum to join: ${{ $setting->saving_registration_fee ?? 5 }} (fee only). Pay ${{ ($setting->saving_registration_fee ?? 5) + ($setting->saving_min_deposit ?? 19) }} to activate immediately.</span>
                </div>
            @else
                <label class="f-label">Account Type</label>
                <div class="f-wrap">
                    <div class="f-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="#94a3b8" stroke-width="1.8"/><path d="M8 21h8M12 17v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </div>
                    <select class="f-input f-select" name="account_type" id="account_type" onchange="updateAccountBadge(this.value)">
                        <option value="standard_investment" {{ old('account_type','standard_investment')=='standard_investment'?'selected':'' }}>Standard Investment</option>
                        <option value="saving" {{ old('account_type')=='saving'?'selected':'' }}>Saving Account</option>
                    </select>
                    <div class="f-select-arrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                </div>
                <span id="account-badge" class="al-badge badge-inv">One-time full investment upfront</span>
                <div id="saving-info-panel" class="d-none" style="margin-top:.65rem;background:rgba(16,185,129,0.06);border:1.5px solid #6ee7b7;border-radius:10px;padding:.85rem 1rem;font-size:.81rem;color:#065f46;">
                    <strong>Saving Account — 25-Month Plan</strong><br>
                    Reg. fee: <strong>${{ $setting->saving_registration_fee ?? 5 }}</strong> | Initial deposit: <strong>${{ $setting->saving_min_deposit ?? 19 }}</strong><br>
                    <span style="color:#b45309;font-weight:600;">Minimum: ${{ $setting->saving_registration_fee ?? 5 }} (fee). Pay ${{ ($setting->saving_registration_fee ?? 5) + ($setting->saving_min_deposit ?? 19) }} to activate.</span>
                </div>
            @endif
        </div>

        @if($isSaving)
        {{-- New / Existing member toggle --}}
        <div class="f-group">
            <label class="f-label">New or existing member? <span style="color:#ef4444;">*</span></label>
            <div style="display:flex;gap:.65rem;margin-top:.25rem;">
                <label id="label-new-user" style="flex:1;display:flex;align-items:center;gap:.5rem;padding:.6rem .9rem;border:1.5px solid #4f46e5;border-radius:10px;cursor:pointer;font-size:.86rem;font-weight:500;color:#374151;">
                    <input type="radio" name="user_type_toggle" value="new" checked onchange="switchUserType('new')" style="accent-color:#4f46e5;" /> New Member
                </label>
                <label id="label-existing-user" style="flex:1;display:flex;align-items:center;gap:.5rem;padding:.6rem .9rem;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;font-size:.86rem;font-weight:500;color:#374151;">
                    <input type="radio" name="user_type_toggle" value="existing" onchange="switchUserType('existing')" style="accent-color:#4f46e5;" /> Existing Member
                </label>
            </div>
        </div>
        @endif

        {{-- ── New user fields ── --}}
        <div id="new-user-fields">
            <div class="f-divider">Personal Details</div>

            <div class="f-row">
                <div class="f-group">
                    <label class="f-label">Full Name <span style="color:#ef4444;">*</span></label>
                    <div class="f-wrap">
                        <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="#94a3b8" stroke-width="1.8"/></svg></div>
                        <input class="f-input" type="text" name="name" placeholder="Your full name" value="{{ old('name') }}" autocomplete="off" />
                    </div>
                    @error('name') <div class="f-error">{{ $message }}</div> @enderror
                </div>
                <div class="f-group">
                    <label class="f-label">Username <span style="color:#ef4444;">*</span></label>
                    <div class="f-wrap">
                        <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#94a3b8" stroke-width="1.8"/><path d="M8 12a4 4 0 1 0 8 0 4 4 0 0 0-8 0" stroke="#94a3b8" stroke-width="1.8"/></svg></div>
                        <input class="f-input" type="text" name="username" placeholder="username" value="{{ old('username') }}" autocomplete="off" />
                    </div>
                    @error('username') <div class="f-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="f-group">
                <label class="f-label">Email Address <span style="color:#ef4444;">*</span></label>
                <div class="f-wrap">
                    <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/><polyline points="22,6 12,13 2,6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/></svg></div>
                    <input class="f-input" type="email" name="email" placeholder="your@email.com" value="{{ old('email') }}" autocomplete="off" />
                </div>
                @error('email') <div class="f-error">{{ $message }}</div> @enderror
            </div>

            <div class="f-row">
                <div class="f-group">
                    <label class="f-label">Password <span style="color:#ef4444;">*</span></label>
                    <div class="f-wrap">
                        <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/></svg></div>
                        <input class="f-input" type="password" name="password" placeholder="Password" autocomplete="off" />
                    </div>
                    @error('password') <div class="f-error">{{ $message }}</div> @enderror
                </div>
                <div class="f-group">
                    <label class="f-label">Confirm <span style="color:#ef4444;">*</span></label>
                    <div class="f-wrap">
                        <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/><path d="M9 16l2 2 4-4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                        <input class="f-input" type="password" name="password_confirmation" placeholder="Confirm" autocomplete="off" />
                    </div>
                </div>
            </div>

            <div class="f-group">
                <label class="f-label">Admission Fee For <span style="color:#ef4444;">*</span></label>
                <div class="f-wrap">
                    <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 10v6M2 10l10-5 10 5-10 5z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><path d="M6 12v5c3 3 9 3 12 0v-5" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/></svg></div>
                    <select class="f-input f-select" required>
                        <option>Global Visioners Educational System</option>
                    </select>
                    <div class="f-select-arrow"><svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
                </div>
            </div>

            <div class="f-group">
                <label class="f-label">Mobile Number <span style="color:#ef4444;">*</span></label>
                <div class="phone-wrap">
                    @include('auth.country-code')
                    <input id="phone" name="phone_number" value="{{ old('phone_number') }}" type="tel" class="f-input" placeholder="Phone number" />
                </div>
                @error('phone_number') <div class="f-error">{{ $message }}</div> @enderror
            </div>
        </div>{{-- end #new-user-fields --}}

        {{-- ── Existing user fields ── --}}
        <div id="existing-user-fields" class="d-none">
            <div class="f-divider">Identify Your Account</div>
            <p style="font-size:.81rem;color:#64748b;margin-bottom:.75rem;">Enter your existing username or email. Only your Savings Program membership is added.</p>

            {{-- 50/50 row: username + referral code --}}
            <div class="f-row">
                <div class="f-group">
                    <label class="f-label">Username or Email <span style="color:#ef4444;">*</span></label>
                    <div class="f-wrap">
                        <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#94a3b8" stroke-width="1.8"/><path d="M8 12a4 4 0 1 0 8 0 4 4 0 0 0-8 0" stroke="#94a3b8" stroke-width="1.8"/></svg></div>
                        <input class="f-input" type="text" name="identifier" id="identifier" placeholder="Username or email" value="{{ old('identifier') }}" autocomplete="off" />
                    </div>
                    @error('identifier') <div class="f-error">{{ $message }}</div> @enderror
                </div>

                <div class="f-group">
                    <label class="f-label">Referral Code <span style="color:#ef4444;">*</span></label>
                    <div class="f-wrap">
                        <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/></svg></div>
                        <input class="f-input" type="text" name="referral_link" id="referral_existing" value="{{ $ref ?? old('referral_link') }}" placeholder="Referral code" autocomplete="off" />
                    </div>
                </div>
            </div>
        </div>{{-- end #existing-user-fields --}}

        {{-- ── Referral Code (new member — hidden when existing mode active) ── --}}
        <div class="f-group" id="referral-new">
            <label class="f-label">Referral Code <span style="color:#ef4444;">*</span></label>
            <div class="f-wrap">
                <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/></svg></div>
                <input class="f-input" type="text" name="referral_link" id="referral_new" value="{{ $ref ?? old('referral_link') }}" placeholder="Referral code" required autocomplete="off" />
            </div>
            @error('referral_link') <div class="f-error">{{ $message }}</div> @enderror
        </div>

        @if($isSaving)
        {{-- ── ADB / FISP — pure CSS toggle switches, no Metronic interference ── --}}
        <div class="f-divider">Additional Options</div>
        <div class="f-group">
            <label class="f-label" style="margin-bottom:.5rem;">Select Optional Insurance Covers</label>
            <div class="sw-row">

                {{-- ADB --}}
                <label class="sw-card">
                    <input type="checkbox" class="sw-input" id="check_adb" name="adb_option" value="1" onchange="calcRegOptions()" {{ old('adb_option') ? 'checked' : '' }} />
                    <span class="sw-text">
                        <span class="sw-title">ADB Option</span>
                        <span class="sw-desc">Accidental Death Benefit &mdash; Rs. 3 per Rs. 1,000 of Sum Assured / month</span>
                        <span class="sw-badge" id="reg_adb_charge"></span>
                    </span>
                    <span class="sw-track"></span>
                </label>

                {{-- FISP --}}
                <label class="sw-card">
                    <input type="checkbox" class="sw-input" id="check_fisp" name="fisp_option" value="1" onchange="calcRegOptions()" {{ old('fisp_option') ? 'checked' : '' }} />
                    <span class="sw-text">
                        <span class="sw-title">FISP Option</span>
                        <span class="sw-desc">Family Income Support Plan &mdash; Rs. 4 per Rs. 1,000 of Sum Assured / month</span>
                        <span class="sw-badge" id="reg_fisp_charge"></span>
                    </span>
                    <span class="sw-track"></span>
                </label>

            </div>

            <div class="charge-box" id="reg_options_summary">
                <div class="charge-box-title">Monthly Charge Breakdown</div>
                <div id="reg_options_detail" style="line-height:1.7;"></div>
            </div>
        </div>
        @endif

        <div class="f-divider">Payment Details</div>

        {{-- ── Payment Method ── --}}
        <div class="f-group">
            <label class="f-label">Choose Payment Method <span style="color:#ef4444;">*</span></label>
            <div class="pm-grid">
                <label class="pm-card">
                    <input type="radio" name="payment_method" value="bank" onclick="togglePayment('bank')" {{ old('payment_method')=='bank'?'checked':'' }} required />
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.8"/><polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="1.8"/></svg>
                    Bank
                </label>
                <label class="pm-card">
                    <input type="radio" name="payment_method" value="usdt" onclick="togglePayment('usdt')" {{ old('payment_method')=='usdt'?'checked':'' }} />
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    USDT
                </label>
                <label class="pm-card">
                    <input type="radio" name="payment_method" value="cash_slip" onclick="togglePayment('cash')" {{ old('payment_method')=='cash_slip'?'checked':'' }} />
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.8"/></svg>
                    Cash Slip
                </label>
                <label class="pm-card">
                    <input type="radio" name="payment_method" value="activation_code" onclick="togglePayment('activation_code')" {{ old('payment_method')=='activation_code'?'checked':'' }} />
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 2H3v6l9 6 9-6V2z" stroke="currentColor" stroke-width="1.8"/><path d="M3 8v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8" stroke="currentColor" stroke-width="1.8"/></svg>
                    Activation Code
                </label>
            </div>
        </div>

        {{-- USDT QR panel --}}
        <div id="usdt-panel" class="d-none">
            <div style="background:rgba(79,70,229,0.04);border:1.5px solid #e0e7ff;border-radius:12px;padding:1rem;margin-bottom:.85rem;">
                <p style="font-size:.78rem;color:#d97706;font-weight:600;text-align:center;margin-bottom:.65rem;">
                    Exchange rate: 1 USD = {{ $setting->usd }} PKR (as of {{ \Carbon\Carbon::today()->format('F j, Y') }})
                </p>
                <img src="{{ asset('assets/custom-images/amount-qr.jpeg') }}" alt="QR Code" class="qr-img" />
                <label class="f-label" style="margin-bottom:.35rem;">Binance Wallet Address</label>
                <div class="wallet-box">
                    <div class="wallet-addr" id="walletAddress">TJaz7ykL6nnpDaVnPYJRNauKXLNtgLUYJP</div>
                    <button type="button" class="wallet-copy" onclick="copyWallet()">Copy</button>
                </div>
            </div>
        </div>

        {{-- Activation code panel --}}
        <div id="activation-panel" class="d-none f-group">
            <label class="f-label">Activation Code <span style="color:#ef4444;">*</span></label>
            <div class="f-wrap">
                <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M21 2H3v6l9 6 9-6V2z" stroke="#94a3b8" stroke-width="1.8"/><path d="M3 8v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8" stroke="#94a3b8" stroke-width="1.8"/></svg></div>
                <input class="f-input" id="activation_code" type="text" name="activation_code" value="{{ old('activation_code') }}" placeholder="Enter activation code" autocomplete="off" />
            </div>
            @error('activation_code') <div class="f-error">{{ $message }}</div> @enderror
        </div>

        {{-- Transaction ID --}}
        <div class="f-group">
            <label class="f-label">Transaction ID <span style="color:#ef4444;">*</span></label>
            <div class="f-wrap">
                <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" stroke="#94a3b8" stroke-width="1.8"/><rect x="9" y="3" width="6" height="4" rx="1" stroke="#94a3b8" stroke-width="1.8"/></svg></div>
                <input class="f-input" type="text" name="transaction_id" value="{{ old('transaction_id') }}" placeholder="Enter transaction ID" autocomplete="off" required />
            </div>
            @error('transaction_id') <div class="f-error">{{ $message }}</div> @enderror
        </div>

        {{-- Amount + USDT --}}
        <div class="f-row">
            <div class="f-group">
                <label class="f-label">Amount Transferred <span style="color:#ef4444;">*</span></label>
                <div class="f-wrap">
                    <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="12" y1="1" x2="12" y2="23" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/></svg></div>
                    <input class="f-input" type="text" name="transferred_amount" id="transferred_amount" value="{{ old('transferred_amount') }}" placeholder="PKR amount" autocomplete="off" required />
                </div>
                <small id="usdInfo" style="font-size:.73rem;color:#d97706;"></small>
                @error('transferred_amount') <div class="f-error">{{ $message }}</div> @enderror
            </div>
            <div class="f-group">
                <label class="f-label">Equiv. USDT <span style="font-size:.65rem;color:#ef4444;">(Min. {{ $isSaving ? '$'.($setting->saving_registration_fee ?? 5) : '60 USD' }})</span></label>
                <div class="f-wrap">
                    <div class="f-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#94a3b8" stroke-width="1.8"/><path d="M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/></svg></div>
                    <input class="f-input" type="text" name="usdt_amount" id="usdt_amount" min="{{ $setting->saving_registration_fee ?? 5 }}" placeholder="Auto-calculated" readonly required />
                </div>
                @error('usdt_amount') <div class="f-error">{{ $message }}</div> @enderror
            </div>
        </div>

        {{-- Transaction Proof --}}
        <div class="f-group" id="proof-container">
            <label class="f-label">Transaction Proof <span style="color:#ef4444;">*</span></label>
            <label class="f-file-label" id="file-label">
                <input type="file" name="amount_src" id="amount_src" class="d-none" accept="image/*" onchange="updateFileLabel(this)" />
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><polyline points="17,8 12,3 7,8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span id="file-label-text">Click to upload payment screenshot</span>
            </label>
            @error('amount_src') <div class="f-error">{{ $message }}</div> @enderror
        </div>

        <div style="margin-top:1.5rem;">
            <button type="submit" class="f-btn" id="submit-btn">
                @if($isSaving) Join Savings Program @else Create Account @endif
            </button>
        </div>

    </form>

    <p style="text-align:center;margin-top:1rem;font-size:.83rem;color:#94a3b8;">
        Already registered?
        <a href="{{ route('login') }}" style="color:#4f46e5;font-weight:600;text-decoration:none;">Sign in here</a>
    </p>

</div>
@endsection

@section('page_js')
<script>
    const savingFee    = {{ $setting->saving_registration_fee ?? 5 }};
    const isSavingPage = {{ $isSaving ? 'true' : 'false' }};
    const usdRate      = {{ (float)($setting->usd ?? 278) }};

    // ── ADB/FISP charge preview ──────────────────────────────
    function calcRegOptions() {
        var usd    = parseFloat(document.getElementById('usdt_amount')?.value || 0);
        var net    = Math.max(0, usd - savingFee);
        var summEl = document.getElementById('reg_options_summary');
        var adbBdg = document.getElementById('reg_adb_charge');
        var fspBdg = document.getElementById('reg_fisp_charge');
        var adbOn  = document.getElementById('check_adb')?.checked  || false;
        var fspOn  = document.getElementById('check_fisp')?.checked || false;

        if (net <= 0 || (!adbOn && !fspOn)) {
            if (summEl) summEl.style.display = 'none';
            if (adbBdg) adbBdg.style.display = 'none';
            if (fspBdg) fspBdg.style.display = 'none';
            return;
        }
        var units   = (net * 25) / 1000;
        var adbUsd  = adbOn ? units * 3 : 0;
        var fspUsd  = fspOn ? units * 4 : 0;
        function fmt(n) { return 'Rs. ' + Math.round(n * usdRate).toLocaleString('en-PK'); }

        if (adbBdg) { adbBdg.textContent = adbOn ? fmt(adbUsd) + '/mo' : ''; adbBdg.style.display = adbOn ? 'inline' : 'none'; }
        if (fspBdg) { fspBdg.textContent = fspOn ? fmt(fspUsd) + '/mo' : ''; fspBdg.style.display = fspOn ? 'inline' : 'none'; }

        var lines = [];
        lines.push('Sum Assured = $' + net.toFixed(2) + ' × 25 = <strong>$' + (net*25).toFixed(2) + '</strong>');
        if (adbOn) lines.push('ADB = ' + Math.round(units) + ' × $3 = <strong>$' + adbUsd.toFixed(2) + '/mo</strong> (' + fmt(adbUsd) + ')');
        if (fspOn) lines.push('FISP = ' + Math.round(units) + ' × $4 = <strong>$' + fspUsd.toFixed(2) + '/mo</strong> (' + fmt(fspUsd) + ')');
        if (adbOn && fspOn) lines.push('<strong>Total: $' + (adbUsd+fspUsd).toFixed(2) + '/mo (' + fmt(adbUsd+fspUsd) + ')</strong>');

        document.getElementById('reg_options_detail').innerHTML = lines.join('<br>');
        if (summEl) summEl.style.display = 'block';
    }

    // ── Payment method toggle ────────────────────────────────
    function togglePayment(method) {
        var usdt  = document.getElementById('usdt-panel');
        var code  = document.getElementById('activation-panel');
        var proof = document.getElementById('proof-container');
        var ci    = document.getElementById('activation_code');
        if (usdt)  usdt.classList.add('d-none');
        if (code)  code.classList.add('d-none');
        if (ci)    ci.removeAttribute('required');
        if (method === 'activation_code') {
            if (code)  code.classList.remove('d-none');
            if (ci)    ci.setAttribute('required','required');
            if (proof) proof.classList.add('d-none');
        } else {
            if (proof) proof.classList.remove('d-none');
            if (method === 'usdt' && usdt) usdt.classList.remove('d-none');
        }
    }

    // ── PKR → USDT ───────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        var rate   = {{ $setting->usd ?? 281.10 }};
        var pkrEl  = document.getElementById('transferred_amount');
        var usdEl  = document.getElementById('usdt_amount');
        var infoEl = document.getElementById('usdInfo');

        function convert() {
            var pkr = parseFloat(pkrEl.value);
            if (!isNaN(pkr) && pkr > 0) {
                var usd = parseFloat((pkr / rate).toFixed(2));
                if (infoEl) infoEl.textContent = '1 USD = PKR ' + rate + '  ·  PKR ' + pkr.toLocaleString() + ' ≈ $' + usd + ' USD';
                usdEl.value = usd;
                calcRegOptions();
            } else {
                usdEl.value = '';
                if (infoEl) infoEl.textContent = '';
            }
        }
        if (pkrEl) { pkrEl.addEventListener('input', convert); pkrEl.addEventListener('blur', convert); }

        // Restore state on validation reload
        var oldMethod = "{{ old('payment_method') }}";
        if (oldMethod) togglePayment(oldMethod);

        if (isSavingPage) {
            var oldType = "{{ old('user_type', 'new') }}";
            switchUserType(oldType);
            document.querySelectorAll('input[name="user_type_toggle"]').forEach(function(r) {
                if (r.value === oldType) r.checked = true;
            });
            if (usdEl) usdEl.min = savingFee;
            calcRegOptions();
        } else {
            var acct = document.getElementById('account_type');
            if (acct) { updateAccountBadge(acct.value); acct.addEventListener('change', function(){ updateAccountBadge(this.value); }); }
        }
    });

    // ── New / Existing member ────────────────────────────────
    function switchUserType(type) {
        var nf      = document.getElementById('new-user-fields');
        var ef      = document.getElementById('existing-user-fields');
        var refNew  = document.getElementById('referral-new');
        var refNewI = document.getElementById('referral_new');
        var refExI  = document.getElementById('referral_existing');
        var uti     = document.getElementById('user_type');
        var btn     = document.getElementById('submit-btn');
        var ln      = document.getElementById('label-new-user');
        var le      = document.getElementById('label-existing-user');

        if (type === 'existing') {
            if (nf)      nf.classList.add('d-none');
            if (ef)      ef.classList.remove('d-none');
            if (refNew)  refNew.classList.add('d-none');
            if (refNewI) refNewI.removeAttribute('required');
            if (refExI)  refExI.setAttribute('required', 'required');
            if (uti)     uti.value = 'existing';
            if (btn)     btn.textContent = 'Enroll in Savings Program';
            if (ln)      ln.style.borderColor = '#e2e8f0';
            if (le)      le.style.borderColor = '#4f46e5';
        } else {
            if (nf)      nf.classList.remove('d-none');
            if (ef)      ef.classList.add('d-none');
            if (refNew)  refNew.classList.remove('d-none');
            if (refNewI) refNewI.setAttribute('required', 'required');
            if (refExI)  refExI.removeAttribute('required');
            if (uti)     uti.value = 'new';
            if (btn)     btn.textContent = 'Join Savings Program';
            if (ln)      ln.style.borderColor = '#4f46e5';
            if (le)      le.style.borderColor = '#e2e8f0';
        }
    }

    // ── Account badge (standard form) ───────────────────────
    function updateAccountBadge(val) {
        var badge = document.getElementById('account-badge');
        var panel = document.getElementById('saving-info-panel');
        var usdEl = document.getElementById('usdt_amount');
        if (val === 'saving') {
            if (badge) { badge.className='al-badge badge-inst'; badge.textContent='Pay in scheduled monthly instalments — 25 months'; }
            if (panel) panel.classList.remove('d-none');
            if (usdEl) usdEl.min = savingFee;
        } else {
            if (badge) { badge.className='al-badge badge-inv'; badge.textContent='One-time full investment upfront'; }
            if (panel) panel.classList.add('d-none');
            if (usdEl) usdEl.min = 60;
        }
    }

    // ── Misc ─────────────────────────────────────────────────
    function updateFileLabel(input) {
        var el = document.getElementById('file-label-text');
        if (el && input.files && input.files[0]) el.textContent = input.files[0].name;
    }

    function copyWallet() {
        var txt = document.getElementById('walletAddress').textContent.trim();
        navigator.clipboard.writeText(txt).then(function(){ toastr.info('Wallet address copied!'); });
    }

    // ── CSRF refresh on submit ───────────────────────────────
    (function() {
        var form = document.querySelector('form[action="{{ route('register.user') }}"]');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var self = this;
            fetch('{{ route('csrf.refresh') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(d){
                form.querySelectorAll('input[name="_token"]').forEach(function(el){ el.value = d.token; });
                self.submit();
            })
            .catch(function(){ self.submit(); });
        }, true);
    })();
</script>
@endsection
