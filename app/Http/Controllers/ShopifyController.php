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
        //$customers = $store->getCustomers()->select(['first_name', 'last_name', 'email', 'phone', 'created_at'])->get();
        return view('customers.index');
    }

    public function list(Request $request) {
        try {
            
            $limit = $request['length'];
            $offset = $request['start'];
        
            $store = Auth::user()->getShopifyStore; //Take the auth user's store
            $customers = $store->getCustomers(); //Load the relationship (Query builder)

            $columns = ['first_name', 'last_name', 'email', 'phone', 'created_at'];
            $customers = $customers->select($columns);
            $customers = $this->filterCustomers($customers, $request);

            $count = $customers->count();        
            $customers = $customers->offset($offset)->limit($limit);
            $customers = $customers->orderBy('created_at', 'desc');
            $data = [];
            $rows = $customers->get();
            if($rows !== null)
                foreach ($rows as $key => $item)
                    $data[] = array_merge(['#' => $key + 1], $item->toArray());
            
            return response()->json([
                "draw" => intval(request()->query('draw')),
                "recordsTotal"    => intval($count),
                "recordsFiltered" => intval($count),
                "data" => $data
            ], 200);

        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function filterCustomers($customers, $request) {
        return $customers->where(function ($e) use ($request) {
            if(isset($request['searchTerm']) && !is_null($request['searchTerm'])) {
                
                if($request['searchBy'] === 'name') 
                    $e->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%".$request['searchTerm']."%");
                
                if($request['searchBy'] === 'email') 
                    $e->where('email', 'LIKE', '%'.$request['searchTerm'].'%');
                
                if($request['searchBy'] === 'phone') 
                    $e->where('phone', 'LIKE', '%'.$request['searchTerm'].'%');
                    
            }
        });
    }
}
