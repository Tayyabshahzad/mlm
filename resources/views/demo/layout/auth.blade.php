<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Global Visioners International | @yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --primary-light: #818cf8;
            --purple: #7c3aed;
            --surface: #ffffff;
            --bg: #f0f2ff;
            --border: #e4e7f0;
            --text: #0f172a;
            --muted: #64748b;
            --placeholder: #b0bac8;
        }

        html, body { height: 100%; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
        }

        /* ══════════════════════════════════════════
           LAYOUT
        ══════════════════════════════════════════ */
        .al-wrap { display: flex; min-height: 100vh; width: 100%; }

        /* ── Left Aside ──────────────────────────── */
        .al-aside {
            width: 440px;
            flex-shrink: 0;
            background: linear-gradient(145deg, #1a1756 0%, #312e81 30%, #4f46e5 65%, #6d28d9 100%);
            display: flex;
            flex-direction: column;
            padding: 3rem 2.75rem;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles */
        .al-aside::before {
            content: '';
            position: absolute; top: -120px; right: -90px;
            width: 380px; height: 380px; border-radius: 50%;
            background: rgba(255,255,255,0.055);
            pointer-events: none;
        }
        .al-aside::after {
            content: '';
            position: absolute; bottom: -100px; left: -70px;
            width: 300px; height: 300px; border-radius: 50%;
            background: rgba(255,255,255,0.04);
            pointer-events: none;
        }
        .al-aside-deco {
            position: absolute; top: 50%; right: -60px;
            width: 180px; height: 180px; border-radius: 50%;
            background: rgba(255,255,255,0.03);
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* Logo */
        .al-aside-logo { margin-bottom: auto; z-index: 1; }
        .al-aside-logo img { height: 48px; filter: brightness(0) invert(1); opacity: .9; }

        /* Body */
        .al-aside-body { z-index: 1; padding: 3rem 0 2.5rem; }
        .al-aside-body h2 {
            font-size: 2.1rem; font-weight: 900; color: #fff;
            line-height: 1.22; margin-bottom: 1rem;
            letter-spacing: -.02em;
        }
        .al-aside-body h2 span {
            background: linear-gradient(90deg, #a5b4fc, #c4b5fd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .al-aside-body p {
            color: rgba(255,255,255,0.65); font-size: .88rem; line-height: 1.8;
        }

        /* Features */
        .al-features { margin-top: 2rem; display: flex; flex-direction: column; gap: .7rem; }
        .al-feature {
            display: flex; align-items: center; gap: .85rem;
            color: rgba(255,255,255,0.85); font-size: .84rem; font-weight: 500;
        }
        .al-feature-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(4px);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Stats row */
        .al-stats {
            display: flex; gap: 1.25rem; margin-top: 2.25rem; z-index: 1;
        }
        .al-stat {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 12px; padding: .7rem 1rem;
            flex: 1;
        }
        .al-stat-num { font-size: 1.35rem; font-weight: 800; color: #fff; line-height: 1; }
        .al-stat-lbl { font-size: .7rem; color: rgba(255,255,255,0.55); margin-top: .2rem; }

        /* Footer */
        .al-aside-footer {
            z-index: 1; display: flex;
            justify-content: space-between; align-items: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 2rem;
        }
        .al-aside-footer span { color: rgba(255,255,255,0.38); font-size: .72rem; }
        .al-aside-footer a { color: rgba(255,255,255,0.5); font-size: .72rem; text-decoration: none; margin-left: .8rem; transition: color .2s; }
        .al-aside-footer a:hover { color: rgba(255,255,255,0.85); }

        /* ── Right Panel ─────────────────────────── */
        .al-content {
            flex: 1;
            display: flex; flex-direction: column;
            background: var(--bg);
            overflow-y: auto;
            position: relative;
        }
        /* Subtle grid background */
        .al-content::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(79,70,229,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(79,70,229,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none; z-index: 0;
        }

        .al-topbar {
            display: flex; justify-content: flex-end; align-items: center;
            gap: .5rem; padding: 1.3rem 2.25rem 0;
            position: relative; z-index: 1;
        }
        .al-topbar span { font-size: .83rem; color: var(--muted); }
        .al-topbar a {
            background: rgba(79,70,229,0.1); color: var(--primary);
            font-weight: 600; font-size: .8rem;
            padding: .38rem 1.1rem; border-radius: 20px;
            text-decoration: none; transition: all .2s;
            border: 1px solid rgba(79,70,229,0.15);
        }
        .al-topbar a:hover { background: var(--primary); color: #fff; }

        .al-body {
            flex: 1; display: flex;
            justify-content: center; align-items: center;
            padding: 2rem 1.5rem 3.5rem;
            position: relative; z-index: 1;
        }

        /* ══════════════════════════════════════════
           CARD
        ══════════════════════════════════════════ */
        .al-card {
            background: #fff;
            border-radius: 24px;
            padding: 2.5rem 2.75rem;
            width: 100%; max-width: 560px;
            box-shadow:
                0 0 0 1px rgba(79,70,229,0.06),
                0 8px 32px rgba(79,70,229,0.1),
                0 2px 8px rgba(0,0,0,0.04);
            animation: cardIn .4s ease both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .al-card-header { text-align: center; margin-bottom: 1.9rem; }
        .al-brand-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--purple));
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 24px rgba(79,70,229,0.4);
            position: relative;
        }
        .al-brand-icon::after {
            content: '';
            position: absolute; inset: -4px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(79,70,229,0.2), rgba(124,58,237,0.2));
            z-index: -1;
        }
        .al-card-title {
            font-size: 1.55rem; font-weight: 800; color: var(--text);
            letter-spacing: -.02em; margin-bottom: .3rem;
        }
        .al-card-sub { font-size: .84rem; color: var(--muted); }

        /* ══════════════════════════════════════════
           FORM ELEMENTS
        ══════════════════════════════════════════ */
        .f-group { margin-bottom: 1rem; }
        .f-label {
            display: flex; align-items: center; gap: .3rem;
            font-size: .68rem; font-weight: 700;
            color: #475569; text-transform: uppercase;
            letter-spacing: .7px; margin-bottom: .4rem;
        }
        .f-wrap { position: relative; }
        .f-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            pointer-events: none; display: flex; align-items: center;
            transition: color .2s;
        }
        .f-input {
            width: 100%;
            padding: .72rem 1rem .72rem 2.65rem;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: #fafbff;
            color: var(--text);
            font-size: .88rem; font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none; appearance: none;
        }
        .f-input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(79,70,229,0.1);
        }
        .f-wrap:focus-within .f-icon svg path,
        .f-wrap:focus-within .f-icon svg circle,
        .f-wrap:focus-within .f-icon svg rect,
        .f-wrap:focus-within .f-icon svg line,
        .f-wrap:focus-within .f-icon svg polyline {
            stroke: var(--primary);
        }
        .f-input::placeholder { color: var(--placeholder); }
        .f-input.no-icon { padding-left: 1rem; }

        /* Select */
        .f-select { cursor: pointer; padding-right: 2.5rem; }
        .f-select-arrow {
            position: absolute; right: 13px; top: 50%;
            transform: translateY(-50%); pointer-events: none;
        }

        /* Row */
        .f-row { display: flex; gap: .85rem; }
        .f-row .f-group { flex: 1; }

        /* Error */
        .f-error {
            font-size: .73rem; color: #ef4444;
            margin-top: .25rem; display: flex; align-items: center; gap: .25rem;
        }

        /* Divider */
        .f-divider {
            display: flex; align-items: center; gap: .75rem;
            font-size: .65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1.2px; color: #94a3b8;
            margin: 1.25rem 0 1rem;
        }
        .f-divider::before, .f-divider::after {
            content: ''; flex: 1; height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
        }

        /* ══════════════════════════════════════════
           PAYMENT RADIO CARDS
        ══════════════════════════════════════════ */
        .pm-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .55rem; }
        .pm-card {
            display: flex; align-items: center; gap: .55rem;
            padding: .65rem .9rem;
            border: 1.5px solid var(--border); border-radius: 11px;
            background: #fafbff; cursor: pointer;
            font-size: .82rem; font-weight: 500; color: #374151;
            transition: all .18s;
        }
        .pm-card:hover { border-color: #a5b4fc; background: #f5f3ff; }
        .pm-card:has(input:checked) {
            border-color: var(--primary);
            background: rgba(79,70,229,0.06);
            color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.08);
        }
        .pm-card input[type="radio"] { accent-color: var(--primary); width: 14px; height: 14px; }

        /* ══════════════════════════════════════════
           CSS TOGGLE SWITCHES (no Metronic)
        ══════════════════════════════════════════ */
        .sw-row { display: flex; flex-direction: column; gap: .55rem; }

        .sw-card {
            display: flex; align-items: center; gap: .9rem;
            padding: .85rem 1.1rem;
            border: 1.5px solid var(--border); border-radius: 13px;
            background: #fafbff; cursor: pointer;
            transition: border-color .2s, background .2s, box-shadow .2s;
            position: relative; overflow: hidden;
        }
        .sw-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(79,70,229,0.04), rgba(124,58,237,0.04));
            opacity: 0; transition: opacity .2s;
        }
        .sw-card:has(.sw-input:checked) {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.09);
        }
        .sw-card:has(.sw-input:checked)::before { opacity: 1; }

        /* Hidden real checkbox */
        .sw-input {
            position: absolute; opacity: 0;
            width: 0; height: 0; pointer-events: none;
        }

        /* Text content */
        .sw-text { flex: 1; z-index: 1; }
        .sw-title { font-size: .87rem; font-weight: 600; color: #111827; display: block; margin-bottom: 2px; }
        .sw-desc  { font-size: .72rem; color: #6b7280; line-height: 1.4; }
        .sw-badge {
            display: none; font-size: .7rem; font-weight: 700;
            color: var(--primary); margin-top: 3px;
            background: rgba(79,70,229,0.1); padding: 1px 6px; border-radius: 20px;
        }

        /* The visual toggle pill */
        .sw-track {
            width: 46px; height: 26px; min-width: 46px;
            background: #d1d5db; border-radius: 13px;
            position: relative; flex-shrink: 0;
            transition: background .25s;
            z-index: 1;
        }
        .sw-track::after {
            content: ''; position: absolute;
            top: 3px; left: 3px;
            width: 20px; height: 20px;
            background: #fff; border-radius: 50%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.18);
            transition: transform .25s cubic-bezier(.34,1.56,.64,1);
        }
        .sw-card:has(.sw-input:checked) .sw-track { background: var(--primary); }
        .sw-card:has(.sw-input:checked) .sw-track::after { transform: translateX(20px); }

        /* Charge summary */
        .charge-box {
            display: none;
            background: linear-gradient(135deg, #f0f4ff, #f5f3ff);
            border: 1px solid #c7d2fe; border-radius: 12px;
            padding: .7rem 1rem; font-size: .77rem;
            margin-top: .7rem; color: #4338ca;
        }
        .charge-box-title { font-weight: 700; color: #3730a3; margin-bottom: .3rem; }

        /* ══════════════════════════════════════════
           FILE UPLOAD
        ══════════════════════════════════════════ */
        .f-file-label {
            display: flex; align-items: center; gap: .6rem;
            padding: .75rem 1.1rem;
            border: 2px dashed #c7d2fe; border-radius: 12px;
            background: #f8f7ff; color: var(--primary);
            font-size: .85rem; font-weight: 500; cursor: pointer;
            transition: all .2s;
        }
        .f-file-label:hover { border-color: var(--primary); background: #eef0ff; }

        /* ══════════════════════════════════════════
           SUBMIT BUTTON
        ══════════════════════════════════════════ */
        .f-btn {
            width: 100%; padding: .88rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--purple) 100%);
            color: #fff; border: none; border-radius: 13px;
            font-size: .93rem; font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer; letter-spacing: .15px;
            box-shadow: 0 4px 18px rgba(79,70,229,0.38);
            transition: all .22s;
            position: relative; overflow: hidden;
        }
        .f-btn::after {
            content: '';
            position: absolute; inset: 0;
            background: rgba(255,255,255,0);
            transition: background .2s;
        }
        .f-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(79,70,229,0.45); }
        .f-btn:hover::after { background: rgba(255,255,255,0.07); }
        .f-btn:active { transform: translateY(0); box-shadow: 0 3px 12px rgba(79,70,229,0.3); }

        /* ══════════════════════════════════════════
           MISC
        ══════════════════════════════════════════ */
        .al-badge {
            display: inline-flex; align-items: center; gap: .3rem;
            font-size: .7rem; font-weight: 600;
            padding: .22rem .65rem; border-radius: 20px; margin-top: .35rem;
        }
        .badge-inv  { background: rgba(16,185,129,0.1); color: #059669; border: 1px solid rgba(16,185,129,0.2); }
        .badge-inst { background: rgba(245,158,11,0.1);  color: #d97706; border: 1px solid rgba(245,158,11,0.2); }

        .wallet-box {
            display: flex; align-items: center;
            border: 1.5px solid var(--border); border-radius: 11px; overflow: hidden;
            margin-top: .4rem;
        }
        .wallet-addr { flex: 1; padding: .6rem .9rem; font-size: .8rem; color: #374151; word-break: break-all; }
        .wallet-copy {
            padding: .6rem .9rem; background: rgba(79,70,229,0.08);
            border: none; color: var(--primary); font-size: .78rem;
            font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;
            transition: background .2s;
        }
        .wallet-copy:hover { background: rgba(79,70,229,0.16); }

        .phone-wrap { display: flex; }
        .phone-wrap select {
            border-radius: 12px 0 0 12px !important;
            border: 1.5px solid var(--border) !important;
            border-right: none !important;
            background: #fafbff !important;
            font-family: 'Inter', sans-serif;
            font-size: .86rem; min-width: 88px;
            padding: .72rem .5rem .72rem .8rem;
            outline: none; color: var(--text);
        }
        .phone-wrap .f-input { border-radius: 0 12px 12px 0 !important; padding-left: 1rem !important; }

        .qr-img { width: 100%; max-width: 190px; border-radius: 12px; border: 2px solid var(--border); display: block; margin: 0 auto .7rem; }

        /* Alert banner */
        .al-alert {
            display: flex; align-items: flex-start; gap: .6rem;
            background: #fef2f2; border: 1px solid #fecaca;
            border-radius: 12px; padding: .75rem 1rem;
            margin-bottom: 1rem; font-size: .81rem; color: #b91c1c;
        }

        /* Info panel */
        .info-panel {
            background: linear-gradient(135deg, rgba(16,185,129,0.06), rgba(5,150,105,0.04));
            border: 1.5px solid #6ee7b7;
            border-radius: 12px; padding: .9rem 1.1rem;
            font-size: .8rem; color: #065f46; line-height: 1.65;
        }

        /* Responsive */
        @media (max-width: 991px) { .al-aside { display: none; } }
        @media (max-width: 600px) {
            .al-card { padding: 1.75rem 1.35rem; }
            .f-row { flex-direction: column; gap: 0; }
            .pm-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>

    @yield('page_css')
</head>
<body>
<div class="al-wrap">

    {{-- ── Left Aside ── --}}
    <aside class="al-aside">
        <div class="al-aside-deco"></div>

        <div class="al-aside-logo">
            <img src="{{ asset('assets/custom-images/gvi-text.png') }}" alt="GVI" />
        </div>

        <div class="al-aside-body">
            <h2>Grow Your<br>Wealth with <span>GVI</span></h2>
            <p>A powerful multi-level investment platform designed for growth, transparency, and long-term financial freedom.</p>

            <div class="al-features">
                <div class="al-feature">
                    <div class="al-feature-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/></svg>
                    </div>
                    7-Level commission structure
                </div>
                <div class="al-feature">
                    <div class="al-feature-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/></svg>
                    </div>
                    Secure multi-wallet system
                </div>
                <div class="al-feature">
                    <div class="al-feature-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    Weekly ROI & profit sharing
                </div>
                <div class="al-feature">
                    <div class="al-feature-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="white" stroke-width="1.8"/><path d="M12 6v6l4 2" stroke="white" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </div>
                    Binary 2x / 7x bonus plans
                </div>
            </div>
        </div>

        <div class="al-stats">
            <div class="al-stat">
                <div class="al-stat-num">7</div>
                <div class="al-stat-lbl">Commission Levels</div>
            </div>
            <div class="al-stat">
                <div class="al-stat-num">25mo</div>
                <div class="al-stat-lbl">Savings Plan</div>
            </div>
            <div class="al-stat">
                <div class="al-stat-num">2×</div>
                <div class="al-stat-lbl">Binary Bonus</div>
            </div>
        </div>

        <div class="al-aside-footer">
            <span>© {{ date('Y') }} Global Visioners International</span>
            <div><a href="#">Privacy</a><a href="#">Terms</a></div>
        </div>
    </aside>

    {{-- ── Right Panel ── --}}
    <div class="al-content">
        <div class="al-topbar">@yield('nav_bar')</div>
        <div class="al-body">@yield('content')</div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    toastr.options = { positionClass: 'toast-top-right', timeOut: 4000, closeButton: true, progressBar: true };
</script>

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <script>toastr.error("{{ addslashes($error) }}");</script>
    @endforeach
@endif
@if (session('error'))
    <script>toastr.error("{{ addslashes(session('error')) }}");</script>
@endif
@if (session('success'))
    <script>toastr.success("{{ addslashes(session('success')) }}");</script>
@endif

@yield('page_js')
</body>
</html>
