@extends('demo.layout.guest')
@section('title','Login')
@section('content')
<div class="login-form login-signin">
	<!--begin::Form-->

	<div class="pb-13 pt-lg-0 pt-5 text-center">
		<img src="{{ asset('assets/custom-images/gvi-text.png') }}" class="max-h-80px" style="width: 90%" alt="" />
	</div>


	
	<form class="form"  method="post" action="{{ route('login') }}" id=" "> 
		@csrf
		<div class="pb-13 pt-lg-0 pt-5">
			<h3 class="font-weight-bolder text-dark font-size-h4 font-size-h1-lg">Welcome to GV International</h3> 
		</div>
		<!--begin::Title-->
		<!--begin::Form group-->
		<div class="form-group">
			<label class="font-size-h6 font-weight-bolder text-dark">Email / Username</label>
			<input class="form-control form-control-solid h-auto py-5 px-6 rounded-md" type="text" name="login" autocomplete="off"  required/>
			@error('login')
            	<div class="text-danger">{{ $message }}</div>
            @enderror
		</div>
		<!--end::Form group-->
		<!--begin::Form group-->
		<div class="form-group">
			<div class="d-flex justify-content-between mt-n5">
				<label class="font-size-h6 font-weight-bolder text-dark pt-5">Password</label> 
			</div>
			<input class="form-control form-control-solid h-auto py-5 px-6 rounded-md" type="password" name="password" autocomplete="off"  required/>
			@error('password')
            	<div class="text-danger">{{ $message }}</div>
            @enderror
		</div>
		<!--end::Form group-->
		<!--begin::Action-->
		<div class="pb-lg-0 pb-5">
			<button type="submit" id="kt_login_signin_submit" class="rounded-0 btn btn-primary  py-4 px-8   btn-sm">Sign In</button> 
			<a href="{{ route('register') }}" class="rounded-0 t-weight-bolder font-size-h6 px-8 py-4 my-3 mr-3 btn-sm">   Register </a>
		</div>
		<!--end::Action-->
	</form>
	<!--end::Form-->
</div>
@endsection
@section('page_js')
    <script> 

    </script>
    
@endsection