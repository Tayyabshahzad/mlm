@extends('demo.layout.guest')
@section('title','Login')
@section('content')

<div class="login-signups container">
	<div class="mb-20">
		<h3 class=" font-weight-normal">Sign Up</h3>
		<p class="">Enter your details to create your account</p>
	</div>
	<form class="form text-center row pl-40 pr-40" id="kt_login_signup_form" method="post" action="{{ route('register.user') }}" enctype="multipart/form-data">
		@csrf
		<div class="col-lg-6">
			<div class="form-group">
				<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
				required value="{{ old('name') }}"
				type="text" placeholder="Full Name *" name="name" />
				@error('name')
            		<small class="text-danger text-left text-sm">{{ $message }}</small>
            	@enderror
			</div>
		</div>

		<div class="col-lg-6">
			<div class="form-group">
				<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
				type="text" placeholder="Username *"
				required value="{{ old('username') }}" 
				name="username" />
				@error('username')
            		<small class="text-danger text-left text-sm">{{ $message }}</small>
            	@enderror
			</div>
		</div>


		<div class="col-lg-12">
			<div class="form-group">
				<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
				type="email" placeholder="Email *"
				required value="{{ old('email') }}" 
				name="email" />
				@error('email')
            		<small class="text-danger text-left text-sm">{{ $message }}</small>
            	@enderror
			</div>
		</div> 


		<div class="col-lg-6">
			<div class="form-group">
				<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
				type="text" placeholder="Referral Link *"
				value="{{ $ref ?? old('referral_link') }}"
				required 
				name="referral_link" />
				@error('referral_link')
            		<small class="text-danger text-left text-sm">{{ $message }}</small>
            	@enderror
			</div>
		</div> 


		<div class="col-lg-6">
			<div class="form-group">
				<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
				type="text" placeholder="Transaction ID *"
				value="{{ old('transaction_id') }}"
				required 
				name="transaction_id" />
				@error('transaction_id')
            		<small class="text-danger text-left text-sm">{{ $message }}</small>
            	@enderror
			</div>
		</div> 
		


		<div class="col-lg-6">
			<div class="form-group">
				<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
				type="password" placeholder="Password *"
				required value="{{ old('password') }}" 
				name="password" />
				@error('password')
            		<small class="text-danger text-left text-sm">{{ $message }}</small>
            	@enderror
			</div>
		</div> 

		<div class="col-lg-6">
			<div class="form-group">
				<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
				type="password" placeholder="Password Confirmation *"
				required value="{{ old('password_confirmation') }}" 
				name="password_confirmation" />
				@error('password_confirmation')
            		<small class="text-danger text-left text-sm">{{ $message }}</small>
            	@enderror
			</div>
		</div> 


		<div class="col-lg-12">
			<div class="form-group">
				<input class="form-control h-auto text-white bg-white-o-5 rounded-0 border-0 py-4 px-8" 
				type="file" placeholder="amount_src *"
				required value="{{ old('amount_src') }}" 
				name="amount_src" />
				@error('amount_src')
            		<small class="text-danger text-left text-sm">{{ $message }}</small>
            	@enderror
			</div>
		</div> 


		<div class="col-lg-12">
			<div class="form-group text-left ">
				<div class="checkbox-inline">
					<label class="checkbox checkbox-outline checkbox-white opacity-60 text-white m-0">
					<input type="checkbox" name="agree" />
					<span></span>I Agree the
					<a href="#" class="text-white font-weight-bold ml-1">terms and conditions</a>.</label>
				</div>
				<div class="form-text text-muted text-center"></div>
			</div>
		</div> 


		<div class="col-lg-12">
			<div class="form-group">
				<button id="" type="submit" class="btn btn-pill btn-warning rounded-0 opacity-90 px-15 py-3 m-2">Sign Up</button>
				<a href="{{ route('login') }}" class="">Already have an Account</a>
			</div>
		</div>  
		
		
	</form>
</div>
 
@endsection
@section('page_js')
    <script> 

    </script>
    
@endsection