<?php

namespace App\Http\Controllers;

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
        //$this->middleware('auth');
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
        $store = $user->getShopifyStore;
        $payload = $this->getDashboardPayload($user, $store);
        return view('home', $payload);
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
