<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('counseling_intake_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');

            // Identifying Information
            $table->string('gender')->nullable();
            $table->integer('age')->nullable();
            $table->string('stream')->nullable();
            $table->string('education_program')->nullable();
            $table->string('g_performance')->nullable();
            $table->string('living_situation')->nullable();

            // Family Information
            $table->string('father_name')->nullable();
            $table->string('father_address')->nullable();
            $table->string('father_occupation')->nullable();
            $table->integer('father_age')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_address')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->integer('mother_age')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('parents_relationship')->nullable();
            $table->integer('siblings_brothers')->nullable();
            $table->integer('siblings_sisters')->nullable();
            $table->string('birth_order')->nullable();

            // Referral
            $table->string('referred_by')->nullable();

            // Therapeutic Information
            $table->text('health_problems')->nullable();
            $table->text('previous_counseling')->nullable();
            $table->text('reason_for_counseling')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->text('understanding_of_services')->nullable();

            // Counseling type
            $table->json('counseling_type')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_intake_forms');
    }
};
