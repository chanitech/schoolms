<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'leave']);
            $table->timestamps();

            $table->unique(['staff_id', 'date']); // prevent duplicate attendance per day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
