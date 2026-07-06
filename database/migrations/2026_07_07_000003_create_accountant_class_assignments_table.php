<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accountant_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'user_id', 'class_id'], 'aca_school_user_class_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accountant_class_assignments');
    }
};
