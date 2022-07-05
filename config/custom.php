<?php 

return [
    'shopify_api_key' => env('SHOPIFY_API_KEY', '8271b83e7ad33fb6a78b9f915d61749e'),
    'shopify_api_secret' => env('SHOPIFY_API_SECRET', '44cf53b978770d7d0652c85aad4634f7'),
    'shopify_api_version' => '2022-07',
    'api_scopes' => 'read_orders,read_fulfillments,read_customers,read_fulfillments,read_locations,read_products'
];