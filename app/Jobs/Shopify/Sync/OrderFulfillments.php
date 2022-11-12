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

class OrderFulfillments implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RequestTrait;
    private $user, $store, $order, $indexes_to_insert;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $store, $order) {
        $this->user = $user;
        $this->store = $store;
        $this->order = $order;
        $this->indexes_to_insert = config('custom.table_indexes.fulfillment_orders_table_index');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try{
            $since_id = 0;
            $payload = [];
            do{
                $orders_payload = [];
                $endpoint = getShopifyURLForStore('orders/'.$this->order->id.'/fulfillment_orders.json?since_id='.$since_id, $this->store);
                $headers = getShopifyHeadersForStore($this->store);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
                if(isset($response) && isset($response['statusCode']) && $response['statusCode'] === 200 && is_array($response) && is_array($response['body']['fulfillment_orders']) && count($response['body']['fulfillment_orders']) > 0) {
                    $payload = $response['body']['fulfillment_orders'];
                    foreach($payload as $shopifyFulfillmentOrderArray){
                        $temp_payload = [];
                        foreach($shopifyFulfillmentOrderArray as $key => $v)
                            $temp_payload[$key] = is_array($v) ? json_encode($v) : $v;
                        $temp_payload = $this->store->getOrderFulfillmentsPayload($temp_payload);
                        $temp_payload['order_table_id'] = (int) $this->order->table_id;                        
                        $orders_payload[] = $temp_payload;
                        $since_id = $shopifyFulfillmentOrderArray['id'];
                    } 
                    $ordersTableString = $this->getFulfillmentOrdersTableString($orders_payload);
                    if($ordersTableString !== null)
                        $this->insertFulfillmentOrders($ordersTableString);    
                } else { $payload = null; } 
            } while($payload !== null && count($payload) > 0);
        } catch (Exception $e) { 
            Log::critical(['code' => $e->getCode(), 'message' => $e->getMessage(), 'trace' => json_encode($e->getTrace())]); 
            throw $e;
        }
        
    }

    private function getFulfillmentOrdersTableString($payload) {
        $ordersTableString = [];
        if($payload !== null && is_array($payload) && count($payload) > 0) {
            foreach($payload as $payload) {
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
    private function insertFulfillmentOrders($fulfillmentOrdersTableString){
		try {
            $updateString = $this->getUpdateString();
            $insertString = $this->getIndexString();
            $query = "INSERT INTO `fulfillment_order_data` (".$insertString.") VALUES ".$fulfillmentOrdersTableString." ON DUPLICATE KEY UPDATE ".$updateString;
            DB::insert($query); 
            return true;
        } catch(\Exception $e) {
            dd($e->getMessage().' '.$e->getLine() );
            return false;
        }
	}
}
