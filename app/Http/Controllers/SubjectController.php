<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\User; // Assuming teachers are users
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // List all subjects
    public function index(Request $request)
    {
        $query = Subject::with('classes', 'teacher'); // eager load teacher

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }

        $subjects = $query->paginate(10)->withQueryString();

        return view('subjects.index', compact('subjects'));
    }

    // Show create form
    public function create()
    {
        $classes = SchoolClass::all();
        $teachers = User::where('role', 'teacher')->get(); // get all teachers
        return view('subjects.create', compact('classes', 'teachers'));
    }

    // Store a new subject
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

       $subject = Subject::create($request->only('name','code','type','teacher_id'));

if ($request->has('classes')) {
    $subject->classes()->sync($request->classes);
}


        return redirect()->route('subjects.index')->with('success','Subject created successfully.');
    }

    // Show edit form
    public function edit(Subject $subject)
    {
        $classes = SchoolClass::all();
        $teachers = User::where('role', 'teacher')->get();
        $subject->load('classes');
        return view('subjects.edit', compact('subject','classes','teachers'));
    }

    // Update subject
    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name,'.$subject->id,
            'code' => 'nullable|unique:subjects,code,'.$subject->id,
            'type' => 'required|in:core,elective',
            'classes' => 'nullable|array',
            'classes.*' => 'exists:school_classes,id',
            'teacher_id' => 'required|exists:users,id',
        ]);

        $subject->update($request->only('name','code','type','teacher_id'));

        if ($request->has('classes')) {
            $subject->classes()->sync($request->classes);
        } else {
            $subject->classes()->detach(); // Remove all classes if none selected
        }

        return redirect()->route('subjects.index')->with('success','Subject updated successfully.');
    }

    // Delete subject
    public function destroy(Subject $subject)
    {
        $subject->classes()->detach(); // Remove pivot entries
        $subject->delete();

        return redirect()->route('subjects.index')->with('success','Subject deleted successfully.');
    }
}
