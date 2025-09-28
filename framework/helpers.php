<?php

use Illuminate\Support\Facades\Auth;

if (! function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        $base = realpath(__DIR__ . '/..');

        if ($base === false) {
            $base = __DIR__ . '/..';
        }

        return rtrim($base, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}

if (! function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }
}

if (! function_exists('config')) {
    function config($key = null, $default = null)
    {
        static $items = [];

        if ($key === null) {
            return $items;
        }

        if (is_array($key)) {
            foreach ($key as $name => $value) {
                set_config_value($items, $name, $value);
            }

            return true;
        }

        [$file, $path] = explode_config_key((string) $key);

        if (! array_key_exists($file, $items)) {
            $filePath = config_path($file . '.php');
            $items[$file] = file_exists($filePath) ? require $filePath : [];
        }

        return get_config_value($items[$file], $path, $default);
    }
}

if (! function_exists('explode_config_key')) {
    function explode_config_key(string $key): array
    {
        $segments = explode('.', $key, 2);
        $file = $segments[0];
        $path = $segments[1] ?? null;

        return [$file, $path];
    }
}

if (! function_exists('get_config_value')) {
    function get_config_value(array $repository, ?string $path, $default)
    {
        if ($path === null) {
            return $repository;
        }

        foreach (explode('.', $path) as $segment) {
            if (! is_array($repository) || ! array_key_exists($segment, $repository)) {
                return $default;
            }

            $repository = $repository[$segment];
        }

        return $repository;
    }
}

if (! function_exists('set_config_value')) {
    function set_config_value(array &$repository, string $key, $value): void
    {
        [$file, $path] = explode_config_key($key);

        if (! array_key_exists($file, $repository)) {
            $filePath = config_path($file . '.php');
            $repository[$file] = file_exists($filePath) ? require $filePath : [];
        }

        if ($path === null) {
            $repository[$file] = $value;
            return;
        }

        $segments = explode('.', $path);
        $target =& $repository[$file];

        foreach ($segments as $segment) {
            if (! is_array($target)) {
                $target = [];
            }

            if (! array_key_exists($segment, $target) || ! is_array($target[$segment])) {
                $target[$segment] = [];
            }

            $target =& $target[$segment];
        }

        $target = $value;
    }
}

if (! function_exists('auth')) {
    function auth(?string $guard = null) {
        return Auth::guard($guard);
    }
}
