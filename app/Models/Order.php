<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
    
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'shipping_address' => 'array',
        'line_items' => 'array',
        'billing_address' => 'array',
        'fulfillments' => 'array',
        'customer' => 'array',
        'shipping_lines' => 'array',
        'discount_applications' => 'array',
        'total_shipping_price_set' => 'array',
        'total_price_set' => 'array',
        'total_tax_set' => 'array',
        'refunds' => 'array',
        'payment_gateway_names' => 'array',
        'total_discounts_set' => 'array',
        'subtotal_price_set' => 'array',
        'tax_lines' => 'array',
        'discount_codes' => 'array',
        'shipping_lines' => 'array'
    ];

    public function getOrderFulfillmentsInfo() {
        return $this->hasMany(OrderFulfillment::class, 'order_id', 'table_id');
    }

    public function getFulfillmentOrderDataInfo() {
        return $this->hasMany(FulfillmentOrderData::class, 'order_table_id', 'table_id');
    }

    public function getLineItems() {
        return is_array($this->line_items) ? $this->line_items : json_decode($this->line_items, true);
    }

    public function getProductIdsForLineItems() {
        $line_items = $this->getLineItems();
        $return_val = [];
        if(is_array($line_items) && count($line_items) > 0) 
            foreach($line_items as $item)
                $return_val[] = $item['product_id'];
        return $return_val;
    }

    public function getPaymentStatus() {
        switch($this->financial_status) {
            case 'paid': return 'Paid';
            case 'pending': return 'COD';
            case 'partially_refunded': return 'Partially Refunded';
            default: return $this->financial_status;
        }
    }

    public function getFulfillmentStatus() {
        return strlen($this->fulfillment_status) > 0 ? ucwords($this->fulfillment_status) : 'Unfulfilled';
    }

    public function getDiscountBreakDown() {
        $returnArr = [];
        $discounts = is_array($this->discount_applications) ? $this->discount_applications : json_decode($this->discount_applications, true);
        if($discounts && $discounts !== null && count($discounts) > 0) {
            foreach($discounts as $discount) {
                if(isset($discount['title']) && isset($discount['value_type']))
                    $returnArr[$discount['title'] ?? $discount['code']] = $discount['value_type'] == 'percentage' ? $this->total_line_items_price * $discount['value'] / 100 : $discount['value'];
            }
        }
        return $returnArr;
    }

}
