<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    // Display paginated staff list
    public function index()
    {
        $staffs = Staff::with('department', 'user')->paginate(10);
        return view('staff.index', compact('staffs'));
    }

    // Show create staff form
    public function create()
    {
        $departments = Department::all();
        return view('staff.create', compact('departments'));
    }

    // Store new staff and linked user
    public function store(Request $request)
    {
        $request->validate([
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'position'      => 'nullable|string',
            'photo'         => 'nullable|image|max:2048',
            'role'          => 'required|string',
        ]);

        // Create user for login
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'name'       => $request->first_name . ' ' . $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make('password123'), // default password
        ]);

        // Upload staff photo if provided
        $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('staff', 'public') : null;

        // Create staff profile linked to user
        Staff::create([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'department_id' => $request->department_id,
            'position'      => $request->position,
            'photo'         => $photoPath,
            'role'          => $request->role,
            'user_id'       => $user->id,
        ]);

        return redirect()->route('staff.index')
                         ->with('success', 'Staff created successfully. Default password: password123');
    }

    // Show edit form
    public function edit(Staff $staff)
    {
        $departments = Department::all();
        return view('staff.edit', compact('staff', 'departments'));
    }

    // Update staff and linked user
    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'first_name'    => 'required|string',
            'last_name'     => 'required|string',
            'email'         => 'required|email|unique:users,email,' . $staff->user_id,
            'phone'         => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'position'      => 'nullable|string',
            'photo'         => 'nullable|image|max:2048',
            'role'          => 'required|string',
        ]);

        // Update linked user
        if ($staff->user) {
            $staff->user->update([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'name'       => $request->first_name . ' ' . $request->last_name,
                'email'      => $request->email,
            ]);
        }

        // Upload new photo if exists
        $photoPath = $staff->photo;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('staff', 'public');
        }

        // Update staff profile
        $staff->update([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'department_id' => $request->department_id,
            'position'      => $request->position,
            'photo'         => $photoPath,
            'role'          => $request->role,
        ]);

        return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
    }

    // Delete staff and linked user
    public function destroy(Staff $staff)
    {
        // Delete linked user first
        if ($staff->user) {
            $staff->user->delete();
        }

        // Delete staff profile
        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff deleted successfully.');
    }
}
