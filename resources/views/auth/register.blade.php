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

        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Admission Fee For <span class="text-danger">*</span></label>
            <select class="form-control form-control-solid h-auto rounded-md"  required>
                    <option value="Global Visioners Educational System"> Global Visioners Educational System </option>
            </select> 
        </div>

        <div class="form-group">
            <label for="phone" class="font-size-h6 font-weight-bolder text-dark">Mobile Number <span class="text-danger">*</span></label>
            <div class="d-flex">
                @include('auth.country-code')
                <input id="phone" 
                name="phone_number" value="{{  old('phone_number') }}" 
                type="tel" class="form-control" placeholder="Enter Phone Number" required>
                @error('phone_number')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
    

        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Referral Link <span class="text-danger">*</span></label>
            <input class="form-control form-control-solid h-auto rounded-md" type="text" name="referral_link" value="{{ $ref ?? old('referral_link') }}" autocomplete="off" required />
            @error('referral_link')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="font-size-h6 font-weight-bolder text-dark">Choose Payment Method <span class="text-danger">*</span></label>
        
            <hr>
            <p class="text-center">
                <strong class="mr-5">
                    <input class="h-auto rounded-md" type="radio" name="payment_method" value="bank" required onclick="toggleReferralLink('bank')" /> Bank
                </strong>
                <strong class="mr-5"> 
                    <input class="h-auto rounded-md" type="radio" name="payment_method" value="usdt" required onclick="toggleReferralLink('usdt')" /> USDT
                </strong>
                <strong>
                    <input class="h-auto rounded-md" type="radio" name="payment_method" value="cash_slip" required onclick="toggleReferralLink('cash')" /> Cash Slip
                </strong>
            </p>
        </div>

        <div class="form-group d-none" id="referral-link-container">
            <label class="font-size-h6 font-weight-bolder text-dark">Scan QR <span class="text-danger">*</span></label>
            <img src="{{ asset('assets/custom-images/amount-qr.jpeg') }}" alt="" class=" img-thumbnail"> 

            <div class="form-group mt-4">
                <label class="font-size-h6 font-weight-bolder text-dark">Copy Binance Wallet Address</label>

                <div class="btn-toolbar mb-3" role="toolbar" aria-label="Toolbar with button groups">
                    <div class="btn-group " role="group" aria-label="First group" onclick="copyAddressToClipboard()"> 
                        <div class=" form-control rounded-0" id="walletAddress" style=""> 
                          
                            TJaz7ykL6nnpDaVnPYJRNauKXLNtgLUYJP
                           
                       </div>
                       <button type="button" class="ml-2 btn btn-md btn-outline-secondary btn-icon rounded-0"><i class="la la-copy"></i></button>
                    </div>
                    
                </div>


                <input class="form-control form-control-solid h-auto rounded-md" type="text" name="referral_link" value="{{ $ref ?? old('referral_link') }}" autocomplete="off" required />
                @error('referral_link')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>


        </div>

        



        <!-- Referral Link Field -->

        <div class="form-group d-none">
            <label class="font-size-h6 font-weight-bolder text-dark">Scan QR <span class="text-danger">*</span></label>
            <img src="{{ asset('assets/custom-images/amount-qr.jpeg') }}" alt=""
            
            class="img img-rounded img-thumbnail">
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
    const countryCodeDropdown = document.getElementById("countryCode");
    const phoneInput = document.getElementById("phone");

    // Event Listener for Change
    countryCodeDropdown.addEventListener("change", function () {
        const selectedCode = countryCodeDropdown.value;
        console.log("Selected Country Code:", selectedCode);
    });
</script>


<script>
    
    function toggleReferralLink(paymentMethod) {
        const referralLinkContainer = document.getElementById('referral-link-container');
        if (paymentMethod === 'usdt') {
            referralLinkContainer.classList.remove('d-none');
        } else {
            referralLinkContainer.classList.add('d-none');
        }
    }

    
</script>

<script>
    function copyAddressToClipboard() {
        // Get the hidden reference link element
        const refLinkElement = document.getElementById("walletAddress");
        // Create a temporary input to hold the link text
        const tempInput = document.createElement("input");
        tempInput.value = refLinkElement.textContent;
        // Append the input to the body, copy its value, and then remove it
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        toastr.info("Wallet Address Copied");
        // Show a toast message
       
         
    }
</script>

@endsection