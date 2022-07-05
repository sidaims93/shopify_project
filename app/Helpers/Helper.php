<?php

if (!function_exists('getShopifyURLForStore')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getShopifyURLForStore($endpoint, $store) {
        return 'https://'.$store['myshopify_domain'].'/admin/api/'.config('custom.shopify_api_version').'/'.$endpoint;
    }

    function getShopifyHeadersForStore($storeDetails) {
        return [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $storeDetails['access_token']
        ];
    }
}
