<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolInfo;
use Illuminate\Support\Facades\Storage;

class SchoolInfoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage settings');
    }

    public function index()
{
    // Get the first (or only) school info record
    $school = \App\Models\SchoolInfo::first();

    // If it doesn't exist, create a default empty one
    if (!$school) {
        $school = \App\Models\SchoolInfo::create([
            'name' => '',
            'motto' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'logo' => null,
        ]);
    }

    return view('settings.school-info', compact('school'));
}


    public function update(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'motto' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $school = SchoolInfo::firstOrNew();

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            if ($school->logo) {
                Storage::disk('public')->delete($school->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $school->fill($data)->save();

        return back()->with('success', 'School information updated successfully.');
    }
}
