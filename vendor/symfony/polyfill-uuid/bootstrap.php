<?php
if (!\function_exists('uuid_create')) {
    function uuid_create(int $uuid_type = 1): string
    {
        $data = \bin2hex(\random_bytes(16));
        return substr($data, 0, 8).'-'.substr($data, 8, 4).'-'.substr($data, 12, 4).'-'.substr($data, 16, 4).'-'.substr($data, 20);
    }
    function uuid_is_valid(string $uuid): bool
    {
        return (bool) \preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $uuid);
    }
}
