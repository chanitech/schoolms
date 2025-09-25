<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('admission_no')->unique()->index();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female'])->index();
            $table->date('date_of_birth');
            $table->string('national_id')->nullable()->unique();
            $table->string('photo')->nullable();
            $table->unsignedBigInteger('guardian_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('dormitory_id')->nullable();
            $table->unsignedBigInteger('academic_session_id')->nullable();
            $table->date('admission_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'suspended'])->default('active')->index();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys (to be created later once those tables exist)
            $table->foreign('guardian_id')->references('id')->on('guardians');
            // $table->foreign('class_id')->references('id')->on('classes');
            // $table->foreign('dormitory_id')->references('id')->on('dormitories');
            // $table->foreign('academic_session_id')->references('id')->on('academic_sessions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
