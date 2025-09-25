<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // e.g., Form 1, Form 2
            $table->string('level');          // e.g., 1, 2, 3, 4
            $table->string('section')->nullable(); // e.g., A, B
            $table->integer('capacity')->default(30);
            $table->unsignedBigInteger('class_teacher_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
