<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const PERMISSIONS = ['view transport', 'manage transport', 'record transport payments'];

    // No dedicated "Transport Officer" role exists yet — gated to school
    // leadership for now; Admin can grant these to any custom role via the
    // existing Roles UI once a school wants to delegate this.
    private const MANAGE_ROLES = ['Admin', 'Principal'];
    private const VIEW_ROLES   = ['Admin', 'Principal', 'treasurer', 'Academic'];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $grant = function (string $roleName, array $permissions) {
            if ($role = Role::where('name', $roleName)->first()) {
                $role->givePermissionTo($permissions);
            }
        };

        foreach (self::MANAGE_ROLES as $r) {
            $grant($r, ['view transport', 'manage transport', 'record transport payments']);
        }
        foreach (self::VIEW_ROLES as $r) {
            $grant($r, ['view transport']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::whereIn('name', self::PERMISSIONS)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
