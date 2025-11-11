<?php

namespace Database\Factories;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'email'      => $this->faker->unique()->safeEmail,
            'password'   => Hash::make('password123'), // default password
            'phone'      => $this->faker->phoneNumber,
            'department_id' => null, // you can link to departments later
            'position'   => $this->faker->jobTitle,
            'role'       => 'Staff', // default role
        ];
    }
}
