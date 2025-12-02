<?php

return [
    'sandbox_mode' => env('SSLCZ_TESTMODE', true),
    'api_domain' => env('SSLCZ_TESTMODE', true)
        ? "https://sandbox.sslcommerz.com"
        : "https://securepay.sslcommerz.com",
    'store_id' => env('SSLCZ_STORE_ID'),
    'store_password' => env('SSLCZ_STORE_PASSWORD'),
    'success_url' => '/api/customer/payment/sslcommerz/success',
    'fail_url' => '/api/customer/payment/sslcommerz/fail',
    'cancel_url' => '/api/customer/payment/sslcommerz/cancel',
    'ipn_url' => '/api/customer/payment/sslcommerz/ipn',
];
