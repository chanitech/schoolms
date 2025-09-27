<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        // Get all classes with teacher relationship (optional)
        $classes = SchoolClass::with('teacher')->paginate(10);
        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        // For now, no teachers are loaded (Staff module not implemented yet)
        $teachers = collect(); // empty collection
        return view('classes.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:50',
            'section' => 'nullable|string|max:10',
            'capacity' => 'nullable|integer|min:0',
            'class_teacher_id' => 'nullable|integer',
        ]);

        SchoolClass::create($request->all());

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
    }

    public function edit(SchoolClass $class)
    {
        $teachers = collect(); // empty collection for now
        return view('classes.edit', compact('class', 'teachers'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:50',
            'section' => 'nullable|string|max:10',
            'capacity' => 'nullable|integer|min:0',
            'class_teacher_id' => 'nullable|integer',
        ]);

        $class->update($request->all());

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    }
}
