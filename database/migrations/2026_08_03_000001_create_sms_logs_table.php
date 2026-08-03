<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->uuid('batch_id')->index();
            $table->string('category', 40); // announcement, fee_reminder, result_published, custom
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone', 20);
            $table->text('message');
            $table->enum('status', ['sent', 'failed', 'invalid'])->default('sent');
            $table->string('provider_request_id')->nullable();
            $table->string('error_message')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
