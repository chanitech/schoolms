<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * The chief accountant is the senior accounting-oversight role (and the
     * first loan approver), yet held fewer rights than the class accountants
     * under them: no finance dashboard, no payment verification, no fee
     * structures, no pocket-money view, and no access to the finance task
     * system. Grants the missing oversight set; also lets accountants
     * participate in the task system (they can be assigned tasks but had no
     * permission to update or justify them).
     */
    private const CHIEF_GRANTS = [
        'view finance dashboard',
        'verify payments', 'flag payments',
        'view fees',
        'view pocket money',
        'view students', 'view staff',
        'manage tasks', 'submit task justification',
    ];

    private const ACCOUNTANT_GRANTS = [
        'manage tasks', 'submit task justification',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $grant = function (string $roleName, array $permissions) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) return;
            foreach ($permissions as $name) {
                if ($permission = Permission::where('name', $name)->first()) {
                    $role->givePermissionTo($permission);
                }
            }
        };

        $grant('chief-accountant', self::CHIEF_GRANTS);
        $grant('accountant', self::ACCOUNTANT_GRANTS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if ($role = Role::where('name', 'chief-accountant')->first()) {
            foreach (self::CHIEF_GRANTS as $name) $role->revokePermissionTo($name);
        }
        if ($role = Role::where('name', 'accountant')->first()) {
            foreach (self::ACCOUNTANT_GRANTS as $name) $role->revokePermissionTo($name);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
