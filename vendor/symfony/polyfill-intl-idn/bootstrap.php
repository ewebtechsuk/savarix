<?php
if (!\function_exists('idn_to_ascii')) {
    if (!\defined('INTL_IDNA_VARIANT_UTS46')) {
        \define('INTL_IDNA_VARIANT_UTS46', 0);
    }
    function idn_to_ascii(string $domain, int $flags = 0, int $variant = INTL_IDNA_VARIANT_UTS46, array &$info = []) {
        return $domain;
    }
    function idn_to_utf8(string $domain, int $flags = 0, int $variant = INTL_IDNA_VARIANT_UTS46, array &$info = []) {
        return $domain;
    }
}
