<?php

namespace App\Jobs\Shopify\Sync;

use App\Traits\RequestTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Order implements ShouldQueue {
    private $store;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RequestTrait;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $store) {
        $this->user = $user;
        $this->store = $store;
        $this->indexes_to_insert = config('custom.table_indexes.orders_table_indexes');
    }

    public function handle() {
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
                            $temp_payload[$key] = is_array($v) ? json_encode($v) : $v;
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
