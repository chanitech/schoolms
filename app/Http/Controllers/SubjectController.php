<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * 📘 List all subjects — supports search & department filtering
     */
    public function index(Request $request)
    {
        $query = Subject::with(['classes', 'teacher', 'department']);

        // 🔍 Filter by department (optional)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // 🔍 Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $subjects = $query->paginate(10)->withQueryString();
        $departments = Department::all();

        return view('subjects.index', compact('subjects', 'departments'));
    }

    /**
     * 📗 Show create form — includes department dropdown
     */
    public function create()
    {
        $classes = SchoolClass::all();
        $departments = Department::all();
        $teachers = User::role('teacher')->get(['id', 'first_name', 'last_name', 'email']);

        return view('subjects.create', compact('classes', 'teachers', 'departments'));
    }

    /**
     * 🟢 Store new subject — department required
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name',
            'code' => 'nullable|unique:subjects,code',
            'type' => 'required|in:core,elective',
            'classes' => 'nullable|array',
            'classes.*' => 'exists:school_classes,id',
            'teacher_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        $subject = Subject::create($request->only('name', 'code', 'type', 'teacher_id', 'department_id'));

        if ($request->filled('classes')) {
            $subject->classes()->sync($request->classes);
        }

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    /**
     * ✏️ Edit subject — includes department info
     */
    public function edit(Subject $subject)
    {
        $classes = SchoolClass::all();
        $departments = Department::all();
        $teachers = User::role('teacher')->get(['id', 'first_name', 'last_name', 'email']);
        $subject->load('classes', 'teacher', 'department');

        return view('subjects.edit', compact('subject', 'classes', 'teachers', 'departments'));
    }

    /**
     * 🔄 Update subject with department link
     */
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name,' . $subject->id,
            'code' => 'nullable|unique:subjects,code,' . $subject->id,
            'type' => 'required|in:core,elective',
            'classes' => 'nullable|array',
            'classes.*' => 'exists:school_classes,id',
            'teacher_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        $subject->update($request->only('name', 'code', 'type', 'teacher_id', 'department_id'));

        if ($request->filled('classes')) {
            $subject->classes()->sync($request->classes);
        } else {
            $subject->classes()->detach();
        }

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    /**
     * ❌ Delete subject safely
     */
    public function destroy(Subject $subject)
    {
        $subject->classes()->detach();
        $subject->students()->detach();
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }

    /**
     * 🎯 Assign individual students to a subject
     */
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

        return back()->with('success', 'Students assigned individually.');
    }

    /**
     * 🟠 Withdraw students from a subject
     */
    public function unassignIndividualStudents(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'students' => 'array',
            'students.*' => 'exists:students,id',
        ]);

        $selectedIds = $validated['students'] ?? [];

        foreach ($selectedIds as $studentId) {
            $subject->students()->updateExistingPivot($studentId, ['withdrawn' => 1]);
        }

        return back()->with('success', 'Students withdrawn from subject.');
    }

    /**
     * 🧾 Assign students (by class)
     */
    public function assignStudents(Subject $subject)
    {
        $classes = $subject->classes()->get();
        $students = Student::whereIn('class_id', $classes->pluck('id'))->get();
        $pivotData = $subject->students()->pluck('student_subject.withdrawn', 'student_id')->toArray();

        return view('subjects.assign-students', compact('subject', 'classes', 'students', 'pivotData'));
    }

    /**
     * 🔁 Update assigned students
     */
    public function updateAssignedStudents(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'students' => 'array',
            'students.*' => 'exists:students,id',
        ]);

        $selectedIds = $validated['students'] ?? [];
        $classStudentIds = Student::whereIn('class_id', $subject->classes->pluck('id'))->pluck('id')->toArray();

        // Withdraw all class students first
        foreach ($classStudentIds as $id) {
            $subject->students()->syncWithoutDetaching([$id => ['withdrawn' => 1]]);
        }

        // Re-assign selected ones
        foreach ($selectedIds as $id) {
            $subject->students()->syncWithoutDetaching([$id => ['withdrawn' => 0]]);
        }

        return back()->with('success', 'Student assignments updated successfully.');
    }
}
