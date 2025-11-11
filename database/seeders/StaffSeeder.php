<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        // Create 5 demo staff users
        Staff::factory()->count(5)->create();

        // Optionally, create a specific HOD user
        Staff::create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'hod@example.com',
            'password'   => \Illuminate\Support\Facades\Hash::make('hodpassword'),
            'phone'      => '255712345678',
            'position'   => 'HOD',
            'role'       => 'HOD',
        ]);
    }
}
