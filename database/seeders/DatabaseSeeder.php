<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(AdminUserSeeder::class);
        $this->call(DormitorySeeder::class);
        $this->call(SchoolClassSeeder::class);
        $this->call(GuardianSeeder::class);
        $this->call(AcademicSessionSeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(EnrollmentSeeder::class);

       


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
