<?php

namespace Offline\Support;

class RouteLoader
{
    public static function load(string $basePath): void
    {
        $routesPath = rtrim($basePath, '/') . '/routes';
        self::requireIfExists($routesPath . '/web.php');
        self::requireIfExists($routesPath . '/api.php');
    }

    private static function requireIfExists(string $path): void
    {
        if (is_file($path)) {
            require $path;
        }
    }
}
