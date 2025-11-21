<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aptitude_questions', function (Blueprint $table) {
            $table->id();
            $table->string('question_text');
            $table->enum('type', ['mcq', 'image_mcq', 'true_false', 'numerical']);
            $table->string('section');
            $table->string('image')->nullable();
            $table->json('options')->nullable(); // For MCQ options (text or image)
            $table->string('correct_answer');
            $table->integer('marks')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('aptitude_questions');
    }
};
