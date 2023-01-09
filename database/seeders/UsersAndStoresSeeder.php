<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersAndStoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 1; $i < 10; $i++) {
            $user = User::updateOrCreate([
                'email' => 'user'.$i.'@gmail.com'
            ], [
                'name' => 'User '.$i,
                'email' => 'user'.$i.'@gmail.com',
                'password' => Hash::make('123456')
            ]);

            $store = $user->getShopifyStore()->updateOrCreate([
                'myshopify_domain' => 'user'.$i.'.myshopify.com'
            ],[
                'id' => 200000 + $i,
                'email' => 'user'.$i.'@gmail.com',
                'name' => 'User '.$i,
                'access_token' => Str::random(20),
                'myshopify_domain' => 'user'.$i.'.myshopify.com',
                'address1' => 'Bogus Address',
                'address2' => 'Bogus Apartments',
                'zip' => '711833'
            ]);
            $user->update(['store_id' => $store->table_id]);
        }
    }
}
