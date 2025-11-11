<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Dormitory;

class DormitorySeeder extends Seeder
{
    public function run(): void
    {
        Dormitory::factory(4)->create(); // creates 4 dormitories
    }
}
