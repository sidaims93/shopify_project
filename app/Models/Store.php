<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Store extends Model {
    
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'table_id';

    public function getCustomers() {
        return $this->hasMany(Customer::class, 'store_id', 'table_id');
    }

    public function getOrders() {
        return $this->hasMany(Order::class, 'store_id', 'table_id');
    }

    public function getProducts() {
        return $this->hasMany(Product::class, 'store_id', 'table_id');
    }

    public function getLocations() {
        return $this->hasMany(StoreLocation::class, 'store_id', 'table_id');
    }

    public function isPublic() {
        $private = isset($this->api_key) && isset($this->api_secret_key)
                && $this->api_key !== null && $this->api_secret_key !== null  
                && strlen($this->api_key) > 0 && strlen($this->api_secret_key) > 0;

        return !$private; // NOT Private means Public
    }

    public function getProductImagesForOrder($order) {
        try {
            $products_images = [];
            $product_ids = $order->getProductIdsForLineItems();
            $products = null;
            if($product_ids !== null && count($product_ids) > 0) {
                $products = $this->getProducts()->whereIn('id', $product_ids)->select(['id', 'images'])->get();
                if($products !== null && $products->count() > 0)
                    foreach($products as $product)
                        $products_images[$product->id] = $product->getImages();
            }
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
            return [];
        }
        return $products_images;
    }

    public function getOrdersPayload($payload) {
        $temp = [];
        foreach(config('custom.table_indexes.orders_table_indexes') as $column => $type)
            $temp[$column] = $payload[$column] ?? null;
        return $temp;
    }

    public function getLocationsPayload($payload) {
        $temp = [];
        foreach(config('custom.table_indexes.locations_table_indexes') as $column => $type)
            $temp[$column] = $payload[$column] ?? null;
        return $temp;
    }

    public function getOrderFulfillmentsPayload($payload) {
        $temp = [];
        foreach(config('custom.table_indexes.fulfillment_orders_table_index') as $column => $type)
            $temp[$column] = $payload[$column] ?? null;
        return $temp;
    }

    public function hasRegisteredForFulfillmentService() {
        return $this->fulfillment_service === 1 || $this->fulfillment_service === true;
    }

    /*
    public function getLocationsStorePayload() {
        $return_arr = [];
        try {
            $locations = $this->getLocations;
            if($locations !== null && $locations->count() > 0) 
                foreach($locations as $location)
                    if($location->isNotAFulfillmentServiceLocation())
                        $return_arr[$location['name']] = $location;
        } catch(Exception $e) {
            $return_arr = [];
        }
        return $return_arr;
    } */
}
