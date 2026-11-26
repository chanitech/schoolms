<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-school switches for automatic parent SMS (absence, results,
        // receipts, fee reminders) plus language, signature and reminder lead time.
        Schema::table('schools', function (Blueprint $table) {
            $table->json('sms_settings')->nullable()->after('plan');
        });

        // Lets an automatic message be sent once per event: re-saving a
        // register or re-publishing an exam must not text parents again.
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->string('dedupe_key', 120)->nullable()->after('category');
            $table->index(['school_id', 'dedupe_key']);
        });
    }

    public function down(): void
    {
        Schema::table('sms_logs', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'dedupe_key']);
            $table->dropColumn('dedupe_key');
        });
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('sms_settings');
        });
    }
};
