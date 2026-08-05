<?php

use App\Models\CounselingIntakeForm;
use App\Models\School;
use Illuminate\Database\Migrations\Migration;

/**
 * DemoModulesSeeder computed intake-form age as
 * now()->diffInYears($student->date_of_birth) — in this Carbon version that
 * returns a signed diff (dob minus now, not now minus dob), so every seeded
 * demo student showed a negative age (e.g. "-15"). Student::age already has
 * the correct Carbon::parse(...)->age accessor; this patches the existing
 * demo-school rows to use it, scoped to the demo school only.
 */
return new class extends Migration
{
    public function up(): void
    {
        $school = School::where('slug', 'demo')->first();
        if (!$school) {
            return;
        }

        CounselingIntakeForm::where('school_id', $school->id)->with('student')
            ->get()->each(function ($form) {
                if ($form->student) {
                    $form->update(['age' => $form->student->age]);
                }
            });
    }

    public function down(): void
    {
        // No-op — a data correction, not worth reverting to the buggy value.
    }
};
