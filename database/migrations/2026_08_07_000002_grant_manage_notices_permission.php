<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const ROLES = ['Admin', 'Principal'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'manage notices', 'guard_name' => 'web']);
        foreach (self::ROLES as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $role->givePermissionTo('manage notices');
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', 'manage notices')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
