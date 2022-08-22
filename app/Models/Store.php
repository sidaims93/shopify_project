<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function isPublic() {
        $private = isset($this->api_key) && isset($this->api_secret_key)
                && $this->api_key !== null && $this->api_secret_key !== null  
                && strlen($this->api_key) > 0 && strlen($this->api_secret_key) > 0;

        return !$private; // NOT Private means Public
    }
}
