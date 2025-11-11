<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicSessionFactory extends Factory
{
    protected $model = AcademicSession::class;

    public function definition(): array
{
    $yearStart = $this->faker->unique()->numberBetween(2023, 2025);
    $yearEnd = $yearStart + 1;

    return [
        'name' => "$yearStart/$yearEnd",
        'start_date' => "$yearStart-09-01",
        'end_date' => "$yearEnd-07-31",
        'is_current' => false,
    ];
}

}
