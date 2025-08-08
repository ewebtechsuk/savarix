<?php
if (!\function_exists('t')) {
    function t(string $message, array $parameters = [], ?string $domain = null): string
    {
        return $message;
    }
}
