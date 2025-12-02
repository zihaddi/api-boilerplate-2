<?php

return [
    'client_id' => config('services.paypal.client_id') ?? env('PAYPAL_CLIENT_ID', ''),
    'client_secret' => config('services.paypal.client_secret') ?? env('PAYPAL_CLIENT_SECRET', ''),
    'sandbox' => config('services.paypal.sandbox') ?? env('PAYPAL_SANDBOX', true),
    'api_url' => env('PAYPAL_SANDBOX', true)
        ? 'https://api-m.sandbox.paypal.com'
        : 'https://api-m.paypal.com',
    'currency' => config('services.paypal.currency') ?? env('PAYPAL_CURRENCY', 'USD'),
    'webhook_id' => config('services.paypal.webhook_id') ?? env('PAYPAL_WEBHOOK_ID', ''),
];
