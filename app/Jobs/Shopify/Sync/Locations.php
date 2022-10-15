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

class Locations implements ShouldQueue
{
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
        $this->indexes_to_insert = config('custom.table_indexes.locations_table_indexes');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        try{
            $payload = [];
            $locations_payload = [];
            $endpoint = getShopifyURLForStore('locations.json', $this->store);
            $headers = getShopifyHeadersForStore($this->store, 'GET');
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
            if(isset($response) && isset($response['statusCode']) && $response['statusCode'] === 200 && is_array($response) && is_array($response['body']['locations']) && count($response['body']['locations']) > 0) {
                $payload = $response['body']['locations'];
                foreach($payload as $shopifyLocationJsonArray){
                    $temp_payload = [];
                    foreach($shopifyLocationJsonArray as $key => $v)
                        $temp_payload[$key] = is_array($v) ? json_encode($v) : $v;
                    $temp_payload = $this->store->getLocationsPayload($temp_payload);
                    $temp_payload['store_id'] = (int) $this->store->table_id;
                    $locations_payload[] = $temp_payload;
                } 
                $locationsTableString = $this->getLocationsTableString($locations_payload);
                if($locationsTableString !== null)
                    $this->insertLocations($locationsTableString);    
            }
        } catch (Exception $e) { 
            Log::critical(['code' => $e->getCode(), 'message' => $e->getMessage(), 'trace' => json_encode($e->getTrace())]); 
            throw $e;
        }
    }

    private function getLocationsTableString($locations_payload) {
        $locationsTableString = [];
        if($locations_payload !== null && is_array($locations_payload) && count($locations_payload) > 0) {
            foreach($locations_payload as $payload) {
                $tempString = '(';
                foreach($this->indexes_to_insert as $index => $dataType) {
                    settype($payload[$index], $dataType);
                    $tempString .= (gettype($payload[$index]) === 'string' ? "'".str_replace("'", '', $payload[$index])."'" : $payload[$index] ?? null).',';
                }
                $tempString = rtrim($tempString, ',');
                $tempString .= ')';
                $locationsTableString[] = $tempString;
            }
        }
        return count($locationsTableString) > 0 ? implode(',', $locationsTableString) : null;
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
    private function insertLocations($locationsTableString){
		try {
            $updateString = $this->getUpdateString();
            $insertString = $this->getIndexString();
            $query = "INSERT INTO `store_locations` (".$insertString.") VALUES ".$locationsTableString." ON DUPLICATE KEY UPDATE ".$updateString;
            DB::insert($query); 
            return true;
        } catch(\Exception $e) {
            dd($e->getMessage().' '.$e->getLine() );
            return false;
        }
	}
}
