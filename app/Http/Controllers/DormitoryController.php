<?php

namespace App\Http\Controllers;

use App\Models\Dormitory;
use App\Models\Staff; // Assuming dorm master is a staff member
use Illuminate\Http\Request;

class DormitoryController extends Controller
{
    // Display all dormitories
    public function index()
    {
        $dormitories = Dormitory::with('dormMaster')->paginate(10);
        return view('dormitories.index', compact('dormitories'));
    }

    // Show form to create a dormitory
    public function create()
    {
        //$teachers = Staff::where('role', 'Teacher')->get(); // Only teachers as dorm masters
        return view('dormitories.create');
    }

    // Store new dormitory
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'gender' => 'required|in:male,female',
            'dorm_master_id' => 'nullable|exists:staff,id',
        ]);

        Dormitory::create($request->all());

        return redirect()->route('dormitories.index')->with('success', 'Dormitory created successfully.');
    }

    // Show form to edit dormitory
    public function edit(Dormitory $dormitory)
    {
        $teachers = Staff::where('role', 'Teacher')->get();
        return view('dormitories.edit', compact('dormitory', 'teachers'));
    }

    // Update dormitory
    public function update(Request $request, Dormitory $dormitory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'gender' => 'required|in:male,female',
            'dorm_master_id' => 'nullable|exists:staff,id',
        ]);

        $dormitory->update($request->all());

        return redirect()->route('dormitories.index')->with('success', 'Dormitory updated successfully.');
    }

    // Delete dormitory
    public function destroy(Dormitory $dormitory)
    {
        $dormitory->delete();
        return redirect()->route('dormitories.index')->with('success', 'Dormitory deleted successfully.');
    }
}
