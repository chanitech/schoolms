<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_counseling_session_reports', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->json('members')->nullable(); // store as JSON array of student IDs
            $table->date('date');
            $table->time('time');
            $table->integer('session_number')->nullable();
            $table->text('presenting_problem')->nullable();
            $table->text('work_done')->nullable();
            $table->text('assessment_progress')->nullable();
            $table->text('intervention_plan')->nullable();
            $table->text('follow_up')->nullable();
            $table->json('biopsychosocial_formulation')->nullable(); // store 4P's JSON
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // counselor
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_counseling_session_reports');
    }
};
