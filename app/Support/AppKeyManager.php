<?php

namespace App\Support;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;

class AppKeyManager
{
    /**
     * Ensure the application has an encryption key.
     */
    public static function ensure(): void
    {
        $key = static::resolveFromEnvironment();

        if (static::configAvailable()) {
            Config::set('app.key', $key);
        }
    }

    /**
     * Resolve the application key, generating a persistent value when missing.
     */
    public static function resolveFromEnvironment(): string
    {
        $key = static::valueFromEnvironment()
            ?? static::readStoredKey();

        if ($key === null) {
            $key = static::generateKey(static::resolveCipher());
            static::storeKey($key);
        } else {
            static::storeKeyIfMissing($key);
        }

        static::inject($key);

        return $key;
    }

    /**
     * Normalise a potential key value.
     */
    protected static function normalise(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * Retrieve an APP_KEY value from the environment.
     */
    protected static function valueFromEnvironment(): ?string
    {
        $value = $_ENV['APP_KEY']
            ?? $_SERVER['APP_KEY']
            ?? getenv('APP_KEY')
            ?: null;

        return static::normalise($value);
    }

    /**
     * Read a stored key from disk.
     */
    protected static function readStoredKey(): ?string
    {
        $path = static::keyStoragePath();

        if (! is_file($path)) {
            return null;
        }

        return static::normalise(@file_get_contents($path) ?: null);
    }

    /**
     * Generate a new key for the application.
     */
    protected static function generateKey(string $cipher): string
    {
        return 'base64:'.base64_encode(Encrypter::generateKey($cipher));
    }

    /**
     * Determine the cipher that should be used when generating a key.
     */
    protected static function resolveCipher(): string
    {
        $value = $_ENV['APP_CIPHER']
            ?? $_SERVER['APP_CIPHER']
            ?? getenv('APP_CIPHER')
            ?: null;

        return static::normalise($value) ?? 'AES-256-CBC';
    }

    /**
     * Store the key on disk if it is missing or different.
     */
    protected static function storeKeyIfMissing(string $key): void
    {
        $path = static::keyStoragePath();

        if (is_file($path)) {
            $existing = static::normalise(@file_get_contents($path) ?: null);

            if ($existing === $key) {
                return;
            }
        }

        static::storeKey($key);
    }

    /**
     * Persist the key to storage.
     */
    protected static function storeKey(string $key): void
    {
        $path = static::keyStoragePath();
        $directory = dirname($path);

        if (! is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        if (! is_writable($directory)) {
            return;
        }

        @file_put_contents($path, $key.PHP_EOL, LOCK_EX);
    }

    /**
     * Resolve the storage path for the key file.
     */
    protected static function keyStoragePath(): string
    {
        return storage_path('app.key');
    }

    /**
     * Inject the key into the runtime configuration and environment.
     */
    protected static function inject(string $key): void
    {
        putenv('APP_KEY='.$key);
        $_ENV['APP_KEY'] = $key;
        $_SERVER['APP_KEY'] = $key;
    }

    /**
     * Determine if the configuration repository is available.
     */
    protected static function configAvailable(): bool
    {
        if (! class_exists(Config::class)) {
            return false;
        }

        if (! function_exists('app')) {
            return false;
        }

        try {
            return app()->bound('config');
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
