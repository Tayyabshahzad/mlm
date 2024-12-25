<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Otp;

class OtpExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */

     
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $otp = Otp::where('otp', $value)
        ->where('expires_at', '>', now()) // Ensure OTP is not expired
        ->first();

        // If the OTP does not exist or has expired, trigger the validation error
        if (!$otp) {
            $fail('The OTP is invalid or has expired.');
        }
    }
}
