<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\TimetableEntry;
use App\Models\TimetablePeriod;
use App\Models\TimetableReview;
use App\Models\TimetableSessionLog;
use App\Models\User;
use App\Notifications\TimetableNotification;
use App\Services\TimetableGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    public function __construct(private TimetableGeneratorService $generator) {}

    // ── Index ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Timetable::with(['session', 'creator'])->latest();

        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('status'))     $query->where('status', $request->status);
        if ($request->filled('session_id')) $query->where('academic_session_id', $request->session_id);

        $timetables = $query->paginate(20)->withQueryString();
        $sessions   = AcademicSession::orderBy('name')->get();

        // For dashboard: get published timetables relevant to current user
        $publishedForUser = $this->getPublishedForUser($user);

        return view('timetables.index', compact('timetables', 'sessions', 'publishedForUser'));
    }

    // ── Create form ───────────────────────────────────────────────────────
    public function create()
    {
        $sessions = AcademicSession::orderBy('name')->get();
        $classes  = SchoolClass::orderBy('name')->get();
        $periods  = TimetablePeriod::where('is_active', true)->orderBy('order_no')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('timetables.create', compact('sessions', 'classes', 'periods', 'subjects'));
    }

    // ── Store (save config then generate) ─────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => 'required|string|max:200',
            'type'                => 'required|in:class,exam',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'class_ids'           => 'required|array|min:1',
            'class_ids.*'         => 'exists:school_classes,id',
            'notes'               => 'nullable|string|max:500',
            // class type settings
            'default_periods_per_week' => 'nullable|integer|min:1|max:10',
            'days'                => 'nullable|array',
            'periods_per_week'    => 'nullable|array',  // [class_subject_key => n]
            'session_duration'    => 'nullable|integer|in:35,40,45,50,60',
            'school_start_time'   => 'nullable|string',
            // exam type settings
            'exam_dates'             => 'nullable|string',
            'exam_duration'          => 'nullable|integer|min:30|max:240',
            'exam_slots'             => 'nullable|array',
            'invigilators_per_slot'  => 'nullable|integer|min:1|max:5',
        ]);

        $settings = [];
        if ($data['type'] === 'class') {
            $settings['days']                    = $data['days'] ?? [1, 2, 3, 4, 5];
            $settings['default_periods_per_week'] = (int)($data['default_periods_per_week'] ?? 5);
            $settings['periods_per_week']         = $data['periods_per_week'] ?? [];
            $settings['session_duration']         = (int)($request->input('session_duration', 40));
            $settings['school_start_time']        = $request->input('school_start_time', '07:30');

            // Parse special sessions (Assembly, Prayer, Self Study, etc.)
            $specialSessions = [];
            foreach ($request->input('special_sessions', []) as $ss) {
                if (!empty($ss['name'])) {
                    $specialSessions[] = [
                        'name'       => trim($ss['name']),
                        'type'       => $ss['type']  ?? 'free',
                        'color'      => $ss['color'] ?? 'secondary',
                        'days'       => array_map('intval', $ss['days'] ?? [1, 2, 3, 4, 5]),
                        'start_time' => $ss['start_time'] ?? '00:00',
                        'end_time'   => $ss['end_time']   ?? '00:00',
                    ];
                }
            }
            // Sort by start_time for clean display
            usort($specialSessions, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
            $settings['special_sessions'] = $specialSessions;

            // Timing mode: auto (compute) or manual (user-defined times per period)
            $settings['timing_mode'] = $request->input('timing_mode', 'auto');
            if ($settings['timing_mode'] === 'manual') {
                $periodTimes = [];
                foreach ($request->input('period_times', []) as $pid => $t) {
                    if (!empty($t['start']) && !empty($t['end'])) {
                        $periodTimes[(int)$pid] = ['start' => $t['start'], 'end' => $t['end']];
                    }
                }
                $settings['period_times'] = $periodTimes;
            }

            // Combination / optional subject groups (with optional class scope)
            $combinations = [];
            foreach ($request->input('combo_subjects', []) as $idx => $subjectIds) {
                $subjectIds = array_filter(array_map('intval', (array)$subjectIds));
                if (count($subjectIds) >= 2) {
                    $classIds2 = array_filter(array_map('intval', (array)$request->input("combo_class_ids.{$idx}", [])));
                    $combinations[] = [
                        'subjects'  => array_values($subjectIds),
                        'shared'    => max(1, (int)($request->input("combo_shared.{$idx}", 2))),
                        'class_ids' => array_values($classIds2),
                    ];
                }
            }
            $settings['combinations']   = $combinations;
            $settings['double_periods'] = array_keys(
                array_filter($request->input('double_periods', []), fn($v) => $v == '1')
            );
        } else {
            $rawDates = array_filter(array_map('trim', explode("\n", $data['exam_dates'] ?? '')));
            $settings['exam_dates']            = array_values($rawDates);
            $settings['exam_duration']         = (int)($data['exam_duration'] ?? 120);
            $settings['invigilators_per_slot'] = (int)($data['invigilators_per_slot'] ?? 2);
            $settings['exam_slots']            = $data['exam_slots'] ?? [
                ['start' => '08:00', 'end' => '10:30', 'label' => 'Morning'],
                ['start' => '14:00', 'end' => '16:30', 'label' => 'Afternoon'],
            ];
        }

        $timetable = Timetable::create([
            'title'               => $data['title'],
            'type'                => $data['type'],
            'academic_session_id' => $data['academic_session_id'],
            'class_ids'           => $data['class_ids'],
            'settings'            => $settings,
            'notes'               => $data['notes'] ?? null,
            'status'              => 'draft',
            'created_by'          => Auth::id(),
        ]);

        // Auto-generate immediately
        return $this->runGenerate($timetable);
    }

    // ── Show (view timetable grid) ─────────────────────────────────────────
    public function show(Timetable $timetable)
    {
        $timetable->load(['session', 'creator', 'reviews.reviewer']);

        $entries = $timetable->entries()
            ->with(['schoolClass', 'subject', 'teacher', 'period'])
            ->get();

        $periods    = TimetablePeriod::where('is_active', true)->orderBy('order_no')->get();
        $classes    = SchoolClass::whereIn('id', $timetable->class_ids)->orderBy('name')->get();
        $collisions = $this->generator->getCollisions($timetable);
        $myReview   = TimetableReview::where('timetable_id', $timetable->id)
                        ->where('reviewer_id', Auth::id())->first();

        // Build a keyed map of all invigilator IDs → User objects for the show view
        $invigilatorIds = $entries->flatMap(fn($e) => $e->invigilator_ids ?? [])->unique()->filter();
        $invigilators   = User::whereIn('id', $invigilatorIds)->get()->keyBy('id');

        // Special sessions (Assembly, Prayer, Self Study, etc.) stored in settings
        $specialSessions = ($timetable->type === 'class')
            ? ($timetable->settings['special_sessions'] ?? [])
            : [];

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        $canReview  = $timetable->status === 'pending_review'
                   && !$myReview
                   && $authUser->hasAnyRole(['HOD', 'Admin', 'Academic']);

        $canPublish = $authUser->hasAnyRole(['Admin', 'Academic'])
                   && in_array($timetable->status, ['pending_review', 'approved']);

        $canSubmit  = $authUser->hasAnyRole(['Admin', 'Academic'])
                   && $timetable->status === 'draft';

        // Capacity analysis panel (class timetables only, shown to admins)
        $capacityAnalysis = (
            $timetable->type === 'class'
            && $authUser->hasAnyRole(['Admin', 'Academic'])
        ) ? $this->generator->getCapacityAnalysis($timetable) : [];

        // Build grid for class timetables: [class_id][day][period_id] => [entries...]
        // Multiple entries per cell = combination/optional subjects sharing that slot
        $grid = [];
        if ($timetable->type === 'class') {
            foreach ($entries as $e) {
                $grid[$e->class_id][$e->day_of_week][$e->period_id][] = $e;
            }
        }

        // Exam list grouped by date then class
        $examGrid = [];
        if ($timetable->type === 'exam') {
            foreach ($entries as $e) {
                $date    = $e->exam_date?->format('Y-m-d') ?? 'Unknown';
                $timeKey = $e->start_time . '-' . $e->end_time;
                $examGrid[$date][$timeKey][$e->class_id][] = $e;
            }
            ksort($examGrid);
        }

        return view('timetables.show', compact(
            'timetable', 'entries', 'periods', 'classes',
            'grid', 'examGrid', 'collisions',
            'myReview', 'canReview', 'canPublish', 'canSubmit',
            'invigilators', 'specialSessions', 'capacityAnalysis'
        ));
    }

    // ── Edit form ─────────────────────────────────────────────────────────
    public function edit(Timetable $timetable)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasAnyRole(['Admin', 'Academic'])) abort(403);
        if ($timetable->status === 'published') {
            return back()->with('error', 'Unpublish the timetable before editing.');
        }

        $sessions = AcademicSession::orderBy('name')->get();
        $classes  = SchoolClass::orderBy('name')->get();
        $periods  = TimetablePeriod::where('is_active', true)->orderBy('order_no')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('timetables.edit', compact('timetable', 'sessions', 'classes', 'periods', 'subjects'));
    }

    // ── Update (save new settings + regenerate) ───────────────────────────
    public function update(Request $request, Timetable $timetable)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasAnyRole(['Admin', 'Academic'])) abort(403);
        if ($timetable->status === 'published') {
            return back()->with('error', 'Unpublish the timetable before editing.');
        }

        $data = $request->validate([
            'title'               => 'required|string|max:200',
            'notes'               => 'nullable|string|max:500',
            'class_ids'           => 'required|array|min:1',
            'class_ids.*'         => 'exists:school_classes,id',
            'default_periods_per_week' => 'nullable|integer|min:1|max:10',
            'days'                => 'nullable|array',
            'periods_per_week'    => 'nullable|array',
            'session_duration'    => 'nullable|integer|in:35,40,45,50,60',
            'school_start_time'   => 'nullable|string',
            'exam_dates'          => 'nullable|string',
            'exam_duration'       => 'nullable|integer|min:30|max:240',
            'exam_slots'          => 'nullable|array',
            'invigilators_per_slot' => 'nullable|integer|min:1|max:5',
        ]);

        $settings = $timetable->settings ?? [];

        if ($timetable->type === 'class') {
            $settings['days']                     = $data['days'] ?? [1, 2, 3, 4, 5];
            $settings['default_periods_per_week'] = (int)($data['default_periods_per_week'] ?? 5);
            $settings['periods_per_week']         = $data['periods_per_week'] ?? [];
            $settings['session_duration']         = (int)($request->input('session_duration', 40));
            $settings['school_start_time']        = $request->input('school_start_time', '07:30');

            $specialSessions = [];
            foreach ($request->input('special_sessions', []) as $ss) {
                if (!empty($ss['name'])) {
                    $specialSessions[] = [
                        'name'       => trim($ss['name']),
                        'type'       => $ss['type']  ?? 'free',
                        'color'      => $ss['color'] ?? 'secondary',
                        'days'       => array_map('intval', $ss['days'] ?? [1, 2, 3, 4, 5]),
                        'start_time' => $ss['start_time'] ?? '00:00',
                        'end_time'   => $ss['end_time']   ?? '00:00',
                    ];
                }
            }
            usort($specialSessions, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));
            $settings['special_sessions'] = $specialSessions;

            // Timing mode
            $settings['timing_mode'] = $request->input('timing_mode', 'auto');
            if ($settings['timing_mode'] === 'manual') {
                $periodTimes = [];
                foreach ($request->input('period_times', []) as $pid => $t) {
                    if (!empty($t['start']) && !empty($t['end'])) {
                        $periodTimes[(int)$pid] = ['start' => $t['start'], 'end' => $t['end']];
                    }
                }
                $settings['period_times'] = $periodTimes;
            } else {
                unset($settings['period_times']);
            }

            // Combinations with optional class scope
            $combinations = [];
            foreach ($request->input('combo_subjects', []) as $idx => $subjectIds) {
                $subjectIds = array_filter(array_map('intval', (array)$subjectIds));
                if (count($subjectIds) >= 2) {
                    $classIds2 = array_filter(array_map('intval', (array)$request->input("combo_class_ids.{$idx}", [])));
                    $combinations[] = [
                        'subjects'  => array_values($subjectIds),
                        'shared'    => max(1, (int)($request->input("combo_shared.{$idx}", 2))),
                        'class_ids' => array_values($classIds2),
                    ];
                }
            }
            $settings['combinations']   = $combinations;
            $settings['double_periods'] = array_keys(
                array_filter($request->input('double_periods', []), fn($v) => $v == '1')
            );
        } else {
            $rawDates = array_filter(array_map('trim', explode("\n", $data['exam_dates'] ?? '')));
            $settings['exam_dates']            = array_values($rawDates);
            $settings['exam_duration']         = (int)($data['exam_duration'] ?? 120);
            $settings['invigilators_per_slot'] = (int)($data['invigilators_per_slot'] ?? 2);
            $settings['exam_slots']            = $data['exam_slots'] ?? $settings['exam_slots'] ?? [];
        }

        $timetable->update([
            'title'     => $data['title'],
            'notes'     => $data['notes'] ?? null,
            'class_ids' => $data['class_ids'],
            'settings'  => $settings,
            'status'    => 'draft',
        ]);

        return $this->runGenerate($timetable);
    }

    // ── Regenerate ────────────────────────────────────────────────────────
    public function regenerate(Timetable $timetable)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasAnyRole(['Admin', 'Academic'])) abort(403);
        if ($timetable->status === 'published') {
            return back()->with('error', 'Cannot regenerate a published timetable.');
        }

        $timetable->update(['status' => 'draft']);
        return $this->runGenerate($timetable);
    }

    private function runGenerate(Timetable $timetable)
    {
        if ($timetable->type === 'class') {
            $result = $this->generator->generateClassTimetable($timetable);
        } else {
            $result = $this->generator->generateExamTimetable($timetable);
        }

        $this->generator->saveEntries($timetable, $result['entries']);

        $warnings = $result['warnings'];
        $slots    = count($result['entries']);

        if (empty($warnings)) {
            return redirect()->route('timetables.show', $timetable)
                ->with('success', "{$slots} slots generated successfully — no conflicts.");
        }

        // Separate capacity/overload warnings from missed-subject warnings
        $critical = array_filter($warnings, fn($w) => str_starts_with($w, '⚠'));
        $missed   = array_filter($warnings, fn($w) => !str_starts_with($w, '⚠'));

        $lines = ["{$slots} slots generated."];
        if ($critical) {
            $lines[] = implode(' ', $critical);
        }
        if ($missed) {
            $lines[] = count($missed) . ' subject(s) partially scheduled: '
                . implode(' | ', array_slice(array_values($missed), 0, 8))
                . (count($missed) > 8 ? ' …and ' . (count($missed) - 8) . ' more.' : '');
        }

        return redirect()->route('timetables.show', $timetable)
            ->with('warning', implode(' ', $lines));
    }

    // ── Submit for review ─────────────────────────────────────────────────
    public function submitForReview(Timetable $timetable)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasAnyRole(['Admin', 'Academic'])) abort(403);
        if ($timetable->status !== 'draft') {
            return back()->with('error', 'Only draft timetables can be submitted for review.');
        }

        $collisions = $this->generator->getCollisions($timetable);
        if (!empty($collisions)) {
            return back()->with('error', 'Cannot submit — timetable has collisions: ' . implode('; ', array_slice($collisions, 0, 3)));
        }

        $timetable->update(['status' => 'pending_review']);

        // Notify HODs and Admins to review
        $notify = new TimetableNotification($timetable, 'submitted', $authUser->name);
        User::role(['HOD', 'Admin', 'Principal'])->where('id', '!=', Auth::id())->each(
            fn($u) => $u->notify($notify)
        );

        return back()->with('success', 'Timetable submitted for HOD review.');
    }

    // ── Review (HOD / Admin approve or reject) ────────────────────────────
    public function review(Request $request, Timetable $timetable): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['HOD', 'Admin', 'Academic'])) abort(403);
        if ($timetable->status !== 'pending_review') {
            return response()->json(['error' => 'Timetable is not pending review.'], 422);
        }

        $request->validate([
            'action' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string|max:500',
        ]);

        $role = $user->hasRole('HOD') ? 'hod' : ($user->hasRole('Academic') ? 'academic' : 'admin');

        TimetableReview::updateOrCreate(
            ['timetable_id' => $timetable->id, 'reviewer_id' => $user->id],
            [
                'reviewer_role' => $role,
                'action'        => $request->action,
                'notes'         => $request->notes,
                'reviewed_at'   => now(),
            ]
        );

        if ($request->action === 'rejected') {
            $timetable->update(['status' => 'rejected']);
        }

        // Notify the creator about the review decision
        $action = $request->action === 'approved' ? 'approved' : 'rejected';
        $notify = new TimetableNotification($timetable, $action, $user->name);
        if ($timetable->created_by && $timetable->created_by !== Auth::id()) {
            User::find($timetable->created_by)?->notify($notify);
        }
        // Also notify Admin if approved by HOD
        if ($action === 'approved') {
            User::role('Admin')->where('id', '!=', Auth::id())->each(fn($u) => $u->notify($notify));
        }

        return response()->json([
            'success'       => true,
            'action'        => $request->action,
            'hod_approvals' => $timetable->fresh()->hodApprovalsCount(),
        ]);
    }

    // ── Publish ───────────────────────────────────────────────────────────
    public function publish(Timetable $timetable)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasAnyRole(['Admin', 'Academic'])) abort(403);
        if (!in_array($timetable->status, ['pending_review', 'approved'])) {
            return back()->with('error', 'Timetable must be under review before publishing.');
        }

        $collisions = $this->generator->getCollisions($timetable);
        if (!empty($collisions)) {
            return back()->with('error', 'Cannot publish — timetable still has collisions.');
        }

        $timetable->update([
            'status'       => 'published',
            'published_by' => Auth::id(),
            'published_at' => now(),
        ]);

        // Notify all teaching staff + creator that the timetable is live
        $notify = new TimetableNotification($timetable, 'published', $authUser->name);
        User::role(['Admin', 'Principal', 'HOD', 'Teacher'])->each(fn($u) => $u->notify($notify));

        return back()->with('success', 'Timetable published and is now visible on all relevant dashboards.');
    }

    // ── Unpublish / back to draft ─────────────────────────────────────────
    public function unpublish(Timetable $timetable)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasAnyRole(['Admin', 'Academic'])) abort(403);

        $timetable->update([
            'status'       => 'draft',
            'published_by' => null,
            'published_at' => null,
        ]);

        // Notify HOD and creator that timetable was withdrawn
        $notify = new TimetableNotification($timetable, 'unpublished', $authUser->name);
        User::role(['HOD', 'Teacher'])->where('id', '!=', Auth::id())->each(fn($u) => $u->notify($notify));

        return back()->with('success', 'Timetable unpublished and moved back to draft.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────
    public function destroy(Timetable $timetable)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser->hasAnyRole(['Admin', 'Academic'])) abort(403);
        $timetable->delete();
        return redirect()->route('timetables.index')->with('success', 'Timetable deleted.');
    }

    // ── API: today's schedule for a teacher or class ──────────────────────
    public function todaySchedule(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $today = now()->dayOfWeekIso; // 1=Mon … 5=Fri, 6=Sat, 7=Sun
        if ($today > 5) {
            return response()->json(['entries' => [], 'message' => 'No classes on weekends.']);
        }

        $query = TimetableEntry::with(['subject', 'schoolClass', 'teacher', 'period'])
            ->whereHas('timetable', fn($q) => $q->where('status', 'published')->where('type', 'class'))
            ->where('day_of_week', $today)
            ->whereNotNull('period_id');

        if ($user->hasRole('Teacher')) {
            $query->where('teacher_id', $user->id);
        } elseif ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $entries = $query->get()->sortBy(fn($e) => $e->period?->order_no ?? 99);

        // Load today's session logs for these entries (teacher can mark status on dashboard)
        $todayDate  = now()->toDateString();
        $entryIds   = $entries->pluck('id')->toArray();
        $logsToday  = [];
        if (!empty($entryIds)) {
            TimetableSessionLog::whereIn('timetable_entry_id', $entryIds)
                ->whereDate('session_date', $todayDate)
                ->get()
                ->each(function ($log) use (&$logsToday) {
                    $logsToday[$log->timetable_entry_id] = $log->status;
                });
        }

        $canLog = $user->hasRole('Teacher');

        $rows = $entries->map(fn($e) => [
            // Prefer stored computed times (set during generation), fall back to period DB times
            'sort_time'  => $e->start_time ?? $e->period?->start_time ?? '99:99',
            'entry_id'   => $e->id,
            'period'     => $e->period?->name,
            'time'       => ($e->start_time ?? $e->period?->start_time ?? '') . ' – '
                          . ($e->end_time   ?? $e->period?->end_time   ?? ''),
            'subject'    => $e->subject?->name,
            'class'      => $e->schoolClass?->name,
            'teacher'    => $e->teacher?->name ?: ($e->teacher ? $e->teacher->first_name . ' ' . $e->teacher->last_name : '—'),
            'room'       => $e->room,
            'is_special' => false,
            'type'       => 'class',
            'log_status' => $logsToday[$e->id] ?? null,
            'can_log'    => $canLog,
        ])->values()->toArray();

        // Merge special sessions (Prayer, Assembly, Self Study…) from published timetables
        // Only Admin/Academic/HOD/Guardian see special sessions (teachers see only their class schedule)
        if (!$user->hasRole('Teacher') || $request->filled('class_id')) {
            $publishedTimetables = Timetable::where('status', 'published')
                ->where('type', 'class')
                ->get(['settings']);

            $addedSessions = [];
            foreach ($publishedTimetables as $t) {
                foreach ($t->settings['special_sessions'] ?? [] as $ss) {
                    $key = $ss['name'] . '_' . $ss['start_time'];
                    if (in_array($today, $ss['days'] ?? []) && !in_array($key, $addedSessions)) {
                        $rows[] = [
                            'sort_time'  => $ss['start_time'],
                            'period'     => $ss['name'],
                            'time'       => $ss['start_time'] . ' – ' . $ss['end_time'],
                            'subject'    => null,
                            'class'      => 'All Classes',
                            'teacher'    => '—',
                            'room'       => '—',
                            'is_special' => true,
                            'type'       => $ss['type'] ?? 'free',
                            'color'      => $ss['color'] ?? 'secondary',
                        ];
                        $addedSessions[] = $key;
                    }
                }
            }
        }

        // Sort all rows by start time
        usort($rows, fn($a, $b) => strcmp($a['sort_time'], $b['sort_time']));

        return response()->json(['entries' => array_values($rows)]);
    }

    // ── API: topics + subtopics for an entry's subject/class lesson plan ──
    public function topicsForEntry(TimetableEntry $entry): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $canView = $user->hasAnyRole(['Admin', 'Academic', 'HOD', 'HR']) || $user->id === $entry->teacher_id;
        if (!$canView) return response()->json(['error' => 'Forbidden'], 403);

        // Search by subject + class only; teacher_id is not a hard filter
        // (plans may be created by admin on behalf of the teacher)
        $plan = \App\Models\LessonPlan::with(['topics.subtopics'])
            ->where('subject_id', $entry->subject_id)
            ->where('class_id', $entry->class_id)
            ->latest()
            ->first();

        if (!$plan) {
            return response()->json(['topics' => [], 'plan_id' => null, 'no_plan' => true]);
        }

        $topics = $plan->topics->map(fn($t) => [
            'id'        => $t->id,
            'title'     => $t->title,
            'subtopics' => $t->subtopics->map(fn($s) => [
                'id'      => $s->id,
                'title'   => $s->title,
                'covered' => $s->status === 'covered',
            ]),
        ]);

        return response()->json(['topics' => $topics, 'plan_id' => $plan->id, 'no_plan' => false]);
    }

    // ── API: log session status (AJAX, used from dashboard) ──────────────
    public function logSessionAjax(Request $request, TimetableEntry $entry): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $canLog = $user->hasAnyRole(['Admin', 'Academic', 'HOD', 'HR']) || $user->id === $entry->teacher_id;
        if (!$canLog) return response()->json(['error' => 'Forbidden'], 403);

        $data = $request->validate([
            'session_date' => 'required|date',
            'status'       => 'required|in:attended,late,absent,other',
            'notes'        => 'nullable|string|max:500',
        ]);

        $log = TimetableSessionLog::updateOrCreate(
            ['timetable_entry_id' => $entry->id, 'session_date' => $data['session_date']],
            [
                'teacher_id'  => $entry->teacher_id,
                'class_id'    => $entry->class_id,
                'subject_id'  => $entry->subject_id,
                'period_id'   => $entry->period_id,
                'status'      => $data['status'],
                'notes'       => $data['notes'] ?? null,
                'recorded_by' => $user->id,
            ]
        );

        return response()->json(['success' => true, 'status' => $log->status]);
    }

    // ── API: get subjects per class for period configuration ──────────────
    public function subjectsByClasses(Request $request): JsonResponse
    {
        $classIds = $request->input('class_ids', []);
        if (empty($classIds)) return response()->json([]);

        $rows = DB::table('subject_class')
            ->join('subjects', 'subjects.id', '=', 'subject_class.subject_id')
            ->join('school_classes', 'school_classes.id', '=', 'subject_class.class_id')
            ->whereIn('subject_class.class_id', $classIds)
            ->select(
                'subject_class.class_id',
                'school_classes.name as class_name',
                'subject_class.subject_id',
                'subjects.name as subject_name',
                'subject_class.teacher_id'
            )
            ->orderBy('school_classes.name')
            ->orderBy('subjects.name')
            ->get();

        return response()->json($rows);
    }

    // ── Teacher Session Dashboard ─────────────────────────────────────────
    public function mySessionsDashboard(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isHR = $user->hasAnyRole(['Admin', 'Academic', 'HOD', 'HR']);

        // For HR: allow viewing any teacher's sessions
        $teacherId = $user->id;
        $viewingTeacher = $user;
        if ($isHR && $request->filled('teacher_id')) {
            $viewingTeacher = User::findOrFail($request->teacher_id);
            $teacherId = $viewingTeacher->id;
        }

        // Week navigation: default to current week (Mon–Fri)
        $weekOffset = (int) $request->input('week', 0);
        $weekStart  = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks($weekOffset);
        $weekEnd    = $weekStart->copy()->addDays(4); // Friday

        // Build date map: day_of_week => Carbon date
        $weekDates = [];
        for ($d = 1; $d <= 5; $d++) {
            $weekDates[$d] = $weekStart->copy()->addDays($d - 1);
        }

        // Load teacher's published class timetable entries for the whole week
        $entries = TimetableEntry::with(['timetable', 'schoolClass', 'subject', 'period'])
            ->where('teacher_id', $teacherId)
            ->whereNotNull('period_id')
            ->whereHas('timetable', fn($q) => $q->where('status', 'published')->where('type', 'class'))
            ->whereIn('day_of_week', [1, 2, 3, 4, 5])
            ->get();

        // Load all session logs for the displayed week
        $entryIds = $entries->pluck('id')->toArray();

        $logsMap = [];
        if (!empty($entryIds)) {
            TimetableSessionLog::with(['topic.subtopics', 'coveredSubtopics'])
                ->whereIn('timetable_entry_id', $entryIds)
                ->whereBetween('session_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get()
                ->each(function ($log) use (&$logsMap) {
                    $logsMap[$log->timetable_entry_id . '_' . $log->session_date->toDateString()] = $log;
                });
        }

        // Group entries by day
        $dayEntries = [];
        foreach ($entries as $entry) {
            $dayEntries[$entry->day_of_week][] = $entry;
        }
        foreach ($dayEntries as &$list) {
            usort($list, fn($a, $b) => ($a->period?->order_no ?? 99) <=> ($b->period?->order_no ?? 99));
        }
        unset($list);

        // Load lesson plans for this teacher keyed by "subjectId_classId"
        $subjectClassPairs = $entries->map(fn($e) => ['s' => $e->subject_id, 'c' => $e->class_id])->unique();
        $lessonPlans = collect();
        foreach ($subjectClassPairs as $pair) {
            $plan = \App\Models\LessonPlan::with([
                    'subject', 'schoolClass',
                    'topics' => fn($q) => $q->withCount(['subtopics', 'subtopics as covered_count' => fn($q2) => $q2->where('status', 'covered')]),
                ])
                ->where('subject_id', $pair['s'])
                ->where('class_id', $pair['c'])
                ->latest()->first();
            if ($plan) {
                $lessonPlans->put("{$pair['s']}_{$pair['c']}", $plan);
            }
        }

        // For HR: load all teachers for filter dropdown
        $teachers = $isHR ? User::role('Teacher')->orderBy('name')->get(['id', 'name']) : collect();

        // Today's day_of_week for default active tab
        $todayDow = (int) now()->dayOfWeekIso;
        $activeTab = ($todayDow >= 1 && $todayDow <= 5 && $weekOffset === 0) ? $todayDow : 1;

        return view('timetables.my-sessions', compact(
            'dayEntries', 'weekDates', 'logsMap',
            'weekStart', 'weekEnd', 'weekOffset',
            'activeTab', 'teachers', 'viewingTeacher',
            'isHR', 'teacherId', 'lessonPlans'
        ));
    }

    // ── Log a session (teacher marks taught / absent) ─────────────────────
    public function logSession(Request $request, TimetableEntry $entry)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Only the assigned teacher or admin/HR can log
        $canLog = $user->hasAnyRole(['Admin', 'Academic', 'HOD', 'HR'])
            || $user->id === $entry->teacher_id;

        if (!$canLog) abort(403, 'You are not assigned to this session.');

        $data = $request->validate([
            'session_date'         => 'required|date',
            'status'               => 'required|in:attended,late,absent,other',
            'notes'                => 'nullable|string|max:500',
            'lesson_topic_id'      => 'nullable|integer|exists:lesson_topics,id',
            'covered_subtopic_ids' => 'nullable|array',
            'covered_subtopic_ids.*' => 'integer|exists:lesson_subtopics,id',
        ]);

        $log = TimetableSessionLog::updateOrCreate(
            ['timetable_entry_id' => $entry->id, 'session_date' => $data['session_date']],
            [
                'teacher_id'       => $entry->teacher_id,
                'class_id'         => $entry->class_id,
                'subject_id'       => $entry->subject_id,
                'period_id'        => $entry->period_id,
                'status'           => $data['status'],
                'notes'            => $data['notes'] ?? null,
                'recorded_by'      => $user->id,
                'lesson_topic_id'  => $data['lesson_topic_id'] ?? null,
            ]
        );

        // Sync covered subtopics and mark them in lesson_subtopics
        $subtopicIds = $data['covered_subtopic_ids'] ?? [];
        $log->coveredSubtopics()->sync($subtopicIds);

        if (!empty($subtopicIds)) {
            \App\Models\LessonSubtopic::whereIn('id', $subtopicIds)->update([
                'status'      => 'covered',
                'date_covered'=> $data['session_date'],
                'covered_by'  => $user->id,
            ]);
        }

        $weekOffset = $request->input('week', 0);
        $teacherId  = $request->input('teacher_id');

        $redirect = route('timetables.my-sessions', array_filter([
            'week'       => $weekOffset ?: null,
            'teacher_id' => $teacherId  ?: null,
        ]));

        return redirect($redirect . '#day-' . now()->dayOfWeekIso)
            ->with('success', 'Session marked as ' . ucfirst($data['status']) . '.');
    }

    // ── Helper: published timetables relevant to the logged-in user ───────
    private function getPublishedForUser(\App\Models\User $user): array
    {
        if ($user->hasAnyRole(['Admin', 'Academic', 'HOD'])) {
            return Timetable::where('status', 'published')->latest('published_at')->take(5)->get()->toArray();
        }

        if ($user->hasRole('Teacher')) {
            $ids = TimetableEntry::where('teacher_id', $user->id)
                ->pluck('timetable_id')->unique();
            return Timetable::whereIn('id', $ids)->where('status', 'published')->get()->toArray();
        }

        return [];
    }
}
