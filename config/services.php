<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'docusign' => [
        'key' => env('DOCUSIGN_API_KEY'),
        'secret' => env('DOCUSIGN_API_SECRET'),
    ],

    'hellosign' => [
        'key' => env('HELLOSIGN_API_KEY'),
    ],

    'rightmove' => [
        'endpoint' => env('RIGHTMOVE_ENDPOINT'),
        'api_key' => env('RIGHTMOVE_API_KEY'),
        'api_secret' => env('RIGHTMOVE_API_SECRET'),
    ],

    'zoopla' => [
        'endpoint' => env('ZOOPLA_ENDPOINT'),
        'api_key' => env('ZOOPLA_API_KEY'),
        'api_secret' => env('ZOOPLA_API_SECRET'),
    ],

    'marketing' => [
        'providers' => [
            [
                'endpoint' => env('MARKETING_EMAIL_ENDPOINT'),
                'api_key' => env('MARKETING_EMAIL_KEY'),
            ],
            [
                'endpoint' => env('MARKETING_SOCIAL_ENDPOINT'),
                'api_key' => env('MARKETING_SOCIAL_KEY'),
            ],
        ],

    ],

    'onfido' => [
        'api_token' => env('ONFIDO_API_TOKEN'),
        'webhook_secret' => env('ONFIDO_WEBHOOK_SECRET'),
        'workflow_id' => env('ONFIDO_WORKFLOW_ID'),
        'base_url' => env('ONFIDO_BASE_URL', 'https://api.eu.onfido.com'),
        'version' => env('ONFIDO_API_VERSION', 'v3.6'),
        'share_link_ttl' => env('ONFIDO_SHARE_LINK_TTL', 3600),
    ],
];
