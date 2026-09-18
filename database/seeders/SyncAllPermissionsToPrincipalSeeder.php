<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncAllPermissionsToPrincipalSeeder extends Seeder
{
    /**
     * Principal (the school head) should have every permission that exists,
     * same as Admin — so a missing 'permission:X' gate never blocks the
     * Principal from a menu item, route, or button. Mirrors
     * SyncAllPermissionsToAdminSeeder; re-run any time new permissions are
     * added so Principal stays a true superset.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if ($principal = Role::where('name', 'Principal')->first()) {
            $principal->syncPermissions(Permission::all());
        }
    }
}
