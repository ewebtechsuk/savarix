<?php

namespace Illuminate\Support\Facades;

use Illuminate\Support\Facades\Hash;

use function config;

class Auth
{
    private static string $defaultGuard = 'web';

    /** @var array<string, AuthGuard> */
    private static array $guards = [];

    public static function guard(?string $name = null): AuthGuard
    {
        $name ??= self::$defaultGuard;

        if (!isset(self::$guards[$name])) {
            $guardConfig = config('auth.guards.' . $name, []);
            $providerName = $guardConfig['provider'] ?? 'users';
            $providerConfig = config('auth.providers.' . $providerName, []);
            $model = $providerConfig['model'] ?? \App\Models\User::class;

            self::$guards[$name] = new AuthGuard(
                $name,
                $model,
                $providerName,
            );
        }

        return self::$guards[$name];
    }

    public static function shouldUse(string $name): void
    {
        self::$defaultGuard = $name;
    }

    public static function check(): bool
    {
        return self::guard()->check();
    }

    public static function user(): ?object
    {
        return self::guard()->user();
    }

    public static function id(): int|string|null
    {
        return self::guard()->id();
    }

    public static function login(object $user, bool $remember = false): void
    {
        self::guard()->login($user, $remember);
    }

    public static function logout(): void
    {
        self::guard()->logout();
    }

    public static function attempt(array $credentials, bool $remember = false): bool
    {
        return self::guard()->attempt($credentials, $remember);
    }
}

class AuthGuard
{
    private ?object $user = null;

    public function __construct(
        private readonly string $name,
        private readonly string $modelClass,
        private readonly string $providerName,
    ) {
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function user(): ?object
    {
        return $this->user;
    }

    public function id(): int|string|null
    {
        if (! $this->user) {
            return null;
        }

        return $this->user->id ?? null;
    }

    public function login(object $user, bool $remember = false): void
    {
        $this->user = $user;
    }

    public function logout(): void
    {
        $this->user = null;
    }

    public function attempt(array $credentials, bool $remember = false): bool
    {
        $user = $this->retrieveByCredentials($credentials);

        if (! $user) {
            return false;
        }

        $passwordField = $this->passwordField();
        $plainPassword = $credentials[$passwordField] ?? null;

        if ($plainPassword === null) {
            return false;
        }

        $hashedPassword = $user->{$passwordField} ?? null;

        if (! is_string($hashedPassword) || ! Hash::check($plainPassword, $hashedPassword)) {
            return false;
        }

        $this->login($user, $remember);

        return true;
    }

    public function validate(array $credentials): bool
    {
        $user = $this->retrieveByCredentials($credentials);

        if (! $user) {
            return false;
        }

        $passwordField = $this->passwordField();
        $plainPassword = $credentials[$passwordField] ?? null;
        $hashedPassword = $user->{$passwordField} ?? null;

        if ($plainPassword === null || ! is_string($hashedPassword)) {
            return false;
        }

        return Hash::check($plainPassword, $hashedPassword);
    }

    private function retrieveByCredentials(array $credentials): ?object
    {
        $emailField = $this->emailField();
        $passwordField = $this->passwordField();

        $email = $credentials[$emailField] ?? null;
        $password = $credentials[$passwordField] ?? null;

        if ($email === null || $password === null) {
            return null;
        }

        $modelClass = $this->modelClass;
        if (! class_exists($modelClass)) {
            return null;
        }

        $query = $modelClass::query()->where($emailField, $email);

        foreach ($credentials as $field => $value) {
            if (in_array($field, [$emailField, $passwordField], true)) {
                continue;
            }

            $query->where($field, $value);
        }

        return $query->first();
    }

    private function emailField(): string
    {
        return match ($this->providerName) {
            'landlords' => 'contact_email',
            default => 'email',
        };
    }

    private function passwordField(): string
    {
        return 'password';
    }
}
