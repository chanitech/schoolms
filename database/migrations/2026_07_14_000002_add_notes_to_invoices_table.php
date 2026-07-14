<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Reason/remarks for invoice decisions — required when the Head Master
    // (DO) rejects an invoice, so the HOD knows why.
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
