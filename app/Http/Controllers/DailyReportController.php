<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DailyReportActivity;
use App\Models\SchoolInfo;
use App\Models\Staff;
use App\Models\TimetableSessionLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DailyReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view daily reports')->only(['index', 'show']);
        $this->middleware('permission:create daily reports')->only(['create', 'store', 'edit']);
        // Matches the 'Staff Reports' menu item's gate (config/adminlte.php),
        // which is Principal-inclusive unlike 'view daily reports'.
        $this->middleware('permission:view department dashboard')->only('hodIndex');
    }

    // ── Teacher: list own reports ──────────────────────────────────────────
    public function index()
    {
        $reports = DailyReport::where('teacher_id', Auth::id())
            ->orderByDesc('report_date')
            ->paginate(20);

        // Single query for session counts keyed by date string (avoids N+1 in the loop)
        $dates = $reports->pluck('report_date')->map(fn($d) => $d->toDateString());
        $sessionCounts = TimetableSessionLog::where('teacher_id', Auth::id())
            ->whereIn(DB::raw('DATE(session_date)'), $dates)
            ->selectRaw('DATE(session_date) as date, COUNT(*) as cnt')
            ->groupByRaw('DATE(session_date)')
            ->pluck('cnt', 'date');

        return view('daily-reports.index', compact('reports', 'sessionCounts'));
    }

    // ── Teacher: create / edit form ────────────────────────────────────────
    public function create(Request $request)
    {
        $date = $request->filled('date')
            ? \Carbon\Carbon::parse($request->date)
            : now();

        // Check if draft already exists for this date
        $existing = DailyReport::where('teacher_id', Auth::id())
            ->whereDate('report_date', $date)->first();

        if ($existing && $existing->isSubmitted()) {
            return redirect()->route('daily-reports.show', $existing)
                ->with('info', 'Report for this date is already submitted.');
        }

        // Sessions logged for this date
        $sessions = TimetableSessionLog::with(['subject', 'schoolClass', 'period', 'topic'])
            ->where('teacher_id', Auth::id())
            ->whereDate('session_date', $date)
            ->orderBy('period_id')
            ->get();

        return view('daily-reports.create', compact('date', 'existing', 'sessions'));
    }

    // ── Teacher: save draft ────────────────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'report_date'      => 'required|date',
            'summary'          => 'nullable|string',
            'challenges'       => 'nullable|string',
            'next_day_plan'    => 'nullable|string',
            'additional_notes' => 'nullable|string',
            'submit'           => 'nullable',
            // Activities
            'activities'               => 'nullable|array',
            'activities.*.type'        => 'required_with:activities|in:meeting,duty,exam_invigilation,training,other',
            'activities.*.title'       => 'required_with:activities|string|max:150',
            'activities.*.description' => 'nullable|string',
            'activities.*.time_from'   => 'nullable|date_format:H:i',
            'activities.*.time_to'     => 'nullable|date_format:H:i',
        ]);

        $isSubmit = $request->has('submit');

        DB::transaction(function () use ($data, $isSubmit, $request) {
            $report = DailyReport::updateOrCreate(
                ['teacher_id' => Auth::id(), 'report_date' => $data['report_date']],
                [
                    'summary'          => $data['summary'] ?? null,
                    'challenges'       => $data['challenges'] ?? null,
                    'next_day_plan'    => $data['next_day_plan'] ?? null,
                    'additional_notes' => $data['additional_notes'] ?? null,
                    'status'           => $isSubmit ? 'submitted' : 'draft',
                    'submitted_at'     => $isSubmit ? now() : null,
                ]
            );

            // Replace activities
            $report->activities()->delete();
            foreach ($data['activities'] ?? [] as $act) {
                if (empty($act['title'])) continue;
                $report->activities()->create([
                    'type'        => $act['type'],
                    'title'       => $act['title'],
                    'description' => $act['description'] ?? null,
                    'time_from'   => $act['time_from'] ?? null,
                    'time_to'     => $act['time_to'] ?? null,
                ]);
            }
        });

        $report = DailyReport::where('teacher_id', Auth::id())
            ->whereDate('report_date', $data['report_date'])->first();

        $msg = $isSubmit ? 'Daily report submitted successfully.' : 'Draft saved.';
        return redirect()->route('daily-reports.show', $report)->with('success', $msg);
    }

    // ── Teacher: view single report ────────────────────────────────────────
    public function show(DailyReport $dailyReport)
    {
        abort_unless(
            Auth::id() === $dailyReport->teacher_id ||
            Auth::user()->hasAnyRole(['Admin', 'Academic', 'HOD', 'HR']),
            403
        );

        $sessions = TimetableSessionLog::with(['subject', 'schoolClass', 'period', 'topic'])
            ->where('teacher_id', $dailyReport->teacher_id)
            ->whereDate('session_date', $dailyReport->report_date)
            ->orderBy('period_id')
            ->get();

        $dailyReport->load(['teacher', 'activities']);
        $school       = SchoolInfo::first();
        $teacherStaff = Staff::where('user_id', $dailyReport->teacher_id)->with('department')->first();

        return view('daily-reports.show', compact('dailyReport', 'sessions', 'school', 'teacherStaff'));
    }

    // ── Teacher: edit draft ────────────────────────────────────────────────
    public function edit(DailyReport $dailyReport)
    {
        abort_unless(Auth::id() === $dailyReport->teacher_id, 403);
        abort_if($dailyReport->isSubmitted(), 403, 'Submitted reports cannot be edited.');

        $date = $dailyReport->report_date;
        $sessions = TimetableSessionLog::with(['subject', 'schoolClass', 'period', 'topic'])
            ->where('teacher_id', Auth::id())
            ->whereDate('session_date', $date)
            ->orderBy('period_id')
            ->get();

        $existing = $dailyReport;
        return view('daily-reports.create', compact('date', 'existing', 'sessions'));
    }

    // ── HOD / Management: list all dept reports ────────────────────────────
    public function hodIndex(Request $request)
    {
        $user  = Auth::user();
        $staff = Staff::where('user_id', $user->id)->first();

        $query = DailyReport::with('teacher')->orderByDesc('report_date');

        // HOD sees only their department
        if ($user->hasRole('HOD') && !$user->hasAnyRole(['Admin', 'Academic'])) {
            abort_unless($staff && $staff->department_id, 403);
            $deptUserIds = Staff::where('department_id', $staff->department_id)
                ->pluck('user_id')->filter();
            $query->whereIn('teacher_id', $deptUserIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('report_date', $request->date);
        }
        if ($request->filled('teacher')) {
            $query->where('teacher_id', $request->teacher);
        }

        $reports  = $query->paginate(30)->withQueryString();
        $teachers = $this->resolveDeptTeachers($user, $staff);
        $teacherIds = $teachers->pluck('id');

        // Stats (all done in controller to avoid blade DB calls)
        $todaySubmitted = DailyReport::whereIn('teacher_id', $teacherIds)
            ->whereDate('report_date', today())->where('status', 'submitted')->count();
        $pendingDrafts  = DailyReport::whereIn('teacher_id', $teacherIds)
            ->where('status', 'draft')->count();

        // Missing staff (submitted today)
        $submittedTodayIds = DailyReport::whereIn('teacher_id', $teacherIds)
            ->whereDate('report_date', today())->where('status', 'submitted')
            ->pluck('teacher_id');
        $missingTeachers = $teachers->whereNotIn('id', $submittedTodayIds)->values();

        // Session counts for each report on this page (single grouped query, no N+1)
        $pageTeacherIds = $reports->pluck('teacher_id')->unique();
        $pageDates      = $reports->pluck('report_date')->map(fn($d) => $d->toDateString())->unique();
        $sessionCounts  = TimetableSessionLog::whereIn('teacher_id', $pageTeacherIds)
            ->whereIn(DB::raw('DATE(session_date)'), $pageDates)
            ->selectRaw('teacher_id, DATE(session_date) as date, COUNT(*) as cnt')
            ->groupByRaw('teacher_id, DATE(session_date)')
            ->get()
            ->keyBy(fn($r) => $r->teacher_id . '_' . $r->date);

        return view('daily-reports.hod', compact(
            'reports', 'teachers', 'todaySubmitted', 'pendingDrafts',
            'missingTeachers', 'sessionCounts'
        ));
    }

    private function resolveDeptTeachers($user, $staff)
    {
        if ($user->hasAnyRole(['Admin', 'Academic'])) {
            return User::whereHas('roles', fn($q) => $q->whereIn('name', ['Teacher', 'HOD']))->get(['id', 'name']);
        }
        if ($staff && $staff->department_id) {
            $ids = Staff::where('department_id', $staff->department_id)->pluck('user_id')->filter();
            return User::whereIn('id', $ids)->get(['id', 'name']);
        }
        return collect();
    }
}
