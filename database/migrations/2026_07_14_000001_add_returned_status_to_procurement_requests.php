<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cashier "return — amount insufficient" stage. The Cashier can never
     * change the approved amount (see ProcurementRequestController::disburse),
     * so when the approved estimate no longer covers the real cost the request
     * is returned with a reason and a corrected request goes back through the
     * Treasurer → Head Master chain.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending', 'treasurer_approved', 'approved', 'rejected', 'completed', 'returned') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->foreignId('returned_by')->nullable()->after('disbursed_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('returned_at')->nullable()->after('disbursed_at');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('returned_by');
            $table->dropColumn('returned_at');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE procurement_requests MODIFY status ENUM('pending', 'treasurer_approved', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");
        }
    }
};
