<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopifyController extends Controller
{
    public function __construct() {   
        $this->middleware('auth');
    }

    public function orders() {
        return view('orders.index');
    }

    public function products() {
        return view('products.index');
    }

    public function customers() {
        return view('customers.index');
    }
}
