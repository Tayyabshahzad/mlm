<!DOCTYPE html>
 
<html lang="en">
	<!--begin::Head-->
	<head> 
		<meta charset="utf-8" />
		<title>Global Visioners International | @yield('title') </title>
		<meta name="description" content="Login page example" />
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
		<link rel="canonical" href="https://keenthemes.com/metronic" />
		<!--begin::Fonts-->
		{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" /> --}}
		<!--end::Fonts-->
		<!--begin::Page Custom Styles(used by this page)-->
		<link href="{{ asset('assets/css/pages/login/login-1.css')}}" rel="stylesheet" type="text/css" />
		<!--end::Page Custom Styles-->
		<!--begin::Global Theme Styles(used by all pages)-->
		<link href="{{ asset('assets/plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
		<!--end::Global Theme Styles-->
		<!--begin::Layout Themes(used by all pages)-->
		<link href="{{ asset('assets/css/themes/layout/header/base/light.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/css/themes/layout/header/menu/light.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/css/themes/layout/brand/dark.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/css/themes/layout/aside/dark.css')}}" rel="stylesheet" type="text/css" />
		
		<script src="https://cdnjs.cloudflare.com/ajax/libs/gojs/2.3.9/go.js"></script> 
		<!--end::Layout Themes-->
		<link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico')}}" /> 
		{{-- <link rel="stylesheet" href="{{ asset('assets/custom-css/flag.css') }}" rel="stylesheet" type="text/css" >  --}}

	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" class="header-fixed header-mobile-fixed subheader-enabled subheader-fixed aside-enabled aside-fixed aside-minimize-hoverable page-loading">
		<!--begin::Main-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Login-->
			<div class="login login-1 login-signin-on d-flex flex-column flex-lg-row flex-column-fluid bg-white" id="kt_login">
				<!--begin::Aside-->
				 
				<!--begin::Aside-->
				<!--begin::Content-->
				<div class="login-content flex-row-fluid d-flex flex-column justify-content-center position-relative overflow-hidden p-7 mx-auto">
					<!--begin::Content body-->
					<div class="d-flex flex-column-fluid flex-center">
						<!--begin::Signin-->
						
						<!--end::Signin-->
						<!--begin::Signup-->
						@section('content')
						@show
						
						<!--end::Forgot-->
					</div>
					<!--end::Content body-->
					<!--begin::Content footer-->
					 
					<!--end::Content footer-->
				</div>
				<!--end::Content-->
			</div>
			<!--end::Login-->
		</div>

		 
		 


		<!--end::Main-->
		<script>var HOST_URL = "https://preview.keenthemes.com/metronic/theme/html/tools/preview";</script>
		<!--begin::Global Config(global config for global JS scripts)-->
		<script>var KTAppSettings = { "breakpoints": { "sm": 576, "md": 768, "lg": 992, "xl": 1200, "xxl": 1400 }, "colors": { "theme": { "base": { "white": "#ffffff", "primary": "#3699FF", "secondary": "#E5EAEE", "success": "#1BC5BD", "info": "#8950FC", "warning": "#FFA800", "danger": "#F64E60", "light": "#E4E6EF", "dark": "#181C32" }, "light": { "white": "#ffffff", "primary": "#E1F0FF", "secondary": "#EBEDF3", "success": "#C9F7F5", "info": "#EEE5FF", "warning": "#FFF4DE", "danger": "#FFE2E5", "light": "#F3F6F9", "dark": "#D6D6E0" }, "inverse": { "white": "#ffffff", "primary": "#ffffff", "secondary": "#3F4254", "success": "#ffffff", "info": "#ffffff", "warning": "#ffffff", "danger": "#ffffff", "light": "#464E5F", "dark": "#ffffff" } }, "gray": { "gray-100": "#F3F6F9", "gray-200": "#EBEDF3", "gray-300": "#E4E6EF", "gray-400": "#D1D3E0", "gray-500": "#B5B5C3", "gray-600": "#7E8299", "gray-700": "#5E6278", "gray-800": "#3F4254", "gray-900": "#181C32" } }, "font-family": "Poppins" };</script>
		<!--end::Global Config-->
		<!--begin::Global Theme Bundle(used by all pages)-->
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js')}}"></script>
		<script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js')}}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js')}}"></script>
		<!--end::Global Theme Bundle-->
		<!--begin::Page Scripts(used by this page)-->
		<script src="{{ asset('assets/js/pages/custom/login/login-general.js')}}"></script>
		<!--end::Page Scripts-->
		@if ($errors->any()) 
			@foreach ($errors->all() as $error) 
				<script>
					toastr.error("{{ $error }}");
				</script> 
			@endforeach 
		@endif

		<script>
			const countryCodeDropdown = document.getElementById("countryCode");
			const phoneInput = document.getElementById("phone");
			 
			countryCodeDropdown.addEventListener("change", function () {
				const selectedCode = countryCodeDropdown.value;
				console.log("Selected Country Code:", selectedCode);
			});
		</script>

		@section('page_js')
		 
		
		
		@show
	</body>

	
	<!--end::Body-->
</html>