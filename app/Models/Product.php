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

    public function getAddToCartStatus() {
        $targetTag = config('custom.add_to_cart_tag_product');
        if(strlen($this->tags) > 0) {
            $tags = explode(', ', $this->tags);
            if($tags !== null && is_array($tags) && count($tags) > 0) {
                if(in_array($targetTag, $tags)) {
                    return [
                        'status' => true,
                        'message' => 'Enable Add to Cart'
                    ];
                } else {
                    return [
                        'status' => false,
                        'message' => 'Remove Add to Cart'
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'message' => 'Remove Add to Cart'
                ];
            }
        } else {
            //No tags found.
            return [
                'status' => false,
                'message' => 'Remove Add to Cart'
            ];
        }
        return $this->tags;
    }
}
