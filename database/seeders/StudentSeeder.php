<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
   

    public function run(): void
{
    // Seed students
    \App\Models\Student::factory(20)->create();

    // Assign students to dormitories
    \App\Models\Student::all()->each(function($student){
        $student->dormitory_id = \App\Models\Dormitory::inRandomOrder()->first()->id;
        $student->save();
    });
}

}
