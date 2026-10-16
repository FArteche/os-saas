<?php

namespace App\Rules;

use App\Support\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates a Brazilian phone already normalized by Phone::normalize() (digits only).
 */
class BrazilianPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! Phone::isValid($value)) {
            $fail('Informe um :attribute válido com DDD, como (11) 98765-4321.');
        }
    }
}
