<?php

namespace Database\Seeders;

use App\Support\AgencyRoles;

class RolePermissionConfig
{
    /**
     * @return array<int, string>
     */
    public static function roles(): array
    {
        return array_values(array_unique(array_merge(
            AgencyRoles::propertyManagers(),
            ['Landlord'],
        )));
    }

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return ['view tenants'];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rolePermissions(): array
    {
        return [
            'Admin' => ['view tenants'],
            'Landlord' => ['view tenants'],
        ];
    }

    public static function guard(): string
    {
        return config('permission.defaults.guard', 'web');
    }
}
