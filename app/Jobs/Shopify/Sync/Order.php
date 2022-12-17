<?php

namespace App\Jobs\Shopify\Sync;

use App\Traits\RequestTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order implements ShouldQueue {
    private $user, $store, $mode, $indexes_to_insert;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RequestTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $store, $mode = 'REST') {
        $this->user = $user;
        $this->store = $store;
        $this->mode = $mode;
        $this->indexes_to_insert = config('custom.table_indexes.orders_table_indexes');
    }

    public function handle() {
        if($this->mode === 'GraphQL')
            $this->handleWithGraphQLAPI();
        else
            $this->handleWithRESTAPI();
    }

    public function handleWithGraphQLAPI() {
        try{
            $headers = getGraphQLHeadersForStore($this->store);
            $endpoint = getShopifyURLForStore('graphql.json', $this->store);
            $cursor = null;
            do {
                $query = $this->getQueryObjectForOrders($cursor);
                $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $query);
                if($response['statusCode'] === 200) 
                    $this->saveOrderResponseInDB($response['body']['data']['orders']['edges']);
                $cursor = $this->getCursorFromResponse($response['body']['data']['orders']['pageInfo']);
            } while($cursor !== null);
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
        }
    }

    private function getCursorFromResponse($pageInfo) {
        try {
            return $pageInfo['hasNextPage'] === true ? $pageInfo['endCursor'] : null;
        } catch(Exception $e) {
            Log::info($e->getMessage());
            return null;
        }
    }

    private function saveOrderResponseInDB($orders) {
        $db_orders = [];
        if($orders !== null && count($orders) > 0) {
            foreach($orders as $order) {
                $db_orders[] = $this->formatOrderForDB($order['node']);
            }
        }
        $ordersTableString = $this->getOrdersTableString($db_orders);
        if($ordersTableString !== null)
            $this->insertOrders($ordersTableString); 
    }

    private function formatOrderForDB($order) {
        $temp_payload = [];
        foreach($order as $attribute => $value) {
            $key = $this->getEquivalentDBColumnForGraphQLKey($attribute);
            if($key !== null)
                $temp_payload[$key] = is_array($value) ? json_encode($value) : $value;
        }
        $temp_payload['store_id'] = $this->store->table_id;
        $temp_payload['line_items'] = $this->formatLineItems($order['lineItems']);
        $temp_payload['shipping_address'] = $this->formatBillingAndShippingAddress($order['shippingAddress']);
        $temp_payload['billing_address'] = $this->formatBillingAndShippingAddress($order['billingAddress']);
        $temp_payload['fulfillments'] = $this->formatFulfillmentsForOrder($order['fulfillments']);
        foreach($temp_payload as $key => $val)
            $temp_payload[$key] = is_array($val) ? json_encode($val) : $val;
        return $temp_payload;
    }

    private function formatFulfillmentsForOrder($fulfillments) {
        try {
            return $fulfillments;
        } catch(Exception $e) {
            return null;
        }
    }

    private function formatBillingAndShippingAddress($shippingAddress) {
        try {
            return [
                'first_name' => $shippingAddress['firstName'],
                'address1' => $shippingAddress['address1'],
                'phone' => $shippingAddress['phone'],
                'city' => $shippingAddress['city'],
                'zip' => $shippingAddress['zip'],
                'province' => $shippingAddress['province'],
                'country' => $shippingAddress['country'],
                'last_name' => $shippingAddress['lastName'],
                'address2' => $shippingAddress['address2'],
                'name' => $shippingAddress['firstName'].' '.$shippingAddress['lastName'],
            ];
        } catch(Exception $e) {
            return null;
        }
    }

    private function formatLineItems($lineItems) {
        try {
            $arr = [];
            $edges = $lineItems['edges'];
            foreach($edges as $nodes) {
                $item = $nodes['node'];
                $arr[] = [
                    'id' => (int) str_replace('gid://shopify/LineItem/', '', $item['id']),
                    'admin_graphql_api_id' => $item['id'],
                    'fulfillable_quantity' => $item['unfulfilledQuantity'],
                    'name' => $item['title'],
                    'variant_title' => $item['variantTitle'],
                    'vendor' => $item['vendor'],
                    'sku' => $item['sku'],
                    'quantity' => $item['quantity'],
                    'price' => $item['variant']['price'],
                    'price_set' => $item['originalTotalSet'],
                    'product_id' => (int) str_replace('gid://shopify/Product/', '', $item['product']['id']),
                    'variant_id' => (int) str_replace('gid://shopify/ProductVariant/', '', $item['variant']['id']),
                    'variant_title' => $item['variant']['title']
                ];
            }
            return $arr;
        } catch(Exception $e) {
            return null;
        }
    }

    private function getEquivalentDBColumnForGraphQLKey($attribute) {
        switch($attribute) {
            case 'email': return 'email';
            case 'name': return 'name';
            case 'processedAt': return 'processed_at';
            case 'taxesIncluded': return 'taxes_included';
            case 'legacyResourceId': return 'id';
            case 'displayFinancialStatus': return 'financial_status';
            case 'closedAt': return 'closed_at';
            case 'cancelReason': return 'cancel_reason';
            case 'cancelledAt': return 'cancelled_at';
            case 'createdAt': return 'created_at';
            case 'updatedAt': return 'updated_at';
            case 'tags': return 'tags';
            case 'phone': return 'phone';
            default: return null;
        }
    }

    public function getQueryObjectForOrders($cursor) {
        try {
            $query = '{';
            $filter = '(first : 5'. ($cursor !== null ? ', after : "'.$cursor.'"' : null).')';
            $query .= '  orders'.$filter.' { 
                            edges { 
                                node { 
                                    id email name processedAt registeredSourceUrl taxesIncluded 
                                    legacyResourceId fulfillable customerLocale phone
                                    displayFinancialStatus confirmed closed closedAt cancelReason cancelledAt 
                                    createdAt updatedAt tags
                                    lineItems (first: 20) {
                                        edges {
                                            node { 
                                                id image { id altText url width } name nonFulfillableQuantity 
                                                originalTotalSet { presentmentMoney { amount currencyCode } shopMoney { amount currencyCode } }
                                                product { id productType title vendor updatedAt tags publishedAt handle descriptionHtml description createdAt } 
                                                quantity sku taxLines { priceSet { presentmentMoney { amount currencyCode } shopMoney { amount currencyCode } } rate ratePercentage title }  
                                                taxable title unfulfilledQuantity variantTitle variant { barcode compareAtPrice createdAt displayName id image { id altText url width } inventoryQuantity price title updatedAt } vendor 
                                            }
                                        }
                                        pageInfo { 
                                            hasNextPage endCursor hasPreviousPage startCursor 
                                        } 
                                    }
                                    fulfillments { createdAt deliveredAt displayStatus estimatedDeliveryAt id inTransitAt legacyResourceId location {id name} name status totalQuantity trackingInfo {company number url} } 
                                    totalPriceSet { presentmentMoney { amount currencyCode } shopMoney { amount currencyCode } } 
                                    shippingLine { carrierIdentifier id title custom code phone originalPriceSet { presentmentMoney { amount currencyCode } shopMoney { amount currencyCode } } source shippingRateHandle }
                                    shippingAddress { address1 address2 city country firstName lastName phone province zip }
                                    billingAddress { address1 address2 city country firstName lastName phone province zip }
                                    fulfillments { id createdAt updatedAt deliveredAt displayStatus estimatedDeliveryAt legacyResourceId name status trackingInfo { company number url } updatedAt }
                                    customer { canDelete createdAt displayName email firstName  hasTimelineComment locale note updatedAt id lastName }
                                    currentSubtotalPriceSet { presentmentMoney { amount currencyCode } shopMoney { amount currencyCode } }
                                    currentTaxLines { channelLiable priceSet { presentmentMoney { amount currencyCode } shopMoney { amount currencyCode } } rate ratePercentage title }
                                } 
                            } 
                            pageInfo { 
                                hasNextPage endCursor hasPreviousPage startCursor 
                            } 
                        }';
            $query .= '}';
            return ['query' => $query];
        } catch(Exception $e) {
            return null;
        }
    }

    public function handleWithRESTAPI() {
        try{
            $since_id = 0;
            $payload = [];
            do{
                $orders_payload = [];
                $endpoint = getShopifyURLForStore('orders.json?since_id='.$since_id, $this->store);
                $headers = getShopifyHeadersForStore($this->store, 'GET');
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
                if(isset($response) && isset($response['statusCode']) && $response['statusCode'] === 200 && is_array($response) && is_array($response['body']['orders']) && count($response['body']['orders']) > 0) {
                    $payload = $response['body']['orders'];
                    foreach($payload as $shopifyOrderJsonArray){
                        $temp_payload = [];
                        foreach($shopifyOrderJsonArray as $key => $v)
                            $temp_payload[$key] = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
                        $temp_payload = $this->store->getOrdersPayload($temp_payload);
                        $temp_payload['store_id'] = (int) $this->store->table_id;
                        $province_and_country = $this->getShippingAddressProvinceAndCountry($shopifyOrderJsonArray);
                        $temp_payload = array_merge($province_and_country, $temp_payload);
                        $since_id = $shopifyOrderJsonArray['id'];
                        $orders_payload[] = $temp_payload;
                    } 
                    $ordersTableString = $this->getOrdersTableString($orders_payload);
                    if($ordersTableString !== null)
                        $this->insertOrders($ordersTableString);    
                } else { $payload = null; } 
            } while($payload !== null && count($payload) > 0);
        } catch (Exception $e) { 
            Log::critical(['code' => $e->getCode(), 'message' => $e->getMessage(), 'trace' => json_encode($e->getTrace())]); 
            throw $e;
        }
    }

    private function getOrdersTableString($orders_payload) {
        $ordersTableString = [];
        if($orders_payload !== null && is_array($orders_payload) && count($orders_payload) > 0) {
            foreach($orders_payload as $payload) {
                $tempString = '(';
                foreach($this->indexes_to_insert as $index => $dataType) {
                    settype($payload[$index], $dataType);
                    $tempString .= (gettype($payload[$index]) === 'string' ? "'".str_replace("'", '', $payload[$index])."'" : $payload[$index] ?? null).',';
                }
                $tempString = rtrim($tempString, ',');
                $tempString .= ')';
                $ordersTableString[] = $tempString;
            }
        }
        return count($ordersTableString) > 0 ? implode(',', $ordersTableString) : null;
    }

    private function getShippingAddressProvinceAndCountry($shopifyOrderJsonArray) {
        try {
            return [
                'ship_country' => $shopifyOrderJsonArray['shipping_address']['country'], 
                'ship_province' => $shopifyOrderJsonArray['shipping_address']['province']
            ];
        } catch(Exception $e) {
            Log::info($e->getMessage());
            return ['ship_country' => null, 'ship_province' => null];
        }
    }

    private function getUpdateString() {
        $returnString = [];
        foreach($this->indexes_to_insert as $index => $dataType)
            $returnString[] = $index.' = VALUES(`'.$index.'`)'; 
        return implode(', ', $returnString);
    }

    private function getIndexString() {
        $returnString = [];
        foreach($this->indexes_to_insert as $index => $dataType) 
            $returnString[] = '`'.$index.'`';
        return implode(', ', $returnString);
    }

    //The function that carries out the DB query to insert orders into the table
    private function insertOrders($ordersTableString){
		try {
            $updateString = $this->getUpdateString();
            $insertString = $this->getIndexString();
            $query = "INSERT INTO `orders` (".$insertString.") VALUES ".$ordersTableString." ON DUPLICATE KEY UPDATE ".$updateString;
            DB::insert($query); 
            return true;
        } catch(\Exception $e) {
            dd($e->getMessage().' '.$e->getLine() );
            return false;
        }
	}
}
