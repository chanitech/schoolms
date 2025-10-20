<?php

namespace App\Http\Controllers;

use App\Models\Dormitory;
use App\Models\Staff; 
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class DormitoryController extends Controller
{
    public function __construct()
    {
        // Apply permission middleware
        $this->middleware('permission:view dormitories')->only(['index', 'show']);
        $this->middleware('permission:create dormitories')->only(['create', 'store']);
        $this->middleware('permission:edit dormitories')->only(['edit', 'update']);
        $this->middleware('permission:delete dormitories')->only('destroy');
    }

    // Display all dormitories
    public function index()
    {
        $dormitories = Dormitory::with('dormMaster')->paginate(10);
        return view('dormitories.index', compact('dormitories'));
    }

    // Show form to create a dormitory
    public function create()
    {
        // Only staff with the "Dorm Master" role
        $dormMasters = Staff::role('Dorm Master')->get();

        return view('dormitories.create', compact('dormMasters'));
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
        $dormMasters = Staff::role('Dorm Master')->get();
        return view('dormitories.edit', compact('dormitory', 'dormMasters'));
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
