<?php

return [
    // Role that represents the full-access owner/administrator within a tenant
    'tenant_owner_role' => 'Admin',

    // Roles that are allowed to manage properties within a tenant context
    'property_manager_roles' => [
        'Admin',
        'Tenant',
        'Agent',
        'PropertyManager',
        'Owner',
        'agency_admin',
        'agent',
        'property_manager',
        'lettings_negotiator',
    ],
];
