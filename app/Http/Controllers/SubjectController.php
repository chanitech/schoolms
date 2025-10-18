<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // 📘 List all subjects
    public function index(Request $request)
    {
        // Eager load teacher and classes to avoid N+1 queries
        $query = Subject::with(['classes', 'teacher']);

        // Optional search
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

        // ✅ Only users with 'teacher' role (Spatie way)
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

        // Eager load existing relations
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
        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
