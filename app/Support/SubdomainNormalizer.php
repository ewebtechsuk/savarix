<?php

namespace App\Support;

use Illuminate\Support\Str;

class SubdomainNormalizer
{
    public static function normalize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = (string) Str::of((string) $value)
            ->trim()
            ->replace('.', '')
            ->lower()
            ->trim();

        return $normalized;
    }
}
