<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

// Career Guidance (Counseling Office): every role that already works with
// counseling or academic results gets it; class teachers see their own class
// through the is-class-coordinator gate without this permission.
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $p = Permission::firstOrCreate(['name' => 'view career guidance', 'guard_name' => 'web']);

        $roles = Role::whereIn('name', ['Admin', 'Principal', 'Academic'])->get()
            ->merge(Role::permission(['view session reports', 'view counseling intake forms', 'view aptitude tests'])->get())
            ->unique('id');
        foreach ($roles as $role) $role->givePermissionTo($p);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::where('name', 'view career guidance')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
