<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin role if not exists
        $role = Role::firstOrCreate(['name' => 'Admin']);

        // Create Admin user
        $admin = User::firstOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password123'), // default password
        ]);

        // Assign role
        $admin->assignRole($role);
    }
}
