<?php

use App\Models\Grade;
use App\Models\Mark;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Excel mark imports (MarksImport, QuestionMarksImport) always saved
     * grade_id as null instead of computing it — this backfills every
     * existing mark whose value falls within a defined grade band.
     */
    public function up(): void
    {
        // Grade bands are per-school (BelongsToSchool) — match marks against
        // their own school's bands, not another school's.
        $schoolIds = Mark::whereNull('grade_id')->distinct()->pluck('school_id');

        foreach ($schoolIds as $schoolId) {
            Grade::where('school_id', $schoolId)->orderBy('min_mark')->get()->each(function (Grade $grade) use ($schoolId) {
                Mark::where('school_id', $schoolId)
                    ->whereNull('grade_id')
                    ->whereBetween('mark', [$grade->min_mark, $grade->max_mark])
                    ->update(['grade_id' => $grade->id]);
            });
        }
    }

    public function down(): void
    {
        // Not reversible — we don't know which grade_id values were null before.
    }
};
