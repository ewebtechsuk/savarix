<?php

declare(strict_types=1);

namespace App\Support;

class AgencyRoles
{
    public static function propertyManagers(): array
    {
        return array_values(array_unique(config('roles.property_manager_roles', [])));
    }

    public static function propertyManagersPipe(): string
    {
        return implode('|', self::propertyManagers());
    }

    public static function tenantOwnerRole(): string
    {
        return (string) config('roles.tenant_owner_role', 'Admin');
    }

    public static function ownerAssignableRoles(): array
    {
        return array_values(array_unique(array_merge(
            [self::tenantOwnerRole()],
            self::propertyManagers(),
        )));
    }
}
