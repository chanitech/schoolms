<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Staff;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view classes')->only(['index', 'show']);
        $this->middleware('permission:create classes')->only(['create', 'store']);
        $this->middleware('permission:edit classes')->only(['edit', 'update']);
        $this->middleware('permission:delete classes')->only('destroy');
    }

    // Display all classes
    public function index()
    {
        $classes = SchoolClass::with('teacher')->paginate(10);
        return view('classes.index', compact('classes'));
    }

    // Show create form
    public function create()
    {
        $teachers = Staff::role('Teacher')->get(); // Only staff with Teacher role
        return view('classes.create', compact('teachers'));
    }

    // Store new class
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:50',
            'section' => 'nullable|string|max:10',
            'capacity' => 'nullable|integer|min:0',
            'class_teacher_id' => 'nullable|exists:staff,id',
        ]);

        SchoolClass::create($request->only([
            'name', 'level', 'section', 'capacity', 'class_teacher_id'
        ]));

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
    }

    // Show edit form
    public function edit(SchoolClass $class)
    {
        $teachers = Staff::role('Teacher')->get();
        return view('classes.edit', compact('class', 'teachers'));
    }

    // Update class
    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:50',
            'section' => 'nullable|string|max:10',
            'capacity' => 'nullable|integer|min:0',
            'class_teacher_id' => 'nullable|exists:staff,id',
        ]);

        $class->update($request->only([
            'name', 'level', 'section', 'capacity', 'class_teacher_id'
        ]));

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    }

    // Delete class
    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    }
}
