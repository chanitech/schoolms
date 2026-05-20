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
       Schema::create('classroom_guidances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('class_id')
          ->constrained('school_classes') // explicitly use correct table name
          ->onDelete('cascade');
    $table->date('date');
    $table->text('tasks')->nullable();
    $table->text('achievements')->nullable();
    $table->text('challenges')->nullable();
    $table->foreignId('created_by')
          ->constrained('users')
          ->onDelete('cascade');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_guidances');
    }
};
