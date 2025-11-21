<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aptitude_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_score')->default(0);
            $table->integer('time_taken')->nullable(); // seconds
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('aptitude_attempts');
    }
};
