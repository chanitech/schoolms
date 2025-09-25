<?php

namespace Database\Factories;

use App\Models\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'relation_to_student' => $this->faker->randomElement(['Father', 'Mother', 'Uncle', 'Aunt', 'Guardian']),
            'phone' => $this->faker->unique()->phoneNumber(),
            'email' => $this->faker->boolean(70) 
                ? $this->faker->unique()->safeEmail()
                : null,
            'address' => $this->faker->address(),
            'occupation' => $this->faker->boolean(70) 
                ? $this->faker->jobTitle()
                : null,
            'national_id' => $this->faker->boolean(70) 
                ? $this->faker->unique()->numerify('########')
                : null,
        ];
    }
}
