<?php

namespace Enjin\Platform\Rules;

use Closure;
use Enjin\Platform\Support\SS58Address;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidSubstrateAddress implements ValidationRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!SS58Address::isValidAddress($value)) {
            $fail('enjin-platform::validation.valid_substrate_address')->translate();
        }
    }
}
