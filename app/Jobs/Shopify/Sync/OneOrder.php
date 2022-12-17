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

class OneOrder implements ShouldQueue {
    private $user, $store, $order_id, $indexes_to_insert;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RequestTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $store, $order_id) {
        $this->user = $user;
        $this->store = $store;
        $this->order_id = $order_id;
        $this->indexes_to_insert = config('custom.table_indexes.orders_table_indexes');
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

    public function handle() {
        try{
            $payload = [];
            do{
                $orders_payload = [];
                $endpoint = getShopifyURLForStore('orders/'.$this->order_id.'.json', $this->store);
                $headers = getShopifyHeadersForStore($this->store, 'GET');
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
                if(isset($response) && isset($response['statusCode']) && $response['statusCode'] === 200 && is_array($response) && is_array($response['body']['order']) && count($response['body']['order']) > 0) {
                    $payload = $response['body']['order'];
                    $temp_payload = [];    
                    foreach($payload as $key => $v)
                        $temp_payload[$key] = is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v;
                    $temp_payload = $this->store->getOrdersPayload($temp_payload);
                    $temp_payload['store_id'] = (int) $this->store->table_id;
                    $province_and_country = $this->getShippingAddressProvinceAndCountry($payload);
                    $temp_payload = array_merge($province_and_country, $temp_payload);
                    $orders_payload[] = $temp_payload;
                
                    $ordersTableString = $this->getOrdersTableString($orders_payload);
                    if($ordersTableString !== null)
                        $this->insertOrders($ordersTableString);    
                } else { $payload = null; } 
            } while(false);
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
