<?php

namespace Illuminate\Foundation\Auth;

class User
{
    /** @var array<string, mixed> */
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
