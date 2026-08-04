<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const ROLES = ['Academic', 'HOD', 'accountant', 'treasurer'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
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
        foreach (self::ROLES as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $role->revokePermissionTo('manage notices');
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
