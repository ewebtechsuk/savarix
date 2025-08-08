<?php
if (!\function_exists('normalizer_is_normalized')) {
    if (!\defined('NORMALIZER_FORM_D')) {
        \define('NORMALIZER_FORM_D', 1);
        \define('NORMALIZER_FORM_C', 2);
        \define('NORMALIZER_FORM_KD', 3);
        \define('NORMALIZER_FORM_KC', 4);
    }
    function normalizer_is_normalized(string $string, int $form = NORMALIZER_FORM_C): bool
    {
        return true;
    }
    function normalizer_normalize(string $string, int $form = NORMALIZER_FORM_C): string
    {
        return $string;
    }
}
