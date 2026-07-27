<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Raw audit trail of every fingerprint scan received from a relay,
        // independent of whether it could be matched to a staff member.
        // Attendance check-in/out times are recomputed FROM this table
        // (first-in/last-out per staff per day) rather than merged
        // incrementally into `attendances`, so retries, out-of-order
        // batches, and >2 punches/day all resolve correctly.
        Schema::create('biometric_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable()->index();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('device_user_id', 50);
            // Deliberately outside the unique index below: nullable +
            // composite-unique is a MySQL foot-gun (NULLs aren't deduped),
            // and no serial is sent by the relay yet anyway.
            $table->string('device_serial', 100)->nullable();
            $table->timestamp('scanned_at');
            $table->timestamps();

            // Makes re-ingestion idempotent: safe for the relay to resend a
            // batch (e.g. after a network timeout) without duplicating scans.
            $table->unique(['school_id', 'device_user_id', 'scanned_at'], 'biometric_scan_logs_dedupe_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_scan_logs');
    }
};
