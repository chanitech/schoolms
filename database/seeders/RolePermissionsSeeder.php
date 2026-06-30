<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Create any permissions that don't exist yet ────────────────────
        $newPerms = [
            'view timetable', 'view daily reports', 'create daily reports',
            'view lesson plans', 'create lesson plans', 'edit lesson plans',
            'view own leaves', 'edit staff',
            'view documents', 'upload documents', 'delete documents',
        ];
        foreach ($newPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── Teacher ────────────────────────────────────────────────────────
        $teacher = Role::firstOrCreate(['name' => 'Teacher']);
        $teacher->givePermissionTo([
            'view exams', 'enter marks', 'view marks', 'delete marks',
            'view sessions', 'view classes', 'view subjects',
            'view student results', 'view results',
            'view timetable', 'view daily reports', 'create daily reports',
            'view lesson plans', 'create lesson plans', 'edit lesson plans',
            'create leaves', 'view own leaves',
            'view own job cards', 'view own jobcards', 'create jobcards',
            'view students', 'view guardians',
            'view documents',
        ]);

        // ── HOD ───────────────────────────────────────────────────────────
        $hod = Role::firstOrCreate(['name' => 'HOD']);
        $hod->givePermissionTo([
            'view exams', 'create exams', 'edit exams',
            'enter marks', 'view marks', 'delete marks',
            'view sessions', 'view classes', 'view subjects', 'view divisions',
            'view student results', 'view results', 'view class results', 'export class results',
            'view timetable', 'view daily reports', 'create daily reports',
            'view lesson plans', 'create lesson plans', 'edit lesson plans',
            'create leaves', 'view own leaves',
            'view own job cards', 'view own jobcards', 'create jobcards',
            'view department dashboard', 'view hod dashboard',
            'view staff', 'view departments',
            'view teacher assignment', 'view subject assignments',
            'view guardians', 'view students', 'view enrollments',
            'create budgets', 'view budgets',
            'view documents', 'upload documents',
        ]);

        // ── HR ────────────────────────────────────────────────────────────
        $hr = Role::firstOrCreate(['name' => 'HR']);
        $hr->givePermissionTo([
            'view staff', 'create staff', 'edit staff',
            'view attendance', 'create attendance', 'edit attendance', 'delete attendance',
            'view leaves', 'approve leaves', 'create leaves', 'edit leaves',
            'view received leaves',
            'view job cards', 'view any jobcards', 'edit any jobcards', 'create job cards',
            'view events', 'create events', 'edit events',
            'view staff report', 'view attendance report', 'view leave report',
            'view job cards report', 'view evaluation report',
            'view departments',
        ]);

        // ── Principal ─────────────────────────────────────────────────────
        $principal = Role::firstOrCreate(['name' => 'Principal']);
        $principal->givePermissionTo([
            'view exams',
            'view marks', 'view results', 'view class results',
            'view student results', 'export results', 'export class results',
            'export student results', 'export marksheets',
            'view sessions', 'view classes', 'view subjects', 'view divisions',
            'view students', 'view guardians', 'view enrollments',
            'view timetable', 'view daily reports', 'view lesson plans',
            'view teacher assignment', 'view subject assignments',
            'view grading',
            'view staff', 'view departments',
            'view attendance', 'view leaves', 'view received leaves',
            'view job cards', 'view any jobcards',
            'view attendance report', 'view leave report',
            'view job cards report', 'view staff report', 'view evaluation report',
            'view budgets', 'view invoices',
            'view documents', 'upload documents',
        ]);

        // ── Academic ──────────────────────────────────────────────────────
        $academic = Role::firstOrCreate(['name' => 'Academic']);
        $academic->givePermissionTo([
            'view sessions', 'create sessions', 'edit sessions', 'delete sessions',
            'view classes', 'create classes', 'edit classes', 'delete classes',
            'view subjects', 'create subjects', 'edit subjects', 'delete subjects',
            'view divisions', 'create divisions', 'edit divisions', 'delete divisions',
            'view departments',
            'view students', 'create students', 'edit students',
            'view guardians',
            'view enrollments', 'manage promotions',
            'view teacher assignment', 'create teacher assignments', 'delete teacher assignments',
            'view subject assignments', 'create subject assignments', 'delete subject assignments',
            'view exams', 'create exams', 'edit exams', 'delete exams',
            'enter marks', 'view marks',
            'view results', 'view student results', 'view class results',
            'export results', 'export class results', 'export student results', 'export marksheets',
            'manage grading', 'view grading',
            'view timetable', 'view daily reports', 'view lesson plans',
            'view staff',
            'view documents', 'upload documents', 'delete documents',
        ]);

        // ── Guardian (can view documents) ─────────────────────────────────
        $guardian = Role::firstOrCreate(['name' => 'guardian']);
        $guardian->givePermissionTo(['view documents']);

        // ── Admin gets everything ──────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions(Permission::all());

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Role permissions updated:');
        $this->command->info('  Teacher:   ' . $teacher->permissions()->count() . ' permissions');
        $this->command->info('  HOD:       ' . $hod->permissions()->count() . ' permissions');
        $this->command->info('  HR:        ' . $hr->permissions()->count() . ' permissions');
        $this->command->info('  Principal: ' . $principal->permissions()->count() . ' permissions');
        $this->command->info('  Academic:  ' . $academic->permissions()->count() . ' permissions');
        $this->command->info('  Admin:     ' . $admin->permissions()->count() . ' permissions');
    }
}
