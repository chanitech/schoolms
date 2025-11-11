<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicSession;

class AcademicSessionSeeder extends Seeder
{
    public function run(): void
    {
        AcademicSession::factory(3)->create()->first()->update(['is_current' => true]);
    }
}
