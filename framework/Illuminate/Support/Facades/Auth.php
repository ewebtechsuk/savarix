<?php

namespace Illuminate\Support\Facades;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Auth
{
    private static string $defaultGuard = 'web';

    /** @var array<string, AuthGuard> */
    private static array $guards = [];

    public static function guard(?string $name = null): AuthGuard
    {
        $name ??= self::$defaultGuard;

        if (!isset(self::$guards[$name])) {
            self::$guards[$name] = new AuthGuard();
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

    public static function user(): ?User
    {
        return self::guard()->user();
    }

    public static function id(): ?int
    {
        return self::guard()->id();
    }

    public static function login(User $user, bool $remember = false): void
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
    private ?User $user = null;

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user?->id;
    }

    public function login(User $user, bool $remember = false): void
    {
        $this->user = $user;
    }

    public function logout(): void
    {
        $this->user = null;
    }

    public function attempt(array $credentials, bool $remember = false): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if ($email === null || $password === null) {
            return false;
        }

        $user = User::findByEmail($email);

        if (! $user) {
            return false;
        }

        if (! Hash::check($password, $user->password)) {
            return false;
        }

        $this->login($user, $remember);

        return true;
    }

    public function validate(array $credentials): bool
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if ($email === null || $password === null) {
            return false;
        }

        $user = User::findByEmail($email);

        if (! $user) {
            return false;
        }

        return Hash::check($password, $user->password);
    }
}
