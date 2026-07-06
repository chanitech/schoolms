<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncAllPermissionsToAdminSeeder extends Seeder
{
    /**
     * Permissions referenced by config/adminlte.php ('can' => ...) or
     * controllers that were never created, leaving those menu items/routes
     * invisible or blocked for everyone — including Admin. Same dead-gate
     * pattern as the Finance Office roles fixed earlier, just app-wide.
     */
    private const MISSING_PERMISSIONS = [
        'create aptitude questions',
        'create counseling intake forms',
        'create group sessions',
        'create session reports',
        'enter marks',
        'export marksheets',
        'manage promotions',
        'manage settings',
        'view ai insights',
        'view aptitude tests',
        'view attendance report',
        'view classroom guidance',
        'view counseling intake forms',
        'view department dashboard',
        'view evaluation report',
        'view grading',
        'view group sessions',
        'view holland code',
        'view interest inventories',
        'view job cards report',
        'view learning preferences',
        'view learning profile reports',
        'view leave report',
        'view mbti test',
        'view multiple intelligence',
        'view own job cards',
        'view session reports',
        'view staff report',
        'view subject assignments',
        'view teacher assignments',
        'view thinking style',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::MISSING_PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin (this app's super-admin role) should always have every
        // permission that exists, not just the ones that existed when it was
        // first seeded. Re-syncing here (and any time new permissions are
        // added) keeps it a true superset going forward.
        if ($admin = Role::where('name', 'Admin')->first()) {
            $admin->syncPermissions(Permission::all());
        }
    }
}
