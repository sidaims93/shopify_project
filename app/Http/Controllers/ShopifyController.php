<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\Sync\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopifyController extends Controller
{
    public function __construct() {   
        $this->middleware('auth');
    }

    public function orders() {
        return view('orders.index');
    }

    public function products() {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $products = $store->getProducts()->select(['title', 'product_type', 'vendor', 'created_at'])->get();
        return view('products.index', ['products' => $products]);
    }

    public function syncProducts() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Product::dispatchNow($user, $store);
            return back()->with('success', 'Product sync successful');
        } catch(Exception $e) {
            dd($e->getMessage());
        }
    }

    public function customers() {
        return view('customers.index');
    }
}
