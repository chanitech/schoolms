<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view subjects')->only(['index', 'show']);
        $this->middleware('permission:create subjects')->only(['create', 'store']);
        $this->middleware('permission:edit subjects')->only(['edit', 'update']);
        $this->middleware('permission:delete subjects')->only('destroy');
        $this->middleware('permission:view subject assignments')->only('assignStudents');
        $this->middleware('permission:manage subjects')->only([
            'assignIndividualStudents', 'unassignIndividualStudents', 'updateAssignedStudents',
        ]);
    }

    /**
     * 📘 List all subjects
     */
    public function index(Request $request)
{
    // Eager load department and classes
    $query = Subject::with(['department', 'classes']);

    if ($request->filled('department_id')) {
        $query->where('department_id', $request->department_id);
    }

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    $subjects = $query->paginate(10)->withQueryString();

    // Collect all teacher IDs from the pivot to avoid N+1 queries.
    // subject_class.teacher_id is a foreign key to staff.id, not users.id.
    $teacherIds = $subjects->flatMap(function ($subject) {
        return $subject->classes->pluck('pivot.teacher_id');
    })->filter()->unique();

    $teachers = Staff::whereIn('id', $teacherIds)->get()->keyBy('id');

    $departments = Department::all();

    return view('subjects.index', compact('subjects', 'departments', 'teachers'));
}


    /**
     * 📗 Show create form
     */
    public function create()
    {
        $classes = SchoolClass::all();
        $departments = Department::all();

        // Teachers — subject_class.teacher_id is a foreign key to staff.id,
        // so the option values here must be Staff IDs, not User IDs.
        $teachers = Staff::whereHas('user', fn ($q) => $q->role('teacher'))
            ->get(['id', 'first_name', 'last_name']);

        return view('subjects.create', compact('classes', 'teachers', 'departments'));
    }

    /**
     * 🟢 Store new subject
     */
    public function store(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name'          => ['required', Rule::unique('subjects', 'name')->where('school_id', $schoolId)],
            'code'          => ['nullable', Rule::unique('subjects', 'code')->where('school_id', $schoolId)],
            'type'          => 'required|in:core,elective',
            'department_id' => 'required|exists:departments,id',

            'classes'       => 'required|array',
            'classes.*'     => 'exists:school_classes,id',

            // teacher per class — must be a Staff ID (subject_class.teacher_id's FK target)
            'teacher'       => 'required|array',
            'teacher.*'     => 'nullable|exists:staff,id',
        ]);

        $subject = Subject::create($request->only('name', 'code', 'type', 'department_id'));

        $pivotData = [];
        foreach ($request->classes as $classId) {
            $pivotData[$classId] = [
                'teacher_id' => $request->teacher[$classId] ?? null,
            ];
        }

        $subject->classes()->sync($pivotData);

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    /**
     * 👁️ Show subject details
     */
    public function show(Subject $subject)
    {
        $subject->load('department', 'classes');
        $activeStudentsCount = $subject->activeStudents()->count();
        $withdrawnStudentsCount = $subject->withdrawnStudents()->count();

        $teacherIds = $subject->classes->pluck('pivot.teacher_id')->filter()->unique();
        $teachers = Staff::whereIn('id', $teacherIds)->get()->keyBy('id');

        return view('subjects.show', compact('subject', 'activeStudentsCount', 'withdrawnStudentsCount', 'teachers'));
    }

    /**
     * ✏️ Edit subject
     */
    public function edit(Subject $subject)
    {
        $classes = SchoolClass::all();
        $departments = Department::all();
        // subject_class.teacher_id is a foreign key to staff.id, not users.id.
        $teachers = Staff::whereHas('user', fn ($q) => $q->role('teacher'))
            ->get(['id', 'first_name', 'last_name']);

        $subject->load('classes', 'department');

        return view('subjects.edit', compact('subject', 'classes', 'teachers', 'departments'));
    }

    /**
     * 🔄 Update subject
     */
    public function update(Request $request, Subject $subject)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name'          => ['required', Rule::unique('subjects', 'name')->ignore($subject->id)->where('school_id', $schoolId)],
            'code'          => ['nullable', Rule::unique('subjects', 'code')->ignore($subject->id)->where('school_id', $schoolId)],
            'type'          => 'required|in:core,elective',
            'department_id' => 'required|exists:departments,id',

            'classes'       => 'required|array',
            'classes.*'     => 'exists:school_classes,id',

            'teacher'       => 'required|array',
            'teacher.*'     => 'nullable|exists:staff,id',
        ]);

        $subject->update($request->only('name', 'code', 'type', 'department_id'));

        $pivotData = [];
        foreach ($request->classes as $classId) {
            $pivotData[$classId] = [
                'teacher_id' => $request->teacher[$classId] ?? null,
            ];
        }

        $subject->classes()->sync($pivotData);

        return redirect()->route('subjects.index')->with('success', 'Subject updated successfully.');
    }

    /**
     * ❌ Delete subject
     */
    public function destroy(Subject $subject)
    {
        $subject->classes()->detach();
        $subject->students()->detach();
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted.');
    }

    /**
     * 🎯 Assign students individually
     */
    public function assignIndividualStudents(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'students'   => 'array',
            'students.*' => 'exists:students,id',
        ]);

        foreach ($validated['students'] ?? [] as $id) {
            $subject->students()->syncWithoutDetaching([$id => ['withdrawn' => 0]]);
        }

        return back()->with('success', 'Students assigned.');
    }

    /**
     * 🟠 Withdraw student
     */
    public function unassignIndividualStudents(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'students'   => 'array',
            'students.*' => 'exists:students,id',
        ]);

        foreach ($validated['students'] ?? [] as $id) {
            $subject->students()->updateExistingPivot($id, ['withdrawn' => 1]);
        }

        return back()->with('success', 'Students withdrawn.');
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
     * 🔁 Update class-level student assignments
     */
    public function updateAssignedStudents(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'students'   => 'array',
            'students.*' => 'exists:students,id',
        ]);

        $selectedIds = $validated['students'] ?? [];
        $classStudentIds = Student::whereIn('class_id', $subject->classes->pluck('id'))->pluck('id')->toArray();

        foreach ($classStudentIds as $id) {
            $subject->students()->syncWithoutDetaching([$id => ['withdrawn' => 1]]);
        }

        foreach ($selectedIds as $id) {
            $subject->students()->syncWithoutDetaching([$id => ['withdrawn' => 0]]);
        }

        return back()->with('success', 'Student assignments updated.');
    }
}




