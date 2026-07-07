<?php

use App\Models\Enrollment;
use App\Models\Mark;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * marks.class_id was only just added (see the previous migration) and
     * every existing row has it null, which would make the teacher-scoping
     * fix in MarkController::index() hide every historical mark instead of
     * correctly scoping them. Backfill from the student's enrollment for
     * that mark's academic session — the same source MarkController::store()
     * itself uses to resolve class_id for a new mark.
     */
    public function up(): void
    {
        Mark::whereNull('class_id')->chunkById(500, function ($marks) {
            foreach ($marks as $mark) {
                $enrollment = Enrollment::where('student_id', $mark->student_id)
                    ->where('academic_session_id', $mark->academic_session_id)
                    ->orderByRaw("status = 'active' desc")
                    ->orderByDesc('id')
                    ->first();

                if ($enrollment) {
                    $mark->update(['class_id' => $enrollment->class_id]);
                }
            }
        });
    }

    public function down(): void
    {
        // Not reversible — we don't know which rows were null before.
    }
};
