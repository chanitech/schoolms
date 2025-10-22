<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view staff')->only('index');
        $this->middleware('permission:create staff')->only(['create', 'store']);
        $this->middleware('permission:edit staff')->only(['edit', 'update']);
        $this->middleware('permission:delete staff')->only('destroy');
    }

    // List staff with roles & departments
    public function index()
    {
        $staffs = Staff::with('department', 'user', 'roles')->paginate(10);
        return view('staff.index', compact('staffs'));
    }

    // Show create staff form
    public function create()
    {
        $departments = Department::all();
        $roles = Role::all();
        return view('staff.create', compact('departments', 'roles'));
    }

    // Store new staff
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
            'roles'         => 'required|array',
            'roles.*'       => 'exists:roles,name',
        ]);

        // Create user
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'name'       => $request->first_name . ' ' . $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make('password123'), // default password
        ]);

        // Assign multiple roles
        $user->syncRoles($request->roles);

        // Handle photo
        $photoPath = $request->hasFile('photo') ? $request->file('photo')->store('staff', 'public') : null;

        // Create staff record
        Staff::create([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'department_id' => $request->department_id,
            'position'      => $request->position,
            'photo'         => $photoPath,
            'user_id'       => $user->id,
        ]);

        return redirect()->route('staff.index')
                         ->with('success', 'Staff created successfully. Default password: password123');
    }

    // Show edit form
    public function edit(Staff $staff)
    {
        $departments = Department::all();
        $roles = Role::all();
        return view('staff.edit', compact('staff', 'departments', 'roles'));
    }

    // Update staff
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
            'roles'         => 'required|array',
            'roles.*'       => 'exists:roles,name',
        ]);

        // Update user info
        if ($staff->user) {
            $staff->user->update([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'name'       => $request->first_name . ' ' . $request->last_name,
                'email'      => $request->email,
            ]);

            // Sync multiple roles
            $staff->user->syncRoles($request->roles);
        }

        // Handle photo
        $photoPath = $staff->photo;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('staff', 'public');
        }

        // Update staff record
        $staff->update([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'department_id' => $request->department_id,
            'position'      => $request->position,
            'photo'         => $photoPath,
        ]);

        return redirect()->route('staff.index')->with('success', 'Staff updated successfully.');
    }

    // Delete staff
    public function destroy(Staff $staff)
    {
        if ($staff->user) {
            $staff->user->delete();
        }

        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff deleted successfully.');
    }
}
