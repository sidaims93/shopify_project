<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FulfillmentOrderData extends Model
{
    use HasFactory;

    protected $casts = [
        'supported_actions' => 'array',
        'destination' => 'array',
        'line_items' => 'array',
        'delivery_method' => 'array',
        'assigned_location' => 'array',
        'merchant_requests' => 'array'
    ];
}
