<?php

    function getShopifyURLForStore($endpoint, $store) {
        return 'https://'.$store['myshopify_domain'].'/admin/api/'.config('custom.shopify_api_version').'/'.$endpoint;
    }

    function getShopifyHeadersForStore($storeDetails, $method = 'GET') {
        return $method == 'GET' ? [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $storeDetails['access_token']
        ] : [
            'Content-Type: application/json',
            'X-Shopify-Access-Token: '.$storeDetails['access_token']
        ];
    }

    function getGraphQLHeadersForStore($storeDetails) {
        return [
            'Content-Type' => 'application/json',
            'X-Shopify-Access-Token' => $storeDetails['access_token'],
            'X-GraphQL-Cost-Include-Fields' => true
        ];
    }

