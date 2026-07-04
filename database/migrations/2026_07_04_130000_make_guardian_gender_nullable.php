<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardians', function ($table) {
            $table->enum('gender', ['male', 'female'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('guardians', function ($table) {
            $table->enum('gender', ['male', 'female'])->nullable(false)->change();
        });
    }
};
