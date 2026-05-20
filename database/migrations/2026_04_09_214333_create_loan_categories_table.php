<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('min_amount', 10, 2);
            $table->decimal('max_amount', 10, 2);
            $table->integer('max_installments');
            $table->decimal('interest_rate', 5, 2); // % per annum
            $table->json('eligibility_criteria')->nullable(); // e.g. {"min_salary":50000,"min_years":2}
            $table->json('restrictions')->nullable(); // e.g. {"allow_multiple_active_loans":false}
            $table->foreignId('created_by_treasurer_id')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_categories');
    }
};