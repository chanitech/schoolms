<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view transport')->only(['index', 'show']);
        $this->middleware('permission:manage transport')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $buses = Bus::with(['driver', 'routes'])->orderBy('plate_number')->get();
        return view('transport.buses.index', compact('buses'));
    }

    public function create()
    {
        $drivers = Staff::orderBy('first_name')->get();
        return view('transport.buses.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $data = $request->validate([
            'plate_number'    => ['required', 'string', 'max:30', Rule::unique('buses', 'plate_number')->where('school_id', $schoolId)],
            'name'            => 'nullable|string|max:150',
            'capacity'        => 'required|integer|min:1|max:200',
            'driver_staff_id' => 'nullable|exists:staff,id',
            'status'          => 'required|in:active,maintenance,inactive',
            'notes'           => 'nullable|string',
        ]);

        Bus::create($data);
        return redirect()->route('transport.buses.index')->with('success', 'Bus added.');
    }

    public function edit(Bus $bus)
    {
        $drivers = Staff::orderBy('first_name')->get();
        return view('transport.buses.edit', compact('bus', 'drivers'));
    }

    public function update(Request $request, Bus $bus)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $data = $request->validate([
            'plate_number'    => ['required', 'string', 'max:30', Rule::unique('buses', 'plate_number')->ignore($bus->id)->where('school_id', $schoolId)],
            'name'            => 'nullable|string|max:150',
            'capacity'        => 'required|integer|min:1|max:200',
            'driver_staff_id' => 'nullable|exists:staff,id',
            'status'          => 'required|in:active,maintenance,inactive',
            'notes'           => 'nullable|string',
        ]);

        $bus->update($data);
        return redirect()->route('transport.buses.index')->with('success', 'Bus updated.');
    }

    public function destroy(Bus $bus)
    {
        if ($bus->routes()->exists()) {
            return back()->with('error', 'Cannot delete a bus with routes assigned to it — reassign or delete those routes first.');
        }
        $bus->delete();
        return back()->with('success', 'Bus removed.');
    }
}
