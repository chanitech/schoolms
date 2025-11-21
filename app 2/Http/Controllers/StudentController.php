<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Dormitory;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['guardian','class','dormitory','academicSession']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhereHas('class', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $students = $query->paginate(10)->withQueryString();

        return view('students.index', compact('students'));
    }

    public function create()
    {
        $guardians = Guardian::all();
        $classes = SchoolClass::all();
        $dormitories = Dormitory::all();
        $sessions = AcademicSession::all();
        return view('students.create', compact('guardians','classes','dormitories','sessions'));
    }

    public function store(Request $request)
{
    $request->validate([
        'admission_no' => 'required|unique:students',
        'first_name' => 'required',
        'last_name' => 'required',
        'gender' => 'required|in:male,female',
        'date_of_birth' => 'required|date',
        'guardian_id' => 'nullable|exists:guardians,id',
        'class_id' => 'nullable|exists:school_classes,id',
        'dormitory_id' => 'nullable|exists:dormitories,id',
        'academic_session_id' => 'nullable|exists:academic_sessions,id',
        'email' => 'nullable|email|unique:students',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $data = $request->all();

    if ($request->hasFile('photo')) {
        $data['photo'] = $request->file('photo')->store('photos', 'public');
    }

    Student::create($data);

    return redirect()->route('students.index')->with('success','Student created successfully.');
}

    public function edit(Student $student)
    {
        $guardians = Guardian::all();
        $classes = SchoolClass::all();
        $dormitories = Dormitory::all();
        $sessions = AcademicSession::all();
        return view('students.edit', compact('student','guardians','classes','dormitories','sessions'));
    }

    public function update(Request $request, Student $student)
{
    $request->validate([
        'admission_no' => 'required|unique:students,admission_no,'.$student->id,
        'first_name' => 'required',
        'last_name' => 'required',
        'gender' => 'required|in:male,female',
        'date_of_birth' => 'required|date',
        'guardian_id' => 'nullable|exists:guardians,id',
        'class_id' => 'nullable|exists:school_classes,id',
        'dormitory_id' => 'nullable|exists:dormitories,id',
        'academic_session_id' => 'nullable|exists:academic_sessions,id',
        'email' => 'nullable|email|unique:students,email,'.$student->id,
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $data = $request->all();

    if ($request->hasFile('photo')) {
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo); // remove old photo
        }
        $data['photo'] = $request->file('photo')->store('photos', 'public');
    }

    $student->update($data);

    return redirect()->route('students.index')->with('success','Student updated successfully.');
}

    public function destroy(Student $student)
    {
        // Delete photo from storage
        if ($student->photo && Storage::exists('public/photos/'.$student->photo)) {
            Storage::delete('public/photos/'.$student->photo);
        }

        $student->delete();
        return redirect()->route('students.index')->with('success','Student deleted successfully.');
    }
}
