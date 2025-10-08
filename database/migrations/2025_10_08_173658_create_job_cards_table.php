<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('job_cards', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
    $table->foreignId('assigned_to')->constrained('staff')->cascadeOnDelete();
    $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
    $table->tinyInteger('rating')->nullable(); // 1-5 scale
    $table->date('due_date')->nullable();
    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('job_cards');
    }
};
