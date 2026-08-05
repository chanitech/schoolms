<?php

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

/**
 * Only the demo school's Principal account (demo@demo.ac.tz) had a known
 * password, so pages gated to other roles (accountant/treasurer finance
 * approvals, HOD-only daily reports, a staff member's own loan) couldn't be
 * demoed or screenshotted. Gives one existing user per additional role a
 * known password, reusing the same convention as the Principal demo login.
 */
return new class extends Migration
{
    private const PASSWORD = 'demo1234';
    private const EMAILS = [
        'furaha.mwakalinga@demo.ac.tz', // accountant
        'frank.massawe@demo.ac.tz',     // HOD
    ];

    public function up(): void
    {
        $school = School::where('slug', 'demo')->first();
        if (!$school) {
            return;
        }

        User::withoutGlobalScopes()->where('school_id', $school->id)
            ->whereIn('email', self::EMAILS)
            ->update(['password' => Hash::make(self::PASSWORD)]);
    }

    public function down(): void
    {
        // No-op — resetting to an unknown prior password isn't recoverable
        // or meaningful; leaving the demo login as-is is harmless.
    }
};
