<?php

namespace Database\Seeders;

class RolePermissionConfig
{
    /**
     * @var array<string, array<int, string>>
     */
    private const MODULE_PERMISSIONS = [
        'contacts' => ['view', 'create', 'update', 'delete'],
        'properties' => ['view', 'create', 'update', 'delete'],
        'viewings' => ['view', 'create', 'update', 'delete'],
        'offers' => ['view', 'create', 'update', 'delete'],
        'tenancies' => ['view', 'create', 'update', 'delete'],
        'maintenance' => ['view', 'create', 'update', 'delete'],
        'documents' => ['view', 'create', 'update', 'delete'],
        'inspections' => ['view', 'create', 'update', 'delete'],
        'diary' => ['view', 'create', 'update', 'delete'],
        'accounts' => ['view', 'create', 'update', 'delete'],
        'workflows' => ['view', 'create', 'update', 'delete'],
        'tenants' => ['view', 'create', 'update', 'delete'],
    ];

    /**
     * @return array<int, string>
     */
    public static function roles(): array
    {
        return ['Admin', 'Landlord', 'Agent', 'Tenant'];
    }

    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return collect(self::MODULE_PERMISSIONS)
            ->flatMap(function (array $actions, string $module) {
                return collect($actions)->map(fn (string $action) => sprintf('%s.%s', $module, $action));
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function rolePermissions(): array
    {
        $all = self::permissions();

        $landlord = self::permissionsForModules([
            'properties',
            'contacts',
            'viewings',
            'offers',
            'tenancies',
            'maintenance',
            'documents',
            'inspections',
            'diary',
            'accounts',
            'workflows',
            'tenants',
        ]);

        $agent = self::permissionsForModules([
            'properties',
            'contacts',
            'viewings',
            'maintenance',
            'documents',
            'inspections',
            'diary',
        ]);

        $tenant = [
            'properties.view',
            'tenancies.view',
            'maintenance.create',
            'maintenance.view',
        ];

        return [
            'Admin' => $all,
            'Landlord' => $landlord,
            'Agent' => $agent,
            'Tenant' => $tenant,
        ];
    }

    public static function guard(): string
    {
        return 'web';
    }

    /**
     * @param array<int, string> $modules
     * @return array<int, string>
     */
    private static function permissionsForModules(array $modules): array
    {
        return collect($modules)
            ->map(function (string $module) {
                return collect(self::MODULE_PERMISSIONS[$module] ?? [])
                    ->map(fn (string $action) => sprintf('%s.%s', $module, $action));
            })
            ->flatten()
            ->values()
            ->all();
    }
}
