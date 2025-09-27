<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function index()
    {
        $guardians = Guardian::with('students')->paginate(10);
        return view('guardians.index', compact('guardians'));
    }

    public function create()
    {
        return view('guardians.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'gender'     => 'required|in:male,female',
            'relation_to_student' => 'required|string|max:50',
            'phone'      => 'required|string|max:20|unique:guardians',
            'email'      => 'nullable|email|unique:guardians',
            'address'    => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'national_id'=> 'nullable|string|max:50|unique:guardians',
        ]);

        Guardian::create($request->all());

        return redirect()->route('guardians.index')->with('success', 'Guardian created successfully.');
    }

    public function show(Guardian $guardian)
    {
        $guardian->load('students'); // eager load students
        return view('guardians.show', compact('guardian'));
    }

    public function edit(Guardian $guardian)
    {
        return view('guardians.edit', compact('guardian'));
    }

    public function update(Request $request, Guardian $guardian)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'gender'     => 'required|in:male,female',
            'relation_to_student' => 'required|string|max:50',
            'phone'      => 'required|string|max:20|unique:guardians,phone,'.$guardian->id,
            'email'      => 'nullable|email|unique:guardians,email,'.$guardian->id,
            'address'    => 'nullable|string|max:255',
            'occupation' => 'nullable|string|max:100',
            'national_id'=> 'nullable|string|max:50|unique:guardians,national_id,'.$guardian->id,
        ]);

        $guardian->update($request->all());

        return redirect()->route('guardians.index')->with('success', 'Guardian updated successfully.');
    }

    public function destroy(Guardian $guardian)
    {
        $guardian->delete();
        return redirect()->route('guardians.index')->with('success', 'Guardian deleted successfully.');
    }
}
