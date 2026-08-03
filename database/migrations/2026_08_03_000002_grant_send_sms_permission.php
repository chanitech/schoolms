<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Bulk SMS costs real money per message, so it's Admin/Academic/Principal
     * only in v1 — not the general 'manage settings' catch-all, so it can be
     * delegated independently later without also handing out system config.
     */
    private const ROLES = ['Admin', 'Academic', 'Principal'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => 'send sms', 'guard_name' => 'web']);
        foreach (self::ROLES as $roleName) {
            if ($role = Role::where('name', $roleName)->first()) {
                $role->givePermissionTo($permission);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        if ($permission = Permission::where('name', 'send sms')->first()) {
            $permission->delete();
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
