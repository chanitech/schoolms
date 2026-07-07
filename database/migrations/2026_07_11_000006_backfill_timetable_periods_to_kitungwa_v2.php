<?php

use App\Models\School;
use App\Models\TimetablePeriod;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The previous attempt (2026_07_11_000005) matched on slug
     * 'kitungwa-adventist-secondary-school', which was only ever true in
     * local dev data. Production's actual slug was shortened to 'kitungwa'
     * earlier this session (School Code login work) — the dev DB was never
     * updated to match, so that migration found no matching school and
     * silently no-op'd (no error, no effect). Match more permissively this
     * time so it's correct regardless of which slug convention is live.
     */
    public function up(): void
    {
        $kitungwaId = School::where('slug', 'kitungwa')
            ->orWhere('slug', 'kitungwa-adventist-secondary-school')
            ->orWhere('name', 'like', '%Kitungwa%')
            ->value('id');

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
