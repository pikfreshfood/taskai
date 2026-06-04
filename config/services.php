<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'paystack' => [
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'upgrade_amount' => (int) env('TASKAI_UPGRADE_AMOUNT_KOBO', 1000000),
        'currency' => env('TASKAI_UPGRADE_CURRENCY', 'NGN'),
        'verify_ssl' => filter_var(env('PAYSTACK_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),
        'plans' => [
            'daily' => [
                'name' => '1 Day',
                'amount' => 100000,
                'duration_days' => 1,
            ],
            'weekly' => [
                'name' => 'Weekly',
                'amount' => 500000,
                'duration_days' => 7,
            ],
            'monthly' => [
                'name' => 'Monthly',
                'amount' => 1000000,
                'duration_days' => 30,
            ],
            'six_months' => [
                'name' => '6 Months',
                'amount' => 2400000,
                'duration_days' => 183,
            ],
            'yearly' => [
                'name' => 'Yearly',
                'amount' => 4000000,
                'duration_days' => 365,
            ],
        ],
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
