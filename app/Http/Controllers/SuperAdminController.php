<?php

namespace App\Http\Controllers;

use App\Http\Requests\InstallPrivateApp;
use App\Models\Store;
use App\Models\User;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class SuperAdminController extends Controller {
    use RequestTrait;

    public function __construct() {
        $this->middleware(['auth', 'permission:all-access']);
    }

    public function index() {
        $stores = Store::select(['id', 'name', 'api_key', 'api_secret_key', 'access_token', 'myshopify_domain', 'created_at'])->get();
        return view('superadmin.stores.index', ['stores' => $stores]);
    }

    public function create() {
        return view('superadmin.stores.create');
    }

    public function store(InstallPrivateApp $request) {
        try {
            $store_arr = [
                'api_key' => $request->api_key,
                'api_secret_key' => $request->api_secret_key,
                'myshopify_domain' => $request->myshopify_domain,
                'access_token' => $request->access_token
            ];
            $endpoint = getShopifyURLForStore('shop.json', $store_arr);
            $headers = getShopifyHeadersForStore($store_arr);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
            if($response['statusCode'] == 200) {
                $shop_body = $response['body']['shop'];
                $newStore = Store::create(array_merge($store_arr, [
                    'id' => $shop_body['id'],
                    'email' => $shop_body['email'],
                    'name' => $shop_body['name'],
                    'phone' => $shop_body['phone'],
                    'address1' => $shop_body['address1'],
                    'address2' => $shop_body['address2'],
                    'zip' => $shop_body['zip']
                ]));
                $user_payload = [
                    'email' => $request['email'],
                    'password' => Hash::make($request['password']),
                    'store_id' => $newStore->table_id,
                    'name' => $shop_body['name']
                ];
                $user = User::updateOrCreate(['email' => $shop_body['email']], $user_payload);
                $user->markEmailAsVerified(); //To mark this user verified without requiring them to.
                $user->assignRole('Admin');
                foreach(config('custom.default_permissions') as $permission)
                    $user->givePermissionTo($permission);
                return back()->with('success', 'Installation Successful');
            } 
            return back()->withInput()->with('error', 'Incorrect Credentials');
        } catch(Exception $e) {
            return back()->with('error', $e->getMessage().' '.$e->getLine());          
        } 
    }

    public function sendIndex() {
        return view('superadmin.notifications.index', ['users' => User::where('id', '<>', Auth::user()->id)->select(['id','name',])->get()->toArray()]);
    }

    public function sendMessage(Request $request) {
        try {
            $user = User::where('id', (int) $request->user)->select(['id', 'name'])->first();
            $channel = $user->getChannelName('messages'); //Function in User.php Model
            $response = $this->sendSocketIONotification($channel, $request->message);
            return response()->json(['status' => true, 'message' => 'Sent message!', 'response' => $response]);    
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' =>$e->getMessage().' '.$e->getLine()]);
        }
    }
}
