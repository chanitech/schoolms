<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\SchoolClass;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    // Show promotion form
    public function index()
    {
        return view('promotion.index', [
            'classes' => SchoolClass::orderBy('level')->get(),
            'sessions' => AcademicSession::orderBy('start_date', 'desc')->get(),
        ]);
    }

    // Promote entire class
    public function promoteClass(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:school_classes,id',
            'from_session_id' => 'required|exists:academic_sessions,id',
            'to_session_id' => 'required|exists:academic_sessions,id',
            'to_class_id' => 'nullable|exists:school_classes,id',
            'auto_next_class' => 'nullable|boolean',
        ]);

        $fromClass = SchoolClass::find($request->from_class_id);

        // Determine target class
        if ($request->auto_next_class) {
            $nextClass = SchoolClass::where('level', $fromClass->level + 1)
                ->where('section', $fromClass->section)
                ->first();

            if (!$nextClass) {
                return back()->with('error', 'No next class found for level ' . ($fromClass->level + 1));
            }
        } else {
            if (!$request->to_class_id) {
                return back()->with('error', 'Please select a target class for promotion.');
            }
            $nextClass = SchoolClass::find($request->to_class_id);
        }

        // Fetch students enrolled in "from class" for that session
        $students = Enrollment::where('class_id', $fromClass->id)
            ->where('academic_session_id', $request->from_session_id)
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'No students found in this class.');
        }

        foreach ($students as $enroll) {

            // Avoid duplicate promotion
            $exists = Enrollment::where([
                'student_id' => $enroll->student_id,
                'class_id' => $nextClass->id,
                'academic_session_id' => $request->to_session_id,
            ])->exists();

            if ($exists) continue;

            // Auto roll number
            $roll_no = $this->generateRollNo($nextClass->id, $request->to_session_id);

            Enrollment::create([
                'student_id' => $enroll->student_id,
                'class_id' => $nextClass->id,
                'academic_session_id' => $request->to_session_id,
                'roll_no' => $roll_no,
                'status' => 'active',
            ]);
        }

        return back()->with('success', 'Class promoted to ' . $nextClass->name . ' successfully!');
    }

    // Generate unique roll number
    private function generateRollNo($class_id, $session_id)
    {
        $class = SchoolClass::find($class_id);

        $prefix = 'F' . $class->level;
        if (!empty($class->section)) {
            $prefix .= strtoupper(substr($class->section, 0, 1));
        }

        $count = Enrollment::where('class_id', $class_id)
            ->where('academic_session_id', $session_id)
            ->count();

        return $prefix . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public function studentsJson(Request $request)
{
    $request->validate([
        'class_id' => 'required|exists:school_classes,id',
        'session_id' => 'required|exists:academic_sessions,id',
    ]);

    $students = Enrollment::with('student')
        ->where('class_id', $request->class_id)
        ->where('academic_session_id', $request->session_id)
        ->get()
        ->map(fn($e) => [
            'id' => $e->student_id,
            'roll_no' => $e->roll_no,
            'first_name' => $e->student->first_name,
            'last_name' => $e->student->last_name,
        ]);

    return response()->json($students);
}
}
