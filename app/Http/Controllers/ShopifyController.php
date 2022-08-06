<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\Sync\Customer;
use App\Jobs\Shopify\Sync\Product;
use App\Models\User;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShopifyController extends Controller {

    use RequestTrait;
    
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

    public function syncCustomers() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Customer::dispatchNow($user, $store);
            return back()->with('success', 'Customer sync successful');
        } catch(Exception $e) {
            dd($e->getMessage());
        }
    }

    public function acceptCharge(Request $request) {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            $charge_id = $request->charge_id;
            $user_id = $request->user_id;
            $endpoint = getShopifyURLForStore('application_charges/'.$charge_id.'.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
            if($response['statusCode'] === 200) {
                $body = $response['body']['application_charge'];
                if($body['status'] === 'active') {
                    return redirect()->route('members.create')->with('success', 'Sub user created!');
                }   
            }
            User::where('id', $user_id)->delete();
            return redirect()->route('members.create')->with('error', 'Some problem occurred while processing the transaction. Please try again.');
        } catch(Exception $e) {

        }
    }

    public function customers() {
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $customers = $store->getCustomers()->select(['first_name', 'last_name', 'email', 'phone', 'created_at'])->get();
        return view('customers.index', ['customers' => $customers]);
    }
}
