<?php
if (!\function_exists('text')) {
    function text(string $label, ...$args)
    {
        return '';
    }
}
if (!\function_exists('confirm')) {
    function confirm(string $label, ...$args): bool
    {
        return false;
    }
}
