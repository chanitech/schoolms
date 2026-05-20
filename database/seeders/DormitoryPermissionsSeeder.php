<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DormitoryPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'view dormitories',
            'create dormitories',
            'edit dormitories',
            'delete dormitories',
            'allocate dormitories',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign to Admin role
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo($permissions);
        
        // Assign to Dorm Master role
        $dormMasterRole = Role::firstOrCreate(['name' => 'Dorm Master']);
        $dormMasterRole->givePermissionTo(['view dormitories', 'allocate dormitories']);
    }
}