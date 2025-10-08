<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            
            $table->decimal('mark', 5, 2); // numeric mark
            $table->foreignId('grade_id')->nullable()->constrained()->nullOnDelete(); // optional link to grade
            $table->timestamps();
            $table->softDeletes(); // supports soft deletes
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
