<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'view results']);
        Permission::firstOrCreate(['name' => 'manage subjects']);
        Permission::firstOrCreate(['name' => 'manage users']);
        Permission::firstOrCreate(['name' => 'view system logs']);

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo(Permission::all());

        $teacher = Role::firstOrCreate(['name' => 'Teacher']);
        $teacher->givePermissionTo(['view results', 'manage subjects']);

        $staff = Role::firstOrCreate(['name' => 'Staff']);
        $staff->givePermissionTo(['view results']);
    }
}
