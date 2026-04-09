<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->
<head>
    <meta charset="utf-8" />
    <title>Global Visioners International | @yield('title')</title>
    <meta name="description" content="GVI Portal" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!--begin::Global Theme Styles-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <!--end::Global Theme Styles-->
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico')}}" />

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: #f0f4ff;
            min-height: 100vh;
        }

        .auth-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ── Left panel ─────────────────────────── */
        .auth-aside {
            width: 420px;
            flex-shrink: 0;
            background: linear-gradient(160deg, #312e81 0%, #4f46e5 50%, #7c3aed 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .auth-aside::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }

        .auth-aside::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .aside-logo img {
            height: 60px;
            filter: brightness(0) invert(1);
        }

        .aside-body {
            z-index: 1;
        }

        .aside-body h2 {
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 1rem;
        }

        .aside-body p {
            color: rgba(255,255,255,0.75);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .aside-features {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .aside-feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255,255,255,0.9);
            font-size: 0.88rem;
        }

        .aside-feature-icon {
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .aside-footer {
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .aside-footer span {
            color: rgba(255,255,255,0.5);
            font-size: 0.78rem;
        }

        .aside-footer a {
            color: rgba(255,255,255,0.6);
            font-size: 0.78rem;
            text-decoration: none;
            margin-left: 1rem;
        }

        .aside-footer a:hover { color: #fff; }

        /* ── Right panel ─────────────────────────── */
        .auth-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(160deg, #f0f4ff 0%, #e8edf8 100%);
            overflow-y: auto;
        }

        .auth-content-inner {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem 1.5rem;
        }

        /* ── Card ─────────────────────────── */
        .auth-card {
            background: #fff;
            border-radius: 24px;
            padding: 2.25rem 2.5rem;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 40px rgba(79, 70, 229, 0.08), 0 2px 12px rgba(0,0,0,0.04);
        }

        .auth-card-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .auth-brand-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 20px rgba(79,70,229,0.3);
        }

        .auth-card-title {
            font-size: 1.55rem;
            font-weight: 700;
            color: #1e1b4b;
            margin: 0 0 0.3rem;
        }

        .auth-card-subtitle {
            font-size: 0.88rem;
            color: #94a3b8;
            margin: 0;
        }

        /* ── Form Elements ─────────────────────── */
        .auth-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .auth-input-wrap {
            position: relative;
        }

        .auth-input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .auth-input {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.6rem !important;
            border: 1.5px solid #e8edf0 !important;
            border-radius: 10px !important;
            background: #f8fafc !important;
            font-size: 0.88rem !important;
            color: #1e1b4b !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
            outline: none !important;
            box-shadow: none !important;
            height: auto !important;
        }

        .auth-input:focus {
            border-color: #4f46e5 !important;
            background: #fff !important;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1) !important;
        }

        .auth-input::placeholder { color: #c4c9d4 !important; }

        .auth-select {
            appearance: none;
            -webkit-appearance: none;
            cursor: pointer;
            padding-right: 2.5rem !important;
        }

        .auth-select-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }

        /* ── File upload ─────────────────────── */
        .auth-file-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1rem;
            border: 1.5px dashed #c7d2fe;
            border-radius: 10px;
            background: #f8f7ff;
            cursor: pointer;
            font-size: 0.88rem;
            color: #4f46e5;
            font-weight: 500;
            transition: all 0.2s;
            width: 100%;
        }

        .auth-file-label:hover {
            border-color: #4f46e5;
            background: #f0f0ff;
        }

        /* ── Account type badge ───────────────── */
        .account-badge {
            display: inline-block;
            font-size: 0.74rem;
            font-weight: 500;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
        }

        .badge-investment { background: rgba(16,185,129,0.1); color: #059669; }
        .badge-installment { background: rgba(245,158,11,0.1); color: #d97706; }

        /* ── Submit button ────────────────────── */
        .auth-btn {
            width: 100%;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #fff;
            border: none;
            padding: 0.85rem 1.5rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(79,70,229,0.3);
            letter-spacing: 0.3px;
        }

        .auth-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79,70,229,0.4);
        }

        .auth-btn:active { transform: translateY(0); }

        .auth-nav-link {
            background: rgba(79,70,229,0.08);
            color: #4f46e5;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .auth-nav-link:hover {
            background: rgba(79,70,229,0.15);
            color: #3730a3;
        }

        .auth-nav-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 0.5rem;
            padding: 1.25rem 2rem 0;
        }

        .auth-nav-bar span {
            font-size: 0.88rem;
            color: #94a3b8;
        }

        .field-group { margin-bottom: 1rem; }

        .text-danger { font-size: 0.78rem; margin-top: 0.25rem; }

        /* ── Responsive ───────────────────────── */
        @media (max-width: 991px) {
            .auth-aside { display: none; }
        }
    </style>

    @section('page_css')
    @show
</head>
<!--end::Head-->
<body>
    <div class="auth-wrapper">

        <!-- Left Aside -->
        <div class="auth-aside">
            <div class="aside-logo">
                <img src="{{ asset('assets/custom-images/gvi-text.png') }}" alt="GVI" />
            </div>

            <div class="aside-body">
                <h2>Welcome to<br>GV International</h2>
                <p>Your gateway to a powerful multi-level investment platform built for growth and financial freedom.</p>

                <div class="aside-features">
                    <div class="aside-feature-item">
                        <div class="aside-feature-icon">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        Multi-level commission system
                    </div>
                    <div class="aside-feature-item">
                        <div class="aside-feature-icon">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="white" stroke-width="1.8" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        Secure wallet & transactions
                    </div>
                    <div class="aside-feature-item">
                        <div class="aside-feature-icon">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        Weekly ROI & profit sharing
                    </div>
                </div>
            </div>

            <div class="aside-footer">
                <span>© 2024 GVI</span>
                <div>
                    <a href="#">Privacy</a>
                    <a href="#">Terms</a>
                    <a href="#">Contact</a>
                </div>
            </div>
        </div>

        <!-- Right Content -->
        <div class="auth-content">
            <div class="auth-nav-bar">
                @yield('nav_bar')
            </div>
            <div class="auth-content-inner">
                @yield('content')
            </div>
        </div>

    </div>

    <script>var HOST_URL = "https://preview.keenthemes.com/metronic/theme/html/tools/preview";</script>
    <script>var KTAppSettings = { "breakpoints": { "sm": 576, "md": 768, "lg": 992, "xl": 1200, "xxl": 1400 }, "colors": { "theme": { "base": { "white": "#ffffff", "primary": "#3699FF", "secondary": "#E5EAEE", "success": "#1BC5BD", "info": "#8950FC", "warning": "#FFA800", "danger": "#F64E60", "light": "#E4E6EF", "dark": "#181C32" }, "light": { "white": "#ffffff", "primary": "#E1F0FF", "secondary": "#EBEDF3", "success": "#C9F7F5", "info": "#EEE5FF", "warning": "#FFF4DE", "danger": "#FFE2E5", "light": "#F3F6F9", "dark": "#D6D6E0" }, "inverse": { "white": "#ffffff", "primary": "#ffffff", "secondary": "#3F4254", "success": "#ffffff", "info": "#ffffff", "warning": "#ffffff", "danger": "#ffffff", "light": "#464E5F", "dark": "#ffffff" } }, "gray": { "gray-100": "#F3F6F9", "gray-200": "#EBEDF3", "gray-300": "#E4E6EF", "gray-400": "#D1D3E0", "gray-500": "#B5B5C3", "gray-600": "#7E8299", "gray-700": "#5E6278", "gray-800": "#3F4254", "gray-900": "#181C32" } }, "font-family": "Poppins" };</script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js')}}"></script>
    <script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js')}}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js')}}"></script>

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <script>toastr.error("{{ $error }}");</script>
        @endforeach
    @endif

    @if (session('error'))
        <script>toastr.error("{{ session('error') }}");</script>
    @endif

    @section('page_js')
    @show
</body>
</html>
