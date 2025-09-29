<?php

namespace App\Rules;

use App\Support\SubdomainNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Subdomain implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = SubdomainNormalizer::normalize($value);

        if ($normalized === '' || !preg_match('/^[a-z0-9-]+$/i', $normalized)) {
            $fail('The ' . str_replace('_', ' ', $attribute) . ' may only contain letters, numbers, and hyphens.');
        }
    }
}
