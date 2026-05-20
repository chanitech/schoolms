<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('interest_inventories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('students')
                ->onDelete('cascade');

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->date('date')->nullable();

            // q1..q17
            for ($i = 1; $i <= 17; $i++) {
                $table->text("q{$i}")->nullable();
            }

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_inventories');
    }
};
