<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
{
    // Reset cached roles and permissions
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    // Create permissions
    Permission::create(['name' => 'lock results']);
    Permission::create(['name' => 'view results']); // if not already

    // Assign to finance role
    $role = Role::firstOrCreate(['name' => 'Finance']);
    $role->givePermissionTo(['lock results', 'view results']);
}
}
