<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $slug = str_replace('.', '_', uniqid('tenant_', true));

        return [
            'id' => $slug,
            'slug' => $slug,
            'name' => ucfirst(str_replace('_', ' ', $slug)),
            'domains' => [],
        ];
    }

}
