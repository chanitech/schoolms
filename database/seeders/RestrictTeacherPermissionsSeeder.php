<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Teacher previously held a few permissions that leaked unrelated menu
 * sections into their sidebar: 'view attendance report' (HR Reports),
 * 'view classes' (School Setup), and 'view results' (Results & Reports —
 * that's Academic's domain, not Teacher's). Revoking them removes both the
 * menu links and direct URL access, since plain givePermissionTo seeders
 * can't retract a permission already granted.
 */
class RestrictTeacherPermissionsSeeder extends Seeder
{
    private const REVOKE = ['view attendance report', 'view classes', 'view results'];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if ($teacher = Role::where('name', 'Teacher')->first()) {
            foreach (self::REVOKE as $permission) {
                if ($teacher->hasPermissionTo($permission)) {
                    $teacher->revokePermissionTo($permission);
                }
            }
        }
    }
}
