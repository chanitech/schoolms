<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('check_in_at')->nullable()->after('status');
            $table->timestamp('check_out_at')->nullable()->after('check_in_at');
            // Distinguishes fingerprint-derived rows from manually-entered
            // ones in the UI/audit trail; manual create/edit/bulk flows keep
            // defaulting to 'manual' and never touch the timestamp columns.
            $table->enum('source', ['manual', 'biometric'])->default('manual')->after('check_out_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['check_in_at', 'check_out_at', 'source']);
        });
    }
};
