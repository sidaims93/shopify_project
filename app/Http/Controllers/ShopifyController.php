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
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    public function syncCustomers() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Customer::dispatchNow($user, $store);
            return back()->with('success', 'Customer sync successful');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
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
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    public function customers() {
        return view('customers.index');
    }

    public function list(Request $request) {
        try {
            $request = $request->all();
            $limit = $request['length'];
            $offset = $request['start'];
        
            $store = Auth::user()->getShopifyStore; //Take the auth user's store
            $customers = $store->getCustomers(); //Load the relationship (Query builder)

            $customers = $customers->select(['first_name', 'last_name', 'email', 'phone', 'created_at']);
            
            if(isset($request['search']) && isset($request['search']['value'])) 
                $customers = $this->filterCustomers($customers, $request);

            $count = $customers->count();        
            $customers = $customers->offset($offset)->limit($limit);
            if(isset($request['order']) && isset($request['order'][0]))
                $customers = $this->orderCustomers($customers, $request);
            
            $data = [];
            $rows = $customers->get();
            if($rows !== null)
                foreach ($rows as $key => $item)
                    $data[] = array_merge(
                                    ['#' => $key + 1], 
                                    $item->toArray()
                              );
            
            return response()->json([
                "draw" => intval(request()->query('draw')),
                "recordsTotal"    => intval($count),
                "recordsFiltered" => intval($count),
                "data" => $data,
                "Request" => $request
            ], 200);

        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function orderCustomers($customers, $request) {
        $column = $request['order'][0]['column'];
        $dir = $request['order'][0]['dir'];
        $db_column = '';
        switch($column) {
            case 0: $db_column = 'table_id'; break;
            case 1: $db_column = 'first_name'; break;
            case 2: $db_column = 'email'; break;
            case 3: $db_column = 'phone'; break; 
            case 4: $db_column = 'created_at'; break;
        }
        return $customers->orderBy($db_column, $dir);   
    }

    public function filterCustomers($customers, $request) {
        return $customers->where(function ($e) use ($request) {
            $term = $request['search']['value'];
            $e->where(function ($query) use ($term) {
                $query->where(DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%".$term."%")
                    ->orWhere('email', 'LIKE', '%'.$term.'%')
                    ->orWhere('phone', 'LIKE', '%'.$term.'%');

            });
        });
    }
}
