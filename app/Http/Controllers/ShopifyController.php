<?php

namespace App\Http\Controllers;

use App\Jobs\Shopify\Sync\Customer;
use App\Jobs\Shopify\Sync\Locations;
use App\Jobs\Shopify\Sync\Order;
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
        $user = Auth::user();
        $store = $user->getShopifyStore;
        $orders = $store->getOrders()->select(['name', 'email', 'phone', 'created_at'])->get();
        return view('orders.index', ['orders' => $orders]);
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
            Product::dispatch($user, $store);
            return back()->with('success', 'Product sync successful');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }

    public function syncCustomers() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Customer::dispatch($user, $store);
            return back()->with('success', 'Customer sync successful');
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error :'.$e->getMessage().' '.$e->getLine()]);
        }
    }


    //Sync orders for Store using either GraphQL or REST API
    public function syncOrders() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Order::dispatch($user, $store, 'GraphQL'); //For using GraphQL API
            //Order::dispatch($user, $store); //For using REST API
            return back()->with('success', 'Order sync successful');
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
            if($request->ajax()) {
                $request = $request->all();
                $store = Auth::user()->getShopifyStore; //Take the auth user's shopify store
                $customers = $store->getCustomers(); //Load the relationship (Query builder)
                $customers = $customers->select(['first_name', 'last_name', 'email', 'phone', 'created_at']); //Select columns
                if(isset($request['search']) && isset($request['search']['value'])) 
                    $customers = $this->filterCustomers($customers, $request); //Filter customers based on the search term
                $count = $customers->count(); //Take the total count returned so far
                $limit = $request['length'];
                $offset = $request['start'];
                $customers = $customers->offset($offset)->limit($limit); //LIMIT and OFFSET logic for MySQL
                if(isset($request['order']) && isset($request['order'][0]))
                    $customers = $this->orderCustomers($customers, $request); //Order customers based on the column
                $data = [];
                $query = $customers->toSql(); //For debugging the SQL query generated so far
                $rows = $customers->get(); //Fetch from DB by using get() function
                if($rows !== null)
                    foreach ($rows as $key => $item)
                        $data[] = array_merge(
                                        ['#' => $key + 1], //To show the first column, NOTE: Do not show the table_id column to the viewer
                                        $item->toArray()
                                );
                return response()->json([
                    "draw" => intval(request()->query('draw')),
                    "recordsTotal"    => intval($count),
                    "recordsFiltered" => intval($count),
                    "data" => $data,
                    "debug" => [
                        "request" => $request,
                        "sqlQuery" => $query
                    ]
                ], 200);
            }
            
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()], 500);
        }
    }

    //Returns a Query builders after setting the logic for ordering customers by specified column
    public function orderCustomers($customers, $request) {
        $column = $request['order'][0]['column'];
        $dir = $request['order'][0]['dir'];
        $db_column = null;
        switch($column) {
            case 0: $db_column = 'table_id'; break;
            case 1: $db_column = 'first_name'; break;
            case 2: $db_column = 'email'; break;
            case 3: $db_column = 'phone'; break; 
            case 4: $db_column = 'created_at'; break;
            default: $db_column = 'table_id';
        }
        return $customers->orderBy($db_column, $dir);   
    }

    //Returns a Query builder after setting the logic for filtering customers by the search term
    public function filterCustomers($customers, $request) {
        $term = $request['search']['value'];
        return $customers->where(function ($query) use ($term) {
            $query->where(
                        DB::raw("CONCAT(`first_name`, ' ', `last_name`)"), 'LIKE', "%".$term."%"
                    )
                  ->orWhere('email', 'LIKE', '%'.$term.'%')
                  ->orWhere('phone', 'LIKE', '%'.$term.'%');
        });
    }
    
    public function syncLocations() {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            Locations::dispatch($user, $store);
            return back()->with('success', 'Locations synced successfully');
        } catch(Exception $e) {
            dd($e->getMessage().' '.$e->getLine());
        }
    }
}
