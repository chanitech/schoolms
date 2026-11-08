<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classes = $this->markableClasses();
        $session = AcademicSession::where('is_current', true)->first();
        $date    = $request->date('date')?->toDateString() ?? now()->toDateString();
        $class   = $classes->firstWhere('id', (int) $request->input('class_id')) ?? ($classes->count() === 1 ? $classes->first() : null);

        $students = collect();
        $saved    = collect();

        if ($class && $session) {
            $students = $this->enrolledStudents($class->id, $session->id);
            $saved    = StudentAttendance::where('class_id', $class->id)->whereDate('date', $date)->pluck('status', 'student_id');
        }

        return view('student_attendance.index', [
            'classes'  => $classes,
            'class'    => $class,
            'session'  => $session,
            'date'     => $date,
            'students' => $students,
            'saved'    => $saved,
            'statuses' => StudentAttendance::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $session = AcademicSession::where('is_current', true)->firstOrFail();

        $data = $request->validate([
            'class_id'     => ['required', Rule::in($this->markableClasses()->pluck('id'))],
            'date'         => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . $session->start_date],
            'attendance'   => ['required', 'array'],
            'attendance.*' => [Rule::in(array_keys(StudentAttendance::STATUSES))],
        ]);

        // Only students actually enrolled in this class are written, whatever was posted.
        $students = $this->enrolledStudents($data['class_id'], $session->id);

        DB::transaction(function () use ($students, $data, $session) {
            foreach ($students as $student) {
                if (isset($data['attendance'][$student->id])) {
                    StudentAttendance::updateOrCreate(
                        ['student_id' => $student->id, 'date' => $data['date']],
                        [
                            'class_id'            => $data['class_id'],
                            'academic_session_id' => $session->id,
                            'status'              => $data['attendance'][$student->id],
                            'recorded_by'         => Auth::id(),
                        ]
                    );
                }
            }
        });

        return redirect()
            ->route('student-attendance.index', ['class_id' => $data['class_id'], 'date' => $data['date']])
            ->with('success', 'Attendance saved.');
    }

    private function markableClasses()
    {
        $user = Auth::user();

        return SchoolClass::orderBy('name')
            ->unless($user->hasAnyRole(['Admin', 'Academic', 'HOD', 'HR', 'Principal']), fn ($q) => $q->where('class_teacher_id', $user->staff?->id))
            ->get();
    }

    private function enrolledStudents(int|string $classId, int|string $sessionId)
    {
        return Enrollment::with('student')
            ->where('class_id', $classId)
            ->where('academic_session_id', $sessionId)
            ->where('status', 'active')
            ->get()
            ->pluck('student')
            ->filter()
            ->sortBy('first_name')
            ->values();
    }
}
