<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('individual_session_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // counselor
            $table->date('date');
            $table->time('time');
            $table->integer('session_number')->nullable();
            $table->text('presenting_problem')->nullable();
            $table->text('work_done')->nullable();
            $table->text('assessment_progress')->nullable();
            $table->text('intervention_plan')->nullable();
            $table->text('follow_up')->nullable();
            $table->json('biopsychosocial_formulation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('individual_session_reports');
    }
};
