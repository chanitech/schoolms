<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_counseling_student', function (Blueprint $table) {
    $table->id();
    $table->foreignId('report_id')
          ->constrained('group_counseling_session_reports')
          ->onDelete('cascade');
    $table->foreignId('student_id')
          ->constrained()
          ->onDelete('cascade');
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('group_counseling_student');
    }
};
