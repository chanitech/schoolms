<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds a Head Master (Principal) approval stage between Treasurer
     * approval and Cashier disbursement: pending -> treasurer_approved ->
     * approved (Head Master signed off) -> completed. 'approved' now means
     * "fully approved, ready to disburse" rather than "Treasurer approved"
     * — disburse() already only checks for status === 'approved', so it
     * needs no code change, just this new meaning.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending', 'treasurer_approved', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");

        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->foreignId('headmaster_approved_by')->nullable()->after('approved_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('headmaster_approved_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropForeign(['headmaster_approved_by']);
            $table->dropColumn(['headmaster_approved_by', 'headmaster_approved_at']);
        });

        DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");
    }
};
