<?php

namespace Database\Seeders;

use App\Models\DevOps;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevOpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $devops = DevOps::updateOrCreate([
            'email' => 'devops@gmail.com'
        ],[
            'name' => 'devops',
            'email' => 'devops@gmail.com',
            'password' => Hash::make('123456')
        ]);
    }
}
