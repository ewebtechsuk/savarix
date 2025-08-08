<?php
if (!\function_exists('grapheme_strlen')) {
    function grapheme_strlen(string $string): int { return mb_strlen($string); }
    function grapheme_substr(string $string, int $start, ?int $length = null): string { return $length === null ? mb_substr($string, $start) : mb_substr($string, $start, $length); }
    function grapheme_strpos(string $haystack, string $needle, int $offset = 0) { return strpos($haystack, $needle, $offset); }
    function grapheme_stripos(string $haystack, string $needle, int $offset = 0) { return stripos($haystack, $needle, $offset); }
    function grapheme_strrpos(string $haystack, string $needle, int $offset = 0) { return strrpos($haystack, $needle, $offset); }
    function grapheme_strripos(string $haystack, string $needle, int $offset = 0) { return strripos($haystack, $needle, $offset); }
    function grapheme_strstr(string $haystack, string $needle, bool $before_needle = false) { return strstr($haystack, $needle, $before_needle); }
    function grapheme_stristr(string $haystack, string $needle, bool $before_needle = false) { return stristr($haystack, $needle, $before_needle); }
    function grapheme_extract(string $haystack, int $length, int $type = 0, int $start = 0, &$next = null) {
        $next = $start + $length;
        return mb_substr($haystack, $start, $length);
    }
}
