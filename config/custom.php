<?php 

return [
    'shopify_api_key' => env('SHOPIFY_API_KEY', '8271b83e7ad33fb6a78b9f915d61749e'),
    'shopify_api_secret' => env('SHOPIFY_API_SECRET', '44cf53b978770d7d0652c85aad4634f7'),
    'shopify_api_version' => '2022-07',
    'fulfillment_service_name' => 'App Fulifllment Service',
    'stripe_api_key' => env('STRIPE_KEY', ''),
    'stripe_api_secret' => env('STRIPE_SECRET', ''),
    'app_embedded' => env('APP_EMBEDDED', false),
    'add_to_cart_tag_product' => 'buy-now',
    'api_scopes' => [
        'write_orders',
        'write_fulfillments',
        'write_customers',
        'write_products',
        'read_third_party_fulfillment_orders',
        'write_third_party_fulfillment_orders',
        'write_assigned_fulfillment_orders',
        'read_assigned_fulfillment_orders',
        'read_merchant_managed_fulfillment_orders',
        'write_merchant_managed_fulfillment_orders'
    ],
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
    ],
    'table_indexes' => [
        'orders_table_indexes' => [ 'store_id' => 'int', 'ship_province' => 'string', 'ship_country' => 'string',
            'id' => 'int', 'email' => 'string', 'created_at' => 'string', 'updated_at' => 'string', 'closed_at' => 'string', 'number' => 'int', 'note' =>  'string', /*'token' => 'string',*/ 'gateway' => 'string', /*'test' => 'string' ,*/ 
            'total_price' => 'float', 'subtotal_price' => 'float', 'total_weight' => 'float', 'total_tax' => 'float', 'taxes_included' => 'string', 'currency' => 'string', 'financial_status' => 'string', /*'confirmed' => 'string',*/ 
            'total_discounts' => 'float', 'total_line_items_price' => 'float', /*'cart_token' => 'string',*/  /*'buyer_accepts_marketing' => 'string',*/  'name' => 'string', /*'referring_site' => 'string', 'landing_site' => 'string',*/ 
            'cancelled_at' => 'string', 'cancel_reason' => 'string', /*'total_price_usd' => 'float',*/  'checkout_token' => 'string', /*'reference' => 'string','user_id' => 'string',*/  'location_id' => 'string', /*'source_identifier' => 'string',*/ 
            /*'source_url' => 'string',*/  'processed_at' => 'string', /*'device_id' => 'string',*/  'phone' => 'string', /*'customer_locale' => 'string',*/ /*'app_id' => 'string', 'browser_ip' => 'string',*/  /*'landing_site_ref' => 'string',*/  'order_number' => 'int',
            'processing_method' => 'string', /*'checkout_id' => 'float', 'source_name' => 'string',*/  'fulfillment_status' => 'string', 'tags' => 'string', 'contact_email' => 'string', /*'order_status_url' => 'string',*/  /*'presentment_currency' => 'string',  'total_tip_received' =>
            'string','admin_graphql_api_id' => 'string',*/ 'discount_applications' => 'string', 'discount_codes' => 'string', /*'note_attributes' => 'string',*/ 'payment_gateway_names' => 'string', 'tax_lines' =>
            'string', /*'total_line_items_price_set' => 'string',*/  'total_discounts_set' => 'string', 'total_shipping_price_set' => 'string', 'subtotal_price_set' => 'string', 'total_price_set' => 'string', 'total_tax_set' =>
            'string', 'line_items' => 'string', 'shipping_lines' => 'string', 'billing_address' => 'string', 'fulfillments' => 'string', 'shipping_address' => 'string', /*'client_details' => 'string',*/ 'refunds' => 'string', /*'payment_details' => 'string',*/ 'customer' =>
            'string'
        ],
        'locations_table_indexes' => [
            'store_id' => 'int', 'id' => 'int', 'name' => 'string', 'address1' => 'string', 'address2' => 'string', 'city' => 'string', 'zip' => 'string', 'province' => 'string', 'phone' => 'string', 
            'created_at' => 'string', 'updated_at' => 'string', 'country_code' => 'string', 'country_name' => 'string', 'province_code' => 'string', 'legacy' => 'int', 'active' => 'int',
            'admin_graphql_api_id' => 'string', 'localized_country_name' => 'string', 'localized_province_name' => 'string'
        ],
        'fulfillment_orders_table_index' => [
            'order_table_id' => 'int', 'id' => 'int', 'shop_id' => 'int', 'order_id' => 'int', 'assigned_location_id' => 'int', 'request_status' => 'string',
            'status' => 'string', 'supported_actions' => 'string', 'fulfill_at' => 'string', 'fulfill_by' => 'string', 'destination' => 'string',
            'line_items' => 'string', 'delivery_method' => 'string', 'assigned_location' => 'string', 'merchant_requests' => 'string'
        ]
    ],
    'one_time_payments' => [
        1 => [
            'name' => 'Simple',
            'price' => 4.99,
            'credits' => 500
        ],
        2 => [
            'name' => 'Extra',
            'price' => 9.99,
            'credits' => 2500
        ],
        3 => [
            'name' => 'Large',
            'price' => 14.99,
            'credits' => 5000
        ]
    ]
];