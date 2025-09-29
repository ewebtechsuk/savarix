<?php

namespace App\Support;

use App\Models\Tenant;

class CompanyIdGenerator
{
    public static function generate(): string
    {
        do {
            $candidate = (string) random_int(1000, 999999);
        } while (Tenant::query()->where('data->company_id', $candidate)->exists());

        return $candidate;
    }
}
