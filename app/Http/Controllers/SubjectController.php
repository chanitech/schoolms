<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;




class SubjectController extends Controller
{
    // 📘 List all subjects
    public function index(Request $request)
    {
        $query = Subject::with(['classes', 'teacher']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $subjects = $query->paginate(10)->withQueryString();

        return view('subjects.index', compact('subjects'));
    }

    // 📗 Show create form
    public function create()
    {
        $classes = SchoolClass::all();
        $teachers = User::role('teacher')->get(['id', 'first_name', 'last_name', 'email']);

        return view('subjects.create', compact('classes', 'teachers'));
    }

    // 🟢 Store new subject
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name',
            'code' => 'nullable|unique:subjects,code',
            'type' => 'required|in:core,elective',
            'classes' => 'nullable|array',
            'classes.*' => 'exists:school_classes,id',
            'teacher_id' => 'required|exists:users,id',
        ]);

        $subject = Subject::create($request->only('name', 'code', 'type', 'teacher_id'));

        if ($request->filled('classes')) {
            $subject->classes()->sync($request->classes);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    // ✏️ Edit subject
    public function edit(Subject $subject)
    {
        $classes = SchoolClass::all();
        $teachers = User::role('teacher')->get(['id', 'first_name', 'last_name', 'email']);
        $subject->load('classes', 'teacher');

        return view('subjects.edit', compact('subject', 'classes', 'teachers'));
    }

    // 🔄 Update subject
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name,' . $subject->id,
            'code' => 'nullable|unique:subjects,code,' . $subject->id,
            'type' => 'required|in:core,elective',
            'classes' => 'nullable|array',
            'classes.*' => 'exists:school_classes,id',
            'teacher_id' => 'required|exists:users,id',
        ]);

        $subject->update($request->only('name', 'code', 'type', 'teacher_id'));

        if ($request->filled('classes')) {
            $subject->classes()->sync($request->classes);
        } else {
            $subject->classes()->detach();
        }

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    // ❌ Delete subject
    public function destroy(Subject $subject)
    {
        $subject->classes()->detach();
        $subject->students()->detach(); // detach assigned students too
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }



public function assignIndividualStudents(Request $request, Subject $subject)
{
    $validated = $request->validate([
        'students' => 'array',
        'students.*' => 'exists:students,id',
    ]);

    $selectedIds = $validated['students'] ?? [];

    foreach ($selectedIds as $studentId) {
        $subject->students()->syncWithoutDetaching([
            $studentId => ['withdrawn' => 0]
        ]);
    }

    return back()->with('success', 'Students assigned to subject individually.');
}




public function unassignIndividualStudents(Request $request, Subject $subject)
{
    $validated = $request->validate([
        'students' => 'array',
        'students.*' => 'exists:students,id',
    ]);

    $selectedIds = $validated['students'] ?? [];

    foreach ($selectedIds as $studentId) {
        $subject->students()->updateExistingPivot([$studentId], ['withdrawn' => 1]);
    }

    return back()->with('success', 'Students withdrawn from subject individually.');
}





public function assignStudents(Subject $subject)
{
    // Load classes for this subject
    $classes = $subject->classes()->get();

    // All students in those classes
    $students = Student::whereIn('class_id', $classes->pluck('id'))->get();

    // Pivot data: student_id => withdrawn status
    $pivotData = $subject->students()
        ->pluck('student_subject.withdrawn', 'student_id')
        ->toArray();

    return view('subjects.assign-students', compact('subject', 'classes', 'students', 'pivotData'));
}

public function updateAssignedStudents(Request $request, Subject $subject)
{
    $validated = $request->validate([
        'students' => 'array',
        'students.*' => 'exists:students,id',
    ]);

    $selectedIds = $validated['students'] ?? [];

    // All students in the subject's classes
    $classStudentIds = Student::whereIn('class_id', $subject->classes->pluck('id'))->pluck('id')->toArray();

    // Get all currently assigned students (both class and individually assigned)
    $allAssignedIds = $subject->students()->pluck('student_id')->toArray();

    // Withdraw all class students first
    foreach ($classStudentIds as $id) {
        $subject->students()->syncWithoutDetaching([$id => ['withdrawn' => 1]]);
    }

    // Re-assign students who are selected in the form (active)
    foreach ($selectedIds as $id) {
        $subject->students()->syncWithoutDetaching([$id => ['withdrawn' => 0]]);
    }

    return back()->with('success', 'Student assignments updated successfully.');
}





}
