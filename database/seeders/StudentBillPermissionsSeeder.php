<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * StudentBillController's edit/update/destroy actions had no permission
 * gate at all — any authenticated user could reach them by URL. Splitting
 * out 'edit student bills' / 'delete student bills' (mirroring the
 * view/create split already in place) and granting them to Finance, which
 * already holds 'create student bills' and is the role actually managing
 * these bills day to day.
 */
class StudentBillPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'edit student bills']);
        Permission::firstOrCreate(['name' => 'delete student bills']);

        if ($finance = Role::where('name', 'Finance')->first()) {
            $finance->givePermissionTo(['edit student bills', 'delete student bills']);
        }

        // Admin's "always has every permission" grant lives in
        // SyncAllPermissionsToAdminSeeder — these two are added to its list
        // too, so re-running that seeder keeps Admin in sync.
    }
}
