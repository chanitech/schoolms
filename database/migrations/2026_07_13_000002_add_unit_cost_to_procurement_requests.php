<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Quantity and Estimated Cost were two independent typed numbers with
     * no relationship enforced — someone could enter Qty=5 and a total
     * cost with no per-unit price behind it. Adds unit_cost; estimated_cost
     * is now always computed server-side as quantity * unit_cost (see
     * ProcurementRequestController::store()), same integrity principle as
     * the disbursement amount fix.
     */
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 2)->nullable()->after('quantity');
        });

        // Backfill existing rows so unit_cost * quantity still equals the
        // original estimated_cost (avoids dividing by zero for quantity=0,
        // though quantity has always been required|min:1).
        DB::table('procurement_requests')->where('quantity', '>', 0)->update([
            'unit_cost' => DB::raw('estimated_cost / quantity'),
        ]);
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
