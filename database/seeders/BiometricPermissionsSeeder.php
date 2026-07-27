<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BiometricPermissionsSeeder extends Seeder
{
    public function run()
    {
        $permission = 'manage biometric devices';

        Permission::firstOrCreate(['name' => $permission]);

        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo($permission);
    }
}
