<?php

    function getShopifyURLForStore($endpoint, $store) {
        return checkIfStoreIsPrivate($store) ? 
            'https://'.$store['api_key'].':'.$store['api_secret_key'].'@'.$store['myshopify_domain'].'/admin/api/'.config('custom.shopify_api_version').'/'.$endpoint 
            :
            'https://'.$store['myshopify_domain'].'/admin/api/'.config('custom.shopify_api_version').'/'.$endpoint;
    }

    function getShopifyHeadersForStore($store, $method = 'GET') {
        return $method == 'GET' ? [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $store['access_token']
        ] : [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: '.$store['access_token']
        ];
    }

    function getGraphQLHeadersForStore($store) {
        return checkIfStoreIsPrivate($store) ? [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $store['api_secret_key'],
            'X-GraphQL-Cost-Include-Fields' => true
        ] : [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $store['access_token'],
            'X-GraphQL-Cost-Include-Fields' => true
        ];
    }

    function checkIfStoreIsPrivate($store) {
        return isset($store['api_key']) && isset($store['api_secret_key'])
                && $store['api_key'] !== null && $store['api_secret_key'] !== null  
                && strlen($store['api_key']) > 0 && strlen($store['api_secret_key']) > 0; 
    }

