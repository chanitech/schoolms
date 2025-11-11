<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('term');
        $table->foreignId('academic_session_id')->constrained()->onDelete('cascade');
        $table->timestamps();
        $table->softDeletes(); // 👈 Add this
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
