<?php

namespace App\Tenancy;

use App\Tenancy\Repositories\DatabaseTenantRepository;

class TenantRepositoryManager
{
    private static ?TenantRepository $repository = null;

    public static function setRepository(TenantRepository $repository): void
    {
        self::$repository = $repository;
    }

    public static function clear(): void
    {
        self::$repository = null;
    }

    public static function getRepository(): TenantRepository
    {
        if (!self::$repository) {
            self::$repository = new DatabaseTenantRepository();
        }

        return self::$repository;
    }
}
