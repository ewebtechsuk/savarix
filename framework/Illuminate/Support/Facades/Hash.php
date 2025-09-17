<?php

namespace Illuminate\Support\Facades;

class Hash
{
    public static function make(string $value): string
    {
        return password_hash($value, PASSWORD_BCRYPT);
    }
}
