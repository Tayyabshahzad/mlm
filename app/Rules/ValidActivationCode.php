<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\ActivationCode;
class ValidActivationCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function __construct(private $paymentMethod) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->paymentMethod !== 'activation_code') {
            return;
        }
        $activationCode = ActivationCode::where('code', $value)->first();
        if (!$activationCode) {
            $fail('The activation code is invalid.');
            return;
        }
        if ($activationCode->admin_approval !== 'approved') {
            $fail('The activation code is not approved.');
            return;
        }
        if ($activationCode->status == 'used') {
            $fail('The activation code has already been used.');
        }
    }
}
