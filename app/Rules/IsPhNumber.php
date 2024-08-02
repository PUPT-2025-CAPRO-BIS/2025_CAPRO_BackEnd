<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IsPhNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(substr($value, 0, 2) != '09')
        {
            $fail("The phone number must start with '09'");
        }
        else if(strlen($value) != 11)
        {
            $fail("The phone number must be exactly 11 numbers long");
        }
    }
}
