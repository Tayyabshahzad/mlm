<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <base href="">

    <meta charset="utf-8" />
    <title>Global Visioners International | @yield('title') </title>
    <meta name="description" content="Login page example" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="canonical" href="https://keenthemes.com/metronic" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Page Custom Styles(used by this page)-->
    <link href="{{ asset('assets/css/pages/login/login-1.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Page Custom Styles-->
    <!--begin::Global Theme Styles(used by all pages)-->

    <link href="{{ asset('assets/css/themes/layout/aside/dark.css') }}" rel="stylesheet" type="text/css" />
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <meta charset="utf-8" />
    <title> Global Visioners International | @yield('title')</title>
    <link rel="canonical" href="https://keenthemes.com/metronic" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/themes/layout/header/base/light.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/themes/layout/header/menu/light.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/themes/layout/brand/dark.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/themes/layout/aside/dark.css') }}" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="{{ asset('assets/custom-images/gvi-icon.png') }}" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gojs/2.3.9/go.js" defer></script>
    <style>
        .second-div {
            transform: translateX(-100%);
            opacity: 0;
            transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
        }

        .second-div.visible {
            transform: translateX(0);
            opacity: 1;
        }


        .timeline.timeline-6:before {
            left: 60% !important;
        }



        .username {
            font-size: 2.5rem;
            /* Adjust size as needed */
            color: #fff;
            /* Text color */
            text-align: center;
            text-shadow: 0 0 3px #00f, 0 0 7px #00f, 0 0 17px #00f, 0 0 37px #0ff, 0 0 77px #0ff, 0 0 113px #0ff;
            animation: glow 1.5s infinite alternate;
        }

        @keyframes glow {
            0% {
                text-shadow: 0 0 5px #00f, 0 0 10px #00f, 0 0 20px #00f, 0 0 40px #0ff, 0 0 80px #0ff, 0 0 120px #0ff;
            }

            100% {
                text-shadow: 0 0 10px #0ff, 0 0 20px #0ff, 0 0 30px #0ff, 0 0 50px #0ff, 0 0 100px #0ff, 0 0 150px #0ff;
            }
        }

        .bg-secondary {
            background-color: rgb(194, 200, 18) !important
        }

        .bg-light {
            background-color: #5AB8B0 !important
        }

        /* ── Wallets menu: violet accent (dark aside) ─────────────────── */
        .aside-menu .menu-nav > .menu-item.menu-wallets > .menu-link .svg-icon svg g [fill] { fill: #a47bff !important; }
        .aside-menu .menu-nav > .menu-item.menu-wallets.menu-item-open > .menu-link,
        .aside-menu .menu-nav > .menu-item.menu-wallets > .menu-link:hover { background-color: rgba(137, 80, 252, .14) !important; }
        .aside-menu .menu-nav > .menu-item.menu-wallets.menu-item-open > .menu-link .menu-text { color: #fff !important; }
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item > .menu-link:hover,
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item.menu-item-open > .menu-link { background-color: rgba(137, 80, 252, .12) !important; }
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item > .menu-link:hover .menu-text { color: #c9b0ff !important; }
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item.menu-item-active > .menu-link {
            background-color: rgba(137, 80, 252, .22) !important;
            box-shadow: inset 3px 0 0 #8950fc;
        }
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item.menu-item-active > .menu-link .menu-text { color: #fff !important; font-weight: 600; }
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item.menu-item-active > .menu-link .menu-bullet > span,
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item.menu-item-open > .menu-link .menu-bullet > span,
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item > .menu-link:hover .menu-bullet > span { background-color: #a47bff !important; }
        .aside-menu .menu-item.menu-wallets .menu-submenu .menu-item.menu-item-open > .menu-link .menu-arrow { color: #a47bff !important; }

  .announcement {
    background-color: #d4edda;
    color: #155724;
    border-radius: 10px;
    padding: 20px;
    font-family: Arial, sans-serif;
    font-size: 16px;
    max-width: 600px;
    margin: auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }

  .announcement p {
    margin: 10px 0;
  }

  .announcement strong {
    font-weight: bold;
  } 
    </style>


    @php
        $inactiveUsersAre = \DB::table('users')->where('can_login', 0)->whereNull('deleted_at')->count();
    @endphp

    @section('custom_css')
    @show
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body"
    class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">
    <!--begin::Main-->
    <!--begin::Header Mobile-->
    <div id="kt_header_mobile" class="header-mobile align-items-center header-mobile-fixed">
        <!--begin::Logo-->
        <a href="{{ route('dashboard') }}">
            <img alt="Logo" src="{{ asset('assets/custom-images/gvi-icon.png') }}" width="30%" />

        </a>



        <h5 style="position: absolute;left:27%;font-size:11px" class="text-white username">
            Welcome: {{ Auth::user()->username }}
        </h5>




        <!--end::Logo-->
        <!--begin::Toolbar-->
        <div class="d-flex align-items-center">

            <!--begin::Aside Mobile Toggle-->
            <button class="p-0 btn burger-icon burger-icon-left" id="kt_aside_mobile_toggle">
                <span></span>
            </button>
            <button class="p-0 ml-4 btn burger-icon d-none" id="kt_header_mobile_toggle">
                <span></span>
            </button>
            <button class="p-0 ml-2 btn btn-hover-text-primary" id="kt_header_mobile_topbar_toggle">
                <span class="svg-icon svg-icon-xl">
                    <!--begin::Svg Icon | path:assets/media/svg/icons/General/User.svg-->
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                        height="24px" viewBox="0 0 24 24" version="1.1">
                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <polygon points="0 0 24 0 24 24 0 24" />
                            <path
                                d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z"
                                fill="#000000" fill-rule="nonzero" opacity="0.3" />
                            <path
                                d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z"
                                fill="#000000" fill-rule="nonzero" />
                        </g>
                    </svg>
                    <!--end::Svg Icon-->
                </span>
            </button>
            <!--end::Topbar Mobile Toggle-->
        </div>
        <!--end::Toolbar-->
    </div>
    <!--end::Header Mobile-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="flex-row d-flex flex-column-fluid page">
            <!--begin::Aside-->
            <div class="aside aside-left aside-fixed d-flex flex-column flex-row-auto" id="kt_aside">
                <div class="brand flex-column-auto" id="kt_brand">
                    <a href="{{ route('dashboard') }}" class="brand-logo">
                        <img alt="Logo" src="{{ asset('assets/custom-images/logo-white.png') }}" width="100%" />
                    </a>
                    <button class="px-0 brand-toggle btn btn-sm" id="kt_aside_toggle">
                        <span class="svg-icon svg-icon-xl">
                            <!--begin::Svg Icon | path:assets/media/svg/icons/Navigation/Angle-double-left.svg-->
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <polygon points="0 0 24 0 24 24 0 24" />
                                    <path
                                        d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z"
                                        fill="#000000" fill-rule="nonzero"
                                        transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999)" />
                                    <path
                                        d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z"
                                        fill="#000000" fill-rule="nonzero" opacity="0.3"
                                        transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999)" />
                                </g>
                            </svg>
                            <!--end::Svg Icon-->
                        </span>
                    </button>
                </div>
                <!--end::Brand-->
                <!--begin::Aside Menu-->
                <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
                    <!--begin::Menu Container-->
                    <div id="kt_aside_menu" class="my-4 aside-menu" data-menu-vertical="1" data-menu-scroll="1"
                        data-menu-dropdown-timeout="500">
                        <!--begin::Menu Nav-->
                        <ul class="menu-nav">
                            @php
                                $isSavingOnly    = auth()->user()->account_type === 'saving';
                                $isOperatorOnly  = auth()->user()->isDataEntryOperatorOnly();
                                $isAdmin         = auth()->user()->hasRole('admin');
                                $savingRootId    = \App\Models\Setting::first()?->saving_parent_user_id;
                                $hasSavingAccess = auth()->id() == $savingRootId
                                    || (auth()->user()->saving_enrolled && auth()->user()->saving_enrollment_activated);
                                // Which wallet groups this member sees:
                                //   saving_only → Saving Wallet (Saving Account) · both → Saving (4 pages) + Standard · standard → Standard
                                $walletCase = ($isSavingOnly && auth()->user()->can_login)
                                    ? 'saving_only'
                                    : ($hasSavingAccess ? 'both' : 'standard');
                                $showSavingTeam = $isSavingOnly || (auth()->user()->saving_enrolled && auth()->user()->saving_enrollment_activated);
                            @endphp

                            {{-- ═════════════════════════ MEMBER ═════════════════════════ --}}
                            <li class="menu-item @if(request()->is('dashboard')) menu-item-active @endif" aria-haspopup="true">
                                <a href="{{ route('dashboard') }}" class="menu-link">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <polygon points="0 0 24 0 24 24 0 24"/>
                                                <path d="M12.9336061,16.072447 L19.36,10.9564761 L19.5181585,10.8312381 C20.1676248,10.3169571 20.2772143,9.3735535 19.7629333,8.72408713 C19.6917232,8.63415859 19.6104327,8.55269514 19.5206557,8.48129411 L12.9336854,3.24257445 C12.3871201,2.80788259 11.6128799,2.80788259 11.0663146,3.24257445 L4.47482784,8.48488609 C3.82645598,9.00054628 3.71887192,9.94418071 4.23453211,10.5925526 C4.30500305,10.6811601 4.38527899,10.7615046 4.47382636,10.8320511 L4.63,10.9564761 L11.0659024,16.0730648 C11.6126744,16.5077525 12.3871218,16.5074963 12.9336061,16.072447 Z" fill="#000000" fill-rule="nonzero"/>
                                                <path d="M11.0563554,18.6706981 L5.33593024,14.122919 C4.94553994,13.8125559 4.37746707,13.8774308 4.06710397,14.2678211 L4.06,14.2768747 C3.75257288,14.6738539 3.82516916,15.244888 4.22214834,15.5523151 L11.0872776,20.8031356 C11.6250734,21.2144692 12.371757,21.2145375 12.909628,20.8033023 L19.7677785,15.559828 C20.1693192,15.2528257 20.2459576,14.6784381 19.9389553,14.2768974 L19.935,14.2717619 C19.6266937,13.8743807 19.0546209,13.8021712 18.6572397,14.1104775 L12.9235044,18.6705218 C12.377022,19.1051477 11.6029199,19.1052208 11.0563554,18.6706981 Z" fill="#000000" opacity="0.3"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Dashboard</span>
                                </a>
                            </li>

                            @if((auth()->user()->account_type === 'saving' && auth()->user()->can_login) || (auth()->user()->saving_enrolled && auth()->user()->saving_enrollment_activated && auth()->user()->savingInstalments()->exists()))
                            <li class="menu-item @if(request()->is('saving/instalments*')) menu-item-active @endif" aria-haspopup="true">
                                <a href="{{ route('saving.user.instalments') }}" class="menu-link">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M5,3 L19,3 C19.5522847,3 20,3.44771525 20,4 L20,20 C20,20.5522847 19.5522847,21 19,21 L5,21 C4.44771525,21 4,20.5522847 4,20 L4,4 C4,3.44771525 4.44771525,3 5,3 Z" fill="#000000" opacity="0.3"/>
                                                <rect fill="#000000" x="7" y="8" width="10" height="2" rx="1"/>
                                                <rect fill="#000000" x="7" y="12" width="7" height="2" rx="1"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">My Saving Plan</span>
                                </a>
                            </li>
                            @endif

                            @if(!$isOperatorOnly)
                            {{-- Genealogy --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('genealogy/team*') || request()->is('genealogy/my-saving-tree*') || request()->is('team-performance*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <!-- Genealogy / family-tree icon (duotone) -->
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <!-- top (sponsor) node -->
                                                <rect fill="#000000" x="8.5" y="2" width="7" height="5.5" rx="1.5"/>
                                                <!-- connectors -->
                                                <rect fill="#000000" x="11.25" y="7.5" width="1.5" height="3"/>
                                                <rect fill="#000000" x="5.25" y="10" width="13.5" height="1.5" rx=".75"/>
                                                <rect fill="#000000" x="5.25" y="10" width="1.5" height="3.5"/>
                                                <rect fill="#000000" x="17.25" y="10" width="1.5" height="3.5"/>
                                                <!-- downline nodes -->
                                                <rect fill="#000000" opacity="0.3" x="2" y="13.5" width="8" height="6.5" rx="1.5"/>
                                                <rect fill="#000000" opacity="0.3" x="14" y="13.5" width="8" height="6.5" rx="1.5"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Genealogy</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        @if(!$isSavingOnly)
                                        <li class="menu-item @if(request()->is('genealogy/team*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('genealogy.team') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">My Team</span>
                                            </a>
                                        </li>
                                        @endif
                                        @if($showSavingTeam)
                                        <li class="menu-item @if(request()->is('genealogy/my-saving-tree*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('genealogy.my.saving.tree') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">My Saving Team</span>
                                            </a>
                                        </li>
                                        @endif
                                        <li class="menu-item @if(request()->is('team-performance*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('team.performance') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">Team Performance</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- Wallets: Online first, Saving ▸ and Standard ▸ nested --}}
                            <li class="menu-item menu-item-submenu menu-wallets @if(request()->is('wallets*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <circle fill="#000000" opacity="0.3" cx="20.5" cy="12.5" r="1.5" />
                                                <rect fill="#000000" opacity="0.3" transform="translate(12.000000, 6.500000) rotate(-15.000000) translate(-12.000000, -6.500000) " x="3" y="3" width="18" height="7" rx="1" />
                                                <path d="M22,9.33681558 C21.5453723,9.12084552 21.0367986,9 20.5,9 C18.5670034,9 17,10.5670034 17,12.5 C17,14.4329966 18.5670034,16 20.5,16 C21.0367986,16 21.5453723,15.8791545 22,15.6631844 L22,18 C22,19.1045695 21.1045695,20 20,20 L4,20 C2.8954305,20 2,19.1045695 2,18 L2,6 C2,4.8954305 2.8954305,4 4,4 L20,4 C21.1045695,4 22,4.8954305 22,6 L22,9.33681558 Z" fill="#000000" />
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Wallets</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('wallets/online*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('wallets.online') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">Online Wallet</span>
                                            </a>
                                        </li>

                                        @if($walletCase !== 'standard')
                                        <li class="menu-item menu-item-submenu @if(request()->is('wallets/saving*')) menu-item-open @endif"
                                            aria-haspopup="true" data-menu-toggle="hover">
                                            <a href="javascript:;" class="menu-link menu-toggle">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">Saving Wallet</span>
                                                <i class="menu-arrow"></i>
                                            </a>
                                            <div class="menu-submenu">
                                                <i class="menu-arrow"></i>
                                                <ul class="menu-subnav">
                                                    <li class="menu-item @if(request()->is('wallets/saving-account*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.saving.account') }}" class="menu-link">
                                                            <i class="menu-bullet menu-bullet-line"><span></span></i>
                                                            <span class="menu-text">Saving Account</span>
                                                        </a>
                                                    </li>
                                                    @if($walletCase === 'both')
                                                    <li class="menu-item @if(request()->is('wallets/saving-direct-indirect*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.saving.direct.indirect') }}" class="menu-link">
                                                            <i class="menu-bullet menu-bullet-line"><span></span></i>
                                                            <span class="menu-text">Savings Direct / Indirect</span>
                                                        </a>
                                                    </li>
                                                    <li class="menu-item @if(request()->is('wallets/saving-roi') || request()->is('wallets/saving-roi/*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.saving.roi') }}" class="menu-link">
                                                            <i class="menu-bullet menu-bullet-line"><span></span></i>
                                                            <span class="menu-text">Savings ROI</span>
                                                        </a>
                                                    </li>
                                                    <li class="menu-item @if(request()->is('wallets/saving-roi-report*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('saving.roi.report') }}" class="menu-link">
                                                            <i class="menu-bullet menu-bullet-line"><span></span></i>
                                                            <span class="menu-text">Saving ROI Report</span>
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </li>
                                        @endif

                                        @if($walletCase !== 'saving_only')
                                        <li class="menu-item menu-item-submenu @if(request()->is('wallets/investment*') || request()->is('wallets/direct-indirect*') || request()->is('wallets/return-on-investment*') || request()->is('wallets/rewards*') || request()->is('wallets/profit-share*') || request()->is('wallets/incentive-wallets*')) menu-item-open @endif"
                                            aria-haspopup="true" data-menu-toggle="hover">
                                            <a href="javascript:;" class="menu-link menu-toggle">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">Standard Wallet</span>
                                                <i class="menu-arrow"></i>
                                            </a>
                                            <div class="menu-submenu">
                                                <i class="menu-arrow"></i>
                                                <ul class="menu-subnav">
                                                    <li class="menu-item @if(request()->is('wallets/investment*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.investment') }}" class="menu-link"><i class="menu-bullet menu-bullet-line"><span></span></i><span class="menu-text">Investment Wallet</span></a>
                                                    </li>
                                                    <li class="menu-item @if(request()->is('wallets/direct-indirect*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.direct.indirect') }}" class="menu-link"><i class="menu-bullet menu-bullet-line"><span></span></i><span class="menu-text">Direct / Indirect</span></a>
                                                    </li>
                                                    <li class="menu-item @if(request()->is('wallets/rewards*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.rewards') }}" class="menu-link"><i class="menu-bullet menu-bullet-line"><span></span></i><span class="menu-text">Rewards</span></a>
                                                    </li>
                                                    <li class="menu-item @if(request()->is('wallets/return-on-investment*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.roi') }}" class="menu-link"><i class="menu-bullet menu-bullet-line"><span></span></i><span class="menu-text">ROI</span></a>
                                                    </li>
                                                    <li class="menu-item @if(request()->is('wallets/profit-share*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.profit.share') }}" class="menu-link"><i class="menu-bullet menu-bullet-line"><span></span></i><span class="menu-text">Profit Share</span></a>
                                                    </li>
                                                    <li class="menu-item @if(request()->is('wallets/incentive-wallets*')) menu-item-active @endif" aria-haspopup="true">
                                                        <a href="{{ route('wallets.incentive') }}" class="menu-link"><i class="menu-bullet menu-bullet-line"><span></span></i><span class="menu-text">Incentive Wallets</span></a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        @endif

                                        <li class="menu-item @if(request()->is('wallets/show-transaction-history*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('show.transaction.history') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">Transactions</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- Digital Cards --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('cards*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <rect fill="#000000" opacity="0.3" x="2" y="5" width="20" height="14" rx="2"/>
                                                <rect fill="#000000" x="2" y="8" width="20" height="3"/>
                                                <rect fill="#000000" opacity="0.3" x="16" y="14" width="4" height="2" rx="1"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">
                                        Digital Cards
                                        @if(($newCardAlerts = auth()->user()->unreadNotifications()->where('type', \App\Notifications\DigitalCardReceivedNotification::class)->count()) > 0)<span class="ml-1 badge badge-pill badge-success">{{ $newCardAlerts }}</span>@endif
                                    </span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('cards') || (request()->is('cards/*') && !request()->is('cards/received*'))) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('cards.index') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">My Cards</span>
                                            </a>
                                        </li>
                                        <li class="menu-item @if(request()->is('cards/received*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('cards.received') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">
                                                    Received Cards
                                                    @if($newCardAlerts > 0)<span class="ml-1 badge badge-pill badge-success">{{ $newCardAlerts }} new</span>@endif
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- My Account --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('profile*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <polygon points="0 0 24 0 24 24 0 24"/>
                                                <path d="M12,11 C9.790861,11 8,9.209139 8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 C16,9.209139 14.209139,11 12,11 Z" fill="#000000" fill-rule="nonzero" opacity="0.3"/>
                                                <path d="M3.00065168,20.1992055 C3.38825852,15.4265159 7.26191235,13 11.9833413,13 C16.7712164,13 20.7048837,15.2931929 20.9979143,20.2 C21.0095879,20.3954741 20.9979143,21 20.2466999,21 C16.541124,21 11.0347247,21 3.72750223,21 C3.47671215,21 2.97953825,20.45918 3.00065168,20.1992055 Z" fill="#000000" fill-rule="nonzero"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">My Account</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('profile/info*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('profile.edit') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Personal Information</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('profile/account*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('account.information') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Account Information</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('profile/bank-details*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('profile.bank.details') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Bank Account</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('profile/social*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('social.account.information') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Social Accounts</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('profile/change-password*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('profile.change.password') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Change Password</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif {{-- !$isOperatorOnly --}}

                            {{-- ═════════════════════════ STAFF TOOLS ═════════════════════════ --}}
                            @if(auth()->user()->hasAnyRole(['admin', 'office-admin', 'operator']))
                            <li class="menu-section">
                                <h4 class="menu-text">Staff Tools</h4>
                                <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
                            </li>

                            @if(auth()->user()->hasAnyRole(['admin', 'office-admin']))
                            <li class="menu-item @if(request()->is('reconciliation*') && !request()->is('reconciliation/office-admins*')) menu-item-active @endif" aria-haspopup="true">
                                <a href="{{ route('reconciliation.index') }}" class="menu-link">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M3,10 L12,4 L21,10 L3,10 Z" fill="#000000"/>
                                                <rect fill="#000000" opacity="0.3" x="5" y="11" width="2" height="7"/>
                                                <rect fill="#000000" opacity="0.3" x="11" y="11" width="2" height="7"/>
                                                <rect fill="#000000" opacity="0.3" x="17" y="11" width="2" height="7"/>
                                                <rect fill="#000000" x="3" y="19" width="18" height="2"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">
                                        Bank Reconciliation
                                        @if(($awaitingBankChecks = \App\Models\SavingInstalment::where('status', 'submitted')->where(fn ($q) => $q->whereNull('payment_method')->orWhere('payment_method', '!=', 'digital_card'))->count()) > 0)
                                            <span class="ml-1 badge badge-pill badge-warning">{{ $awaitingBankChecks }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->hasAnyRole(['admin', 'operator']))
                            <li class="menu-item @if(request()->is('payment-sheets*')) menu-item-active @endif" aria-haspopup="true">
                                <a href="{{ route('payment-sheets.index') }}" class="menu-link">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M5.857,2 L13.736,2 C14.091,2 14.434,2.126 14.705,2.355 L19.469,6.384 C19.806,6.669 20,7.088 20,7.529 L20,19.083 C20,20.874 19.980,21 18.143,21 L5.857,21 C4.020,21 4,20.874 4,19.083 L4,3.917 C4,2.126 4.020,2 5.857,2 Z" fill="#000000" opacity="0.3"/>
                                                <path d="M11,14 L9,14 L12,11 L15,14 L13,14 L13,18 L11,18 L11,14 Z" fill="#000000"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Upload Payment Sheet</span>
                                </a>
                            </li>
                            @endif
                            @endif

                            {{-- ═════════════════════════ ADMINISTRATION ═════════════════════════ --}}
                            @if($isAdmin)
                            <li class="menu-section">
                                <h4 class="menu-text">Administration</h4>
                                <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
                            </li>

                            {{-- Members --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('users/index*') || request()->is('users/deleted*') || request()->is('users/change-parent*') || request()->is('users/activation-code*') || request()->is('users/info*') || request()->is('reconciliation/office-admins*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <polygon points="0 0 24 0 24 24 0 24"/>
                                                <path d="M18,14 C16.343,14 15,12.657 15,11 C15,9.343 16.343,8 18,8 C19.657,8 21,9.343 21,11 C21,12.657 19.657,14 18,14 Z M9,11 C6.791,11 5,9.209 5,7 C5,4.791 6.791,3 9,3 C11.209,3 13,4.791 13,7 C13,9.209 11.209,11 9,11 Z" fill="#000000" opacity="0.3"/>
                                                <path d="M17.6,15 C21.007,15.037 23.788,16.759 23.997,20.4 C24.005,20.547 23.997,21 23.453,21 L19.6,21 C19.6,18.749 18.856,16.672 17.6,15 Z M0.001,20.199 C0.388,15.427 4.262,13 8.983,13 C13.771,13 17.705,15.293 17.998,20.2 C18.010,20.395 17.998,21 17.247,21 C13.541,21 8.035,21 0.728,21 C0.477,21 -0.021,20.459 0.001,20.199 Z" fill="#000000"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Members</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('users/index*') && request('tab') !== 'saving') menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('users.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">All Members</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('users/index*') && request('tab') === 'saving') menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ url('users/index?tab=saving') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Saving Members</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('users/deleted*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('deleted.users') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Deleted Members</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('users/change-parent*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.change.parent') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Change Parent</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('users/activation-code*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('user.activation.code') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Activation Codes</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('reconciliation/office-admins*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.office-admins.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Staff Roles</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- Savings Program --}}
                            <li class="menu-item menu-item-submenu @if((request()->is('saving-accounts*') && !request()->is('saving-accounts/commission-config*')) || request()->is('saving-roi-report*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M5,9 L19,9 C20.1045695,9 21,9.8954305 21,11 L21,18 C21,19.1045695 20.1045695,20 19,20 L5,20 C3.8954305,20 3,19.1045695 3,18 L3,11 C3,9.8954305 3.8954305,9 5,9 Z" fill="#000000" opacity="0.3"/>
                                                <circle fill="#000000" cx="12" cy="14.5" r="2.5"/>
                                                <path d="M8,9 L8,7 C8,4.790861 9.790861,3 12,3 C14.209139,3 16,4.790861 16,7 L16,9 L14,9 L14,7 C14,5.8954305 13.1045695,5 12,5 C10.8954305,5 10,5.8954305 10,7 L10,9 Z" fill="#000000"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">
                                        Savings Program
                                        @if(($savingAlerts = \App\Models\User::where('saving_enrolled', true)->where('account_type', '!=', 'saving')->where('saving_enrollment_activated', false)->count() + \App\Models\SavingInstalmentCommission::where('status', 'pending')->count()) > 0)
                                            <span class="ml-1 badge badge-pill badge-warning">{{ $savingAlerts }}</span>
                                        @endif
                                    </span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('saving-accounts') || (request()->is('saving-accounts/*') && !request()->is('saving-accounts/enrollments*') && !request()->is('saving-accounts/instalment-commissions*') && !request()->is('saving-accounts/adjust-plan*') && !request()->is('saving-accounts/commission-config*'))) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.saving.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Saving Accounts</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('saving-accounts/enrollments*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.saving.enrollments') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">
                                                    Enrollments
                                                    @if(($pendingEnrollments = \App\Models\User::where('saving_enrolled', true)->where('account_type', '!=', 'saving')->where('saving_enrollment_activated', false)->count()) > 0)
                                                        <span class="ml-1 badge badge-pill badge-warning">{{ $pendingEnrollments }}</span>
                                                    @endif
                                                </span>
                                            </a>
                                        </li>
                                        <li class="menu-item @if(request()->is('saving-accounts/instalment-commissions*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.saving.instalment-commissions') }}" class="menu-link">
                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                <span class="menu-text">
                                                    Instalment Commissions
                                                    @if(($pendingInstalmentCommissions = \App\Models\SavingInstalmentCommission::where('status', 'pending')->count()) > 0)
                                                        <span class="ml-1 badge badge-pill badge-warning">{{ $pendingInstalmentCommissions }}</span>
                                                    @endif
                                                </span>
                                            </a>
                                        </li>
                                        <li class="menu-item @if(request()->is('saving-accounts/adjust-plan*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.saving.adjust-plan') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Adjust Instalment Plan</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('saving-roi-report*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.saving.roi.report') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Saving ROI Report</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- Finance --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('withdrawals/requests*') || request()->is('withdrawals/analytics*') || request()->is('account/top-ups*') || request()->is('users/incentive-wallet*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <rect fill="#000000" opacity="0.3" x="2" y="6" width="20" height="13" rx="2"/>
                                                <circle fill="#000000" cx="12" cy="12.5" r="3"/>
                                                <rect fill="#000000" x="4" y="3" width="16" height="2" rx="1"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Finance</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('withdrawals/requests*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('withdrawals.requests') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Withdrawal Requests</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('withdrawals/analytics*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('withdrawals.analytics') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Withdrawal Analytics</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('account/top-ups') || (request()->is('account/top-ups/*') && !request()->is('account/top-ups/analytics*'))) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('account.topups.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Account Top-ups</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('account/top-ups/analytics*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('account.topups.analytics') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Top-up Analytics</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('users/incentive-wallet*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.incentive.wallet') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Incentive Wallet</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- ROI & Rental --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('roi-monitoring*') || request()->is('users/roi/payments*') || request()->is('rental*') || request()->is('roi-reversal*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <rect fill="#000000" opacity="0.3" x="4" y="13" width="4" height="7" rx="1"/>
                                                <rect fill="#000000" opacity="0.3" x="10" y="9" width="4" height="11" rx="1"/>
                                                <rect fill="#000000" x="16" y="4" width="4" height="16" rx="1"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">ROI &amp; Rental</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('roi-monitoring*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('users.roi.monitoring') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">ROI Monitoring</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('users/roi/payments*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('roi.payments') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">ROI Payments</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('rental*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('rental.percentage') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Rental Percentage</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('roi-reversal*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.roi-reversal.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">ROI Reversal</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- Rewards --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('pending-rewards*') || request()->is('reward-review*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M12,4.5 L14.351,9.264 L19.608,10.028 L15.804,13.736 L16.702,18.972 L12,16.5 L7.298,18.972 L8.196,13.736 L4.392,10.028 L9.649,9.264 Z" fill="#000000"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Rewards</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('pending-rewards*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.pending-rewards.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Pending Rewards</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('reward-review*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.reward-review.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Reward Review</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- Digital Cards (admin) --}}
                            <li class="menu-item @if(request()->is('admin/digital-cards*') && !request()->is('admin/digital-cards/settings*')) menu-item-active @endif" aria-haspopup="true">
                                <a href="{{ route('admin.cards.index') }}" class="menu-link">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <rect fill="#000000" opacity="0.3" x="2" y="5" width="20" height="14" rx="2"/>
                                                <rect fill="#000000" x="2" y="8" width="20" height="3"/>
                                                <rect fill="#000000" opacity="0.3" x="16" y="14" width="4" height="2" rx="1"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">
                                        Digital Cards
                                        @if(($cardsPendingReview = \App\Models\DigitalCard::where('status', 'reserved')->count()) > 0)
                                            <span class="ml-1 badge badge-pill badge-warning">{{ $cardsPendingReview }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>

                            {{-- Reports --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('report*') || request()->is('genealogy/saving-tree*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M5.857,2 L13.736,2 C14.091,2 14.434,2.126 14.705,2.355 L19.469,6.384 C19.806,6.669 20,7.088 20,7.529 L20,19.083 C20,20.874 19.980,21 18.143,21 L5.857,21 C4.020,21 4,20.874 4,19.083 L4,3.917 C4,2.126 4.020,2 5.857,2 Z" fill="#000000" opacity="0.3"/>
                                                <rect fill="#000000" x="7" y="10" width="2" height="7" rx="1"/>
                                                <rect fill="#000000" x="11" y="12" width="2" height="5" rx="1"/>
                                                <rect fill="#000000" x="15" y="8" width="2" height="9" rx="1"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Reports</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('report/genealogy') || (request()->is('report/genealogy/*') && !request()->is('report/genealogy/tree*'))) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('report.genealogy.tree') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Genealogy Tree Report</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('report/genealogy/tree*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('report.genealogy.tree.view') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Genealogy Tree View</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('genealogy/saving-tree*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('genealogy.saving.tree') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Saving Account Tree</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            {{-- Settings --}}
                            <li class="menu-item menu-item-submenu @if(request()->is('setting*') || request()->is('roi-settings*') || request()->is('reward-settings*') || request()->is('saving-accounts/commission-config*') || request()->is('admin/digital-cards/settings*')) menu-item-open @endif"
                                aria-haspopup="true" data-menu-toggle="hover">
                                <a href="javascript:;" class="menu-link menu-toggle">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M7,3 L17,3 C19.209139,3 21,4.790861 21,7 C21,9.209139 19.209139,11 17,11 L7,11 C4.790861,11 3,9.209139 3,7 C3,4.790861 4.790861,3 7,3 Z M7,9 C8.1045695,9 9,8.1045695 9,7 C9,5.8954305 8.1045695,5 7,5 C5.8954305,5 5,5.8954305 5,7 C5,8.1045695 5.8954305,9 7,9 Z" fill="#000000"/>
                                                <path d="M7,13 L17,13 C19.209139,13 21,14.790861 21,17 C21,19.209139 19.209139,21 17,21 L7,21 C4.790861,21 3,19.209139 3,17 C3,14.790861 4.790861,13 7,13 Z M17,19 C18.1045695,19 19,18.1045695 19,17 C19,15.8954305 18.1045695,15 17,15 C15.8954305,15 15,15.8954305 15,17 C15,18.1045695 15.8954305,19 17,19 Z" fill="#000000" opacity="0.3"/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Settings</span>
                                    <i class="menu-arrow"></i>
                                </a>
                                <div class="menu-submenu">
                                    <i class="menu-arrow"></i>
                                    <ul class="menu-subnav">
                                        <li class="menu-item @if(request()->is('setting/basic*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('setting.basic') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Basic Settings</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('roi-settings') || request()->is('roi-settings/update*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.roi-settings.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">ROI Settings</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('roi-settings/user-plans*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.roi-settings.user-plans') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">User Plans (Gold/Silver)</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('roi-settings/commission-bonuses*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.roi-settings.commission-bonuses') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Commission &amp; Bonuses</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('roi-settings/profit-share-settings*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.roi-settings.profit-share-settings') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Profit Share Settings</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('reward-settings*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.reward-settings.index') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Reward Settings</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('setting/saving-account*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('setting.saving') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Saving Account Settings</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('saving-accounts/commission-config*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.saving.commission-config') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Instalment Commission Rates</span></a>
                                        </li>
                                        <li class="menu-item @if(request()->is('admin/digital-cards/settings*')) menu-item-active @endif" aria-haspopup="true">
                                            <a href="{{ route('admin.cards.settings') }}" class="menu-link"><i class="menu-bullet menu-bullet-dot"><span></span></i><span class="menu-text">Card Settings</span></a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endif {{-- $isAdmin --}}

                            {{-- ═════════════════════════ LOGOUT ═════════════════════════ --}}
                            <li class="menu-section">
                                <h4 class="menu-text">Session</h4>
                                <i class="menu-icon ki ki-bold-more-hor icon-md"></i>
                            </li>
                            <li class="menu-item" aria-haspopup="true">
                                <a href="#" class="menu-link" onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
                                    <span class="svg-icon menu-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24"/>
                                                <path d="M14.0069431,7.00607258 C13.4546584,7.00607258 13.0069431,6.55855153 13.0069431,6.00650634 C13.0069431,5.45446114 13.4546584,5.00694009 14.0069431,5.00694009 L15.0069431,5.00694009 C17.2160821,5.00694009 19.0069431,6.7970243 19.0069431,9.00520507 L19.0069431,15.001735 C19.0069431,17.2099158 17.2160821,19 15.0069431,19 L3.00694311,19 C0.797804106,19 -0.993056895,17.2099158 -0.993056895,15.001735 L-0.993056895,8.99826498 C-0.993056895,6.7900842 0.797804106,5 3.00694311,5 L4.00694793,5 C4.55923268,5 5.00694793,5.44752105 5.00694793,5.99956624 C5.00694793,6.55161144 4.55923268,6.99913249 4.00694793,6.99913249 L3.00694311,6.99913249 C1.90237361,6.99913249 1.00694311,7.89417459 1.00694311,8.99826498 L1.00694311,15.001735 C1.00694311,16.1058254 1.90237361,17.0008675 3.00694311,17.0008675 L15.0069431,17.0008675 C16.1115126,17.0008675 17.0069431,16.1058254 17.0069431,15.001735 L17.0069431,9.00520507 C17.0069431,7.90111468 16.1115126,7.00607258 15.0069431,7.00607258 L14.0069431,7.00607258 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(9.006943, 12.000000) scale(-1, 1) rotate(-90.000000) translate(-9.006943, -12.000000) "/>
                                                <rect fill="#000000" opacity="0.3" transform="translate(14.000000, 12.000000) rotate(-270.000000) translate(-14.000000, -12.000000) " x="13" y="6" width="2" height="12" rx="1"/>
                                                <path d="M21.7928932,9.79289322 C22.1834175,9.40236893 22.8165825,9.40236893 23.2071068,9.79289322 C23.5976311,10.1834175 23.5976311,10.8165825 23.2071068,11.2071068 L20.2071068,14.2071068 C19.8165825,14.5976311 19.1834175,14.5976311 18.7928932,14.2071068 L15.7928932,11.2071068 C15.4023689,10.8165825 15.4023689,10.1834175 15.7928932,9.79289322 C16.1834175,9.40236893 16.8165825,9.40236893 17.2071068,9.79289322 L19.5,12.0857864 L21.7928932,9.79289322 Z" fill="#000000" fill-rule="nonzero" transform="translate(19.500000, 12.000000) rotate(-90.000000) translate(-19.500000, -12.000000) "/>
                                            </g>
                                        </svg>
                                    </span>
                                    <span class="menu-text">Logout</span>
                                </a>
                                <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                        <!--end::Menu Nav-->
                    </div>
                    <!--end::Menu Container-->
                </div>
                <!--end::Aside Menu-->
            </div>
            <!--end::Aside-->
            <!--begin::Wrapper-->
            <div class="d-flex flex-column flex-row-fluid wrapper" id="kt_wrapper">
                <!--begin::Header-->
                <div id="kt_header" class="header header-fixed">
                    <!--begin::Container-->
                    <div class="container-fluid d-flex align-items-stretch justify-content-between">
                        <!--begin::Header Menu Wrapper-->
                        <div class="header-menu-wrapper header-menu-wrapper-left" id="kt_header_menu_wrapper">
                            <!--begin::Header Menu-->
                            <div id="kt_header_menu"
                                class="pt-5 header-menu header-menu-mobile header-menu-layout-default">

                                <div class="flex-wrap mb-4 d-flex align-items-center" style="gap:0.5rem;">
                                    <!-- Hidden links for JS reference -->
                                    {{-- Staff accounts (office admin / operator) have no referral link --}}
                                    @if(Auth::user()->reflink && !$isOperatorOnly)
                                    <span id="refLink"
                                        style="display: none;"> {{ url('/register/ref/' . Auth::user()->reflink->link) }}</span>
                                    @if(Auth::user()->canSeeSavingLink())
                                    <span id="refLinkSaving"
                                        style="display: none;"> {{ url('/register/ref/' . Auth::user()->reflink->link . '?type=saving') }}</span>
                                    @endif

                                    <!-- Standard referral link -->
                                    <button onclick="copyToClipboard()" class="btn btn-sm rounded-0 btn-info">
                                        Copy Referral Link
                                    </button>
                                    @endif

                                    @if(Auth::user()->reflink && !$isOperatorOnly && Auth::user()->canSeeSavingLink())
                                    <!-- Savings Program referral link -->
                                    <button onclick="copySavingLinkToClipboard()" class="btn btn-sm rounded-0 btn-success">
                                        Copy Savings Program Link
                                    </button>
                                    @endif

                                    <!-- Toast notification -->
                                    <div id="toastMessage"
                                        style="display: none; position: fixed; bottom: 20px; right: 20px; background-color: #333; color: #fff; padding: 10px; border-radius: 5px;">
                                        Link Copied!
                                    </div>
                                </div>




                            </div>
                            <!--end::Header Menu-->
                        </div>
                        <!--end::Header Menu Wrapper-->
                        <!--begin::Topbar-->
                        <div class="topbar">
                            <!--begin::Search-->
                            <div class="dropdown" id="kt_quick_search_toggle">
                                <!--begin::Toggle-->
                                <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                                    <div class="mr-1 btn btn-icon btn-clean btn-lg btn-dropdown">

                                    </div>
                                </div>
                                <!--end::Toggle-->
                                <!--begin::Dropdown-->

                                <!--end::Dropdown-->
                            </div>
                            <!--end::Search-->
                            <!--begin::Notifications-->
                            <div class="dropdown">
                                <!--begin::Toggle-->
                                <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                                    <div class="mr-1 btn btn-icon btn-clean btn-dropdown btn-lg pulse pulse-primary">
                                        <span class="svg-icon svg-icon-xl svg-icon-primary">
                                            <!--begin::Svg Icon | path:assets/media/svg/icons/Code/Compiling.svg-->
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                                height="24px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none"
                                                    fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24" />
                                                    <path
                                                        d="M2.56066017,10.6819805 L4.68198052,8.56066017 C5.26776695,7.97487373 6.21751442,7.97487373 6.80330086,8.56066017 L8.9246212,10.6819805 C9.51040764,11.267767 9.51040764,12.2175144 8.9246212,12.8033009 L6.80330086,14.9246212 C6.21751442,15.5104076 5.26776695,15.5104076 4.68198052,14.9246212 L2.56066017,12.8033009 C1.97487373,12.2175144 1.97487373,11.267767 2.56066017,10.6819805 Z M14.5606602,10.6819805 L16.6819805,8.56066017 C17.267767,7.97487373 18.2175144,7.97487373 18.8033009,8.56066017 L20.9246212,10.6819805 C21.5104076,11.267767 21.5104076,12.2175144 20.9246212,12.8033009 L18.8033009,14.9246212 C18.2175144,15.5104076 17.267767,15.5104076 16.6819805,14.9246212 L14.5606602,12.8033009 C13.9748737,12.2175144 13.9748737,11.267767 14.5606602,10.6819805 Z"
                                                        fill="#000000" opacity="0.3" />
                                                    <path
                                                        d="M8.56066017,16.6819805 L10.6819805,14.5606602 C11.267767,13.9748737 12.2175144,13.9748737 12.8033009,14.5606602 L14.9246212,16.6819805 C15.5104076,17.267767 15.5104076,18.2175144 14.9246212,18.8033009 L12.8033009,20.9246212 C12.2175144,21.5104076 11.267767,21.5104076 10.6819805,20.9246212 L8.56066017,18.8033009 C7.97487373,18.2175144 7.97487373,17.267767 8.56066017,16.6819805 Z M8.56066017,4.68198052 L10.6819805,2.56066017 C11.267767,1.97487373 12.2175144,1.97487373 12.8033009,2.56066017 L14.9246212,4.68198052 C15.5104076,5.26776695 15.5104076,6.21751442 14.9246212,6.80330086 L12.8033009,8.9246212 C12.2175144,9.51040764 11.267767,9.51040764 10.6819805,8.9246212 L8.56066017,6.80330086 C7.97487373,6.21751442 7.97487373,5.26776695 8.56066017,4.68198052 Z"
                                                        fill="#000000" />
                                                </g>
                                            </svg>
                                            <!--end::Svg Icon-->
                                        </span>
                                        <span class="pulse-ring"></span>
                                    </div>
                                </div>
                                <!--end::Toggle-->
                                <!--begin::Dropdown-->
                                <div
                                    class="p-0 m-0 dropdown-menu dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg">
                                    <form>
                                        <!--begin::Header-->
                                        <div class="pt-12 d-flex flex-column bgi-size-cover bgi-no-repeat rounded-top"
                                            style="background-image: url(assets/media/misc/bg-1.jpg)">
                                            <!--begin::Title-->
                                            <h4 class="d-flex flex-center rounded-top">
                                                <span class="text-white">User Notifications</span>
                                                <span
                                                    class="ml-2 btn btn-text btn-success btn-sm font-weight-bold btn-font-md">23
                                                    new</span>
                                            </h4>
                                            <!--end::Title-->
                                            <!--begin::Tabs-->
                                            <ul class="px-8 mt-3 nav nav-bold nav-tabs nav-tabs-line nav-tabs-line-3x nav-tabs-line-transparent-white nav-tabs-line-active-border-success"
                                                role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active show" data-toggle="tab"
                                                        href="#topbar_notifications_notifications">Alerts</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-toggle="tab"
                                                        href="#topbar_notifications_events">Events</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-toggle="tab"
                                                        href="#topbar_notifications_logs">Logs</a>
                                                </li>
                                            </ul>
                                            <!--end::Tabs-->
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Content-->
                                        <div class="tab-content">
                                            <!--begin::Tabpane-->
                                            <div class="p-8 tab-pane active show"
                                                id="topbar_notifications_notifications" role="tabpanel">
                                                <!--begin::Scroll-->
                                                <div class="scroll pr-7 mr-n7" data-scroll="true" data-height="300"
                                                    data-mobile-height="200">
                                                    <!--begin::Item-->
                                                    <div class="mb-6 d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="mr-5 symbol symbol-40 symbol-light-primary">
                                                            <span class="symbol-label">
                                                                <span class="svg-icon svg-icon-lg svg-icon-primary">
                                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        width="24px" height="24px"
                                                                        viewBox="0 0 24 24" version="1.1">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <rect x="0" y="0" width="24"
                                                                                height="24" />
                                                                            <path
                                                                                d="M5,3 L6,3 C6.55228475,3 7,3.44771525 7,4 L7,20 C7,20.5522847 6.55228475,21 6,21 L5,21 C4.44771525,21 4,20.5522847 4,20 L4,4 C4,3.44771525 4.44771525,3 5,3 Z M10,3 L11,3 C11.5522847,3 12,3.44771525 12,4 L12,20 C12,20.5522847 11.5522847,21 11,21 L10,21 C9.44771525,21 9,20.5522847 9,20 L9,4 C9,3.44771525 9.44771525,3 10,3 Z"
                                                                                fill="#000000" />
                                                                            <rect fill="#000000" opacity="0.3"
                                                                                transform="translate(17.825568, 11.945519) rotate(-19.000000) translate(-17.825568, -11.945519)"
                                                                                x="16.3255682" y="2.94551858"
                                                                                width="3" height="18"
                                                                                rx="1" />
                                                                        </g>
                                                                    </svg>
                                                                    <!--end::Svg Icon-->
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <!--end::Symbol-->
                                                        <!--begin::Text-->
                                                        <div class="d-flex flex-column font-weight-bold">
                                                            <a href="#"
                                                                class="mb-1 text-dark text-hover-primary font-size-lg">Cool
                                                                App</a>
                                                            <span class="text-muted">Marketing campaign planning</span>
                                                        </div>
                                                        <!--end::Text-->
                                                    </div>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <div class="mb-6 d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="mr-5 symbol symbol-40 symbol-light-warning">
                                                            <span class="symbol-label">
                                                                <span class="svg-icon svg-icon-lg svg-icon-warning">
                                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Write.svg-->
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        width="24px" height="24px"
                                                                        viewBox="0 0 24 24" version="1.1">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <rect x="0" y="0" width="24"
                                                                                height="24" />
                                                                            <path
                                                                                d="M12.2674799,18.2323597 L12.0084872,5.45852451 C12.0004303,5.06114792 12.1504154,4.6768183 12.4255037,4.38993949 L15.0030167,1.70195304 L17.5910752,4.40093695 C17.8599071,4.6812911 18.0095067,5.05499603 18.0083938,5.44341307 L17.9718262,18.2062508 C17.9694575,19.0329966 17.2985816,19.701953 16.4718324,19.701953 L13.7671717,19.701953 C12.9505952,19.701953 12.2840328,19.0487684 12.2674799,18.2323597 Z"
                                                                                fill="#000000" fill-rule="nonzero"
                                                                                transform="translate(14.701953, 10.701953) rotate(-135.000000) translate(-14.701953, -10.701953)" />
                                                                            <path
                                                                                d="M12.9,2 C13.4522847,2 13.9,2.44771525 13.9,3 C13.9,3.55228475 13.4522847,4 12.9,4 L6,4 C4.8954305,4 4,4.8954305 4,6 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,13 C20,12.4477153 20.4477153,12 21,12 C21.5522847,12 22,12.4477153 22,13 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,6 C2,3.790861 3.790861,2 6,2 L12.9,2 Z"
                                                                                fill="#000000" fill-rule="nonzero"
                                                                                opacity="0.3" />
                                                                        </g>
                                                                    </svg>
                                                                    <!--end::Svg Icon-->
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <!--end::Symbol-->
                                                        <!--begin::Text-->
                                                        <div class="d-flex flex-column font-weight-bold">
                                                            <a href="#"
                                                                class="mb-1 text-dark-75 text-hover-primary font-size-lg">Awesome
                                                                SAAS</a>
                                                            <span class="text-muted">Project status update
                                                                meeting</span>
                                                        </div>
                                                        <!--end::Text-->
                                                    </div>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <div class="mb-6 d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="mr-5 symbol symbol-40 symbol-light-success">
                                                            <span class="symbol-label">
                                                                <span class="svg-icon svg-icon-lg svg-icon-success">
                                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Group-chat.svg-->
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        width="24px" height="24px"
                                                                        viewBox="0 0 24 24" version="1.1">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <rect x="0" y="0" width="24"
                                                                                height="24" />
                                                                            <path
                                                                                d="M16,15.6315789 L16,12 C16,10.3431458 14.6568542,9 13,9 L6.16183229,9 L6.16183229,5.52631579 C6.16183229,4.13107011 7.29290239,3 8.68814808,3 L20.4776218,3 C21.8728674,3 23.0039375,4.13107011 23.0039375,5.52631579 L23.0039375,13.1052632 L23.0206157,17.786793 C23.0215995,18.0629336 22.7985408,18.2875874 22.5224001,18.2885711 C22.3891754,18.2890457 22.2612702,18.2363324 22.1670655,18.1421277 L19.6565168,15.6315789 L16,15.6315789 Z"
                                                                                fill="#000000" />
                                                                            <path
                                                                                d="M1.98505595,18 L1.98505595,13 C1.98505595,11.8954305 2.88048645,11 3.98505595,11 L11.9850559,11 C13.0896254,11 13.9850559,11.8954305 13.9850559,13 L13.9850559,18 C13.9850559,19.1045695 13.0896254,20 11.9850559,20 L4.10078614,20 L2.85693427,21.1905292 C2.65744295,21.3814685 2.34093638,21.3745358 2.14999706,21.1750444 C2.06092565,21.0819836 2.01120804,20.958136 2.01120804,20.8293182 L2.01120804,18.32426 C1.99400175,18.2187196 1.98505595,18.1104045 1.98505595,18 Z M6.5,14 C6.22385763,14 6,14.2238576 6,14.5 C6,14.7761424 6.22385763,15 6.5,15 L11.5,15 C11.7761424,15 12,14.7761424 12,14.5 C12,14.2238576 11.7761424,14 11.5,14 L6.5,14 Z M9.5,16 C9.22385763,16 9,16.2238576 9,16.5 C9,16.7761424 9.22385763,17 9.5,17 L11.5,17 C11.7761424,17 12,16.7761424 12,16.5 C12,16.2238576 11.7761424,16 11.5,16 L9.5,16 Z"
                                                                                fill="#000000" opacity="0.3" />
                                                                        </g>
                                                                    </svg>
                                                                    <!--end::Svg Icon-->
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <!--end::Symbol-->
                                                        <!--begin::Text-->
                                                        <div class="d-flex flex-column font-weight-bold">
                                                            <a href="#"
                                                                class="mb-1 text-dark text-hover-primary font-size-lg">Claudy
                                                                Sys</a>
                                                            <span class="text-muted">Project Deployment &amp;
                                                                Launch</span>
                                                        </div>
                                                        <!--end::Text-->
                                                    </div>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <div class="mb-6 d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="mr-5 symbol symbol-40 symbol-light-danger">
                                                            <span class="symbol-label">
                                                                <span class="svg-icon svg-icon-lg svg-icon-danger">
                                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/General/Attachment2.svg-->
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        width="24px" height="24px"
                                                                        viewBox="0 0 24 24" version="1.1">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <rect x="0" y="0" width="24"
                                                                                height="24" />
                                                                            <path
                                                                                d="M11.7573593,15.2426407 L8.75735931,15.2426407 C8.20507456,15.2426407 7.75735931,15.6903559 7.75735931,16.2426407 C7.75735931,16.7949254 8.20507456,17.2426407 8.75735931,17.2426407 L11.7573593,17.2426407 L11.7573593,18.2426407 C11.7573593,19.3472102 10.8619288,20.2426407 9.75735931,20.2426407 L5.75735931,20.2426407 C4.65278981,20.2426407 3.75735931,19.3472102 3.75735931,18.2426407 L3.75735931,14.2426407 C3.75735931,13.1380712 4.65278981,12.2426407 5.75735931,12.2426407 L9.75735931,12.2426407 C10.8619288,12.2426407 11.7573593,13.1380712 11.7573593,14.2426407 L11.7573593,15.2426407 Z"
                                                                                fill="#000000" opacity="0.3"
                                                                                transform="translate(7.757359, 16.242641) rotate(-45.000000) translate(-7.757359, -16.242641)" />
                                                                            <path
                                                                                d="M12.2426407,8.75735931 L15.2426407,8.75735931 C15.7949254,8.75735931 16.2426407,8.30964406 16.2426407,7.75735931 C16.2426407,7.20507456 15.7949254,6.75735931 15.2426407,6.75735931 L12.2426407,6.75735931 L12.2426407,5.75735931 C12.2426407,4.65278981 13.1380712,3.75735931 14.2426407,3.75735931 L18.2426407,3.75735931 C19.3472102,3.75735931 20.2426407,4.65278981 20.2426407,5.75735931 L20.2426407,9.75735931 C20.2426407,10.8619288 19.3472102,11.7573593 18.2426407,11.7573593 L14.2426407,11.7573593 C13.1380712,11.7573593 12.2426407,10.8619288 12.2426407,9.75735931 L12.2426407,8.75735931 Z"
                                                                                fill="#000000"
                                                                                transform="translate(16.242641, 7.757359) rotate(-45.000000) translate(-16.242641, -7.757359)" />
                                                                            <path
                                                                                d="M5.89339828,3.42893219 C6.44568303,3.42893219 6.89339828,3.87664744 6.89339828,4.42893219 L6.89339828,6.42893219 C6.89339828,6.98121694 6.44568303,7.42893219 5.89339828,7.42893219 C5.34111353,7.42893219 4.89339828,6.98121694 4.89339828,6.42893219 L4.89339828,4.42893219 C4.89339828,3.87664744 5.34111353,3.42893219 5.89339828,3.42893219 Z M11.4289322,5.13603897 C11.8194565,5.52656326 11.8194565,6.15972824 11.4289322,6.55025253 L10.0147186,7.96446609 C9.62419433,8.35499039 8.99102936,8.35499039 8.60050506,7.96446609 C8.20998077,7.5739418 8.20998077,6.94077682 8.60050506,6.55025253 L10.0147186,5.13603897 C10.4052429,4.74551468 11.0384079,4.74551468 11.4289322,5.13603897 Z M0.600505063,5.13603897 C0.991029355,4.74551468 1.62419433,4.74551468 2.01471863,5.13603897 L3.42893219,6.55025253 C3.81945648,6.94077682 3.81945648,7.5739418 3.42893219,7.96446609 C3.0384079,8.35499039 2.40524292,8.35499039 2.01471863,7.96446609 L0.600505063,6.55025253 C0.209980772,6.15972824 0.209980772,5.52656326 0.600505063,5.13603897 Z"
                                                                                fill="#000000" opacity="0.3"
                                                                                transform="translate(6.014719, 5.843146) rotate(-45.000000) translate(-6.014719, -5.843146)" />
                                                                            <path
                                                                                d="M17.9142136,15.4497475 C18.4664983,15.4497475 18.9142136,15.8974627 18.9142136,16.4497475 L18.9142136,18.4497475 C18.9142136,19.0020322 18.4664983,19.4497475 17.9142136,19.4497475 C17.3619288,19.4497475 16.9142136,19.0020322 16.9142136,18.4497475 L16.9142136,16.4497475 C16.9142136,15.8974627 17.3619288,15.4497475 17.9142136,15.4497475 Z M23.4497475,17.1568542 C23.8402718,17.5473785 23.8402718,18.1805435 23.4497475,18.5710678 L22.0355339,19.9852814 C21.6450096,20.3758057 21.0118446,20.3758057 20.6213203,19.9852814 C20.2307961,19.5947571 20.2307961,18.9615921 20.6213203,18.5710678 L22.0355339,17.1568542 C22.4260582,16.76633 23.0592232,16.76633 23.4497475,17.1568542 Z M12.6213203,17.1568542 C13.0118446,16.76633 13.6450096,16.76633 14.0355339,17.1568542 L15.4497475,18.5710678 C15.8402718,18.9615921 15.8402718,19.5947571 15.4497475,19.9852814 C15.0592232,20.3758057 14.4260582,20.3758057 14.0355339,19.9852814 L12.6213203,18.5710678 C12.2307961,18.1805435 12.2307961,17.5473785 12.6213203,17.1568542 Z"
                                                                                fill="#000000" opacity="0.3"
                                                                                transform="translate(18.035534, 17.863961) scale(1, -1) rotate(45.000000) translate(-18.035534, -17.863961)" />
                                                                        </g>
                                                                    </svg>
                                                                    <!--end::Svg Icon-->
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <!--end::Symbol-->
                                                        <!--begin::Text-->
                                                        <div class="d-flex flex-column font-weight-bold">
                                                            <a href="#"
                                                                class="mb-1 text-dark text-hover-primary font-size-lg">Trilo
                                                                Service</a>
                                                            <span class="text-muted">Analytics &amp; Requirement
                                                                Study</span>
                                                        </div>
                                                        <!--end::Text-->
                                                    </div>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <div class="mb-6 d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="mr-5 symbol symbol-40 symbol-light-info">
                                                            <span class="symbol-label">
                                                                <span class="svg-icon svg-icon-lg svg-icon-info">
                                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Shield-user.svg-->
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        width="24px" height="24px"
                                                                        viewBox="0 0 24 24" version="1.1">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <rect x="0" y="0" width="24"
                                                                                height="24" />
                                                                            <path
                                                                                d="M4,4 L11.6314229,2.5691082 C11.8750185,2.52343403 12.1249815,2.52343403 12.3685771,2.5691082 L20,4 L20,13.2830094 C20,16.2173861 18.4883464,18.9447835 16,20.5 L12.5299989,22.6687507 C12.2057287,22.8714196 11.7942713,22.8714196 11.4700011,22.6687507 L8,20.5 C5.51165358,18.9447835 4,16.2173861 4,13.2830094 L4,4 Z"
                                                                                fill="#000000" opacity="0.3" />
                                                                            <path
                                                                                d="M12,11 C10.8954305,11 10,10.1045695 10,9 C10,7.8954305 10.8954305,7 12,7 C13.1045695,7 14,7.8954305 14,9 C14,10.1045695 13.1045695,11 12,11 Z"
                                                                                fill="#000000" opacity="0.3" />
                                                                            <path
                                                                                d="M7.00036205,16.4995035 C7.21569918,13.5165724 9.36772908,12 11.9907452,12 C14.6506758,12 16.8360465,13.4332455 16.9988413,16.5 C17.0053266,16.6221713 16.9988413,17 16.5815,17 C14.5228466,17 11.463736,17 7.4041679,17 C7.26484009,17 6.98863236,16.6619875 7.00036205,16.4995035 Z"
                                                                                fill="#000000" opacity="0.3" />
                                                                        </g>
                                                                    </svg>
                                                                    <!--end::Svg Icon-->
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <!--end::Symbol-->
                                                        <!--begin::Text-->
                                                        <div class="d-flex flex-column font-weight-bold">
                                                            <a href="#"
                                                                class="mb-1 text-dark text-hover-primary font-size-lg">Bravia
                                                                SAAS</a>
                                                            <span class="text-muted">Reporting Application</span>
                                                        </div>
                                                        <!--end::Text-->
                                                    </div>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <div class="mb-6 d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="mr-5 symbol symbol-40 symbol-light-danger">
                                                            <span class="symbol-label">
                                                                <span class="svg-icon svg-icon-lg svg-icon-danger">
                                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Mail-notification.svg-->
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        width="24px" height="24px"
                                                                        viewBox="0 0 24 24" version="1.1">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <rect x="0" y="0" width="24"
                                                                                height="24" />
                                                                            <path
                                                                                d="M21,12.0829584 C20.6747915,12.0283988 20.3407122,12 20,12 C16.6862915,12 14,14.6862915 14,18 C14,18.3407122 14.0283988,18.6747915 14.0829584,19 L5,19 C3.8954305,19 3,18.1045695 3,17 L3,8 C3,6.8954305 3.8954305,6 5,6 L19,6 C20.1045695,6 21,6.8954305 21,8 L21,12.0829584 Z M18.1444251,7.83964668 L12,11.1481833 L5.85557487,7.83964668 C5.4908718,7.6432681 5.03602525,7.77972206 4.83964668,8.14442513 C4.6432681,8.5091282 4.77972206,8.96397475 5.14442513,9.16035332 L11.6444251,12.6603533 C11.8664074,12.7798822 12.1335926,12.7798822 12.3555749,12.6603533 L18.8555749,9.16035332 C19.2202779,8.96397475 19.3567319,8.5091282 19.1603533,8.14442513 C18.9639747,7.77972206 18.5091282,7.6432681 18.1444251,7.83964668 Z"
                                                                                fill="#000000" />
                                                                            <circle fill="#000000" opacity="0.3"
                                                                                cx="19.5" cy="17.5"
                                                                                r="2.5" />
                                                                        </g>
                                                                    </svg>
                                                                    <!--end::Svg Icon-->
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <!--end::Symbol-->
                                                        <!--begin::Text-->
                                                        <div class="d-flex flex-column font-weight-bold">
                                                            <a href="#"
                                                                class="mb-1 text-dark text-hover-primary font-size-lg">Express
                                                                Wind</a>
                                                            <span class="text-muted">Software Analytics &amp;
                                                                Design</span>
                                                        </div>
                                                        <!--end::Text-->
                                                    </div>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <div class="mb-6 d-flex align-items-center">
                                                        <!--begin::Symbol-->
                                                        <div class="mr-5 symbol symbol-40 symbol-light-success">
                                                            <span class="symbol-label">
                                                                <span class="svg-icon svg-icon-lg svg-icon-success">
                                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Design/Bucket.svg-->
                                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                                                        width="24px" height="24px"
                                                                        viewBox="0 0 24 24" version="1.1">
                                                                        <g stroke="none" stroke-width="1"
                                                                            fill="none" fill-rule="evenodd">
                                                                            <rect x="0" y="0" width="24"
                                                                                height="24" />
                                                                            <path
                                                                                d="M5,5 L5,15 C5,15.5948613 5.25970314,16.1290656 5.6719139,16.4954176 C5.71978107,16.5379595 5.76682388,16.5788906 5.81365532,16.6178662 C5.82524933,16.6294602 15,7.45470952 15,7.45470952 C15,6.9962515 15,6.17801499 15,5 L5,5 Z M5,3 L15,3 C16.1045695,3 17,3.8954305 17,5 L17,15 C17,17.209139 15.209139,19 13,19 L7,19 C4.790861,19 3,17.209139 3,15 L3,5 C3,3.8954305 3.8954305,3 5,3 Z"
                                                                                fill="#000000" fill-rule="nonzero"
                                                                                transform="translate(10.000000, 11.000000) rotate(-315.000000) translate(-10.000000, -11.000000)" />
                                                                            <path
                                                                                d="M20,22 C21.6568542,22 23,20.6568542 23,19 C23,17.8954305 22,16.2287638 20,14 C18,16.2287638 17,17.8954305 17,19 C17,20.6568542 18.3431458,22 20,22 Z"
                                                                                fill="#000000" opacity="0.3" />
                                                                        </g>
                                                                    </svg>
                                                                    <!--end::Svg Icon-->
                                                                </span>
                                                            </span>
                                                        </div>
                                                        <!--end::Symbol-->
                                                        <!--begin::Text-->
                                                        <div class="d-flex flex-column font-weight-bold">
                                                            <a href="#"
                                                                class="mb-1 text-dark text-hover-primary font-size-lg">Bruk
                                                                Fitness</a>
                                                            <span class="text-muted">Web Design &amp;
                                                                Development</span>
                                                        </div>
                                                        <!--end::Text-->
                                                    </div>
                                                    <!--end::Item-->
                                                </div>
                                                <!--end::Scroll-->
                                                <!--begin::Action-->
                                                <div class="d-flex flex-center pt-7">
                                                    <a href="#"
                                                        class="text-center btn btn-light-primary font-weight-bold">See
                                                        All</a>
                                                </div>
                                                <!--end::Action-->
                                            </div>
                                            <!--end::Tabpane-->
                                            <!--begin::Tabpane-->
                                            <div class="tab-pane" id="topbar_notifications_events" role="tabpanel">
                                                <!--begin::Nav-->
                                                <div class="my-4 navi navi-hover scroll" data-scroll="true"
                                                    data-height="300" data-mobile-height="200">
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-line-chart text-success"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">New report has been
                                                                    received</div>
                                                                <div class="text-muted">23 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-paper-plane text-danger"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">Finance report has been
                                                                    generated</div>
                                                                <div class="text-muted">25 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i
                                                                    class="flaticon2-user flaticon2-line- text-success"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">New order has been
                                                                    received</div>
                                                                <div class="text-muted">2 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-pin text-primary"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">New customer is
                                                                    registered</div>
                                                                <div class="text-muted">3 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-sms text-danger"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">Application has been
                                                                    approved</div>
                                                                <div class="text-muted">3 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-pie-chart-3 text-warning"></i>
                                                            </div>
                                                            <div class="navinavinavi-text">
                                                                <div class="font-weight-bold">New file has been
                                                                    uploaded</div>
                                                                <div class="text-muted">5 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon-pie-chart-1 text-info"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">New user feedback
                                                                    received</div>
                                                                <div class="text-muted">8 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-settings text-success"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">System reboot has been
                                                                    successfully completed</div>
                                                                <div class="text-muted">12 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i
                                                                    class="flaticon-safe-shield-protection text-primary"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">New order has been placed
                                                                </div>
                                                                <div class="text-muted">15 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-notification text-primary"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">Company meeting canceled
                                                                </div>
                                                                <div class="text-muted">19 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-fax text-success"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">New report has been
                                                                    received</div>
                                                                <div class="text-muted">23 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon-download-1 text-danger"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">Finance report has been
                                                                    generated</div>
                                                                <div class="text-muted">25 hrs ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon-security text-warning"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">New customer comment
                                                                    recieved</div>
                                                                <div class="text-muted">2 days ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                    <!--begin::Item-->
                                                    <a href="#" class="navi-item">
                                                        <div class="navi-link">
                                                            <div class="mr-2 navi-icon">
                                                                <i class="flaticon2-analytics-1 text-success"></i>
                                                            </div>
                                                            <div class="navi-text">
                                                                <div class="font-weight-bold">New customer is
                                                                    registered</div>
                                                                <div class="text-muted">3 days ago</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                    <!--end::Item-->
                                                </div>
                                                <!--end::Nav-->
                                            </div>
                                            <!--end::Tabpane-->
                                            <!--begin::Tabpane-->
                                            <div class="tab-pane" id="topbar_notifications_logs" role="tabpanel">
                                                <!--begin::Nav-->
                                                <div class="text-center d-flex flex-center text-muted min-h-200px">All
                                                    caught up!
                                                    <br />No new notifications.
                                                </div>
                                                <!--end::Nav-->
                                            </div>
                                            <!--end::Tabpane-->
                                        </div>
                                        <!--end::Content-->
                                    </form>
                                </div>
                                <!--end::Dropdown-->
                            </div>
                            <!--end::Notifications-->
                            <!--begin::Quick Actions-->
                            <div class="dropdown">
                                <!--begin::Toggle-->
                                <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                                    <div class="mr-1 btn btn-icon btn-clean btn-dropdown btn-lg">
                                        <span class="svg-icon svg-icon-xl svg-icon-primary">
                                            <!--begin::Svg Icon | path:assets/media/svg/icons/Media/Equalizer.svg-->
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                                height="24px" viewBox="0 0 24 24" version="1.1">
                                                <g stroke="none" stroke-width="1" fill="none"
                                                    fill-rule="evenodd">
                                                    <rect x="0" y="0" width="24" height="24" />
                                                    <rect fill="#000000" opacity="0.3" x="13" y="4"
                                                        width="3" height="16" rx="1.5" />
                                                    <rect fill="#000000" x="8" y="9" width="3" height="11"
                                                        rx="1.5" />
                                                    <rect fill="#000000" x="18" y="11" width="3"
                                                        height="9" rx="1.5" />
                                                    <rect fill="#000000" x="3" y="13" width="3"
                                                        height="7" rx="1.5" />
                                                </g>
                                            </svg>
                                            <!--end::Svg Icon-->
                                        </span>
                                    </div>
                                </div>
                                <!--end::Toggle-->
                                <!--begin::Dropdown-->
                                <div
                                    class="p-0 m-0 dropdown-menu dropdown-menu-right dropdown-menu-anim-up dropdown-menu-lg">
                                    <!--begin:Header-->
                                    <div class="py-10 d-flex flex-column flex-center bgi-size-cover bgi-no-repeat rounded-top"
                                        style="background-image: url(assets/media/misc/bg-1.jpg)">
                                        <h4 class="text-white font-weight-bold">Quick Actions</h4>
                                        <span class="mt-2 btn btn-success btn-sm font-weight-bold font-size-sm">23
                                            tasks pending</span>
                                    </div>
                                    <!--end:Header-->
                                    <!--begin:Nav-->
                                    <div class="row row-paddingless">
                                        <!--begin:Item-->
                                        <div class="col-6">
                                            <a href="#"
                                                class="px-5 py-10 text-center d-block bg-hover-light border-right border-bottom">
                                                <span class="svg-icon svg-icon-3x svg-icon-success">
                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Shopping/Euro.svg-->
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                                        height="24px" viewBox="0 0 24 24" version="1.1">
                                                        <g stroke="none" stroke-width="1" fill="none"
                                                            fill-rule="evenodd">
                                                            <rect x="0" y="0" width="24" height="24" />
                                                            <path
                                                                d="M4.3618034,10.2763932 L4.8618034,9.2763932 C4.94649941,9.10700119 5.11963097,9 5.30901699,9 L15.190983,9 C15.4671254,9 15.690983,9.22385763 15.690983,9.5 C15.690983,9.57762255 15.6729105,9.65417908 15.6381966,9.7236068 L15.1381966,10.7236068 C15.0535006,10.8929988 14.880369,11 14.690983,11 L4.80901699,11 C4.53287462,11 4.30901699,10.7761424 4.30901699,10.5 C4.30901699,10.4223775 4.32708954,10.3458209 4.3618034,10.2763932 Z M14.6381966,13.7236068 L14.1381966,14.7236068 C14.0535006,14.8929988 13.880369,15 13.690983,15 L4.80901699,15 C4.53287462,15 4.30901699,14.7761424 4.30901699,14.5 C4.30901699,14.4223775 4.32708954,14.3458209 4.3618034,14.2763932 L4.8618034,13.2763932 C4.94649941,13.1070012 5.11963097,13 5.30901699,13 L14.190983,13 C14.4671254,13 14.690983,13.2238576 14.690983,13.5 C14.690983,13.5776225 14.6729105,13.6541791 14.6381966,13.7236068 Z"
                                                                fill="#000000" opacity="0.3" />
                                                            <path
                                                                d="M17.369,7.618 C16.976998,7.08599734 16.4660031,6.69750122 15.836,6.4525 C15.2059968,6.20749878 14.590003,6.085 13.988,6.085 C13.2179962,6.085 12.5180032,6.2249986 11.888,6.505 C11.2579969,6.7850014 10.7155023,7.16999755 10.2605,7.66 C9.80549773,8.15000245 9.45550123,8.72399671 9.2105,9.382 C8.96549878,10.0400033 8.843,10.7539961 8.843,11.524 C8.843,12.3360041 8.96199881,13.0779966 9.2,13.75 C9.43800119,14.4220034 9.7774978,14.9994976 10.2185,15.4825 C10.6595022,15.9655024 11.1879969,16.3399987 11.804,16.606 C12.4200031,16.8720013 13.1129962,17.005 13.883,17.005 C14.681004,17.005 15.3879969,16.8475016 16.004,16.5325 C16.6200031,16.2174984 17.1169981,15.8010026 17.495,15.283 L19.616,16.774 C18.9579967,17.6000041 18.1530048,18.2404977 17.201,18.6955 C16.2489952,19.1505023 15.1360064,19.378 13.862,19.378 C12.6999942,19.378 11.6325049,19.1855019 10.6595,18.8005 C9.68649514,18.4154981 8.8500035,17.8765035 8.15,17.1835 C7.4499965,16.4904965 6.90400196,15.6645048 6.512,14.7055 C6.11999804,13.7464952 5.924,12.6860058 5.924,11.524 C5.924,10.333994 6.13049794,9.25950479 6.5435,8.3005 C6.95650207,7.34149521 7.5234964,6.52600336 8.2445,5.854 C8.96550361,5.18199664 9.8159951,4.66400182 10.796,4.3 C11.7760049,3.93599818 12.8399943,3.754 13.988,3.754 C14.4640024,3.754 14.9609974,3.79949954 15.479,3.8905 C15.9970026,3.98150045 16.4939976,4.12149906 16.97,4.3105 C17.4460024,4.49950095 17.8939979,4.7339986 18.314,5.014 C18.7340021,5.2940014 19.0909985,5.62999804 19.385,6.022 L17.369,7.618 Z"
                                                                fill="#000000" />
                                                        </g>
                                                    </svg>
                                                    <!--end::Svg Icon-->
                                                </span>
                                                <span
                                                    class="mt-2 mb-1 d-block text-dark-75 font-weight-bold font-size-h6">Accounting</span>
                                                <span class="d-block text-dark-50 font-size-lg">eCommerce</span>
                                            </a>
                                        </div>
                                        <!--end:Item-->
                                        <!--begin:Item-->
                                        <div class="col-6">
                                            <a href="#"
                                                class="px-5 py-10 text-center d-block bg-hover-light border-bottom">
                                                <span class="svg-icon svg-icon-3x svg-icon-success">
                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Mail-attachment.svg-->
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                                        height="24px" viewBox="0 0 24 24" version="1.1">
                                                        <g stroke="none" stroke-width="1" fill="none"
                                                            fill-rule="evenodd">
                                                            <rect x="0" y="0" width="24" height="24" />
                                                            <path
                                                                d="M14.8571499,13 C14.9499122,12.7223297 15,12.4263059 15,12.1190476 L15,6.88095238 C15,5.28984632 13.6568542,4 12,4 L11.7272727,4 C10.2210416,4 9,5.17258756 9,6.61904762 L10.0909091,6.61904762 C10.0909091,5.75117158 10.823534,5.04761905 11.7272727,5.04761905 L12,5.04761905 C13.0543618,5.04761905 13.9090909,5.86843034 13.9090909,6.88095238 L13.9090909,12.1190476 C13.9090909,12.4383379 13.8240964,12.7385644 13.6746497,13 L10.3253503,13 C10.1759036,12.7385644 10.0909091,12.4383379 10.0909091,12.1190476 L10.0909091,9.5 C10.0909091,9.06606198 10.4572216,8.71428571 10.9090909,8.71428571 C11.3609602,8.71428571 11.7272727,9.06606198 11.7272727,9.5 L11.7272727,11.3333333 L12.8181818,11.3333333 L12.8181818,9.5 C12.8181818,8.48747796 11.9634527,7.66666667 10.9090909,7.66666667 C9.85472911,7.66666667 9,8.48747796 9,9.5 L9,12.1190476 C9,12.4263059 9.0500878,12.7223297 9.14285008,13 L6,13 C5.44771525,13 5,12.5522847 5,12 L5,3 C5,2.44771525 5.44771525,2 6,2 L18,2 C18.5522847,2 19,2.44771525 19,3 L19,12 C19,12.5522847 18.5522847,13 18,13 L14.8571499,13 Z"
                                                                fill="#000000" opacity="0.3" />
                                                            <path
                                                                d="M9,10.3333333 L9,12.1190476 C9,13.7101537 10.3431458,15 12,15 C13.6568542,15 15,13.7101537 15,12.1190476 L15,10.3333333 L20.2072547,6.57253826 C20.4311176,6.4108595 20.7436609,6.46126971 20.9053396,6.68513259 C20.9668779,6.77033951 21,6.87277228 21,6.97787787 L21,17 C21,18.1045695 20.1045695,19 19,19 L5,19 C3.8954305,19 3,18.1045695 3,17 L3,6.97787787 C3,6.70173549 3.22385763,6.47787787 3.5,6.47787787 C3.60510559,6.47787787 3.70753836,6.51099993 3.79274528,6.57253826 L9,10.3333333 Z M10.0909091,11.1212121 L12,12.5 L13.9090909,11.1212121 L13.9090909,12.1190476 C13.9090909,13.1315697 13.0543618,13.952381 12,13.952381 C10.9456382,13.952381 10.0909091,13.1315697 10.0909091,12.1190476 L10.0909091,11.1212121 Z"
                                                                fill="#000000" />
                                                        </g>
                                                    </svg>
                                                    <!--end::Svg Icon-->
                                                </span>
                                                <span
                                                    class="mt-2 mb-1 d-block text-dark-75 font-weight-bold font-size-h6">Administration</span>
                                                <span class="d-block text-dark-50 font-size-lg">Console</span>
                                            </a>
                                        </div>
                                        <!--end:Item-->
                                        <!--begin:Item-->
                                        <div class="col-6">
                                            <a href="#"
                                                class="px-5 py-10 text-center d-block bg-hover-light border-right">
                                                <span class="svg-icon svg-icon-3x svg-icon-success">
                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Shopping/Box2.svg-->
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                                        height="24px" viewBox="0 0 24 24" version="1.1">
                                                        <g stroke="none" stroke-width="1" fill="none"
                                                            fill-rule="evenodd">
                                                            <rect x="0" y="0" width="24" height="24" />
                                                            <path
                                                                d="M4,9.67471899 L10.880262,13.6470401 C10.9543486,13.689814 11.0320333,13.7207107 11.1111111,13.740321 L11.1111111,21.4444444 L4.49070127,17.526473 C4.18655139,17.3464765 4,17.0193034 4,16.6658832 L4,9.67471899 Z M20,9.56911707 L20,16.6658832 C20,17.0193034 19.8134486,17.3464765 19.5092987,17.526473 L12.8888889,21.4444444 L12.8888889,13.6728275 C12.9050191,13.6647696 12.9210067,13.6561758 12.9368301,13.6470401 L20,9.56911707 Z"
                                                                fill="#000000" />
                                                            <path
                                                                d="M4.21611835,7.74669402 C4.30015839,7.64056877 4.40623188,7.55087574 4.5299008,7.48500698 L11.5299008,3.75665466 C11.8237589,3.60013944 12.1762411,3.60013944 12.4700992,3.75665466 L19.4700992,7.48500698 C19.5654307,7.53578262 19.6503066,7.60071528 19.7226939,7.67641889 L12.0479413,12.1074394 C11.9974761,12.1365754 11.9509488,12.1699127 11.9085461,12.2067543 C11.8661433,12.1699127 11.819616,12.1365754 11.7691509,12.1074394 L4.21611835,7.74669402 Z"
                                                                fill="#000000" opacity="0.3" />
                                                        </g>
                                                    </svg>
                                                    <!--end::Svg Icon-->
                                                </span>
                                                <span
                                                    class="mt-2 mb-1 d-block text-dark-75 font-weight-bold font-size-h6">Projects</span>
                                                <span class="d-block text-dark-50 font-size-lg">Pending Tasks</span>
                                            </a>
                                        </div>
                                        <!--end:Item-->
                                        <!--begin:Item-->
                                        <div class="col-6">
                                            <a href="#" class="px-5 py-10 text-center d-block bg-hover-light">
                                                <span class="svg-icon svg-icon-3x svg-icon-success">
                                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Group.svg-->
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                                        height="24px" viewBox="0 0 24 24" version="1.1">
                                                        <g stroke="none" stroke-width="1" fill="none"
                                                            fill-rule="evenodd">
                                                            <polygon points="0 0 24 0 24 24 0 24" />
                                                            <path
                                                                d="M18,14 C16.3431458,14 15,12.6568542 15,11 C15,9.34314575 16.3431458,8 18,8 C19.6568542,8 21,9.34314575 21,11 C21,12.6568542 19.6568542,14 18,14 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                                fill="#000000" fill-rule="nonzero"
                                                                opacity="0.3" />
                                                            <path
                                                                d="M17.6011961,15.0006174 C21.0077043,15.0378534 23.7891749,16.7601418 23.9984937,20.4 C24.0069246,20.5466056 23.9984937,21 23.4559499,21 L19.6,21 C19.6,18.7490654 18.8562935,16.6718327 17.6011961,15.0006174 Z M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                                fill="#000000" fill-rule="nonzero" />
                                                        </g>
                                                    </svg>
                                                    <!--end::Svg Icon-->
                                                </span>
                                                <span
                                                    class="mt-2 mb-1 d-block text-dark-75 font-weight-bold font-size-h6">Customers</span>
                                                <span class="d-block text-dark-50 font-size-lg">Latest cases</span>
                                            </a>
                                        </div>
                                        <!--end:Item-->
                                    </div>
                                    <!--end:Nav-->
                                </div>
                                <!--end::Dropdown-->
                            </div>

                            <div class="topbar-item">
                                <div class="mr-1 btn btn-icon btn-clean btn-lg" id="kt_quick_panel_toggle">
                                    <span class="svg-icon svg-icon-xl svg-icon-primary">
                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Layout/Layout-4-blocks.svg-->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                            height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none"
                                                fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <rect fill="#000000" x="4" y="4" width="7" height="7"
                                                    rx="1.5" />
                                                <path
                                                    d="M5.5,13 L9.5,13 C10.3284271,13 11,13.6715729 11,14.5 L11,18.5 C11,19.3284271 10.3284271,20 9.5,20 L5.5,20 C4.67157288,20 4,19.3284271 4,18.5 L4,14.5 C4,13.6715729 4.67157288,13 5.5,13 Z M14.5,4 L18.5,4 C19.3284271,4 20,4.67157288 20,5.5 L20,9.5 C20,10.3284271 19.3284271,11 18.5,11 L14.5,11 C13.6715729,11 13,10.3284271 13,9.5 L13,5.5 C13,4.67157288 13.6715729,4 14.5,4 Z M14.5,13 L18.5,13 C19.3284271,13 20,13.6715729 20,14.5 L20,18.5 C20,19.3284271 19.3284271,20 18.5,20 L14.5,20 C13.6715729,20 13,19.3284271 13,18.5 L13,14.5 C13,13.6715729 13.6715729,13 14.5,13 Z"
                                                    fill="#000000" opacity="0.3" />
                                            </g>
                                        </svg>
                                        <!--end::Svg Icon-->
                                    </span>
                                </div>
                            </div>
                            <!--end::Quick panel-->
                            <!--begin::Chat-->
                            <div class="topbar-item">
                                <div class="mr-1 btn btn-icon btn-clean btn-lg" data-toggle="modal"
                                    data-target="#kt_chat_modal">
                                    <span class="svg-icon svg-icon-xl svg-icon-primary">
                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Group-chat.svg-->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                            height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none"
                                                fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <path
                                                    d="M16,15.6315789 L16,12 C16,10.3431458 14.6568542,9 13,9 L6.16183229,9 L6.16183229,5.52631579 C6.16183229,4.13107011 7.29290239,3 8.68814808,3 L20.4776218,3 C21.8728674,3 23.0039375,4.13107011 23.0039375,5.52631579 L23.0039375,13.1052632 L23.0206157,17.786793 C23.0215995,18.0629336 22.7985408,18.2875874 22.5224001,18.2885711 C22.3891754,18.2890457 22.2612702,18.2363324 22.1670655,18.1421277 L19.6565168,15.6315789 L16,15.6315789 Z"
                                                    fill="#000000" />
                                                <path
                                                    d="M1.98505595,18 L1.98505595,13 C1.98505595,11.8954305 2.88048645,11 3.98505595,11 L11.9850559,11 C13.0896254,11 13.9850559,11.8954305 13.9850559,13 L13.9850559,18 C13.9850559,19.1045695 13.0896254,20 11.9850559,20 L4.10078614,20 L2.85693427,21.1905292 C2.65744295,21.3814685 2.34093638,21.3745358 2.14999706,21.1750444 C2.06092565,21.0819836 2.01120804,20.958136 2.01120804,20.8293182 L2.01120804,18.32426 C1.99400175,18.2187196 1.98505595,18.1104045 1.98505595,18 Z M6.5,14 C6.22385763,14 6,14.2238576 6,14.5 C6,14.7761424 6.22385763,15 6.5,15 L11.5,15 C11.7761424,15 12,14.7761424 12,14.5 C12,14.2238576 11.7761424,14 11.5,14 L6.5,14 Z M9.5,16 C9.22385763,16 9,16.2238576 9,16.5 C9,16.7761424 9.22385763,17 9.5,17 L11.5,17 C11.7761424,17 12,16.7761424 12,16.5 C12,16.2238576 11.7761424,16 11.5,16 L9.5,16 Z"
                                                    fill="#000000" opacity="0.3" />
                                            </g>
                                        </svg>
                                        <!--end::Svg Icon-->
                                    </span>
                                </div>
                            </div>
                            <!--end::Chat-->
                            <!--begin::Languages-->
                            <div class="dropdown">
                                <!--begin::Toggle-->
                                <div class="topbar-item" data-toggle="dropdown" data-offset="10px,0px">
                                    <div class="mr-1 btn btn-icon btn-clean btn-dropdown btn-lg">
                                        <img class="rounded-sm h-20px w-20px"
                                            src="{{ asset('assets/media/svg/flags/226-united-states.svg') }}"
                                            alt="" />
                                    </div>
                                </div>
                                <!--end::Toggle-->
                                <!--begin::Dropdown-->
                                <div
                                    class="p-0 m-0 dropdown-menu dropdown-menu-anim-up dropdown-menu-sm dropdown-menu-right">
                                    <!--begin::Nav-->
                                    <ul class="py-4 navi navi-hover">
                                        <!--begin::Item-->
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="mr-3 symbol symbol-20">
                                                    <img src="{{ asset('assets/media/svg/flags/226-united-states.svg') }}"
                                                        alt="" />
                                                </span>
                                                <span class="navi-text">English</span>
                                            </a>
                                        </li>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <li class="navi-item active">
                                            <a href="#" class="navi-link">
                                                <span class="mr-3 symbol symbol-20">
                                                    <img src="{{ asset('assets/media/svg/flags/128-spain.svg') }}"
                                                        alt="" />
                                                </span>
                                                <span class="navi-text">Spanish</span>
                                            </a>
                                        </li>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="mr-3 symbol symbol-20">
                                                    <img src="{{ asset('assets/media/svg/flags/162-germany.svg') }}"
                                                        alt="" />
                                                </span>
                                                <span class="navi-text">German</span>
                                            </a>
                                        </li>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="mr-3 symbol symbol-20">
                                                    <img src="{{ asset('assets/media/svg/flags/063-japan.svg') }}"
                                                        alt="" />
                                                </span>
                                                <span class="navi-text">Japanese</span>
                                            </a>
                                        </li>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="mr-3 symbol symbol-20">
                                                    <img src="{{ asset('assets/media/svg/flags/195-france.svg') }}"
                                                        alt="" />
                                                </span>
                                                <span class="navi-text">French</span>
                                            </a>
                                        </li>
                                        <!--end::Item-->
                                    </ul>
                                    <!--end::Nav-->
                                </div>
                                <!--end::Dropdown-->
                            </div>
                            <!--end::Languages-->
                            <!--begin::User-->
                            <div class="topbar-item">
                                <div class="w-auto px-2 btn btn-icon btn-icon-mobile btn-clean d-flex align-items-center btn-lg"
                                    id="kt_quick_user_toggle">
                                    <span
                                        class="mr-1 text-muted font-weight-bold font-size-base d-none d-md-inline">Hi,</span>
                                    <span
                                        class="mr-3 text-dark-50 font-weight-bolder font-size-base d-none d-md-inline">{{ Auth::user()->username }}</span>
                                    <span class="symbol symbol-lg-35 symbol-25 symbol-light-success">
                                        <span
                                            class="symbol-label font-size-h5 font-weight-bold">{{ substr(Auth::user()->username, 0, 1) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <!--end::User-->
                        </div>
                        <!--end::Topbar-->
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Header-->
                <!--begin::Content-->
                <div class="py-2 subheader py-lg-4 subheader-solid" id="kt_subheader2">
                    <div class="container-fluid align-items-center justify-content-between ">
                        <!--begin::Info-->
                        <div class="flex-wrap mr-2 align-items-center d-flex" style="gap:1rem;">
                            @if(Auth::user()->reflink && !$isOperatorOnly)
                            <!-- Standard referral link -->
                            <p class="mt-2 mb-2 ml-5 text-dark font-weight-bold" style="cursor:pointer;" onclick="copyToClipboard()" title="Copy standard referral link">
                                {{ url('/register/ref/' . Auth::user()->reflink->link) }}
                                <i class="la la-copy"></i>
                            </p>
                            @endif
                            @if(Auth::user()->reflink && !$isOperatorOnly && Auth::user()->canSeeSavingLink())
                            <!-- Savings Program referral link -->
                            <p class="mt-2 mb-2 text-success font-weight-bold" style="cursor:pointer;" onclick="copySavingLinkToClipboard()" title="Copy Savings Program link">
                                {{ url('/register/ref/' . Auth::user()->reflink->link . '?type=saving') }}
                                <i class="la la-copy"></i>
                                <span class="ml-1 badge badge-success" style="font-size:0.7rem;">Savings</span>
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                @section('content')
                @show


            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::Main-->
    <!-- begin::User Panel-->
    <div id="kt_quick_user" class="p-10 offcanvas offcanvas-right">
        <!--begin::Header-->
        <div class="pb-5 offcanvas-header d-flex align-items-center justify-content-between">
            <h3 class="m-0 font-weight-bold">User Profile </h3>
            <a href="#" class="btn btn-xs btn-icon btn-light btn-hover-primary" id="kt_quick_user_close">
                <i class="ki ki-close icon-xs text-muted"></i>
            </a>
        </div>
        <!--end::Header-->
        <!--begin::Content-->
        <div class="pr-5 offcanvas-content mr-n5">
            <!--begin::Header-->
            <div class="mt-5 d-flex align-items-center">
                <div class="mr-5 symbol symbol-100">
                    <div class="symbol-label"
                        style="background-image:url({{ asset(Auth::user()->getFirstMediaUrl('user_profile_images')) }})">
                    </div>
                    <i class="symbol-badge bg-success"></i>
                </div>
                <div class="d-flex flex-column">
                    <a href="#" class="font-weight-bold font-size-h5 text-dark-75 text-hover-primary">
                        {{ Auth::user()->name }} </a>
                    <div class="mt-1 text-muted"> {{ Auth::user()->username }} </div>
                    <div class="mt-2 navi">
                        <a href="#" class="navi-item">
                            <span class="p-0 pb-2 navi-link">
                                <span class="mr-1 navi-icon">
                                    <span class="svg-icon svg-icon-lg svg-icon-primary">
                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Mail-notification.svg-->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                            height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none"
                                                fill-rule="evenodd">
                                                <rect x="0" y="0" width="24" height="24" />
                                                <path
                                                    d="M21,12.0829584 C20.6747915,12.0283988 20.3407122,12 20,12 C16.6862915,12 14,14.6862915 14,18 C14,18.3407122 14.0283988,18.6747915 14.0829584,19 L5,19 C3.8954305,19 3,18.1045695 3,17 L3,8 C3,6.8954305 3.8954305,6 5,6 L19,6 C20.1045695,6 21,6.8954305 21,8 L21,12.0829584 Z M18.1444251,7.83964668 L12,11.1481833 L5.85557487,7.83964668 C5.4908718,7.6432681 5.03602525,7.77972206 4.83964668,8.14442513 C4.6432681,8.5091282 4.77972206,8.96397475 5.14442513,9.16035332 L11.6444251,12.6603533 C11.8664074,12.7798822 12.1335926,12.7798822 12.3555749,12.6603533 L18.8555749,9.16035332 C19.2202779,8.96397475 19.3567319,8.5091282 19.1603533,8.14442513 C18.9639747,7.77972206 18.5091282,7.6432681 18.1444251,7.83964668 Z"
                                                    fill="#000000" />
                                                <circle fill="#000000" opacity="0.3" cx="19.5"
                                                    cy="17.5" r="2.5" />
                                            </g>
                                        </svg>
                                        <!--end::Svg Icon-->
                                    </span>
                                </span>
                                <span
                                    class="navi-text text-muted text-hover-primary">{{ Auth::user()->email }}</span>
                            </span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                            style="display: none;">
                            @csrf
                        </form>


                        <a href="#" class="px-5 py-2 btn btn-sm btn-light-primary font-weight-bolder"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Sign Out
                        </a>


                    </div>
                </div>
            </div>
            <!--end::Header-->
            <!--begin::Separator-->
            <div class="mt-8 mb-5 separator separator-dashed"></div>
            <!--end::Separator-->
            <!--begin::Nav-->
            <div class="p-0 navi navi-spacer-x-0">
                <!--begin::Item-->
                @if(!$isOperatorOnly)
                <a href="{{ route('profile.edit') }}" class="navi-item">
                    <div class="navi-link">
                        <div class="mr-3 symbol symbol-40 bg-light">
                            <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-success">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/General/Notification2.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <path
                                                d="M13.2070325,4 C13.0721672,4.47683179 13,4.97998812 13,5.5 C13,8.53756612 15.4624339,11 18.5,11 C19.0200119,11 19.5231682,10.9278328 20,10.7929675 L20,17 C20,18.6568542 18.6568542,20 17,20 L7,20 C5.34314575,20 4,18.6568542 4,17 L4,7 C4,5.34314575 5.34314575,4 7,4 L13.2070325,4 Z"
                                                fill="#000000" />
                                            <circle fill="#000000" opacity="0.3" cx="18.5" cy="5.5"
                                                r="2.5" />
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </div>
                        </div>
                        <div class="navi-text">
                            <div class="font-weight-bold">My Profile</div>
                            <div class="text-muted">Account settings and more
                                <span class="label label-light-danger label-inline font-weight-bold">update</span>
                            </div>
                        </div>
                    </div>
                </a>
                @endif
                <!--end:Item-->
                <!--begin::Item-->
                <a href="custom/apps/user/profile-3.html" class="navi-item">
                    <div class="navi-link">
                        <div class="mr-3 symbol symbol-40 bg-light">
                            <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-warning">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Shopping/Chart-bar1.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <rect fill="#000000" opacity="0.3" x="12" y="4" width="3"
                                                height="13" rx="1.5" />
                                            <rect fill="#000000" opacity="0.3" x="7" y="9" width="3"
                                                height="8" rx="1.5" />
                                            <path
                                                d="M5,19 L20,19 C20.5522847,19 21,19.4477153 21,20 C21,20.5522847 20.5522847,21 20,21 L4,21 C3.44771525,21 3,20.5522847 3,20 L3,4 C3,3.44771525 3.44771525,3 4,3 C4.55228475,3 5,3.44771525 5,4 L5,19 Z"
                                                fill="#000000" fill-rule="nonzero" />
                                            <rect fill="#000000" opacity="0.3" x="17" y="11" width="3"
                                                height="6" rx="1.5" />
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </div>
                        </div>
                        <div class="navi-text">
                            <div class="font-weight-bold">My Messages</div>
                            <div class="text-muted">Inbox and tasks</div>
                        </div>
                    </div>
                </a>
                <!--end:Item-->
                <!--begin::Item-->
                <a href="custom/apps/user/profile-2.html" class="navi-item">
                    <div class="navi-link">
                        <div class="mr-3 symbol symbol-40 bg-light">
                            <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-danger">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Files/Selected-file.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24" />
                                            <path
                                                d="M4.85714286,1 L11.7364114,1 C12.0910962,1 12.4343066,1.12568431 12.7051108,1.35473959 L17.4686994,5.3839416 C17.8056532,5.66894833 18,6.08787823 18,6.52920201 L18,19.0833333 C18,20.8738751 17.9795521,21 16.1428571,21 L4.85714286,21 C3.02044787,21 3,20.8738751 3,19.0833333 L3,2.91666667 C3,1.12612489 3.02044787,1 4.85714286,1 Z M8,12 C7.44771525,12 7,12.4477153 7,13 C7,13.5522847 7.44771525,14 8,14 L15,14 C15.5522847,14 16,13.5522847 16,13 C16,12.4477153 15.5522847,12 15,12 L8,12 Z M8,16 C7.44771525,16 7,16.4477153 7,17 C7,17.5522847 7.44771525,18 8,18 L11,18 C11.5522847,18 12,17.5522847 12,17 C12,16.4477153 11.5522847,16 11,16 L8,16 Z"
                                                fill="#000000" fill-rule="nonzero" opacity="0.3" />
                                            <path
                                                d="M6.85714286,3 L14.7364114,3 C15.0910962,3 15.4343066,3.12568431 15.7051108,3.35473959 L20.4686994,7.3839416 C20.8056532,7.66894833 21,8.08787823 21,8.52920201 L21,21.0833333 C21,22.8738751 20.9795521,23 19.1428571,23 L6.85714286,23 C5.02044787,23 5,22.8738751 5,21.0833333 L5,4.91666667 C5,3.12612489 5.02044787,3 6.85714286,3 Z M8,12 C7.44771525,12 7,12.4477153 7,13 C7,13.5522847 7.44771525,14 8,14 L15,14 C15.5522847,14 16,13.5522847 16,13 C16,12.4477153 15.5522847,12 15,12 L8,12 Z M8,16 C7.44771525,16 7,16.4477153 7,17 C7,17.5522847 7.44771525,18 8,18 L11,18 C11.5522847,18 12,17.5522847 12,17 C12,16.4477153 11.5522847,16 11,16 L8,16 Z"
                                                fill="#000000" fill-rule="nonzero" />
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </div>
                        </div>
                        <div class="navi-text">
                            <div class="font-weight-bold">My Activities</div>
                            <div class="text-muted">Logs and notifications</div>
                        </div>
                    </div>
                </a>
                <!--end:Item-->
                <!--begin::Item-->
                <a href="custom/apps/userprofile-1/overview.html" class="navi-item">
                    <div class="navi-link">
                        <div class="mr-3 symbol symbol-40 bg-light">
                            <div class="symbol-label">
                                <span class="svg-icon svg-icon-md svg-icon-primary">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Mail-opened.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <path
                                                d="M6,2 L18,2 C18.5522847,2 19,2.44771525 19,3 L19,12 C19,12.5522847 18.5522847,13 18,13 L6,13 C5.44771525,13 5,12.5522847 5,12 L5,3 C5,2.44771525 5.44771525,2 6,2 Z M7.5,5 C7.22385763,5 7,5.22385763 7,5.5 C7,5.77614237 7.22385763,6 7.5,6 L13.5,6 C13.7761424,6 14,5.77614237 14,5.5 C14,5.22385763 13.7761424,5 13.5,5 L7.5,5 Z M7.5,7 C7.22385763,7 7,7.22385763 7,7.5 C7,7.77614237 7.22385763,8 7.5,8 L10.5,8 C10.7761424,8 11,7.77614237 11,7.5 C11,7.22385763 10.7761424,7 10.5,7 L7.5,7 Z"
                                                fill="#000000" opacity="0.3" />
                                            <path
                                                d="M3.79274528,6.57253826 L12,12.5 L20.2072547,6.57253826 C20.4311176,6.4108595 20.7436609,6.46126971 20.9053396,6.68513259 C20.9668779,6.77033951 21,6.87277228 21,6.97787787 L21,17 C21,18.1045695 20.1045695,19 19,19 L5,19 C3.8954305,19 3,18.1045695 3,17 L3,6.97787787 C3,6.70173549 3.22385763,6.47787787 3.5,6.47787787 C3.60510559,6.47787787 3.70753836,6.51099993 3.79274528,6.57253826 Z"
                                                fill="#000000" />
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </div>
                        </div>
                        <div class="navi-text">
                            <div class="font-weight-bold">My Tasks</div>
                            <div class="text-muted">latest tasks and projects</div>
                        </div>
                    </div>
                </a>
                <!--end:Item-->
            </div>
            <!--end::Nav-->
            <!--begin::Separator-->
            <div class="separator separator-dashed my-7"></div>
            <!--end::Separator-->
            <!--begin::Notifications-->
            <div>
                <!--begin:Heading-->
                <h5 class="mb-5">Recent Notifications</h5>
                <!--end:Heading-->
                <!--begin::Item-->
                <div class="p-5 rounded d-flex align-items-center bg-light-warning gutter-b">
                    <span class="mr-5 svg-icon svg-icon-warning">
                        <span class="svg-icon svg-icon-lg">
                            <!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24" />
                                    <path
                                        d="M5,3 L6,3 C6.55228475,3 7,3.44771525 7,4 L7,20 C7,20.5522847 6.55228475,21 6,21 L5,21 C4.44771525,21 4,20.5522847 4,20 L4,4 C4,3.44771525 4.44771525,3 5,3 Z M10,3 L11,3 C11.5522847,3 12,3.44771525 12,4 L12,20 C12,20.5522847 11.5522847,21 11,21 L10,21 C9.44771525,21 9,20.5522847 9,20 L9,4 C9,3.44771525 9.44771525,3 10,3 Z"
                                        fill="#000000" />
                                    <rect fill="#000000" opacity="0.3"
                                        transform="translate(17.825568, 11.945519) rotate(-19.000000) translate(-17.825568, -11.945519)"
                                        x="16.3255682" y="2.94551858" width="3" height="18"
                                        rx="1" />
                                </g>
                            </svg>
                            <!--end::Svg Icon-->
                        </span>
                    </span>
                    <div class="mr-2 d-flex flex-column flex-grow-1">
                        <a href="#"
                            class="mb-1 font-weight-normal text-dark-75 text-hover-primary font-size-lg">Another
                            purpose persuade</a>
                        <span class="text-muted font-size-sm">Due in 2 Days</span>
                    </div>
                    <span class="py-1 font-weight-bolder text-warning font-size-lg">+28%</span>
                </div>
                <!--end::Item-->
                <!--begin::Item-->
                <div class="p-5 rounded d-flex align-items-center bg-light-success gutter-b">
                    <span class="mr-5 svg-icon svg-icon-success">
                        <span class="svg-icon svg-icon-lg">
                            <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Write.svg-->
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24" />
                                    <path
                                        d="M12.2674799,18.2323597 L12.0084872,5.45852451 C12.0004303,5.06114792 12.1504154,4.6768183 12.4255037,4.38993949 L15.0030167,1.70195304 L17.5910752,4.40093695 C17.8599071,4.6812911 18.0095067,5.05499603 18.0083938,5.44341307 L17.9718262,18.2062508 C17.9694575,19.0329966 17.2985816,19.701953 16.4718324,19.701953 L13.7671717,19.701953 C12.9505952,19.701953 12.2840328,19.0487684 12.2674799,18.2323597 Z"
                                        fill="#000000" fill-rule="nonzero"
                                        transform="translate(14.701953, 10.701953) rotate(-135.000000) translate(-14.701953, -10.701953)" />
                                    <path
                                        d="M12.9,2 C13.4522847,2 13.9,2.44771525 13.9,3 C13.9,3.55228475 13.4522847,4 12.9,4 L6,4 C4.8954305,4 4,4.8954305 4,6 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,13 C20,12.4477153 20.4477153,12 21,12 C21.5522847,12 22,12.4477153 22,13 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,6 C2,3.790861 3.790861,2 6,2 L12.9,2 Z"
                                        fill="#000000" fill-rule="nonzero" opacity="0.3" />
                                </g>
                            </svg>
                            <!--end::Svg Icon-->
                        </span>
                    </span>
                    <div class="mr-2 d-flex flex-column flex-grow-1">
                        <a href="#"
                            class="mb-1 font-weight-normal text-dark-75 text-hover-primary font-size-lg">Would be to
                            people</a>
                        <span class="text-muted font-size-sm">Due in 2 Days</span>
                    </div>
                    <span class="py-1 font-weight-bolder text-success font-size-lg">+50%</span>
                </div>
                <!--end::Item-->
                <!--begin::Item-->
                <div class="p-5 rounded d-flex align-items-center bg-light-danger gutter-b">
                    <span class="mr-5 svg-icon svg-icon-danger">
                        <span class="svg-icon svg-icon-lg">
                            <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Group-chat.svg-->
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24" />
                                    <path
                                        d="M16,15.6315789 L16,12 C16,10.3431458 14.6568542,9 13,9 L6.16183229,9 L6.16183229,5.52631579 C6.16183229,4.13107011 7.29290239,3 8.68814808,3 L20.4776218,3 C21.8728674,3 23.0039375,4.13107011 23.0039375,5.52631579 L23.0039375,13.1052632 L23.0206157,17.786793 C23.0215995,18.0629336 22.7985408,18.2875874 22.5224001,18.2885711 C22.3891754,18.2890457 22.2612702,18.2363324 22.1670655,18.1421277 L19.6565168,15.6315789 L16,15.6315789 Z"
                                        fill="#000000" />
                                    <path
                                        d="M1.98505595,18 L1.98505595,13 C1.98505595,11.8954305 2.88048645,11 3.98505595,11 L11.9850559,11 C13.0896254,11 13.9850559,11.8954305 13.9850559,13 L13.9850559,18 C13.9850559,19.1045695 13.0896254,20 11.9850559,20 L4.10078614,20 L2.85693427,21.1905292 C2.65744295,21.3814685 2.34093638,21.3745358 2.14999706,21.1750444 C2.06092565,21.0819836 2.01120804,20.958136 2.01120804,20.8293182 L2.01120804,18.32426 C1.99400175,18.2187196 1.98505595,18.1104045 1.98505595,18 Z M6.5,14 C6.22385763,14 6,14.2238576 6,14.5 C6,14.7761424 6.22385763,15 6.5,15 L11.5,15 C11.7761424,15 12,14.7761424 12,14.5 C12,14.2238576 11.7761424,14 11.5,14 L6.5,14 Z M9.5,16 C9.22385763,16 9,16.2238576 9,16.5 C9,16.7761424 9.22385763,17 9.5,17 L11.5,17 C11.7761424,17 12,16.7761424 12,16.5 C12,16.2238576 11.7761424,16 11.5,16 L9.5,16 Z"
                                        fill="#000000" opacity="0.3" />
                                </g>
                            </svg>
                            <!--end::Svg Icon-->
                        </span>
                    </span>
                    <div class="mr-2 d-flex flex-column flex-grow-1">
                        <a href="#"
                            class="mb-1 font-weight-normel text-dark-75 text-hover-primary font-size-lg">Purpose would
                            be to persuade</a>
                        <span class="text-muted font-size-sm">Due in 2 Days</span>
                    </div>
                    <span class="py-1 font-weight-bolder text-danger font-size-lg">-27%</span>
                </div>
                <!--end::Item-->
                <!--begin::Item-->
                <div class="p-5 rounded d-flex align-items-center bg-light-info">
                    <span class="mr-5 svg-icon svg-icon-info">
                        <span class="svg-icon svg-icon-lg">
                            <!--begin::Svg Icon | path:assets/media/svg/icons/General/Attachment2.svg-->
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <rect x="0" y="0" width="24" height="24" />
                                    <path
                                        d="M11.7573593,15.2426407 L8.75735931,15.2426407 C8.20507456,15.2426407 7.75735931,15.6903559 7.75735931,16.2426407 C7.75735931,16.7949254 8.20507456,17.2426407 8.75735931,17.2426407 L11.7573593,17.2426407 L11.7573593,18.2426407 C11.7573593,19.3472102 10.8619288,20.2426407 9.75735931,20.2426407 L5.75735931,20.2426407 C4.65278981,20.2426407 3.75735931,19.3472102 3.75735931,18.2426407 L3.75735931,14.2426407 C3.75735931,13.1380712 4.65278981,12.2426407 5.75735931,12.2426407 L9.75735931,12.2426407 C10.8619288,12.2426407 11.7573593,13.1380712 11.7573593,14.2426407 L11.7573593,15.2426407 Z"
                                        fill="#000000" opacity="0.3"
                                        transform="translate(7.757359, 16.242641) rotate(-45.000000) translate(-7.757359, -16.242641)" />
                                    <path
                                        d="M12.2426407,8.75735931 L15.2426407,8.75735931 C15.7949254,8.75735931 16.2426407,8.30964406 16.2426407,7.75735931 C16.2426407,7.20507456 15.7949254,6.75735931 15.2426407,6.75735931 L12.2426407,6.75735931 L12.2426407,5.75735931 C12.2426407,4.65278981 13.1380712,3.75735931 14.2426407,3.75735931 L18.2426407,3.75735931 C19.3472102,3.75735931 20.2426407,4.65278981 20.2426407,5.75735931 L20.2426407,9.75735931 C20.2426407,10.8619288 19.3472102,11.7573593 18.2426407,11.7573593 L14.2426407,11.7573593 C13.1380712,11.7573593 12.2426407,10.8619288 12.2426407,9.75735931 L12.2426407,8.75735931 Z"
                                        fill="#000000"
                                        transform="translate(16.242641, 7.757359) rotate(-45.000000) translate(-16.242641, -7.757359)" />
                                    <path
                                        d="M5.89339828,3.42893219 C6.44568303,3.42893219 6.89339828,3.87664744 6.89339828,4.42893219 L6.89339828,6.42893219 C6.89339828,6.98121694 6.44568303,7.42893219 5.89339828,7.42893219 C5.34111353,7.42893219 4.89339828,6.98121694 4.89339828,6.42893219 L4.89339828,4.42893219 C4.89339828,3.87664744 5.34111353,3.42893219 5.89339828,3.42893219 Z M11.4289322,5.13603897 C11.8194565,5.52656326 11.8194565,6.15972824 11.4289322,6.55025253 L10.0147186,7.96446609 C9.62419433,8.35499039 8.99102936,8.35499039 8.60050506,7.96446609 C8.20998077,7.5739418 8.20998077,6.94077682 8.60050506,6.55025253 L10.0147186,5.13603897 C10.4052429,4.74551468 11.0384079,4.74551468 11.4289322,5.13603897 Z M0.600505063,5.13603897 C0.991029355,4.74551468 1.62419433,4.74551468 2.01471863,5.13603897 L3.42893219,6.55025253 C3.81945648,6.94077682 3.81945648,7.5739418 3.42893219,7.96446609 C3.0384079,8.35499039 2.40524292,8.35499039 2.01471863,7.96446609 L0.600505063,6.55025253 C0.209980772,6.15972824 0.209980772,5.52656326 0.600505063,5.13603897 Z"
                                        fill="#000000" opacity="0.3"
                                        transform="translate(6.014719, 5.843146) rotate(-45.000000) translate(-6.014719, -5.843146)" />
                                    <path
                                        d="M17.9142136,15.4497475 C18.4664983,15.4497475 18.9142136,15.8974627 18.9142136,16.4497475 L18.9142136,18.4497475 C18.9142136,19.0020322 18.4664983,19.4497475 17.9142136,19.4497475 C17.3619288,19.4497475 16.9142136,19.0020322 16.9142136,18.4497475 L16.9142136,16.4497475 C16.9142136,15.8974627 17.3619288,15.4497475 17.9142136,15.4497475 Z M23.4497475,17.1568542 C23.8402718,17.5473785 23.8402718,18.1805435 23.4497475,18.5710678 L22.0355339,19.9852814 C21.6450096,20.3758057 21.0118446,20.3758057 20.6213203,19.9852814 C20.2307961,19.5947571 20.2307961,18.9615921 20.6213203,18.5710678 L22.0355339,17.1568542 C22.4260582,16.76633 23.0592232,16.76633 23.4497475,17.1568542 Z M12.6213203,17.1568542 C13.0118446,16.76633 13.6450096,16.76633 14.0355339,17.1568542 L15.4497475,18.5710678 C15.8402718,18.9615921 15.8402718,19.5947571 15.4497475,19.9852814 C15.0592232,20.3758057 14.4260582,20.3758057 14.0355339,19.9852814 L12.6213203,18.5710678 C12.2307961,18.1805435 12.2307961,17.5473785 12.6213203,17.1568542 Z"
                                        fill="#000000" opacity="0.3"
                                        transform="translate(18.035534, 17.863961) scale(1, -1) rotate(45.000000) translate(-18.035534, -17.863961)" />
                                </g>
                            </svg>
                            <!--end::Svg Icon-->
                        </span>
                    </span>
                    <div class="mr-2 d-flex flex-column flex-grow-1">
                        <a href="#"
                            class="mb-1 font-weight-normel text-dark-75 text-hover-primary font-size-lg">The best
                            product</a>
                        <span class="text-muted font-size-sm">Due in 2 Days</span>
                    </div>
                    <span class="py-1 font-weight-bolder text-info font-size-lg">+8%</span>
                </div>
                <!--end::Item-->
            </div>
            <!--end::Notifications-->
        </div>
        <!--end::Content-->
    </div>
    <!-- end::User Panel-->

    <!--end::Quick Cart-->
    <!--begin::Quick Panel-->
    <div id="kt_quick_panel" class="pt-5 pb-10 offcanvas offcanvas-right">
        <!--begin::Header-->
        <div class="mb-5 offcanvas-header offcanvas-header-navs d-flex align-items-center justify-content-between">
            <ul class="px-10 nav nav-bold nav-tabs nav-tabs-line nav-tabs-line-3x nav-tabs-primary flex-grow-1"
                role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#kt_quick_panel_logs">Audit Logs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#kt_quick_panel_notifications">Notifications</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#kt_quick_panel_settings">Settings</a>
                </li>
            </ul>
            <div class="pr-5 offcanvas-close mt-n1">
                <a href="#" class="btn btn-xs btn-icon btn-light btn-hover-primary"
                    id="kt_quick_panel_close">
                    <i class="ki ki-close icon-xs text-muted"></i>
                </a>
            </div>
        </div>
        <!--end::Header-->
        <!--begin::Content-->
        <div class="px-10 offcanvas-content">
            <div class="tab-content">
                <!--begin::Tabpane-->
                <div class="pt-3 pr-5 tab-pane fade show mr-n5 active" id="kt_quick_panel_logs" role="tabpanel">
                    <!--begin::Section-->
                    <div class="mb-15">
                        <h5 class="mb-5 font-weight-bold">System Messages</h5>
                        <!--begin: Item-->
                        <div class="flex-wrap mb-5 d-flex align-items-center">
                            <div class="mr-5 symbol symbol-50 symbol-light">
                                <span class="symbol-label">
                                    <img src="{{ asset('assets/media/svg/misc/006-plurk.svg') }}"
                                        class="h-50 align-self-center" alt="" />
                                </span>
                            </div>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-bolder text-dark-75 text-hover-primary font-size-lg">Top
                                    Authors</a>
                                <span class="text-muted font-weight-bold">Most Successful Fellas</span>
                            </div>
                            <span
                                class="py-1 my-2 btn btn-sm btn-light font-weight-bolder my-lg-0 text-dark-50">+82$</span>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="flex-wrap mb-5 d-flex align-items-center">
                            <div class="mr-5 symbol symbol-50 symbol-light">
                                <span class="symbol-label">
                                    <img src="{{ asset('assets/media/svg/misc/015-telegram.svg') }}"
                                        class="h-50 align-self-center" alt="" />
                                </span>
                            </div>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-bolder text-dark-75 text-hover-primary font-size-lg">Popular
                                    Authors</a>
                                <span class="text-muted font-weight-bold">Most Successful Fellas</span>
                            </div>
                            <span
                                class="py-1 my-2 btn btn-sm btn-light font-weight-bolder my-lg-0 text-dark-50">+280$</span>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="flex-wrap mb-5 d-flex align-items-center">
                            <div class="mr-5 symbol symbol-50 symbol-light">
                                <span class="symbol-label">
                                    <img src="{{ asset('assets/media/svg/misc/003-puzzle.svg') }}"
                                        class="h-50 align-self-center" alt="" />
                                </span>
                            </div>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-bolder text-dark-75 text-hover-primary font-size-lg">New
                                    Users</a>
                                <span class="text-muted font-weight-bold">Most Successful Fellas</span>
                            </div>
                            <span
                                class="py-1 my-2 btn btn-sm btn-light font-weight-bolder my-lg-0 text-dark-50">+4500$</span>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="flex-wrap mb-5 d-flex align-items-center">
                            <div class="mr-5 symbol symbol-50 symbol-light">
                                <span class="symbol-label">
                                    <img src="{{ asset('assets/media/svg/misc/005-bebo.svg') }}"
                                        class="h-50 align-self-center" alt="" />
                                </span>
                            </div>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-bolder text-dark-75 text-hover-primary font-size-lg">Active
                                    Customers</a>
                                <span class="text-muted font-weight-bold">Most Successful Fellas</span>
                            </div>
                            <span
                                class="py-1 my-2 btn btn-sm btn-light font-weight-bolder my-lg-0 text-dark-50">+4500$</span>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="flex-wrap d-flex align-items-center">
                            <div class="mr-5 symbol symbol-50 symbol-light">
                                <span class="symbol-label">
                                    <img src="{{ asset('assets/media/svg/misc/014-kickstarter.svg') }}"
                                        class="h-50 align-self-center" alt="" />
                                </span>
                            </div>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-bolder text-dark-75 text-hover-primary font-size-lg">Bestseller
                                    Theme</a>
                                <span class="text-muted font-weight-bold">Most Successful Fellas</span>
                            </div>
                            <span
                                class="py-1 my-2 btn btn-sm btn-light font-weight-bolder my-lg-0 text-dark-50">+4500$</span>
                        </div>
                        <!--end: Item-->
                    </div>
                    <!--end::Section-->
                    <!--begin::Section-->
                    <div class="mb-5">
                        <h5 class="mb-5 font-weight-bold">Notifications</h5>
                        <!--begin: Item-->
                        <div class="p-5 mb-5 rounded d-flex align-items-center bg-light-warning">
                            <span class="mr-5 svg-icon svg-icon-warning">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Home/Library.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <path
                                                d="M5,3 L6,3 C6.55228475,3 7,3.44771525 7,4 L7,20 C7,20.5522847 6.55228475,21 6,21 L5,21 C4.44771525,21 4,20.5522847 4,20 L4,4 C4,3.44771525 4.44771525,3 5,3 Z M10,3 L11,3 C11.5522847,3 12,3.44771525 12,4 L12,20 C12,20.5522847 11.5522847,21 11,21 L10,21 C9.44771525,21 9,20.5522847 9,20 L9,4 C9,3.44771525 9.44771525,3 10,3 Z"
                                                fill="#000000" />
                                            <rect fill="#000000" opacity="0.3"
                                                transform="translate(17.825568, 11.945519) rotate(-19.000000) translate(-17.825568, -11.945519)"
                                                x="16.3255682" y="2.94551858" width="3" height="18"
                                                rx="1" />
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </span>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-normal text-dark-75 text-hover-primary font-size-lg">Another
                                    purpose persuade</a>
                                <span class="text-muted font-size-sm">Due in 2 Days</span>
                            </div>
                            <span class="py-1 font-weight-bolder text-warning font-size-lg">+28%</span>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="p-5 mb-5 rounded d-flex align-items-center bg-light-success">
                            <span class="mr-5 svg-icon svg-icon-success">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Write.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <path
                                                d="M12.2674799,18.2323597 L12.0084872,5.45852451 C12.0004303,5.06114792 12.1504154,4.6768183 12.4255037,4.38993949 L15.0030167,1.70195304 L17.5910752,4.40093695 C17.8599071,4.6812911 18.0095067,5.05499603 18.0083938,5.44341307 L17.9718262,18.2062508 C17.9694575,19.0329966 17.2985816,19.701953 16.4718324,19.701953 L13.7671717,19.701953 C12.9505952,19.701953 12.2840328,19.0487684 12.2674799,18.2323597 Z"
                                                fill="#000000" fill-rule="nonzero"
                                                transform="translate(14.701953, 10.701953) rotate(-135.000000) translate(-14.701953, -10.701953)" />
                                            <path
                                                d="M12.9,2 C13.4522847,2 13.9,2.44771525 13.9,3 C13.9,3.55228475 13.4522847,4 12.9,4 L6,4 C4.8954305,4 4,4.8954305 4,6 L4,18 C4,19.1045695 4.8954305,20 6,20 L18,20 C19.1045695,20 20,19.1045695 20,18 L20,13 C20,12.4477153 20.4477153,12 21,12 C21.5522847,12 22,12.4477153 22,13 L22,18 C22,20.209139 20.209139,22 18,22 L6,22 C3.790861,22 2,20.209139 2,18 L2,6 C2,3.790861 3.790861,2 6,2 L12.9,2 Z"
                                                fill="#000000" fill-rule="nonzero" opacity="0.3" />
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </span>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-normal text-dark-75 text-hover-primary font-size-lg">Would
                                    be to people</a>
                                <span class="text-muted font-size-sm">Due in 2 Days</span>
                            </div>
                            <span class="py-1 font-weight-bolder text-success font-size-lg">+50%</span>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="p-5 mb-5 rounded d-flex align-items-center bg-light-danger">
                            <span class="mr-5 svg-icon svg-icon-danger">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Group-chat.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <path
                                                d="M16,15.6315789 L16,12 C16,10.3431458 14.6568542,9 13,9 L6.16183229,9 L6.16183229,5.52631579 C6.16183229,4.13107011 7.29290239,3 8.68814808,3 L20.4776218,3 C21.8728674,3 23.0039375,4.13107011 23.0039375,5.52631579 L23.0039375,13.1052632 L23.0206157,17.786793 C23.0215995,18.0629336 22.7985408,18.2875874 22.5224001,18.2885711 C22.3891754,18.2890457 22.2612702,18.2363324 22.1670655,18.1421277 L19.6565168,15.6315789 L16,15.6315789 Z"
                                                fill="#000000" />
                                            <path
                                                d="M1.98505595,18 L1.98505595,13 C1.98505595,11.8954305 2.88048645,11 3.98505595,11 L11.9850559,11 C13.0896254,11 13.9850559,11.8954305 13.9850559,13 L13.9850559,18 C13.9850559,19.1045695 13.0896254,20 11.9850559,20 L4.10078614,20 L2.85693427,21.1905292 C2.65744295,21.3814685 2.34093638,21.3745358 2.14999706,21.1750444 C2.06092565,21.0819836 2.01120804,20.958136 2.01120804,20.8293182 L2.01120804,18.32426 C1.99400175,18.2187196 1.98505595,18.1104045 1.98505595,18 Z M6.5,14 C6.22385763,14 6,14.2238576 6,14.5 C6,14.7761424 6.22385763,15 6.5,15 L11.5,15 C11.7761424,15 12,14.7761424 12,14.5 C12,14.2238576 11.7761424,14 11.5,14 L6.5,14 Z M9.5,16 C9.22385763,16 9,16.2238576 9,16.5 C9,16.7761424 9.22385763,17 9.5,17 L11.5,17 C11.7761424,17 12,16.7761424 12,16.5 C12,16.2238576 11.7761424,16 11.5,16 L9.5,16 Z"
                                                fill="#000000" opacity="0.3" />
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </span>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-normel text-dark-75 text-hover-primary font-size-lg">Purpose
                                    would be to persuade</a>
                                <span class="text-muted font-size-sm">Due in 2 Days</span>
                            </div>
                            <span class="py-1 font-weight-bolder text-danger font-size-lg">-27%</span>
                        </div>
                        <!--end: Item-->
                        <!--begin: Item-->
                        <div class="p-5 rounded d-flex align-items-center bg-light-info">
                            <span class="mr-5 svg-icon svg-icon-info">
                                <span class="svg-icon svg-icon-lg">
                                    <!--begin::Svg Icon | path:assets/media/svg/icons/General/Attachment2.svg-->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px"
                                        viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <rect x="0" y="0" width="24" height="24" />
                                            <path
                                                d="M11.7573593,15.2426407 L8.75735931,15.2426407 C8.20507456,15.2426407 7.75735931,15.6903559 7.75735931,16.2426407 C7.75735931,16.7949254 8.20507456,17.2426407 8.75735931,17.2426407 L11.7573593,17.2426407 L11.7573593,18.2426407 C11.7573593,19.3472102 10.8619288,20.2426407 9.75735931,20.2426407 L5.75735931,20.2426407 C4.65278981,20.2426407 3.75735931,19.3472102 3.75735931,18.2426407 L3.75735931,14.2426407 C3.75735931,13.1380712 4.65278981,12.2426407 5.75735931,12.2426407 L9.75735931,12.2426407 C10.8619288,12.2426407 11.7573593,13.1380712 11.7573593,14.2426407 L11.7573593,15.2426407 Z"
                                                fill="#000000" opacity="0.3"
                                                transform="translate(7.757359, 16.242641) rotate(-45.000000) translate(-7.757359, -16.242641)" />
                                            <path
                                                d="M12.2426407,8.75735931 L15.2426407,8.75735931 C15.7949254,8.75735931 16.2426407,8.30964406 16.2426407,7.75735931 C16.2426407,7.20507456 15.7949254,6.75735931 15.2426407,6.75735931 L12.2426407,6.75735931 L12.2426407,5.75735931 C12.2426407,4.65278981 13.1380712,3.75735931 14.2426407,3.75735931 L18.2426407,3.75735931 C19.3472102,3.75735931 20.2426407,4.65278981 20.2426407,5.75735931 L20.2426407,9.75735931 C20.2426407,10.8619288 19.3472102,11.7573593 18.2426407,11.7573593 L14.2426407,11.7573593 C13.1380712,11.7573593 12.2426407,10.8619288 12.2426407,9.75735931 L12.2426407,8.75735931 Z"
                                                fill="#000000"
                                                transform="translate(16.242641, 7.757359) rotate(-45.000000) translate(-16.242641, -7.757359)" />
                                            <path
                                                d="M5.89339828,3.42893219 C6.44568303,3.42893219 6.89339828,3.87664744 6.89339828,4.42893219 L6.89339828,6.42893219 C6.89339828,6.98121694 6.44568303,7.42893219 5.89339828,7.42893219 C5.34111353,7.42893219 4.89339828,6.98121694 4.89339828,6.42893219 L4.89339828,4.42893219 C4.89339828,3.87664744 5.34111353,3.42893219 5.89339828,3.42893219 Z M11.4289322,5.13603897 C11.8194565,5.52656326 11.8194565,6.15972824 11.4289322,6.55025253 L10.0147186,7.96446609 C9.62419433,8.35499039 8.99102936,8.35499039 8.60050506,7.96446609 C8.20998077,7.5739418 8.20998077,6.94077682 8.60050506,6.55025253 L10.0147186,5.13603897 C10.4052429,4.74551468 11.0384079,4.74551468 11.4289322,5.13603897 Z M0.600505063,5.13603897 C0.991029355,4.74551468 1.62419433,4.74551468 2.01471863,5.13603897 L3.42893219,6.55025253 C3.81945648,6.94077682 3.81945648,7.5739418 3.42893219,7.96446609 C3.0384079,8.35499039 2.40524292,8.35499039 2.01471863,7.96446609 L0.600505063,6.55025253 C0.209980772,6.15972824 0.209980772,5.52656326 0.600505063,5.13603897 Z"
                                                fill="#000000" opacity="0.3"
                                                transform="translate(6.014719, 5.843146) rotate(-45.000000) translate(-6.014719, -5.843146)" />
                                            <path
                                                d="M17.9142136,15.4497475 C18.4664983,15.4497475 18.9142136,15.8974627 18.9142136,16.4497475 L18.9142136,18.4497475 C18.9142136,19.0020322 18.4664983,19.4497475 17.9142136,19.4497475 C17.3619288,19.4497475 16.9142136,19.0020322 16.9142136,18.4497475 L16.9142136,16.4497475 C16.9142136,15.8974627 17.3619288,15.4497475 17.9142136,15.4497475 Z M23.4497475,17.1568542 C23.8402718,17.5473785 23.8402718,18.1805435 23.4497475,18.5710678 L22.0355339,19.9852814 C21.6450096,20.3758057 21.0118446,20.3758057 20.6213203,19.9852814 C20.2307961,19.5947571 20.2307961,18.9615921 20.6213203,18.5710678 L22.0355339,17.1568542 C22.4260582,16.76633 23.0592232,16.76633 23.4497475,17.1568542 Z M12.6213203,17.1568542 C13.0118446,16.76633 13.6450096,16.76633 14.0355339,17.1568542 L15.4497475,18.5710678 C15.8402718,18.9615921 15.8402718,19.5947571 15.4497475,19.9852814 C15.0592232,20.3758057 14.4260582,20.3758057 14.0355339,19.9852814 L12.6213203,18.5710678 C12.2307961,18.1805435 12.2307961,17.5473785 12.6213203,17.1568542 Z"
                                                fill="#000000" opacity="0.3"
                                                transform="translate(18.035534, 17.863961) scale(1, -1) rotate(45.000000) translate(-18.035534, -17.863961)" />
                                        </g>
                                    </svg>
                                    <!--end::Svg Icon-->
                                </span>
                            </span>
                            <div class="mr-2 d-flex flex-column flex-grow-1">
                                <a href="#"
                                    class="mb-1 font-weight-normel text-dark-75 text-hover-primary font-size-lg">The
                                    best product</a>
                                <span class="text-muted font-size-sm">Due in 2 Days</span>
                            </div>
                            <span class="py-1 font-weight-bolder text-info font-size-lg">+8%</span>
                        </div>
                        <!--end: Item-->
                    </div>
                    <!--end::Section-->
                </div>
                <!--end::Tabpane-->
                <!--begin::Tabpane-->
                <div class="pt-2 pr-5 tab-pane fade mr-n5" id="kt_quick_panel_notifications" role="tabpanel">
                    <!--begin::Nav-->
                    <div class="navi navi-icon-circle navi-spacer-x-0">
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon-bell text-success icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">5 new user generated report</div>
                                    <div class="text-muted">Reports based on sales</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon2-box text-danger icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">2 new items submited</div>
                                    <div class="text-muted">by Grog John</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon-psd text-primary icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">79 PSD files generated</div>
                                    <div class="text-muted">Reports based on sales</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon2-supermarket text-warning icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">$2900 worth producucts sold</div>
                                    <div class="text-muted">Total 234 items</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon-paper-plane-1 text-success icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">4.5h-avarage response time</div>
                                    <div class="text-muted">Fostest is Barry</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon-safe-shield-protection text-danger icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">3 Defence alerts</div>
                                    <div class="text-muted">40% less alerts thar last week</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon-notepad text-primary icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">Avarage 4 blog posts per author</div>
                                    <div class="text-muted">Most posted 12 time</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon-users-1 text-warning icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">16 authors joined last week</div>
                                    <div class="text-muted">9 photodrapehrs, 7 designer</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon2-box text-info icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">2 new items have been submited</div>
                                    <div class="text-muted">by Grog John</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon2-download text-success icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">2.8 GB-total downloads size</div>
                                    <div class="text-muted">Mostly PSD end AL concepts</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon2-supermarket text-danger icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">$2900 worth producucts sold</div>
                                    <div class="text-muted">Total 234 items</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon-bell text-primary icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">7 new user generated report</div>
                                    <div class="text-muted">Reports based on sales</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <a href="#" class="navi-item">
                            <div class="rounded navi-link">
                                <div class="mr-3 symbol symbol-50">
                                    <div class="symbol-label">
                                        <i class="flaticon-paper-plane-1 text-success icon-lg"></i>
                                    </div>
                                </div>
                                <div class="navi-text">
                                    <div class="font-weight-bold font-size-lg">4.5h-avarage response time</div>
                                    <div class="text-muted">Fostest is Barry</div>
                                </div>
                            </div>
                        </a>
                        <!--end::Item-->
                    </div>
                    <!--end::Nav-->
                </div>
                <!--end::Tabpane-->
                <!--begin::Tabpane-->
                <div class="pt-3 pr-5 tab-pane fade mr-n5" id="kt_quick_panel_settings" role="tabpanel">
                    <form class="form">
                        <!--begin::Section-->
                        <div>
                            <h5 class="mb-3 font-weight-bold">Customer Care</h5>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Enable Notifications:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-success switch-sm">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Enable Case Tracking:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-success switch-sm">
                                        <label>
                                            <input type="checkbox" name="quick_panel_notifications_2" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Support Portal:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-success switch-sm">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!--end::Section-->
                        <div class="my-6 separator separator-dashed"></div>
                        <!--begin::Section-->
                        <div class="pt-2">
                            <h5 class="mb-3 font-weight-bold">Reports</h5>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Generate Reports:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-sm switch-danger">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Enable Report Export:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-sm switch-danger">
                                        <label>
                                            <input type="checkbox" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Allow Data Collection:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-sm switch-danger">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!--end::Section-->
                        <div class="my-6 separator separator-dashed"></div>
                        <!--begin::Section-->
                        <div class="pt-2">
                            <h5 class="mb-3 font-weight-bold">Memebers</h5>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Enable Member singup:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-sm switch-primary">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Allow User Feedbacks:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-sm switch-primary">
                                        <label>
                                            <input type="checkbox" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-0 form-group row align-items-center">
                                <label class="col-8 col-form-label">Enable Customer Portal:</label>
                                <div class="col-4 d-flex justify-content-end">
                                    <span class="switch switch-sm switch-primary">
                                        <label>
                                            <input type="checkbox" checked="checked" name="select" />
                                            <span></span>
                                        </label>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!--end::Section-->
                    </form>
                </div>
                <!--end::Tabpane-->
            </div>
        </div>
        <!--end::Content-->
    </div>
    <!--end::Quick Panel-->
    <!--begin::Chat Panel-->
    <div class="modal modal-sticky modal-sticky-bottom-right" id="kt_chat_modal" role="dialog"
        data-backdrop="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <!--begin::Card-->
                <div class="card card-custom">
                    <!--begin::Header-->
                    <div class="px-4 py-3 card-header align-items-center">
                        <div class="text-left flex-grow-1">
                            <!--begin::Dropdown Menu-->
                            <div class="dropdown dropdown-inline">
                                <button type="button" class="btn btn-clean btn-sm btn-icon btn-icon-md"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="svg-icon svg-icon-lg">
                                        <!--begin::Svg Icon | path:assets/media/svg/icons/Communication/Add-user.svg-->
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                                            height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none"
                                                fill-rule="evenodd">
                                                <polygon points="0 0 24 0 24 24 0 24" />
                                                <path
                                                    d="M18,8 L16,8 C15.4477153,8 15,7.55228475 15,7 C15,6.44771525 15.4477153,6 16,6 L18,6 L18,4 C18,3.44771525 18.4477153,3 19,3 C19.5522847,3 20,3.44771525 20,4 L20,6 L22,6 C22.5522847,6 23,6.44771525 23,7 C23,7.55228475 22.5522847,8 22,8 L20,8 L20,10 C20,10.5522847 19.5522847,11 19,11 C18.4477153,11 18,10.5522847 18,10 L18,8 Z M9,11 C6.790861,11 5,9.209139 5,7 C5,4.790861 6.790861,3 9,3 C11.209139,3 13,4.790861 13,7 C13,9.209139 11.209139,11 9,11 Z"
                                                    fill="#000000" fill-rule="nonzero" opacity="0.3" />
                                                <path
                                                    d="M0.00065168429,20.1992055 C0.388258525,15.4265159 4.26191235,13 8.98334134,13 C13.7712164,13 17.7048837,15.2931929 17.9979143,20.2 C18.0095879,20.3954741 17.9979143,21 17.2466999,21 C13.541124,21 8.03472472,21 0.727502227,21 C0.476712155,21 -0.0204617505,20.45918 0.00065168429,20.1992055 Z"
                                                    fill="#000000" fill-rule="nonzero" />
                                            </g>
                                        </svg>
                                        <!--end::Svg Icon-->
                                    </span>
                                </button>
                                <div class="p-0 m-0 dropdown-menu dropdown-menu-right dropdown-menu-md">
                                    <!--begin::Navigation-->
                                    <ul class="py-5 navi navi-hover">
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <i class="flaticon2-drop"></i>
                                                </span>
                                                <span class="navi-text">New Group</span>
                                            </a>
                                        </li>
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <i class="flaticon2-list-3"></i>
                                                </span>
                                                <span class="navi-text">Contacts</span>
                                            </a>
                                        </li>
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <i class="flaticon2-rocket-1"></i>
                                                </span>
                                                <span class="navi-text">Groups</span>
                                                <span class="navi-link-badge">
                                                    <span
                                                        class="label label-light-primary label-inline font-weight-bold">new</span>
                                                </span>
                                            </a>
                                        </li>
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <i class="flaticon2-bell-2"></i>
                                                </span>
                                                <span class="navi-text">Calls</span>
                                            </a>
                                        </li>
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <i class="flaticon2-gear"></i>
                                                </span>
                                                <span class="navi-text">Settings</span>
                                            </a>
                                        </li>
                                        <li class="my-3 navi-separator"></li>
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <i class="flaticon2-magnifier-tool"></i>
                                                </span>
                                                <span class="navi-text">Help</span>
                                            </a>
                                        </li>
                                        <li class="navi-item">
                                            <a href="#" class="navi-link">
                                                <span class="navi-icon">
                                                    <i class="flaticon2-bell-2"></i>
                                                </span>
                                                <span class="navi-text">Privacy</span>
                                                <span class="navi-link-badge">
                                                    <span
                                                        class="label label-light-danger label-rounded font-weight-bold">5</span>
                                                </span>
                                            </a>
                                        </li>
                                    </ul>
                                    <!--end::Navigation-->
                                </div>
                            </div>
                            <!--end::Dropdown Menu-->
                        </div>
                        <div class="text-center flex-grow-1">
                            <div class="text-dark-75 font-weight-bold font-size-h5">Matt Pears</div>
                            <div>
                                <span class="label label-dot label-success"></span>
                                <span class="font-weight-bold text-muted font-size-sm">Active</span>
                            </div>
                        </div>
                        <div class="text-right flex-grow-1">
                            <button type="button" class="btn btn-clean btn-sm btn-icon btn-icon-md"
                                data-dismiss="modal">
                                <i class="ki ki-close icon-1x"></i>
                            </button>
                        </div>
                    </div>
                    <!--end::Header-->
                    <!--begin::Body-->
                    <div class="card-body">
                        <!--begin::Scroll-->
                        <div class="scroll scroll-pull" data-height="375" data-mobile-height="300">
                            <!--begin::Messages-->
                            <div class="messages">
                                <!--begin::Message In-->
                                <div class="mb-5 d-flex flex-column align-items-start">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 symbol symbol-circle symbol-40">
                                            <img alt="Pic" src="assets/media/users/300_12.jpg" />
                                        </div>
                                        <div>
                                            <a href="#"
                                                class="text-dark-75 text-hover-primary font-weight-bold font-size-h6">Matt
                                                Pears</a>
                                            <span class="text-muted font-size-sm">2 Hours</span>
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 mt-2 text-left rounded bg-light-success text-dark-50 font-weight-bold font-size-lg max-w-400px">
                                        How likely are you to recommend our company to your friends and family?</div>
                                </div>
                                <!--end::Message In-->
                                <!--begin::Message Out-->
                                <div class="mb-5 d-flex flex-column align-items-end">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="text-muted font-size-sm">3 minutes</span>
                                            <a href="#"
                                                class="text-dark-75 text-hover-primary font-weight-bold font-size-h6">You</a>
                                        </div>
                                        <div class="ml-3 symbol symbol-circle symbol-40">
                                            <img alt="Pic" src="assets/media/users/300_21.jpg" />
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 mt-2 text-right rounded bg-light-primary text-dark-50 font-weight-bold font-size-lg max-w-400px">
                                        Hey there, we’re just writing to let you know that you’ve been subscribed to a
                                        repository on GitHub.</div>
                                </div>
                                <!--end::Message Out-->
                                <!--begin::Message In-->
                                <div class="mb-5 d-flex flex-column align-items-start">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 symbol symbol-circle symbol-40">
                                            <img alt="Pic" src="assets/media/users/300_21.jpg" />
                                        </div>
                                        <div>
                                            <a href="#"
                                                class="text-dark-75 text-hover-primary font-weight-bold font-size-h6">Matt
                                                Pears</a>
                                            <span class="text-muted font-size-sm">40 seconds</span>
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 mt-2 text-left rounded bg-light-success text-dark-50 font-weight-bold font-size-lg max-w-400px">
                                        Ok, Understood!</div>
                                </div>
                                <!--end::Message In-->
                                <!--begin::Message Out-->
                                <div class="mb-5 d-flex flex-column align-items-end">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="text-muted font-size-sm">Just now</span>
                                            <a href="#"
                                                class="text-dark-75 text-hover-primary font-weight-bold font-size-h6">You</a>
                                        </div>
                                        <div class="ml-3 symbol symbol-circle symbol-40">
                                            <img alt="Pic" src="assets/media/users/300_21.jpg" />
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 mt-2 text-right rounded bg-light-primary text-dark-50 font-weight-bold font-size-lg max-w-400px">
                                        You’ll receive notifications for all issues, pull requests!</div>
                                </div>
                                <!--end::Message Out-->
                                <!--begin::Message In-->
                                <div class="mb-5 d-flex flex-column align-items-start">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 symbol symbol-circle symbol-40">
                                            <img alt="Pic" src="assets/media/users/300_12.jpg" />
                                        </div>
                                        <div>
                                            <a href="#"
                                                class="text-dark-75 text-hover-primary font-weight-bold font-size-h6">Matt
                                                Pears</a>
                                            <span class="text-muted font-size-sm">40 seconds</span>
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 mt-2 text-left rounded bg-light-success text-dark-50 font-weight-bold font-size-lg max-w-400px">
                                        You can unwatch this repository immediately by clicking here:
                                        <a href="#">https://github.com</a>
                                    </div>
                                </div>
                                <!--end::Message In-->
                                <!--begin::Message Out-->
                                <div class="mb-5 d-flex flex-column align-items-end">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="text-muted font-size-sm">Just now</span>
                                            <a href="#"
                                                class="text-dark-75 text-hover-primary font-weight-bold font-size-h6">You</a>
                                        </div>
                                        <div class="ml-3 symbol symbol-circle symbol-40">
                                            <img alt="Pic" src="assets/media/users/300_21.jpg" />
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 mt-2 text-right rounded bg-light-primary text-dark-50 font-weight-bold font-size-lg max-w-400px">
                                        Discover what students who viewed Learn Figma - UI/UX Design. Essential Training
                                        also viewed</div>
                                </div>
                                <!--end::Message Out-->
                                <!--begin::Message In-->
                                <div class="mb-5 d-flex flex-column align-items-start">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3 symbol symbol-circle symbol-40">
                                            <img alt="Pic" src="assets/media/users/300_12.jpg" />
                                        </div>
                                        <div>
                                            <a href="#"
                                                class="text-dark-75 text-hover-primary font-weight-bold font-size-h6">Matt
                                                Pears</a>
                                            <span class="text-muted font-size-sm">40 seconds</span>
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 mt-2 text-left rounded bg-light-success text-dark-50 font-weight-bold font-size-lg max-w-400px">
                                        Most purchased Business courses during this sale!</div>
                                </div>
                                <!--end::Message In-->
                                <!--begin::Message Out-->
                                <div class="mb-5 d-flex flex-column align-items-end">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <span class="text-muted font-size-sm">Just now</span>
                                            <a href="#"
                                                class="text-dark-75 text-hover-primary font-weight-bold font-size-h6">You</a>
                                        </div>
                                        <div class="ml-3 symbol symbol-circle symbol-40">
                                            <img alt="Pic" src="assets/media/users/300_21.jpg" />
                                        </div>
                                    </div>
                                    <div
                                        class="p-5 mt-2 text-right rounded bg-light-primary text-dark-50 font-weight-bold font-size-lg max-w-400px">
                                        Company BBQ to celebrate the last quater achievements and goals. Food and drinks
                                        provided</div>
                                </div>
                                <!--end::Message Out-->
                            </div>
                            <!--end::Messages-->
                        </div>
                        <!--end::Scroll-->
                    </div>
                    <!--end::Body-->
                    <!--begin::Footer-->
                    <div class="card-footer align-items-center">
                        <!--begin::Compose-->
                        <textarea class="p-0 border-0 form-control" rows="2" placeholder="Type a message"></textarea>
                        <div class="mt-5 d-flex align-items-center justify-content-between">
                            <div class="mr-3">
                                <a href="#" class="mr-1 btn btn-clean btn-icon btn-md">
                                    <i class="flaticon2-photograph icon-lg"></i>
                                </a>
                                <a href="#" class="btn btn-clean btn-icon btn-md">
                                    <i class="flaticon2-photo-camera icon-lg"></i>
                                </a>
                            </div>
                            <div>
                                <button type="button"
                                    class="px-6 py-2 btn btn-primary btn-md text-uppercase font-weight-bold chat-send">Send</button>
                            </div>
                        </div>
                        <!--begin::Compose-->
                    </div>
                    <!--end::Footer-->
                </div>
                <!--end::Card-->
            </div>
        </div>
    </div>
    <!--end::Chat Panel-->
    <!--begin::Scrolltop-->
    <div id="kt_scrolltop" class="scrolltop">
        <span class="svg-icon">
            <!--begin::Svg Icon | path:assets/media/svg/icons/Navigation/Up-2.svg-->
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px"
                height="24px" viewBox="0 0 24 24" version="1.1">
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                    <polygon points="0 0 24 0 24 24 0 24" />
                    <rect fill="#000000" opacity="0.3" x="11" y="10" width="2" height="10"
                        rx="1" />
                    <path
                        d="M6.70710678,12.7071068 C6.31658249,13.0976311 5.68341751,13.0976311 5.29289322,12.7071068 C4.90236893,12.3165825 4.90236893,11.6834175 5.29289322,11.2928932 L11.2928932,5.29289322 C11.6714722,4.91431428 12.2810586,4.90106866 12.6757246,5.26284586 L18.6757246,10.7628459 C19.0828436,11.1360383 19.1103465,11.7686056 18.7371541,12.1757246 C18.3639617,12.5828436 17.7313944,12.6103465 17.3242754,12.2371541 L12.0300757,7.38413782 L6.70710678,12.7071068 Z"
                        fill="#000000" fill-rule="nonzero" />
                </g>
            </svg>
            <!--end::Svg Icon-->
        </span>
    </div>


    <!--end::Demo Panel-->
    <script>
        var HOST_URL = "https://preview.keenthemes.com/metronic/theme/html/tools/preview";
    </script>
    <!--begin::Global Config(global config for global JS scripts)-->
    <script>
        var KTAppSettings = {
            "breakpoints": {
                "sm": 576,
                "md": 768,
                "lg": 992,
                "xl": 1200,
                "xxl": 1400
            },
            "colors": {
                "theme": {
                    "base": {
                        "white": "#ffffff",
                        "primary": "#3699FF",
                        "secondary": "#E5EAEE",
                        "success": "#1BC5BD",
                        "info": "#8950FC",
                        "warning": "#FFA800",
                        "danger": "#F64E60",
                        "light": "#E4E6EF",
                        "dark": "#181C32"
                    },
                    "light": {
                        "white": "#ffffff",
                        "primary": "#E1F0FF",
                        "secondary": "#EBEDF3",
                        "success": "#C9F7F5",
                        "info": "#EEE5FF",
                        "warning": "#FFF4DE",
                        "danger": "#FFE2E5",
                        "light": "#F3F6F9",
                        "dark": "#D6D6E0"
                    },
                    "inverse": {
                        "white": "#ffffff",
                        "primary": "#ffffff",
                        "secondary": "#3F4254",
                        "success": "#ffffff",
                        "info": "#ffffff",
                        "warning": "#ffffff",
                        "danger": "#ffffff",
                        "light": "#464E5F",
                        "dark": "#ffffff"
                    }
                },
                "gray": {
                    "gray-100": "#F3F6F9",
                    "gray-200": "#EBEDF3",
                    "gray-300": "#E4E6EF",
                    "gray-400": "#D1D3E0",
                    "gray-500": "#B5B5C3",
                    "gray-600": "#7E8299",
                    "gray-700": "#5E6278",
                    "gray-800": "#3F4254",
                    "gray-900": "#181C32"
                }
            },
            "font-family": "Poppins"
        };
    </script>
    <!--end::Global Config-->
    <!--begin::Global Theme Bundle(used by all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <!--end::Global Theme Bundle-->
    <!--begin::Page Vendors(used by this page)-->
    <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
    <!--end::Page Vendors-->
    <!--begin::Page Scripts(used by this page)-->
    <script src="{{ asset('assets/js/pages/widgets.js') }}"></script>



    @if (session('success'))
        <script>
            toastr.success("{{ session('success') }}");
        </script>
    @endif
    @if (session('error'))
        <script>
            toastr.error("{{ session('error') }}");
        </script>
    @endif

    @if (session('warning'))
        <script>
            toastr.warning("{{ session('warning') }}");
        </script>
    @endif

    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <script>
                toastr.error("{{ $error }}");
            </script>
        @endforeach
    @endif

    <script>
        function copyToClipboard() {
            const refLinkElement = document.getElementById("refLink");
            const tempInput = document.createElement("input");
            tempInput.value = refLinkElement.textContent.trim();
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            const toast = document.getElementById("toastMessage");
            toast.textContent = "Referral Link Copied!";
            toast.style.display = "block";
            setTimeout(() => { toast.style.display = "none"; }, 2000);
        }

        function copySavingLinkToClipboard() {
            const refLinkElement = document.getElementById("refLinkSaving");
            const tempInput = document.createElement("input");
            tempInput.value = refLinkElement.textContent.trim();
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            const toast = document.getElementById("toastMessage");
            toast.textContent = "Savings Program Link Copied!";
            toast.style.display = "block";
            setTimeout(() => { toast.style.display = "none"; }, 2000);
        }
    </script>
    @section('page_js')
    @show
    <!--end::Page Scripts-->

<!-- ═══════════════════════════════════════════════════════════
     INVESTMENT CALCULATOR MODAL
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="calcModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:520px;">
        <div class="modal-content" style="border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 12px 40px rgba(0,0,0,0.12);">

            <!-- Header -->
            <div class="border-0 modal-header" style="padding:1.1rem 1.4rem 0;">
                <div>
                    <h6 class="mb-0 modal-title" style="font-size:.97rem; font-weight:700; color:#111827;">
                        <i class="mr-2 fas fa-calculator" style="color:#6366f1;"></i> Investment Calculator
                    </h6>
                    <p class="mt-1 mb-0" style="font-size:.75rem; color:#9ca3af;">25-Month Saving Plan</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#9ca3af; opacity:1; margin-top:-8px;"><span>&times;</span></button>
            </div>

            <div class="modal-body" style="padding:1.2rem 1.4rem;">

                <!-- Input row -->
                <div style="display:flex; gap:.75rem; align-items:flex-end; margin-bottom:1rem;">
                    <div style="flex:1;">
                        <label style="font-size:.72rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:.35rem;">
                            Invest Amount (Rs.)
                        </label>
                        <input type="number" id="calcAmount" min="1" placeholder="e.g. 4000"
                               style="width:100%; padding:.5rem .75rem; border:1.5px solid #e5e7eb; border-radius:7px; font-size:.93rem; color:#111827; outline:none; transition:border .15s;"
                               onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='#e5e7eb'">
                    </div>
                    <button onclick="calcRun()" id="calcBtn"
                            style="padding:.5rem 1.1rem; background:#6366f1; color:#fff; border:none; border-radius:7px; font-size:.85rem; font-weight:600; cursor:pointer; white-space:nowrap; transition:background .15s;"
                            onmouseover="this.style.background='#4f46e5'" onmouseout="this.style.background='#6366f1'">
                        Calculate
                    </button>
                </div>

                <!-- Options -->
                <div style="display:flex; gap:1.2rem; margin-bottom:1.1rem; flex-wrap:wrap;">
                    <label style="display:flex; align-items:center; gap:.4rem; font-size:.82rem; color:#374151; cursor:pointer;">
                        <input type="checkbox" id="calcAdb" onchange="calcRun()" style="accent-color:#6366f1; width:14px; height:14px;">
                        ADB Option <span style="color:#9ca3af; font-size:.75rem;">(Rs. 3/1000)</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:.4rem; font-size:.82rem; color:#374151; cursor:pointer;">
                        <input type="checkbox" id="calcFisp" onchange="calcRun()" style="accent-color:#6366f1; width:14px; height:14px;">
                        FISP Option <span style="color:#9ca3af; font-size:.75rem;">(Rs. 4/1000)</span>
                    </label>
                </div>

                <!-- Results -->
                <div id="calcResults" style="display:none;">
                    <div style="border-top:1px solid #f3f4f6; padding-top:1rem; margin-bottom:1rem;">

                        <!-- Summary cards row -->
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:.6rem; margin-bottom:.75rem;">
                            <div style="background:#f5f3ff; border-radius:8px; padding:.7rem .9rem;">
                                <div style="font-size:.7rem; color:#7c3aed; font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:.2rem;">Sum Assured</div>
                                <div id="rSumAssured" style="font-size:1.05rem; font-weight:700; color:#1e1b4b;"></div>
                            </div>
                            <div style="background:#f0fdf4; border-radius:8px; padding:.7rem .9rem;">
                                <div style="font-size:.7rem; color:#15803d; font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:.2rem;">Maturity</div>
                                <div id="rMaturity" style="font-size:1.05rem; font-weight:700; color:#14532d;"></div>
                            </div>
                            <div style="background:#eff6ff; border-radius:8px; padding:.7rem .9rem;">
                                <div style="font-size:.7rem; color:#1d4ed8; font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:.2rem;">Natural Death Benefit</div>
                                <div id="rNDB" style="font-size:1.05rem; font-weight:700; color:#1e3a8a;"></div>
                            </div>
                            <div style="background:#fef9c3; border-radius:8px; padding:.7rem .9rem;">
                                <div style="font-size:.7rem; color:#a16207; font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:.2rem;">Total Return</div>
                                <div id="rTotal" style="font-size:1.05rem; font-weight:700; color:#713f12;"></div>
                            </div>
                        </div>

                        <!-- Breakdown -->
                        <div style="background:#fafafa; border-radius:8px; padding:.7rem .9rem; font-size:.8rem;">
                            <div style="display:flex; justify-content:space-between; padding:.25rem 0; border-bottom:1px solid #f0f0f0;">
                                <span style="color:#6b7280;">Invest Amount</span>
                                <span id="rInvest" style="font-weight:600; color:#111827;"></span>
                            </div>
                            <div style="display:flex; justify-content:space-between; padding:.25rem 0; border-bottom:1px solid #f0f0f0;">
                                <span style="color:#6b7280;">Processing Fee</span>
                                <span style="font-weight:600; color:#111827;">$5</span>
                            </div>
                            <div id="rAdbRow" style="display:none; justify-content:space-between; padding:.25rem 0; border-bottom:1px solid #f0f0f0;">
                                <span style="color:#6b7280;">ADB Charge (Rs. 3/1000)</span>
                                <span id="rAdbVal" style="font-weight:600; color:#111827;"></span>
                            </div>
                            <div id="rFispRow" style="display:none; justify-content:space-between; padding:.25rem 0;">
                                <span style="color:#6b7280;">FISP Charge (Rs. 4/1000)</span>
                                <span id="rFispVal" style="font-weight:600; color:#111827;"></span>
                            </div>
                        </div>
                    </div>

                    <!-- View Schedule button -->
                    <div style="text-align:center;">
                        <button onclick="calcShowSchedule()"
                                style="width:100%; padding:.55rem; background:#fff; color:#6366f1; border:1.5px solid #6366f1; border-radius:7px; font-size:.85rem; font-weight:600; cursor:pointer; transition:all .15s;"
                                onmouseover="this.style.background='#f5f3ff'" onmouseout="this.style.background='#fff'">
                            <i class="mr-1 fas fa-table"></i> View 25-Month Payment Schedule
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     PAYMENT SCHEDULE MODAL
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="calcScheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:min(96vw,680px);">
        <div class="modal-content" style="border:1px solid #e5e7eb; border-radius:12px; overflow:hidden; box-shadow:0 12px 40px rgba(0,0,0,0.12);">

            <div class="border-0 modal-header" style="padding:1.1rem 1.4rem .5rem; display:flex; align-items:flex-start; justify-content:space-between;">
                <div>
                    <h6 class="mb-0 modal-title" style="font-size:.97rem; font-weight:700; color:#111827;">
                        <i class="mr-2 fas fa-calendar-alt" style="color:#6366f1;"></i> 25-Month Payment Schedule
                    </h6>
                    <p id="schedSubtitle" class="mt-1 mb-0" style="font-size:.75rem; color:#9ca3af;"></p>
                </div>
                <button type="button" class="close" data-dismiss="modal" style="color:#9ca3af; opacity:1; margin-top:-4px;"><span>&times;</span></button>
            </div>

            <div class="p-0 modal-body">
                <div style="overflow-x:auto; max-height:62vh; overflow-y:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:.87rem;">
                        <thead>
                            <tr style="background:#f9fafb; position:sticky; top:0; z-index:2;">
                                <th style="padding:.65rem 1rem; font-size:.72rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:center; width:60px;">Month</th>
                                <th style="padding:.65rem 1rem; font-size:.72rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:right;">Instalment</th>
                                <th style="padding:.65rem 1rem; font-size:.72rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:right;">Extra Charges</th>
                                <th style="padding:.65rem 1rem; font-size:.72rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e5e7eb; text-align:right;">Total Payable</th>
                            </tr>
                        </thead>
                        <tbody id="schedBody"></tbody>
                    </table>
                </div>
                <!-- Footer totals -->
                <div id="schedFooter" style="padding:.75rem 1.1rem; background:#fafafa; border-top:1px solid #e5e7eb; font-size:.82rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.5rem;">
                </div>
            </div>

            <div class="border-0 modal-footer" style="padding:.75rem 1.4rem;">
                <button type="button" data-dismiss="modal"
                        style="padding:.4rem .9rem; font-size:.82rem; border-radius:7px; border:1px solid #e5e7eb; background:#fff; color:#374151; cursor:pointer;">
                    Close
                </button>
                <button onclick="$('#calcScheduleModal').modal('hide'); $('#calcModal').modal('show');"
                        style="padding:.4rem .9rem; font-size:.82rem; border-radius:7px; border:none; background:#6366f1; color:#fff; cursor:pointer;">
                    <i class="mr-1 fas fa-arrow-left"></i> Back to Calculator
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var _amount = 0, _adb = false, _fisp = false;

    function fmt(n) {
        return 'Rs. ' + Number(n).toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    window.calcRun = function () {
        var raw = parseFloat(document.getElementById('calcAmount').value);
        if (!raw || raw <= 0) return;

        _amount = raw;
        _adb  = document.getElementById('calcAdb').checked;
        _fisp = document.getElementById('calcFisp').checked;

        var sumAssured = raw * 25;
        var maturity   = sumAssured * 0.5;
        var totalRet   = sumAssured + maturity;
        var ndb        = raw * 25;
        var adbCharge  = _adb  ? (raw / 1000) * 3 : 0;
        var fispCharge = _fisp ? (raw / 1000) * 4 : 0;

        document.getElementById('rSumAssured').textContent = fmt(sumAssured);
        document.getElementById('rMaturity').textContent   = fmt(maturity);
        document.getElementById('rTotal').textContent      = fmt(totalRet);
        document.getElementById('rNDB').textContent        = fmt(ndb);
        document.getElementById('rInvest').textContent     = fmt(raw);

        var adbRow  = document.getElementById('rAdbRow');
        var fispRow = document.getElementById('rFispRow');

        if (_adb) {
            adbRow.style.display = 'flex';
            document.getElementById('rAdbVal').textContent = fmt(adbCharge);
        } else {
            adbRow.style.display = 'none';
        }
        if (_fisp) {
            fispRow.style.display = 'flex';
            document.getElementById('rFispVal').textContent = fmt(fispCharge);
        } else {
            fispRow.style.display = 'none';
        }

        document.getElementById('calcResults').style.display = 'block';
    };

    window.calcShowSchedule = function () {
        if (_amount <= 0) return;

        var adbCharge  = _adb  ? (_amount / 1000) * 3 : 0;
        var fispCharge = _fisp ? (_amount / 1000) * 4 : 0;
        var processingFee = 5;   // USD — shown as note
        var extraMonth1 = adbCharge + fispCharge;

        var subtitle = 'Monthly instalment: ' + fmt(_amount);
        if (_adb || _fisp) {
            var opts = [];
            if (_adb)  opts.push('ADB ' + fmt(adbCharge));
            if (_fisp) opts.push('FISP ' + fmt(fispCharge));
            subtitle += ' · ' + opts.join(', ') + ' (Month 1 only)';
        }
        document.getElementById('schedSubtitle').textContent = subtitle;

        var tbody = document.getElementById('schedBody');
        tbody.innerHTML = '';

        var grandTotal = 0;

        for (var m = 1; m <= 25; m++) {
            var instalment = _amount;
            var extra      = 0;
            var extraLabel = '—';

            if (m === 1) {
                extra = extraMonth1;
                var parts = [];
                if (processingFee) parts.push('Processing $' + processingFee);
                if (_adb)  parts.push('ADB ' + fmt(adbCharge));
                if (_fisp) parts.push('FISP ' + fmt(fispCharge));
                extraLabel = parts.length ? parts.join(' + ') : '—';
            }

            var rowTotal = instalment + extra;
            grandTotal  += rowTotal;

            var isFirst = m === 1;
            var rowBg   = isFirst ? '#f5f3ff' : (m % 2 === 0 ? '#fafafa' : '#fff');

            var tr = document.createElement('tr');
            tr.style.background = rowBg;

            tr.innerHTML =
                '<td style="padding:.6rem 1rem; border-bottom:1px solid #f3f4f6; text-align:center; font-weight:' + (isFirst ? '700' : '400') + '; color:' + (isFirst ? '#4f46e5' : '#6b7280') + ';">' + m + '</td>' +
                '<td style="padding:.6rem 1rem; border-bottom:1px solid #f3f4f6; text-align:right; color:#111827; font-weight:500;">' + fmt(instalment) + '</td>' +
                '<td style="padding:.6rem 1rem; border-bottom:1px solid #f3f4f6; text-align:right; font-size:.78rem; color:' + (isFirst && extra > 0 ? '#7c3aed' : '#9ca3af') + ';">' +
                    (isFirst && extra > 0 ? extraLabel : '—') +
                '</td>' +
                '<td style="padding:.6rem 1rem; border-bottom:1px solid #f3f4f6; text-align:right; font-weight:' + (isFirst ? '700' : '600') + '; color:#111827;">' + fmt(rowTotal) + '</td>';

            tbody.appendChild(tr);
        }

        // Footer
        var footer = document.getElementById('schedFooter');
        footer.innerHTML =
            '<span style="color:#6b7280;">25 Instalments · Processing fee ($5) in Month 1</span>' +
            '<span style="font-weight:700; color:#111827;">Grand Total: ' + fmt(grandTotal) + ' <span style="font-size:.74rem; color:#9ca3af; font-weight:400;">(+ $5 fee)</span></span>';

        $('#calcModal').modal('hide');
        $('#calcScheduleModal').modal('show');
    };

    // Allow Enter key to trigger calculate
    document.getElementById('calcAmount').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') calcRun();
    });

    // Reset on modal close
    document.getElementById('calcModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('calcResults').style.display = 'none';
        document.getElementById('calcAmount').value = '';
        document.getElementById('calcAdb').checked  = false;
        document.getElementById('calcFisp').checked = false;
        _amount = 0; _adb = false; _fisp = false;
    });
})();
</script>
</body>
<!--end::Body-->

</html>
