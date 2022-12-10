<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stores = [
            [
                'id' => 43243242432,
                'email' => 'demo.store1@gmail.com',
                'name' => 'Store Demo',
                'access_token' => 'sngajgna_astgajgja_ajgdjsgd',
                'myshopify_domain' => 'storedemo1.myshopify.com',
                'phone' => '454535235235',
                'address1' => '123 Downing Street',
                'address2' => 'London',
                'zip' => '434355353',
            ],[
                'id' => 45535325569,
                'email' => 'store.demo2@gmail.com',
                'name' => 'Vaseline',
                'access_token' => 'dkgndggk_sdngjdg',
                'myshopify_domain' => 'vaseline.shopify.com',
                'phone' => '9349349394',
                'address1' => 'Viking Street',
                'address2' => 'Downtown Amsterdam',
                'zip' => '234353533',
            ],[
                'id' => 5643424242,
                'email' => 'dental24@gmail.com',
                'name' => 'Dental Orient',
                'access_token' => 'gdsgsdgsdgd_dngdsng',
                'myshopify_domain' => 'dentalorient.myshopify.com',
                'phone' => '43858484854',
                'address1' => 'Pointing Road',
                'address2' => 'New Delhi',
                'zip' => '85748499332',
            ],[
                'id' => 43559902001,
                'email' => 'lightbulbs@gmail.com',
                'name' => 'Light Bulbs',
                'access_token' => 'dgsgjdsgsdg_jdsngjs',
                'myshopify_domain' => 'lightbulbs.myshopify.com',
                'phone' => '49459495945',
                'address1' => 'Kings Street',
                'address2' => 'Uptown New York',
                'zip' => '84883992',
            ],[
                'id' => 93748388222,
                'email' => 'heineken@gmail.com',
                'name' => 'Oneplus',
                'access_token' => 'dgsdgkdgkdgj_sad',
                'myshopify_domain' => 'oneplus@shopify.com',
                'phone' => '2343434343',
                'address1' => 'Oneplus Street',
                'address2' => 'Downtown new york',
                'zip' => '394394394',
            ]
        ];

        foreach($stores as $store)
            Store::updateOrCreate(['id' => $store['id']], $store);
    }
}
