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
        'webhook/shop/updated',
        'webhook/order/created',
        'webhook/order/updated',
        'webhook/product/created',
        'webhook/app/uninstall',
        'service_callback',
        'service_callback/fulfillment_order_notification',
        'service_callback/fetch_stock',
        'service_callback/fetch_tracking_numbers',
        'validate-pincode'
    ];
}
