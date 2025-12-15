<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * List enrollments
     */
    public function index(Request $request)
    {
        $query = Enrollment::with(['student', 'class', 'academicSession']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($s) use ($search) {
                    $s->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('admission_no', 'like', "%{$search}%");
                })
                ->orWhereHas('class', function ($c) use ($search) {
                    $c->where('name', 'like', "%{$search}%");
                });
            });
        }

        $enrollments = $query->paginate(15)->withQueryString();

        return view('enrollments.index', compact('enrollments'));
    }

    /**
     * Show create form
     */
    public function create()
{
    $students = Student::orderBy('first_name')->get();
    $classes = SchoolClass::orderBy('level')->get();
    
    // Order sessions by start_date descending (latest first)
    $sessions = AcademicSession::orderBy('start_date', 'desc')->get();

    return view('enrollments.create', compact('students', 'classes', 'sessions'));
}


    /**
     * Store enrollment with auto-roll and duplicate prevention
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Prevent duplicate enrollment
        $exists = Enrollment::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->where('academic_session_id', $request->academic_session_id)
            ->first();

        if ($exists) {
            return back()->with('error', 'This student is already enrolled in this class & session.');
        }

        // Auto-generate roll number
        $roll_no = $this->generateRollNumber($request->class_id, $request->academic_session_id);

        Enrollment::create([
            'student_id' => $request->student_id,
            'class_id'   => $request->class_id,
            'academic_session_id' => $request->academic_session_id,
            'roll_no'    => $roll_no,
            'status'     => 'active',
            'remarks'    => $request->remarks,
        ]);

        return redirect()->route('enrollments.index')
                         ->with('success', 'Enrollment created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(Enrollment $enrollment)
    {
        return view('enrollments.create', [
            'enrollment' => $enrollment,
            'students'   => Student::orderBy('first_name')->get(),
            'classes'    => SchoolClass::orderBy('level')->get(),
            'sessions'   => AcademicSession::orderBy('start_date', 'desc')->get(),
            
        ]);
    }

    /**
     * Update enrollment
     */
    public function update(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        // Prevent duplicates
        $exists = Enrollment::where('student_id', $request->student_id)
            ->where('class_id', $request->class_id)
            ->where('academic_session_id', $request->academic_session_id)
            ->where('id', '!=', $enrollment->id)
            ->first();

        if ($exists) {
            return back()->with('error', 'This student already has another enrollment for that class & session.');
        }

        $enrollment->update([
            'student_id' => $request->student_id,
            'class_id'   => $request->class_id,
            'academic_session_id' => $request->academic_session_id,
            'remarks'    => $request->remarks,
        ]);

        return redirect()->route('enrollments.index')
                         ->with('success', 'Enrollment updated successfully.');
    }

    /**
     * Delete enrollment
     */
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()->route('enrollments.index')
                         ->with('success', 'Enrollment deleted successfully.');
    }

    /**
     * ⭐ PROMOTION — Auto detect next class based on level + section
     */
    public function promote(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'from_class_id' => 'required',
            'to_session_id' => 'required',
        ]);

        $student   = Student::findOrFail($request->student_id);
        $fromClass = SchoolClass::findOrFail($request->from_class_id);

        // Auto-detect next class (Very professional logic)
        $nextClass = SchoolClass::where('level', $fromClass->level + 1)
            ->where('section', $fromClass->section)  // same stream
            ->first();

        if (!$nextClass) {
            return back()->with('error', 'Next class (level ' . ($fromClass->level + 1) . ') not found.');
        }

        // Prevent duplicate promotion
        $exists = Enrollment::where('student_id', $student->id)
            ->where('class_id', $nextClass->id)
            ->where('academic_session_id', $request->to_session_id)
            ->first();

        if ($exists) {
            return back()->with('error', 'Student already promoted to this class for the selected session.');
        }

        // Auto roll number
        $roll_no = $this->generateRollNumber($nextClass->id, $request->to_session_id);

        // Create promotion enrollment
        Enrollment::create([
            'student_id' => $student->id,
            'class_id' => $nextClass->id,
            'academic_session_id' => $request->to_session_id,
            'roll_no' => $roll_no,
            'status' => 'active',
            'remarks' => 'Promoted from ' . $fromClass->name,
        ]);

        return back()->with('success', 'Student promoted successfully to ' . $nextClass->name);
    }

    /**
     * ⭐ Auto Roll Number Generator
     */
    private function generateRollNumber($class_id, $session_id)
    {
        $class = SchoolClass::find($class_id);
        $session = AcademicSession::find($session_id);

        $count = Enrollment::where('class_id', $class_id)
            ->where('academic_session_id', $session_id)
            ->count() + 1;

        $prefix = 'F' . $class->level;
        $year = $session->year;

        return $prefix . '-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
