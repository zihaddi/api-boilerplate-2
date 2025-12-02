<?php

return [
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    'gateways' => [
        'stripe' => [
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'usd'),
        ],

        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'mode' => env('PAYPAL_MODE', 'sandbox'),
            'api_url' => env('PAYPAL_MODE', 'sandbox') === 'sandbox'
                ? 'https://api-m.sandbox.paypal.com'
                : 'https://api-m.paypal.com',
            'currency' => env('PAYPAL_CURRENCY', 'USD'),
        ],

        'sslcommerz' => [
            'store_id' => env('SSLCZ_STORE_ID'),
            'store_password' => env('SSLCZ_STORE_PASSWORD'),
            'sandbox_mode' => env('SSLCZ_SANDBOX_MODE', true),
            'api_domain' => env('SSLCZ_SANDBOX_MODE', true)
                ? 'https://sandbox.sslcommerz.com'
                : 'https://securepay.sslcommerz.com',
            'success_url' => '/api/payments/sslcommerz/success',
            'fail_url' => '/api/payments/sslcommerz/fail',
            'cancel_url' => '/api/payments/sslcommerz/cancel',
            'ipn_url' => '/api/payments/sslcommerz/ipn',
        ],
    ],
];
