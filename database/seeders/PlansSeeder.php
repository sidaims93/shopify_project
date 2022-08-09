<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder {
    /**
     * Run the database seeds.
     * @return void
     */
    public function run() {
        $plans = [
            [
                'name' => 'Free',
                'price' => 0,
                'status' => true,
                'credits' => 50
            ], [
                'name' => 'Basic',
                'price' => 6.99,
                'status' => true,
                'credits' => 500
            ], [
                'name' => 'Pro',
                'price' => 14.99,
                'status' => true,
                'credits' => 2000
            ], [
                'name' => 'Enterprise',
                'price' => 29.99,
                'status' => true,
                'credits' => 10000
            ]
        ];

        foreach($plans as $plan) {
            Plan::updateOrCreate([
                'name' => $plan['name'], 
                'price' => $plan['price']
            ], $plan);
        }
    }
}
