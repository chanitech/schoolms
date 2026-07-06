<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Assigns permissions to each non-Admin role based on what that role is
 * actually responsible for doing or seeing, day to day. Additive only
 * (givePermissionTo, not syncPermissions) — never removes anything a role
 * already has, including anything assigned by hand since. Safe to re-run.
 */
class RoleResponsibilityPermissionsSeeder extends Seeder
{
    private const MAP = [
        'Teacher' => [
            'view students', 'view classes', 'view subjects',
            'view subject assignments', 'view teacher assignments', 'view timetable',
            'view exams', 'view marks', 'create marks', 'edit marks', 'enter marks',
            'view student results', 'view class results', 'view results',
            'view attendance', 'create attendance', 'edit attendance', 'view attendance report',
            'create leaves', 'view own leaves',
            'view lesson plans', 'create lesson plans', 'edit lesson plans',
            'view daily reports', 'create daily reports',
            'view own jobcards', 'create jobcards',
            'view documents', 'view profile', 'edit profile',
        ],

        'Staff' => [
            'view profile', 'edit profile',
            'create leaves', 'view own leaves',
            'view attendance', 'view timetable', 'view documents',
            'view own jobcards', 'create jobcards',
        ],

        'Dorm Master' => [
            'view dormitories', 'create dormitories', 'edit dormitories', 'allocate dormitories',
            'view students', 'view profile', 'edit profile',
        ],

        'HOD' => [
            'view students', 'view staff', 'view department dashboard', 'view hod dashboard',
            'view subjects', 'manage subjects', 'view subject assignments', 'view teacher assignments',
            'view exams', 'view class results', 'view results', 'view timetable',
            'view lesson plans', 'view daily reports',
            'create budgets', 'view budgets', 'edit budgets',
            'view leaves', 'approve received leaves', 'reject received leaves', 'view leave report',
            'view attendance report',
            'view any jobcards', 'rate jobcards',
            'view profile', 'edit profile',
        ],

        'HR' => [
            'view staff', 'create staff', 'edit staff',
            'view leaves', 'approve received leaves', 'reject received leaves',
            'view hr reports', 'view leave report', 'view staff report', 'view hr summary dashboard',
            'view attendance report',
            'view events', 'create events', 'edit events', 'view event report',
            'manage promotions', 'view job cards report',
            'view documents', 'upload documents',
            'view profile', 'edit profile',
        ],

        'Principal' => [
            'view exams', 'view results', 'view timetable',
            'view department dashboard', 'view hod dashboard', 'view hr summary dashboard',
            'view staff report', 'approve budget items', 'view budgets', 'view invoices',
            'view students', 'view staff', 'view classes',
            'view fee reports', 'view payments', 'pay invoices',
            'view attendance report', 'view event report', 'view leave report',
            'export results', 'view any jobcards', 'rate jobcards',
            'view profile', 'edit profile',
        ],

        'Academic' => [
            'view exams', 'create exams', 'edit exams', 'view class results',
            'lock results', 'view results', 'view timetable',
            'view marks', 'create marks', 'edit marks', 'enter marks',
            'export marksheets', 'manage grading', 'view grading', 'export results',
            'view subjects', 'manage subjects', 'view subject assignments', 'view teacher assignments',
            'view daily reports', 'view lesson plans', 'view department dashboard',
            'view profile', 'edit profile',
        ],

        'Finance' => [
            'view fees', 'create fees', 'edit fees',
            'view payments', 'create payments', 'view fee reports',
            'view student bills', 'create student bills',
            'view bills', 'view invoices', 'pay invoices',
            'view profile', 'edit profile',
        ],

        'treasurer' => [
            'view students', 'view staff',
            'view fee reports', 'view payments', 'view invoices', 'view bills',
            'view budgets', 'view student bills', 'view inventory',
            'view profile', 'edit profile',
        ],

        'chief-accountant' => [
            'view budgets', 'view invoices', 'view bills', 'view student bills',
            'approve loans', 'view pending approvals',
            'view profile', 'edit profile',
        ],

        'accountant' => [
            'view budgets', 'view invoices', 'view bills', 'view student bills',
            'view pending approvals',
            'view profile', 'edit profile',
        ],

        'class_accountant' => [
            'view student bills', 'view fee reports',
            'view profile', 'edit profile',
        ],

        'cashier' => [
            'view payments', 'create payments', 'pay invoices',
            'view pocket money', 'manage pocket money',
            'view profile', 'edit profile',
        ],

        'storekeeper' => [
            'view inventory', 'view profile', 'edit profile',
        ],

        // Explicit ask: procurement officer needs to see inventory (to know
        // what's low in stock before requesting a purchase) — view only,
        // not 'manage stock', which stays Storekeeper's job.
        'procurement_officer' => [
            'view pending approvals', 'view inventory',
            'view profile', 'edit profile',
        ],
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $existingPermissions = Permission::pluck('name')->all();

        foreach (self::MAP as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) continue;

            $valid = array_values(array_intersect($permissions, $existingPermissions));
            $role->givePermissionTo($valid);
        }

        // Every role here represents an actual staff member — regardless of
        // their specific function, they all need to be able to request and
        // see their own leave. 'guardian' is excluded (not a staff role).
        // HOD/HR additionally hold 'view leaves' (the approver side) from
        // the map above, so this doesn't change what they can already see.
        $baselineLeavePermissions = array_values(array_intersect(
            ['create leaves', 'view own leaves'],
            $existingPermissions
        ));
        foreach (array_keys(self::MAP) as $roleName) {
            if ($roleName === 'guardian') continue;
            $role = Role::where('name', $roleName)->first();
            if (! $role) continue;
            $role->givePermissionTo($baselineLeavePermissions);
        }
    }
}
