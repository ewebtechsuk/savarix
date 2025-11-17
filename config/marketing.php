<?php

declare(strict_types=1);

$rawDomains = env('MARKETING_DOMAINS', 'savarix.com');

if (is_string($rawDomains)) {
    $rawDomains = preg_split('/[,\s]+/', $rawDomains) ?: [];
}

if (! is_array($rawDomains)) {
    $rawDomains = [];
}

return [
    'domains' => array_values(array_filter(array_map(
        static function ($domain): string {
            return strtolower(trim((string) $domain));
        },
        $rawDomains
    ))),
];
