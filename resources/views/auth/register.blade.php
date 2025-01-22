@extends('demo.layout.guest')
@section('title','Register')
@section('content')
<div class="login-form login-signin">
    <!--begin::Form-->

    <div class="pb-13 pt-lg-0 pt-5 text-center">
		<img src="{{ asset('assets/custom-images/gvi-text.png') }}" class="max-h-70px" style="width: 90%" alt="" />
	</div>

    
    <form class="form" method="post" action="{{ route('register.user') }}" id=" " enctype="multipart/form-data">
        @csrf
        <div class="pb-13 pt-lg-0 pt-5">
            <h3 class="font-weight-bolder text-dark font-size-h4 font-size-h1-lg">Welcome to GV International</h3>
        </div>
        <!--begin::Title-->

        <!-- Name Field -->
        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Name <span class="text-danger">*</span> </label>
            <input class="form-control form-control-solid h-auto rounded-md" type="text" name="name" autocomplete="off" required value="{{ old('name') }}" />
            @error('name')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Username Field -->
        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Username <span class="text-danger">*</span> </label>
            <input class="form-control form-control-solid h-auto rounded-md" type="text" name="username" autocomplete="off" required value="{{ old('username') }}" />
            @error('username')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Field -->
        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Email <span class="text-danger">*</span></label>
            <input class="form-control form-control-solid h-auto rounded-md" type="email" name="email" autocomplete="off" required value="{{ old('email') }}" />
            @error('email')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password Field -->
        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Password <span class="text-danger">*</span></label>
            <input class="form-control form-control-solid h-auto rounded-md" type="password" name="password" autocomplete="off" required />
            @error('password')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password Field -->
        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Confirm Password <span class="text-danger">*</span></label>
            <input class="form-control form-control-solid h-auto rounded-md" type="password" name="password_confirmation" autocomplete="off" required />
        </div>

        <!-- Referral Link Field -->
        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Referral Link <span class="text-danger">*</span></label>
            <input class="form-control form-control-solid h-auto rounded-md" type="text" name="referral_link" value="{{ $ref ?? old('referral_link') }}" autocomplete="off" required />
            @error('referral_link')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Transaction ID <span class="text-danger">*</span></label>
            <input class="form-control form-control-solid h-auto rounded-md" type="text" name="transaction_id" value="{{ old('transaction_id') }}"    autocomplete="off" required />
            @error('transaction_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        

        <!-- Amount Proof Field -->
        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Transaction  Proof <span class="text-danger">*</span> </label>
            <input class="form-control form-control-solid h-auto rounded-md" type="file" name="amount_src" autocomplete="off" required   />
            @error('amount_src')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="pb-lg-0 pb-5 mt-10 mb-10">
            <button type="submit" id="kt_login_signin_submit" class="btn-sm btn btn-primary rounded-0  my-3 mr-3">Register</button>
            <a href="{{ route('login') }}" class=" font-size-h6 ">Already Registered</a>
        </div>
    </form>
    <!--end::Form-->
</div>
@endsection

@section('page_js')
<script>
    // Additional custom JS if needed
</script>
@endsection