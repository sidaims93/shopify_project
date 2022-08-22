<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        
    }

    public function base(Request $request) {
        return redirect()->route('login');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        $user = Auth::user();
        if($user->hasRole('SuperAdmin')) {
            $payload = $this->getSuperAdminDashboardPayload($user);
            return view('superadmin.home', $payload);   
        } else {
            $store = $user->getShopifyStore;
            $payload = $this->getDashboardPayload($user, $store);
            return view('home', $payload);
        }
    }

    public function getSuperAdminDashboardPayload($user) {
        try {
            $stores_count = Store::count();
            $private_stores = Store::where('api_key', '<>', null)->where('api_secret_key', '<>', null)->count();
            $public_stores = Store::where('api_key', null)->where('api_secret_key', null)->count();
            return [
                'stores_count' => $stores_count,
                'private_stores' => $private_stores,
                'public_stores' => $public_stores
            ];
        } catch(Exception $e) {
            return [
                'stores_count' => 0,
                'private_stores' => 0,
                'public_stores' => 0
            ];
        }
    }

    private function getDashboardPayload($user, $store) {
        try {
            $orders = $store->getOrders();
            return [
                'orders_count' => $orders->count() ?? 0,
                'orders_revenue' => $orders->sum('total_price') ?? 0,
                'customers_count' => $store->getCustomers()->count() ?? 0
            ];
        } catch(Exception $e) {
            return [
                'orders_count' => 0,
                'orders_revenue' => 0,
                'customers_count' => 0
            ];
        }
    }
}
