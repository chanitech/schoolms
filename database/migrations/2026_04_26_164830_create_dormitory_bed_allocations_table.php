<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitory_bed_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bed_id')->constrained('dormitory_beds')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_session_id')->constrained('academic_sessions');
            $table->date('allocation_date');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled', 'transferred'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('allocated_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_bed_allocations');
    }
};