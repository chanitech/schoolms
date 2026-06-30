<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['class', 'exam']);
            $table->foreignId('academic_session_id')->constrained('academic_sessions');
            $table->enum('status', ['draft', 'pending_review', 'published', 'rejected'])->default('draft');
            $table->json('class_ids');             // array of school_class ids included
            $table->json('settings')->nullable();  // periods_per_week, days, exam_dates, etc.
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
