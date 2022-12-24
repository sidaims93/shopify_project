<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlans;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller {
    
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        $user = Auth::user();
        $intent = $user->createSetupIntent();
        $last_plan_info = $user->getLastPlanInfo;
        $one_time_payments = config('custom.one_time_payments');
        $plans = SubscriptionPlans::get();
        return view('subscriptions.index', ['one_time_payments' => $one_time_payments,'last_plan_info' => $last_plan_info, 'user' => $user, 'plans' => $plans, 'intent' => $intent, 'stripe_key' => config('custom.stripe_api_key')]);
    }

    public function addCardToUser(Request $request) {
        try{
            if($request->ajax()) {
                $request = $request->all()['card_details'];
                $user = Auth::user();
                /**
                 * {
                    *    "cancellation_reason" :  null,
                    *    "client_secret" :  "seti_1MFye7SJPy7VH3kildERkTTI_secret_MzybMIDjf73iwAX7DzBEXmKbPIXyuk0",
                    *    "created" :  1671276819,
                    *    "description" :  null,
                    *    "id" :  "seti_1MFye7SJPy7VH3kildERkTTI",
                    *    "last_setup_error" :  null,
                    *    "livemode" :  false,
                    *    "next_action" :  null,
                    *    "object" :  "setup_intent",
                    *    "payment_method" :  "pm_1MFyejSJPy7VH3kibj14wssW",
                    *    "payment_method_types" : [ "card"],
                    *    "status" :  "succeeded",
                    *    "usage" :  "off_session"
                *  }
                 */
                $user->addPaymentMethod($request['payment_method']);
                return response()->json(['status' => true, 'message' => 'Added Card!']);
            }
        } catch(Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage().' '.$e->getLine()]);
        }
    }

    public function purchaseSubscription($id) {
        try {
            $user = Auth::user();
            $plan = SubscriptionPlans::where('id', $id)->first();
            
            if($user->hasPaymentMethod()) {
                
                $paymentMethod = $user->paymentMethods()->toArray()[0];
                $subscription_object = $user->newSubscription(strtoupper($plan->stripe_plan_id), $plan->stripe_plan_id)->create($paymentMethod['id']);
                
                Log::info('Subscription object returned from Stripe '.gettype($subscription_object).' '.json_encode($subscription_object));
                
                $user->getLastPlanInfo()->create(['user_id' => $user->id, 'plan_id' => $plan->id, 'price' => $plan->price, 'credits' => $plan->credits]);
                $user->assignCredits($user->getCredits() + $plan->credits); 
                
                return back()->with('success', 'Subscription Activated');

            } 
            return back()->with('error', 'No Payment Methods Found! Please add a card');
        } catch(Exception $e) {
            return back()->with('error', $e->getMessage().' '.$e->getLine());
        }
    }

    public function purchaseOneTimeCredits($id) {
        try {
            $user = Auth::user();
            $plan = config('custom.one_time_payments')[$id];
            
            if($user->hasPaymentMethod()) {
            
                $paymentMethod = $user->paymentMethods()->toArray()[0];
                $charge = $user->charge($plan['price'] * 100, $paymentMethod['id'], [
                    'metadata' => [
                        'description' => 'Charged on '.date('Y-m-d'),
                        'email' => $user->email,
                        'name'  => $user->name,
                    ],
                    'description' => 'Charge for credits'
                ]);

                Log::info('Charge object returned from Stripe '.gettype($charge).' '.json_encode($charge));

                $user->assignCredits($user->getCredits() + $plan['credits']);
                return back()->with('success', 'Charge successful!');
            }
            return back()->with('error', 'No Payment Methods Found! Please add a card');
        } catch(Exception $e) {
            return back()->with('error', $e->getMessage().' '.$e->getLine());
        }
    }

    public function billingPortal() {
        $user = Auth::user();
        return $user->redirectToBillingPortal();
    }
}
