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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'customer_auth_guard' => [
        'name' => env('USER_AUTH_GUARD_CUSTOMER')
    ],
    'admin_auth_guard' => [
        'name' => env('USER_AUTH_GUARD_ADMIN')
    ],
    'cms_auth_guard' => [
        'name' => env('USER_AUTH_GUARD_CMS')
    ],
    'domain_title' => env('APP_NAME', ''),
    'domain-title' => env('APP_NAME', ''),
    'domain_url' => env('APP_DOMAIN', ''),
    'http_protocol' => env('HTTP_PROTOCOL', ''),
    'storage_disk' => env('FILESYSTEM_DISK', 'public'),
    'storage_base_url' => env('APP_URL', ''),
    'base_url' => env('APP_URL', ''),
    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'key' => env('STRIPE_PUBLIC_KEY'),
    ],
    'sms' => [
        'api_key' => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID'),
        'url' => env('SMS_URL'),
    ],
    'aws' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'google' => [
        'maps' => [
            'key' => env('GOOGLE_MAPS_API_KEY'),
        ],
        'recaptcha' => [
            'key' => env('GOOGLE_RECAPTCHA_SITE_KEY'),
            'secret' => env('GOOGLE_RECAPTCHA_SECRET_KEY'),
        ],
        'pay' => [
            'merchant_id' => env('GOOGLE_PAY_MERCHANT_ID'),
            'merchant_name' => env('GOOGLE_PAY_MERCHANT_NAME'),
            'environment' => env('GOOGLE_PAY_ENVIRONMENT', 'TEST'),
        ],
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ],
    'google_oauth' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],
    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT'),
    ],
    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect' => env('LINKEDIN_REDIRECT'),
    ],
    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT'),
    ],
    'sslcommerz' => [
        'store_id' => env('SSLCZ_STORE_ID'),
        'store_password' => env('SSLCZ_STORE_PASSWORD'),
        'sandbox' => env('SSLCOMMERZ_SANDBOX'),
        'success_url' => env('SSLCZ_SUCCESS_URL'),
        'fail_url' => env('SSLCZ_FAIL_URL'),
        'cancel_url' => env('SSLCZ_CANCEL_URL'),
        'ipn_url' => env('SSLCZ_IPN_URL'),
        'testmode' => env('SSLCZ_TESTMODE'),
        'is_local' => env('IS_LOCALHOST'),
        'api_domain' => env('SSLCZ_API_DOMAIN'),
    ],
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'sandbox' => env('PAYPAL_SANDBOX', true),
        'currency' => env('PAYPAL_CURRENCY', 'USD'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],
    'frontend_url' => env('HTTP_PROTOCOL', 'https') . '://' . env('APP_DOMAIN', 'localhost'),
];
