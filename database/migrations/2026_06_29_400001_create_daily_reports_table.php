<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->date('report_date');
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->text('summary')->nullable();
            $table->text('challenges')->nullable();
            $table->text('next_day_plan')->nullable();
            $table->text('additional_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'report_date']);
        });

        Schema::create('daily_report_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->enum('type', ['meeting', 'duty', 'exam_invigilation', 'training', 'other']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_activities');
        Schema::dropIfExists('daily_reports');
    }
};
