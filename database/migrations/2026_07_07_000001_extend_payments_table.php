<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('class_id')->nullable()->after('student_bill_id')->constrained('school_classes')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->after('recorded_by')->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'verified', 'flagged'])->default('pending')->after('verified_by');
        });

        // Backfill class_id for existing rows from student_bill -> bill -> class_id
        DB::statement('
            UPDATE payments p
            INNER JOIN student_bills sb ON sb.id = p.student_bill_id
            INNER JOIN bills b ON b.id = sb.bill_id
            SET p.class_id = b.class_id
            WHERE p.class_id IS NULL AND b.class_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['class_id', 'verified_by', 'status']);
        });
    }
};
