<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_question_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mark_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->timestamps();

            $table->unique(['mark_id', 'exam_question_id']);
            $table->index('mark_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_question_scores');
    }
};
