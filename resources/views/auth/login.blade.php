@extends('demo.layout.guest')
@section('title','Login')
@section('content')
	<div class="login-signups container">
		<div class="mb-20">
			<h3 class=" font-weight-normal">Sign In</h3>
			<p class="">Enter your details to login</p>
		</div>
		<form class="form text-center row pl-40 pr-40" id="kt_login_signup_form" method="post" action="{{ route('login') }}" enctype="multipart/form-data">
			@csrf
			<div class="col-lg-12">
				<div class="form-group">
					<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
					required value="{{ old('login') }}"
					type="text" placeholder="Email / Username *" name="login" />
					@error('login')
						<small class="text-danger text-left text-sm">{{ $message }}</small>
					@enderror
				</div>
			</div>
	
			<div class="col-lg-12">
				<div class="form-group">
					<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
					type="password" placeholder="Password"
					required value="{{ old('password') }}" 
					name="password" />
					@error('password')
						<small class="text-danger text-left text-sm">{{ $message }}</small>
					@enderror
				</div>


				<div class="form-group d-flex flex-wrap justify-content-between align-items-center  opacity-60">
					<div class="checkbox-inline">
						<label class="checkbox checkbox-outline checkbox-white text-white m-0">
						<input type="checkbox" name="remember" />
						<span></span>Remember me</label>
					</div>
					<a href="javascript:;" id="kt_login_forgot" class="text-white font-weight-bold">Forget Password ?</a>
				</div> 
			</div>  
			<div class="col-lg-12 text-left">
				<div class="form-group">
					<button id="" type="submit" class="btn btn-pill btn-warning rounded-0 opacity-90 px-15 py-3" style="">Sign In</button>
					<a href="{{ route('register') }}" class=" rounded-0 btn-outline-white opacity-70 px-15 py-3 m-2">Don't have an Account</a>
				</div>
			</div>  
			
			
		</form>
	</div>

 
@endsection
@section('page_js')
    <script> 

    </script>
    
@endsection