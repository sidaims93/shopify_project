<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {

    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function getImages() {
        return is_array($this->images) ? $this->images : json_decode($this->images, true); 
    }

}
