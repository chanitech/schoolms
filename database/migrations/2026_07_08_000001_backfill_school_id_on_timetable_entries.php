<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Entries generated before the TimetableGeneratorService fix were
     * inserted via raw DB::table()->insert(), which skips the
     * BelongsToSchool auto-fill — leaving school_id NULL and the entries
     * invisible under every scoped query. Backfill from the parent
     * timetable, which is always correctly scoped.
     */
    public function up(): void
    {
        DB::statement('
            UPDATE timetable_entries te
            INNER JOIN timetables t ON t.id = te.timetable_id
            SET te.school_id = t.school_id
            WHERE te.school_id IS NULL AND t.school_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        // Not reversible — we don't know which rows were NULL before.
    }
};
