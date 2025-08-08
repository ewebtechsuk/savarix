<?php
if (!\function_exists('json_validate')) {
    function json_validate(string $json, int $depth = 512, int $flags = 0): bool
    {
        json_decode($json, false, $depth, $flags);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
