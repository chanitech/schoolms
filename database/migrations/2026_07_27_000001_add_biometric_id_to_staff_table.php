<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // The numeric ID a fingerprint device (e.g. ZKTeco K40) assigns
            // when a staff member's fingerprint is enrolled on that unit.
            // Scoped unique per school, not globally: two different schools'
            // devices independently number their own enrolled users from 1.
            $table->string('biometric_id', 50)->nullable()->after('position');
            $table->unique(['school_id', 'biometric_id']);
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'biometric_id']);
            $table->dropColumn('biometric_id');
        });
    }
};
