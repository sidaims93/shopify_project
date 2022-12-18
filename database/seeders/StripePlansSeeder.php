<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlans;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class StripePlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Stripe\Stripe::setApiKey(config('custom.stripe_api_secret'));

        $plans = [
            [
                'name' => 'Basic',
                'price' => 9.99,
                'credits' => 500
            ], [
                'name' => 'Pro',
                'price' => 19.99,
                'credits' => 1500
            ], [
                'name' => 'Ultimate',
                'price' => 49.99,
                'credits' => 6000
            ]
        ];

        foreach($plans as $plan) {

            try {
                $stripe_plan = \Stripe\Plan::retrieve(strtolower($plan['name']));
            } catch(Exception $e) {
                $stripe_plan = null;
            }
            
            if($stripe_plan !== null && isset($stripe_plan->id)) {
                SubscriptionPlans::updateOrCreate(['stripe_plan_id' => $stripe_plan->id], ['stripe_plan_id' => $stripe_plan->id, 'price' => $plan['price'], 'credits' => $plan['credits']]);
            } else {
                $stripe_plan = \Stripe\Plan::create([
                    "amount" => $plan['price'] * 100,
                    "interval" => "month",
                    "product" => [
                        "name" => $plan['name']
                    ],
                    "currency" => "usd",
                    "id" => strtolower($plan['name'])
                ]);
                SubscriptionPlans::updateOrCreate([
                    'stripe_plan_id' => $stripe_plan->id
                    ], [
                        'stripe_plan_id' => $stripe_plan->id, 'price' => $plan['price'], 'credits' => $plan['credits']
                    ]
                );
            }
        }
    }
}
