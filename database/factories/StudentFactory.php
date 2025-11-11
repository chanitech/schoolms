<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'admission_no' => 'ADM' . $this->faker->unique()->numberBetween(1000, 9999),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->optional()->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'date_of_birth' => $this->faker->date(),
            'national_id' => $this->faker->boolean(70) 
                ? $this->faker->unique()->numerify('########')
                : null,
            'photo' => null,
            'guardian_id' => null,
            'class_id' => \App\Models\SchoolClass::inRandomOrder()->first()->id ?? null,
            'dormitory_id' => null,
            'academic_session_id' => \App\Models\AcademicSession::inRandomOrder()->first()->id ?? null,
            'admission_date' => $this->faker->date(),
            'status' => 'active',
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
