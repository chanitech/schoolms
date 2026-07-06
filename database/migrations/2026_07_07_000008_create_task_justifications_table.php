<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_justifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_log_id')->constrained('task_logs')->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->timestamp('submitted_at');
            $table->timestamp('treasurer_reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_justifications');
    }
};
