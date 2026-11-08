<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// Staff actions are now gated by these names; a name missing on an environment
// would lock out everyone. Admin and Principal hold every permission.
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'view guardians', 'create guardians', 'edit guardians', 'delete guardians',
            'view enrollments', 'create enrollments', 'edit enrollments', 'delete enrollments',
            'view lendings', 'create lendings', 'edit lendings',
            'manage promotions', 'create students', 'enter marks', 'create dormitories',
            'view session reports', 'create session reports',
            'view group sessions', 'create group sessions',
            'view classroom guidance', 'view interest inventories',
            'view aptitude tests', 'create aptitude questions',
        ] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (['Admin', 'Principal'] as $role) {
            Role::where('name', $role)->first()?->syncPermissions(Permission::all());
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
    }
};
