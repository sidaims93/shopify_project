<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function isNotAFulfillmentServiceLocation() {
        return $this->legacy === 0 || $this->legacy === false;
    }
}
