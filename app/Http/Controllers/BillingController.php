<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\UserPlans;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller {
    use FunctionTrait, RequestTrait;

    public function index() {
        $user = Auth::user();
        $plans = Plan::where('price', '>', 0)
                     ->where('status', true)
                     ->select(['id', 'name', 'credits', 'price'])
                     ->get();

        $last_plan_info = Auth::user()->getLastPlanInfo;
        $credits = $user->getCredits();
        
        return view('billing.index', ['plans' => $plans, 'last_plan_info' => $last_plan_info, 'credits' => $credits]);
    }

    public function buyThisPlan($id) {
        try {
            $user = Auth::user();
            $store = $user->getShopifyStore;
            $plan = Plan::where('id', $id)->first();
            $endpoint = getShopifyURLForStore('recurring_application_charges.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $payload = [
                'recurring_application_charge' => [
                    'name' => config('app.name').' Charge for Plan '.$plan->name,
                    'price' => (float) $plan->price,
                    'test' => 'true',
                    'return_url' => config('app.url').'shopify/rac/accept?plan_id='.$plan->id
                ]
            ];
            $response = $this->makeAnAPICallToShopify('POST', $endpoint, null, $headers, $payload);
            if($response['statusCode'] === 201) {
                $body = $response['body']['recurring_application_charge'];
                return redirect($body['confirmation_url']);
            }
            return back()->with('error', 'Some problem occurred. Please try again in some time.');
        } catch(Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
            return back()->with('error', 'Some problem occurred. Please try again in some time.');
        }
    }

    public function acceptSubscriptionCharge(Request $request) {
        try {
            $request = $request->only(['charge_id', 'plan_id']);
            $user = Auth::user();
            $store = $user->getShopifyStore;
            $plan_id = $request['plan_id'];
            $plan = Plan::where('id', $plan_id)->first();
            $endpoint = getShopifyURLForStore('recurring_application_charges/'.$request['charge_id'].'.json', $store);
            $headers = getShopifyHeadersForStore($store);
            $response = $this->makeAnAPICallToShopify('GET', $endpoint, null, $headers);
            if($response['statusCode'] === 200) {
                $body = $response['body']['recurring_application_charge'];
                if($body['status'] && $body['status'] === 'active') {
                    UserPlans::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan_id,
                        'credits' => $plan->credits,
                        'price' => $plan->price
                    ]);
                    $user->assignCredits($plan->credits);
                    return redirect()->route('billing.index')->with('success', 'Plan purchased successfully');
                }
            }
            return back()->with('error', 'Some problem occurred. Please try again after some time.');
        } catch(Exception $e) {
            Log::info($e->getMessage());
            return back()->with('error', 'Some problem occurred. Please try again after some time.');
        }
    }

    public function consumeCredits(Request $request) {
        try {
            $user = Auth::user();
            $user->consumeCredits(5000);
            return redirect()->route('billing.index');
        } catch(Exception $e) {
            return redirect()->route('billing.index')->with('error', $e->getMessage());
        }
    }
}
