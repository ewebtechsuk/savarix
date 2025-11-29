<?php

return [
    'dsn' => env('SENTRY_DSN'),

    'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV')),

    'release' => env('SENTRY_RELEASE'),

    'traces_sample_rate' => env('APP_ENV') === 'production'
        ? (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.0)
        : 0.0,

    'profiles_sample_rate' => env('APP_ENV') === 'production'
        ? (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0)
        : 0.0,
];
