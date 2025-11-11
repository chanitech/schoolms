<?php

namespace Database\Factories;

use App\Models\Dormitory;
use Illuminate\Database\Eloquent\Factories\Factory;

class DormitoryFactory extends Factory
{
    protected $model = Dormitory::class;

    public function definition(): array
    {
        return [
            'name' => 'Dorm ' . $this->faker->unique()->word(),
            'capacity' => $this->faker->numberBetween(20, 100),
            'gender' => $this->faker->randomElement(['male','female']),
            'dorm_master_id' => null, // can link later to a staff member
        ];
    }
}
