<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    private static int $nextId = 1;

    /** @var array<int, self> */
    private static array $store = [];

    public static function create(array $attributes = []): self
    {
        $record = [
            'id' => $attributes['id'] ?? self::$nextId++,
            'name' => $attributes['name'] ?? 'User',
            'email' => $attributes['email'] ?? 'user@example.com',
            'password' => $attributes['password'] ?? '',
            'is_admin' => (bool) ($attributes['is_admin'] ?? false),
            'remember_token' => $attributes['remember_token'] ?? null,
            'email_verified_at' => $attributes['email_verified_at'] ?? null,
        ];

        $user = new self($record);
        self::$store[$user->id] = $user;

        return $user;
    }

    public static function find(int $id): ?self
    {
        return self::$store[$id] ?? null;
    }

    public static function findByEmail(string $email): ?self
    {
        foreach (self::$store as $user) {
            if (strcasecmp($user->email, $email) === 0) {
                return $user;
            }
        }

        return null;
    }

    public static function truncate(): void
    {
        self::$store = [];
        self::$nextId = 1;
    }
}
