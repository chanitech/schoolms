<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MissingCoreRolesSeeder extends Seeder
{
    /**
     * HOD/HR/Principal/Academic are referenced throughout the app (budget
     * approval chains, exam/timetable notification targeting, department
     * head dropdowns) via both hasRole() (safe, returns false if missing)
     * and the role() query scope (throws RoleDoesNotExist if missing).
     * None of these roles existed at all — e.g. DepartmentController's
     * Staff::role('HOD') crashed the /departments/create page outright.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $hod = Role::firstOrCreate(['name' => 'HOD']);
        $hod->givePermissionTo(array_values(array_filter([
            'view department dashboard',
            'view hod dashboard',
            'view budgets',
            'create budgets',
            'view staff',
            'view students',
        ], fn ($p) => Permission::where('name', $p)->exists())));

        $hr = Role::firstOrCreate(['name' => 'HR']);
        $hr->givePermissionTo(array_values(array_filter([
            'view hr summary dashboard',
            'view hr reports',
            'view staff report',
            'view leave report',
            'view leaves',
            'approve received leaves',
            'reject received leaves',
            'view staff',
        ], fn ($p) => Permission::where('name', $p)->exists())));

        $principal = Role::firstOrCreate(['name' => 'Principal']);
        $principal->givePermissionTo(array_values(array_filter([
            'view department dashboard',
            'view hod dashboard',
            'view hr summary dashboard',
            'view staff report',
            'view budgets',
            'approve budget items',
            'view invoices',
            'view exams',
            'view results',
            'view timetable',
        ], fn ($p) => Permission::where('name', $p)->exists())));

        $academic = Role::firstOrCreate(['name' => 'Academic']);
        $academic->givePermissionTo(array_values(array_filter([
            'view exams',
            'create exams',
            'edit exams',
            'view results',
            'lock results',
            'view timetable',
            'view class results',
        ], fn ($p) => Permission::where('name', $p)->exists())));
    }
}
