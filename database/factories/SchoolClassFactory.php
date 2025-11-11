<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $levels = ['1','2','3','4'];
        $sections = ['A','B','C','D'];

        return [
            'name' => 'Form ' . $this->faker->randomElement($levels),
            'level' => $this->faker->randomElement($levels),
            'section' => $this->faker->randomElement($sections),
            'capacity' => $this->faker->numberBetween(25, 50),
            'class_teacher_id' => null,
        ];
    }
}
