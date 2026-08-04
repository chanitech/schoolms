<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        if ($role = Role::where('name', 'HR')->first()) {
            $role->givePermissionTo('manage notices');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        if ($role = Role::where('name', 'HR')->first()) {
            $role->revokePermissionTo('manage notices');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
