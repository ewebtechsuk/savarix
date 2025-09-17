<?php

namespace App\Models;

class User
{
    private static int $nextId = 1;
    /** @var array<int, User> */
    private static array $store = [];

    public int $id;
    public string $name;
    public string $email;
    public string $password;

    private function __construct(array $attributes)
    {
        $this->id = $attributes['id'];
        $this->name = $attributes['name'];
        $this->email = $attributes['email'];
        $this->password = $attributes['password'];
    }

    public static function create(array $attributes): self
    {
        $record = [
            'id' => self::$nextId++,
            'name' => $attributes['name'] ?? 'User',
            'email' => $attributes['email'] ?? 'user@example.com',
            'password' => $attributes['password'] ?? '',
        ];

        $user = new self($record);
        self::$store[$user->id] = $user;
        return $user;
    }
}
