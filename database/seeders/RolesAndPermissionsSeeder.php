<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        
        $roles = [
            'Admin', 'SuperAdmin', 'SubUser'
        ];
        foreach($roles as $role)
            Role::updateOrCreate(['name' => $role]);

        $permissions = array_merge(config('custom.default_permissions'), [
            'all-access', //For SuperAdmin   
        ]);

        foreach($permissions as $permission)
            Permission::updateOrCreate(['name' => $permission]);

        //Assign default Permissions
        $superadmin = Role::where('name', 'SuperAdmin')->first();
        $superadmin->givePermissionTo('all-access');

        $admin = Role::where('name', 'Admin')->first();
        $admin->givePermissionTo(['write-products', 'write-orders', 'write-customers', 'write-members']);

        // I had to comment this out
        // Dont do this if you want sub users to have all read permissions by default
        // $subuser = Role::where('name', 'SubUser')->first();
        // $subuser->givePermissionTo(['read-products', 'read-orders', 'read-customers', 'read-members']);
    }
}
