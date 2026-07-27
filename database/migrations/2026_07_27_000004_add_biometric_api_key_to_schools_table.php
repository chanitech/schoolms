<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            // Per-school secret for the fingerprint-relay ingestion endpoint,
            // distinct from the shared global PUBLIC_API_KEY used by the
            // guardian-registration/student-directory public API. A leaked
            // relay credential then only exposes one school, and admins can
            // regenerate it independently from Settings > Biometric Devices.
            $table->string('biometric_api_key', 64)->nullable()->after('plan');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('biometric_api_key');
        });
    }
};
