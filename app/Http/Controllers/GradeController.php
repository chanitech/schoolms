<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
        public function __construct()
    {
        $this->middleware('permission:view grading')->only(['index']);
        $this->middleware('permission:manage grading')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }
    public function index()
    {
        $grades = Grade::orderBy('min_mark', 'desc')->get();
        return view('grades.index', compact('grades'));
    }

    public function create()
    {
        return view('grades.create');
    }

    public function store(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name' => ['required', 'string', 'max:2', Rule::unique('grades', 'name')->where('school_id', $schoolId)],
            'min_mark' => 'required|numeric|between:0,100',
            'max_mark' => 'required|numeric|between:0,100|gte:min_mark',
            'point' => 'required|numeric|between:0,5',
            'description' => 'nullable|string|max:255',
        ]);

        Grade::create($request->all());

        return redirect()->route('grades.index')->with('success', 'Grade created successfully.');
    }

    public function edit(Grade $grade)
    {
        return view('grades.edit', compact('grade'));
    }

    public function update(Request $request, Grade $grade)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name' => ['required', 'string', 'max:2', Rule::unique('grades', 'name')->ignore($grade->id)->where('school_id', $schoolId)],
            'min_mark' => 'required|numeric|between:0,100',
            'max_mark' => 'required|numeric|between:0,100|gte:min_mark',
            'point' => 'required|numeric|between:0,5',
            'description' => 'nullable|string|max:255',
        ]);

        $grade->update($request->all());

        return redirect()->route('grades.index')->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        return redirect()->route('grades.index')->with('success', 'Grade deleted successfully.');
    }
}
