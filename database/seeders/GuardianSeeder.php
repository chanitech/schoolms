<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guardian;

class GuardianSeeder extends Seeder
{
    public function run(): void
    {
        Guardian::factory()->count(10)->create();
    }
}
