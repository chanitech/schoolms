<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    // Submitting a suggestion / seeing your own is open to every
    // authenticated user (staff and guardians alike) — no permission
    // needed, same self-scoped pattern as the finance office's My
    // Dashboard. Only reviewing/responding to everyone's submissions is
    // gated, to school leadership.
    private const ROLES = ['Admin', 'Principal'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::firstOrCreate(['name' => 'manage suggestions', 'guard_name' => 'web']);

        foreach (self::ROLES as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $role->givePermissionTo('manage suggestions');
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::where('name', 'manage suggestions')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
