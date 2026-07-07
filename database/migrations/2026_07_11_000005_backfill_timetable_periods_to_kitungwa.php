<?php

use App\Models\School;
use App\Models\TimetablePeriod;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The previous backfill (2026_07_11_000004) only ran when exactly one
     * school existed, to avoid guessing on a genuinely ambiguous multi-
     * tenant database. Production actually has two schools (Kitungwa,
     * MEMA ASEP), so that migration silently did nothing and left all 11
     * periods orphaned (school_id null) — still invisible to every
     * tenant-scoped query. These periods were seeded before MEMA existed
     * (back when this app was single-tenant), so they unambiguously
     * belong to Kitungwa, the original school — not a guess this time.
     * MEMA still needs its own periods configured separately.
     */
    public function up(): void
    {
        $kitungwaId = School::where('slug', 'kitungwa-adventist-secondary-school')->value('id');

        if (! $kitungwaId) {
            return;
        }

        TimetablePeriod::withoutGlobalScopes()
            ->whereNull('school_id')
            ->update(['school_id' => $kitungwaId]);
    }

    public function down(): void
    {
        // Not reversible — we don't know which rows were null before.
    }
};
