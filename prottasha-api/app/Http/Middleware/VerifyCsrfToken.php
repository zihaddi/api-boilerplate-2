<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // ...existing code...
        '/api/customer/payment/sslcommerz/success',
        '/api/customer/payment/sslcommerz/fail',
        '/api/customer/payment/sslcommerz/cancel',
        '/api/customer/payment/sslcommerz/ipn',
    ];
}
