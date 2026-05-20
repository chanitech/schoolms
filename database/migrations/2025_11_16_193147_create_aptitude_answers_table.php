<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aptitude_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('aptitude_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('aptitude_questions')->cascadeOnDelete();
            $table->string('student_answer');
            $table->integer('obtained_marks')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('aptitude_answers');
    }
};
