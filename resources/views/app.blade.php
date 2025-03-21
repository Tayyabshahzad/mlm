<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <base href="">
    <meta charset="utf-8" />
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="Metronic admin dashboard live demo. Check out all the features of the admin panel. A large number of settings, additional services and widgets." />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="canonical" href="https://keenthemes.com/metronic" /> 
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" /> 
    
    <!-- Metronic CSS -->
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" /> 
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" /> 
    <link href="{{ asset('assets/css/themes/layout/header/base/light.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/themes/layout/header/menu/light.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/themes/layout/brand/dark.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/themes/layout/aside/dark.css') }}" rel="stylesheet" type="text/css" /> 
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />

    <!-- Inertia and Routes -->
    @routes
    @inertiaHead
</head>
<body id="kt_body" class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">
    <!-- Metronic Configuration -->
    <script>
        var HOST_URL = "https://preview.keenthemes.com/metronic/theme/html/tools/preview";
        var KTAppSettings = { 
            "breakpoints": { "sm": 576, "md": 768, "lg": 992, "xl": 1200, "xxl": 1400 },
            "colors": { 
                "theme": { 
                    "base": { 
                        "white": "#ffffff", "primary": "#3699FF", "secondary": "#E5EAEE",
                        "success": "#1BC5BD", "info": "#8950FC", "warning": "#FFA800",
                        "danger": "#F64E60", "light": "#E4E6EF", "dark": "#181C32" 
                    },
                    "light": {
                        "white": "#ffffff", "primary": "#E1F0FF", "secondary": "#EBEDF3",
                        "success": "#C9F7F5", "info": "#EEE5FF", "warning": "#FFF4DE",
                        "danger": "#FFE2E5", "light": "#F3F6F9", "dark": "#D6D6E0"
                    },
                    "inverse": {
                        "white": "#ffffff", "primary": "#ffffff", "secondary": "#3F4254",
                        "success": "#ffffff", "info": "#ffffff", "warning": "#ffffff",
                        "danger": "#ffffff", "light": "#464E5F", "dark": "#ffffff"
                    }
                },
                "gray": {
                    "gray-100": "#F3F6F9", "gray-200": "#EBEDF3", "gray-300": "#E4E6EF",
                    "gray-400": "#D1D3E0", "gray-500": "#B5B5C3", "gray-600": "#7E8299",
                    "gray-700": "#5E6278", "gray-800": "#3F4254", "gray-900": "#181C32"
                }
            },
            "font-family": "Poppins"
        };
    </script>

    <!-- Metronic Core Scripts -->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <!-- Inertia App -->
    @vite(['resources/js/app.js'])
    @inertia
</body>
</html>