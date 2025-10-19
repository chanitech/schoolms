<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Staff;
use Illuminate\Http\Request;

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
        $request->validate([
            'name' => 'required|unique:departments,name',
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:staff,id', // must be a valid staff
        ]);

        Department::create($request->only(['name', 'description', 'head_id']));

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
        $request->validate([
            'name' => 'required|unique:departments,name,' . $department->id,
            'description' => 'nullable|string',
            'head_id' => 'nullable|exists:staff,id', // must be a valid staff
        ]);

        $department->update($request->only(['name', 'description', 'head_id']));

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
