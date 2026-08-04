<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            // Nullable on purpose: true anonymity means not recording who
            // sent it at all, not just hiding it in the UI.
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('submitter_role')->nullable(); // snapshot for triage even when anonymous
            $table->boolean('is_anonymous')->default(false);
            $table->enum('category', ['suggestion', 'complaint', 'compliment', 'opinion', 'other'])->default('suggestion');
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['new', 'in_review', 'resolved', 'dismissed'])->default('new');
            $table->text('admin_response')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suggestions');
    }
};
