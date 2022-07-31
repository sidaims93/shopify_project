<?php 

return [
    'shopify_api_key' => env('SHOPIFY_API_KEY', '8271b83e7ad33fb6a78b9f915d61749e'),
    'shopify_api_secret' => env('SHOPIFY_API_SECRET', '44cf53b978770d7d0652c85aad4634f7'),
    'shopify_api_version' => '2022-07',
    'api_scopes' => 'write_orders,write_fulfillments,write_customers,write_fulfillments,write_products',
    'webhook_events' => [
        'orders/create' => 'order/created', //When the store recieves an order
        'orders/updated' => 'order/updated', //When an order is updated
        'products/create' => 'product/created', //When products are created
        'app/uninstalled' => 'app/uninstall', //To know when the app has been removed. 
        'shop/update' => 'shop/updated', //To keep latest data in the stores table
    ],
    'default_permissions' => [
        'write-products', 'read-products',
        'write-orders', 'read-orders',
        'write-customers', 'read-customers',
        'write-members', 'read-members'
    ]
];