<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function __construct()
    {
        // Apply Spatie permissions if needed
        $this->middleware('permission:view departments')->only('index');
        $this->middleware('permission:create departments')->only(['create', 'store']);
        $this->middleware('permission:edit departments')->only(['edit', 'update']);
        $this->middleware('permission:delete departments')->only('destroy');
    }

    public function index()
    {
        $departments = Department::with('head')->paginate(10); // eager load head
        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        // Only staff with HOD role can be assigned as head
        $hods = Staff::role('HOD')->get();
        return view('departments.create', compact('hods'));
    }

    public function store(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name' => ['required', Rule::unique('departments', 'name')->where('school_id', $schoolId)],
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:staff,id', // must be a valid staff
            'rank_requires_7_subjects' => 'nullable|boolean', // new validation
        ]);

        Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'head_id' => $request->head_id,
            'rank_requires_7_subjects' => $request->has('rank_requires_7_subjects'), // ✅ save checkbox value
        ]);

        return redirect()->route('departments.index')
                         ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $hods = Staff::role('HOD')->get();
        return view('departments.edit', compact('department', 'hods'));
    }

    public function update(Request $request, Department $department)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name' => ['required', Rule::unique('departments', 'name')->ignore($department->id)->where('school_id', $schoolId)],
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:staff,id', // must be a valid staff
            'rank_requires_7_subjects' => 'nullable|boolean', // new validation
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'head_id' => $request->head_id,
            'rank_requires_7_subjects' => $request->has('rank_requires_7_subjects'), // ✅ update checkbox value
        ]);

        return redirect()->route('departments.index')
                         ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')
                         ->with('success', 'Department deleted successfully.');
    }
}
