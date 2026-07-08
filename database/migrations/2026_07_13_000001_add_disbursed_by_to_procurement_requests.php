<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->foreignId('disbursed_by')->nullable()->after('headmaster_approved_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('disbursed_at')->nullable()->after('headmaster_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropForeign(['disbursed_by']);
            $table->dropColumn(['disbursed_by', 'disbursed_at']);
        });
    }
};
