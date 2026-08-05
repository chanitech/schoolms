<?php

use App\Models\Bus;
use App\Models\BusRoute;
use App\Models\BusStop;
use App\Models\Notice;
use App\Models\School;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentTransportAssignment;
use App\Models\Suggestion;
use App\Models\TimetableEntry;
use App\Models\TimetableSessionLog;
use App\Models\TransportFee;
use App\Models\TransportPayment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * The "Chani Demo Secondary School" tenant is what prospects and reviewers
 * see first. Several modules built this session (Transport, Notice Board,
 * Suggestions, session attendance) went live with zero demo rows, so those
 * screens looked broken/empty on a demo walkthrough. This backfills
 * realistic-looking data for that one tenant only — every write below is
 * scoped to school_id = the "demo" school, nothing else is touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        $school = School::where('slug', 'demo')->first();
        if (!$school) {
            return;
        }

        app()->instance('currentSchool', $school);

        $this->seedTransport($school);
        $this->seedNotices($school);
        $this->seedSuggestions($school);
        $this->seedSessionLogs($school);
    }

    public function down(): void
    {
        $school = School::where('slug', 'demo')->first();
        if (!$school) {
            return;
        }

        TransportPayment::where('school_id', $school->id)->delete();
        TransportFee::where('school_id', $school->id)->delete();
        StudentTransportAssignment::where('school_id', $school->id)->delete();
        BusStop::where('school_id', $school->id)->delete();
        BusRoute::where('school_id', $school->id)->delete();
        Bus::where('school_id', $school->id)->delete();
        Notice::where('school_id', $school->id)->delete();
        Suggestion::where('school_id', $school->id)->delete();
        TimetableSessionLog::where('school_id', $school->id)->delete();
    }

    private function seedTransport(School $school): void
    {
        if (Bus::where('school_id', $school->id)->exists()) {
            return;
        }

        $drivers = Staff::where('school_id', $school->id)->inRandomOrder()->take(2)->get();
        $principal = User::withoutGlobalScopes()->where('school_id', $school->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Principal'))->first();

        $bus1 = Bus::create([
            'plate_number' => 'T 123 ABC', 'name' => 'Bus 1', 'capacity' => 45,
            'driver_staff_id' => $drivers[0]->id ?? null, 'status' => 'active',
        ]);
        $bus2 = Bus::create([
            'plate_number' => 'T 456 DEF', 'name' => 'Bus 2', 'capacity' => 40,
            'driver_staff_id' => $drivers[1]->id ?? null, 'status' => 'active',
        ]);

        $route1 = BusRoute::create([
            'bus_id' => $bus1->id, 'name' => 'Kaloleni - Town Route',
            'description' => 'Serves Kaloleni, Msasani, and Town Centre neighbourhoods.',
            'monthly_fee' => 25000, 'status' => 'active',
        ]);
        $route2 = BusRoute::create([
            'bus_id' => $bus2->id, 'name' => 'Mbezi - Tegeta Route',
            'description' => 'Serves Mbezi Beach, Tegeta, and Wazo neighbourhoods.',
            'monthly_fee' => 30000, 'status' => 'active',
        ]);

        $route1Stops = collect([
            ['name' => 'Kaloleni Stage', 'stop_order' => 1, 'pickup_time' => '06:30', 'dropoff_time' => '16:00'],
            ['name' => 'Msasani Junction', 'stop_order' => 2, 'pickup_time' => '06:45', 'dropoff_time' => '15:50'],
            ['name' => 'Town Centre', 'stop_order' => 3, 'pickup_time' => '07:00', 'dropoff_time' => '15:40'],
        ])->map(fn ($s) => BusStop::create($s + ['route_id' => $route1->id]));

        $route2Stops = collect([
            ['name' => 'Mbezi Beach Stage', 'stop_order' => 1, 'pickup_time' => '06:20', 'dropoff_time' => '16:10'],
            ['name' => 'Tegeta Junction', 'stop_order' => 2, 'pickup_time' => '06:40', 'dropoff_time' => '15:55'],
            ['name' => 'Wazo Hill', 'stop_order' => 3, 'pickup_time' => '06:55', 'dropoff_time' => '15:45'],
        ])->map(fn ($s) => BusStop::create($s + ['route_id' => $route2->id]));

        $students = Student::where('school_id', $school->id)->inRandomOrder()->take(16)->get();

        foreach ($students->take(8) as $i => $student) {
            StudentTransportAssignment::create([
                'student_id' => $student->id, 'route_id' => $route1->id,
                'stop_id' => $route1Stops[$i % 3]->id, 'status' => 'active',
                'start_date' => now()->subMonths(2), 'assigned_by' => $principal->id ?? null,
            ]);
        }
        foreach ($students->slice(8, 8)->values() as $i => $student) {
            StudentTransportAssignment::create([
                'student_id' => $student->id, 'route_id' => $route2->id,
                'stop_id' => $route2Stops[$i % 3]->id, 'status' => 'active',
                'start_date' => now()->subMonths(2), 'assigned_by' => $principal->id ?? null,
            ]);
        }

        // Two months of fees, with realistic partial payment history.
        foreach ([now()->subMonth(), now()] as $idx => $monthDate) {
            $isPastMonth = $idx === 0;
            foreach (StudentTransportAssignment::where('school_id', $school->id)->get() as $assignment) {
                $route = BusRoute::find($assignment->route_id);
                $fee = TransportFee::create([
                    'student_id' => $assignment->student_id, 'route_id' => $route->id,
                    'month' => $monthDate->month, 'year' => $monthDate->year,
                    'amount' => $route->monthly_fee, 'balance' => $route->monthly_fee,
                    'status' => 'unpaid', 'due_date' => $monthDate->copy()->endOfMonth(),
                ]);

                $roll = rand(1, 100);
                if ($isPastMonth && $roll <= 75) {
                    TransportPayment::create([
                        'transport_fee_id' => $fee->id, 'amount' => $route->monthly_fee,
                        'payment_method' => 'cash', 'payment_date' => $monthDate->copy()->addDays(rand(1, 15)),
                        'recorded_by' => $principal->id ?? null,
                    ]);
                    $fee->update(['amount_paid' => $route->monthly_fee, 'balance' => 0, 'status' => 'paid']);
                } elseif ($roll <= 30) {
                    $partial = round($route->monthly_fee * 0.5);
                    TransportPayment::create([
                        'transport_fee_id' => $fee->id, 'amount' => $partial,
                        'payment_method' => 'mobile_money', 'payment_date' => $monthDate->copy()->addDays(rand(1, 15)),
                        'recorded_by' => $principal->id ?? null,
                    ]);
                    $fee->update(['amount_paid' => $partial, 'balance' => $route->monthly_fee - $partial, 'status' => 'partial']);
                }
            }
        }
    }

    private function seedNotices(School $school): void
    {
        if (Notice::where('school_id', $school->id)->exists()) {
            return;
        }

        $poster = User::withoutGlobalScopes()->where('school_id', $school->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Principal'))->first();

        $notices = [
            ['title' => 'Mid-Term Examinations Timetable Released', 'audience' => 'all', 'pinned' => true, 'days_ago' => 1,
                'body' => "The mid-term examination timetable for all forms has been published. Please check with your class teacher and prepare accordingly.\n\nExaminations begin Monday next week and run for five days."],
            ['title' => 'School Fees Payment Deadline Reminder', 'audience' => 'guardians', 'pinned' => true, 'days_ago' => 0,
                'body' => "This is a reminder that all outstanding school fees for this term must be settled by the end of this month to avoid late payment penalties. Contact the bursar's office for payment plans."],
            ['title' => 'Staff Meeting - Friday 2:00 PM', 'audience' => 'staff', 'pinned' => false, 'days_ago' => 2,
                'body' => 'All teaching staff are required to attend the end-of-term staff meeting in the staff room this Friday at 2:00 PM. Agenda includes term results review and next term planning.'],
            ['title' => 'Sports Day - Save the Date', 'audience' => 'all', 'pinned' => false, 'days_ago' => 5,
                'body' => 'Our annual Inter-House Sports Day will be held next month. Parents and guardians are warmly invited to attend and cheer on their children.'],
            ['title' => 'Parent-Teacher Conference Schedule', 'audience' => 'guardians', 'pinned' => false, 'days_ago' => 9,
                'body' => "Individual parent-teacher conferences will be held over two days next week. Please book your preferred time slot through your child's class teacher."],
            ['title' => 'New Library Books Arrived', 'audience' => 'all', 'pinned' => false, 'days_ago' => 14,
                'body' => 'The library has received a new shipment of reference and story books across all subjects. Students are encouraged to visit and borrow during break time.'],
            ['title' => 'Lesson Plan Submission Deadline', 'audience' => 'staff', 'pinned' => false, 'days_ago' => 18,
                'body' => 'All subject teachers should submit next term lesson plans to the Academic office by end of this week for review and approval.'],
        ];

        foreach ($notices as $n) {
            $when = now()->subDays($n['days_ago']);
            Notice::create([
                'school_id' => $school->id,
                'title' => $n['title'], 'body' => $n['body'], 'audience' => $n['audience'],
                'pinned' => $n['pinned'], 'posted_by' => $poster->id ?? null,
                'created_at' => $when, 'updated_at' => $when,
            ]);
        }
    }

    private function seedSuggestions(School $school): void
    {
        if (Suggestion::where('school_id', $school->id)->exists()) {
            return;
        }

        $teachers = User::withoutGlobalScopes()->where('school_id', $school->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Teacher'))->get();
        $principal = User::withoutGlobalScopes()->where('school_id', $school->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Principal'))->first();

        $rows = [
            ['category' => 'suggestion', 'subject' => 'Extend library opening hours', 'anon' => false, 'days_ago' => 3, 'status' => 'new'],
            ['category' => 'complaint', 'subject' => 'Water shortage in Form II block', 'anon' => false, 'days_ago' => 6,
                'status' => 'resolved', 'response' => 'Thank you for flagging this — the plumbing issue has been fixed and water supply is restored to all Form II classrooms.'],
            ['category' => 'compliment', 'subject' => 'Great job on the science fair', 'anon' => true, 'days_ago' => 8,
                'status' => 'resolved', 'response' => 'Thank you for the kind words — we will pass this along to the science department.'],
            ['category' => 'opinion', 'subject' => 'Consider adding a computer club', 'anon' => false, 'days_ago' => 11, 'status' => 'in_review'],
            ['category' => 'complaint', 'subject' => 'Bus route running late in the mornings', 'anon' => true, 'days_ago' => 2, 'status' => 'new'],
            ['category' => 'suggestion', 'subject' => 'More parking space needed at pickup time', 'anon' => false, 'days_ago' => 15,
                'status' => 'dismissed', 'response' => "We've reviewed this and unfortunately don't have space to expand parking at this time, but we're exploring a staggered pickup schedule."],
        ];

        foreach ($rows as $i => $r) {
            $submitter = $r['anon'] ? null : ($teachers[$i % max($teachers->count(), 1)] ?? null);
            $when = now()->subDays($r['days_ago']);
            $responded = isset($r['response']);

            Suggestion::create([
                'school_id' => $school->id,
                'submitted_by' => $submitter?->id,
                'submitter_role' => $r['anon'] ? 'Teacher' : 'Teacher',
                'is_anonymous' => $r['anon'],
                'category' => $r['category'],
                'subject' => $r['subject'],
                'message' => $r['subject'] . ' — submitted via the staff Suggestions & Opinions box.',
                'status' => $r['status'],
                'admin_response' => $r['response'] ?? null,
                'responded_by' => $responded ? ($principal->id ?? null) : null,
                'responded_at' => $responded ? $when->copy()->addDay() : null,
                'created_at' => $when, 'updated_at' => $when,
            ]);
        }
    }

    private function seedSessionLogs(School $school): void
    {
        if (TimetableSessionLog::where('school_id', $school->id)->exists()) {
            return;
        }

        $recorder = User::withoutGlobalScopes()->where('school_id', $school->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'Academic'))->first();
        if (!$recorder) {
            return;
        }

        $monday = now()->startOfWeek(\Carbon\Carbon::MONDAY)->subWeek();

        for ($day = 0; $day < 5; $day++) {
            $date = $monday->copy()->addDays($day);
            $entries = TimetableEntry::where('school_id', $school->id)->where('day_of_week', $day + 1)->get();

            foreach ($entries as $entry) {
                $roll = rand(1, 100);
                $status = $roll <= 78 ? 'attended' : ($roll <= 92 ? 'late' : 'absent');

                TimetableSessionLog::create([
                    'school_id' => $school->id,
                    'timetable_entry_id' => $entry->id, 'teacher_id' => $entry->teacher_id,
                    'class_id' => $entry->class_id, 'subject_id' => $entry->subject_id,
                    'period_id' => $entry->period_id, 'session_date' => $date,
                    'status' => $status, 'recorded_by' => $recorder->id,
                ]);
            }
        }
    }
};
