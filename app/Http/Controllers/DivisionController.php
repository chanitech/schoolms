<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view divisions')->only(['index']);
        $this->middleware('permission:create divisions')->only(['create', 'store']);
        $this->middleware('permission:edit divisions')->only(['edit', 'update']);
        $this->middleware('permission:delete divisions')->only(['destroy']);
    }
    public function index()
    {
        $divisions = Division::paginate(10);
        return view('divisions.index', compact('divisions'));
    }

    public function create()
    {
        return view('divisions.create');
    }

    public function store(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name' => ['required', Rule::unique('divisions', 'name')->where('school_id', $schoolId)],
            'min_points' => 'required|integer',
            'max_points' => 'required|integer|gte:min_points',
            'description' => 'nullable|string',
        ]);

        Division::create($request->all());

        return redirect()->route('divisions.index')->with('success','Division created successfully.');
    }

    public function edit(Division $division)
    {
        return view('divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'name' => ['required', Rule::unique('divisions', 'name')->ignore($division->id)->where('school_id', $schoolId)],
            'min_points' => 'required|integer',
            'max_points' => 'required|integer|gte:min_points',
            'description' => 'nullable|string',
        ]);

        $division->update($request->all());

        return redirect()->route('divisions.index')->with('success','Division updated successfully.');
    }

    public function destroy(Division $division)
    {
        $division->delete();
        return redirect()->route('divisions.index')->with('success','Division deleted successfully.');
    }
}
