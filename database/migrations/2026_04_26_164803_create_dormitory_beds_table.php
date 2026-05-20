<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitory_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('dormitory_rooms')->onDelete('cascade');
            $table->string('bed_number');
            $table->enum('bed_type', ['single', 'bunk_upper', 'bunk_lower'])->default('single');
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->foreignId('current_student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->text('features')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['room_id', 'bed_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_beds');
    }
};