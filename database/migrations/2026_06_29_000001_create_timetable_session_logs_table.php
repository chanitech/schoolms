<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_entry_id')->constrained('timetable_entries')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->foreignId('period_id')->constrained('timetable_periods');
            $table->date('session_date');
            $table->enum('status', ['taught', 'absent']);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->unique(['timetable_entry_id', 'session_date'], 'unique_session_log');
            $table->index(['teacher_id', 'session_date']);
            $table->index(['class_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_session_logs');
    }
};
