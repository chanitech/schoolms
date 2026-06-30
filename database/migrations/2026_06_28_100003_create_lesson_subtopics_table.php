<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_subtopics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_topic_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('order_no')->default(0);
            $table->enum('status', ['pending', 'covered'])->default('pending');
            $table->date('date_covered')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('covered_by')->nullable();  // users.id
            $table->foreign('covered_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index('lesson_topic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_subtopics');
    }
};
