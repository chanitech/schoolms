<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_log_subtopics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_log_id')->constrained('timetable_session_logs')->cascadeOnDelete();
            $table->foreignId('subtopic_id')->constrained('lesson_subtopics')->cascadeOnDelete();
            $table->unique(['session_log_id', 'subtopic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_log_subtopics');
    }
};
