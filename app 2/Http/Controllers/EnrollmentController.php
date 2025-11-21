<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    // List enrollments
    public function index(Request $request)
    {
        $query = Enrollment::with(['student', 'class', 'academicSession']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            })
            ->orWhereHas('class', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->paginate(10)->withQueryString();

        return view('enrollments.index', compact('enrollments'));
    }

    // Show create form
    public function create()
    {
        $students = Student::all();
        $classes = SchoolClass::all();
        $sessions = AcademicSession::all();

        return view('enrollments.create', compact('students', 'classes', 'sessions'));
    }

    // Store new enrollment
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'roll_no' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string|max:255',
        ]);

        Enrollment::create($request->all());

        return redirect()->route('enrollments.index')
                         ->with('success', 'Enrollment created successfully.');
    }

    // Show edit form
    public function edit(Enrollment $enrollment)
    {
        $students = Student::all();
        $classes = SchoolClass::all();
        $sessions = AcademicSession::all();

        return view('enrollments.create', compact('enrollment', 'students', 'classes', 'sessions'));
    }

    // Update enrollment
    public function update(Request $request, Enrollment $enrollment)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:school_classes,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'roll_no' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'remarks' => 'nullable|string|max:255',
        ]);

        $enrollment->update($request->all());

        return redirect()->route('enrollments.index')
                         ->with('success', 'Enrollment updated successfully.');
    }

    // Delete enrollment
    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()->route('enrollments.index')
                         ->with('success', 'Enrollment deleted successfully.');
    }
}
