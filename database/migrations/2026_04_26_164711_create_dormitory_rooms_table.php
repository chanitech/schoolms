<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitory_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dormitory_id')->constrained()->onDelete('cascade');
            $table->string('room_number');
            $table->string('floor')->nullable();
            $table->integer('capacity')->default(4);
            $table->integer('occupied_beds')->default(0);
            $table->enum('room_type', ['single', 'double', 'triple', 'quad', 'dormitory'])->default('double');
            $table->boolean('has_attached_bathroom')->default(false);
            $table->boolean('has_balcony')->default(false);
            $table->boolean('is_available')->default(true);
            $table->text('facilities')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['dormitory_id', 'room_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_rooms');
    }
};