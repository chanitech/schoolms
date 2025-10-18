<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Students Management
            'view students',
            'create students',
            'edit students',
            'delete students',

            'view guardians',
            'create guardians',
            'edit guardians',
            'delete guardians',

            'view enrollments',
            'create enrollments',
            'edit enrollments',
            'delete enrollments',

            // Academic Management
            'view classes',
            'create classes',
            'edit classes',
            'delete classes',

            'view dormitories',
            'create dormitories',
            'edit dormitories',
            'delete dormitories',

            'view sessions',
            'create sessions',
            'edit sessions',
            'delete sessions',

            'view subjects',
            'create subjects',
            'edit subjects',
            'delete subjects',

            'view exams',
            'create exams',
            'edit exams',
            'delete exams',

            'view marks',
            'create marks',
            'edit marks',
            'delete marks',

            'view divisions',
            'create divisions',
            'edit divisions',
            'delete divisions',

            'view grades',
            'create grades',
            'edit grades',
            'delete grades',

            'view student results',
            'view class results',

            // HR & Staff
            'view staff',
            'create staff',
            'edit staff',
            'delete staff',

            'view departments',
            'create departments',
            'edit departments',
            'delete departments',

            'view job cards',
            'create job cards',
            'edit job cards',
            'delete job cards',

            'view attendance',
            'create attendance',
            'edit attendance',
            'delete attendance',

            'view leaves',
            'create leaves',
            'edit leaves',
            'delete leaves',

            'view received leaves',
            'approve received leaves',
            'reject received leaves',

            'view events',
            'create events',
            'edit events',
            'delete events',

            'view hr reports',

            // Fees & Finance
            'view fees',
            'create fees',
            'edit fees',
            'delete fees',

            'view payments',
            'create payments',
            'edit payments',
            'delete payments',

            'view fee reports',

            // Library
            'view books',
            'create books',
            'edit books',
            'delete books',

            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            'view lendings',
            'create lendings',
            'edit lendings',
            'delete lendings',

            // System Settings
            'view profile',
            'edit profile',

            'view school info',
            'edit school info',

            'view academic years',
            'edit academic years',

            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',

            'view system logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('Permissions seeded!');
    }
}
