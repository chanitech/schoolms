<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * group_counseling_session_reports (created 2026-11-15) was meant to be
 * covered by 2026_06_30_151320_add_school_id_to_all_tenant_tables.php,
 * which lists it — but that migration silently `continue`s past any table
 * that doesn't exist yet, and this table wasn't created until five months
 * later. The model already uses BelongsToSchool (assumes school_id exists),
 * so every query against it has been failing with "Unknown column" since
 * the feature was built. Existing rows (if any) are left with school_id
 * null rather than guessed — same as BelongsToSchool's own documented
 * behavior for an unscoped create.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('group_counseling_session_reports', 'school_id')) {
            return;
        }

        Schema::table('group_counseling_session_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('school_id')->nullable()->after('id');
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('group_counseling_session_reports', 'school_id')) {
            return;
        }

        Schema::table('group_counseling_session_reports', function (Blueprint $table) {
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
