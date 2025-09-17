<?php

namespace Offline\Support;

class Env
{
    public static function generateAppKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    public static function setAppKey(string $basePath, ?string $key = null): bool
    {
        $envPath = rtrim($basePath, '/') . '/.env';
        if (!is_file($envPath)) {
            return false;
        }

        $key = $key ?? self::generateAppKey();
        $contents = file_get_contents($envPath);
        if ($contents === false) {
            return false;
        }

        if (preg_match('/^APP_KEY=.*$/m', $contents)) {
            $contents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $contents);
        } else {
            $contents = rtrim($contents) . PHP_EOL . 'APP_KEY=' . $key . PHP_EOL;
        }

        return file_put_contents($envPath, $contents) !== false;
    }
}
