<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicSession;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $sessions = AcademicSession::all();
        $classes = SchoolClass::all();

        foreach($students as $student){
            $session = $sessions->random();
            $class = $classes->random();

            Enrollment::create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'academic_session_id' => $session->id,
                'roll_no' => rand(1,50),
                'status' => 'active',
                'remarks' => null,
            ]);
        }
    }
}
