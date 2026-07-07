<?php

use App\Models\School;
use App\Models\TimetablePeriod;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The original create_timetable_periods_table migration seeded 11
     * default periods via a raw DB::table()->insert() that never set
     * school_id — BelongsToSchool's global scope then hid all of them from
     * every tenant-scoped query, making every school look like it had zero
     * teaching periods configured (crashing timetable generation/capacity
     * analysis with a division by zero). These defaults were seeded back
     * when this app was single-tenant, so they belong to the original
     * school — safe to backfill only when there's exactly one school
     * (true for this app's current production data); any school added
     * later needs its own periods configured separately.
     */
    public function up(): void
    {
        if (School::count() !== 1) {
            return;
        }

        $schoolId = School::value('id');

        TimetablePeriod::withoutGlobalScopes()
            ->whereNull('school_id')
            ->update(['school_id' => $schoolId]);
    }

    public function down(): void
    {
        // Not reversible — we don't know which rows were null before.
    }
};
