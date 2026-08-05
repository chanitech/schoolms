<?php

use App\Models\Notice;
use App\Models\School;
use App\Models\Suggestion;
use Illuminate\Database\Migrations\Migration;

/**
 * Data patch for 2026_08_09_000001_seed_demo_school_data: that migration
 * passed 'created_at'/'updated_at' to Notice::create()/Suggestion::create(),
 * but those columns aren't in either model's $fillable, so Eloquent
 * silently dropped them and every seeded row got the migration's run time
 * instead of the intended historical spread — every notice/suggestion
 * showed a "New" badge and one response even predated its own suggestion.
 * Laravel tracks migrations by filename, not content, so a plain
 * `migrate --force` on an environment that already ran the buggy version
 * won't re-seed with corrected code — this re-applies the correct dates
 * in place, matched by title/subject, scoped to the demo school only.
 */
return new class extends Migration
{
    public function up(): void
    {
        $school = School::where('slug', 'demo')->first();
        if (!$school) {
            return;
        }

        $notices = [
            'Mid-Term Examinations Timetable Released' => 1,
            'School Fees Payment Deadline Reminder' => 0,
            'Staff Meeting - Friday 2:00 PM' => 2,
            'Sports Day - Save the Date' => 5,
            'Parent-Teacher Conference Schedule' => 9,
            'New Library Books Arrived' => 14,
            'Lesson Plan Submission Deadline' => 18,
        ];

        foreach ($notices as $title => $daysAgo) {
            $when = now()->subDays($daysAgo);
            Notice::where('school_id', $school->id)->where('title', $title)
                ->get()->each(function ($notice) use ($when) {
                    $notice->forceFill(['created_at' => $when, 'updated_at' => $when])->save();
                });
        }

        $suggestions = [
            'Extend library opening hours' => ['days_ago' => 3, 'responded' => false],
            'Water shortage in Form II block' => ['days_ago' => 6, 'responded' => true],
            'Great job on the science fair' => ['days_ago' => 8, 'responded' => true],
            'Consider adding a computer club' => ['days_ago' => 11, 'responded' => false],
            'Bus route running late in the mornings' => ['days_ago' => 2, 'responded' => false],
            'More parking space needed at pickup time' => ['days_ago' => 15, 'responded' => true],
        ];

        foreach ($suggestions as $subject => $meta) {
            $when = now()->subDays($meta['days_ago']);
            Suggestion::where('school_id', $school->id)->where('subject', $subject)
                ->get()->each(function ($suggestion) use ($when, $meta) {
                    $attrs = ['created_at' => $when, 'updated_at' => $when];
                    if ($meta['responded']) {
                        $attrs['responded_at'] = $when->copy()->addDay();
                    }
                    $suggestion->forceFill($attrs)->save();
                });
        }
    }

    public function down(): void
    {
        // No-op — this only corrects timestamps on rows the previous
        // migration's down() already knows how to remove entirely.
    }
};
