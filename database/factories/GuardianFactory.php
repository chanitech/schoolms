<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Guardian;

class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'relation_to_student' => $this->faker->randomElement(['Father', 'Mother', 'Uncle', 'Aunt']),
            'phone' => $this->faker->unique()->numerify('07########'),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'occupation' => $this->faker->jobTitle(), // remove optional()
            'national_id' => $this->faker->unique()->numerify('########'), // remove optional()
        ];
    }
}
