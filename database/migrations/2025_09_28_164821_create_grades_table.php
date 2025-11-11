<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name', 2)->unique(); // e.g., A, B, C
            $table->decimal('min_mark', 5, 2);   // minimum mark for grade
            $table->decimal('max_mark', 5, 2);   // maximum mark for grade
            $table->decimal('point', 3, 2);      // GPA point e.g., 5.0, 4.0
            $table->string('description')->nullable(); // optional description
            $table->timestamps();
            $table->softDeletes(); // optional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
