<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;

class DivisionController extends Controller
{
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
        $request->validate([
            'name' => 'required|unique:divisions,name',
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
        $request->validate([
            'name' => 'required|unique:divisions,name,'.$division->id,
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
