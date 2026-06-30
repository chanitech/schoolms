<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes');
            $table->foreignId('subject_id')->constrained('subjects');
            $table->unsignedBigInteger('teacher_id')->nullable();
            $table->foreign('teacher_id')->references('id')->on('users')->nullOnDelete();

            // Class timetable fields
            $table->tinyInteger('day_of_week')->nullable();    // 1=Mon … 5=Fri
            $table->foreignId('period_id')->nullable()->constrained('timetable_periods')->nullOnDelete();

            // Exam timetable fields
            $table->date('exam_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->string('room', 100)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['timetable_id', 'class_id']);
            $table->index(['timetable_id', 'day_of_week', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
