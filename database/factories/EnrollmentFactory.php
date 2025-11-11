<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        $student = Student::inRandomOrder()->first();
        $class = SchoolClass::inRandomOrder()->first();
        $session = AcademicSession::inRandomOrder()->first();

        return [
            'student_id' => $student ? $student->id : null,
            'class_id' => $class ? $class->id : null,
            'academic_session_id' => $session ? $session->id : null,
            'roll_no' => $this->faker->unique()->numberBetween(1,50),
            'status' => 'active',
            'remarks' => $this->faker->optional()->sentence(),
        ];
    }
}
