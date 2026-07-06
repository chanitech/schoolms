<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The two earlier migrations with this same purpose
 * (2025_11_10_092637 and 2025_11_10_094112) both had their actual
 * Schema::table() calls commented out, so they ran as no-ops — the column
 * was never actually created anywhere, including production, which crashed
 * on every department create/update with "Unknown column
 * 'rank_requires_7_subjects'".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('departments', 'rank_requires_7_subjects')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table) {
            $table->boolean('rank_requires_7_subjects')->default(true)->after('head_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('departments', 'rank_requires_7_subjects')) {
            return;
        }

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('rank_requires_7_subjects');
        });
    }
};
