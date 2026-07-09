<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolInfo;
use Illuminate\Support\Facades\Storage;

class SchoolInfoController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:manage settings');
    }

    /**
     * Show the school info form.
     */
    public function index()
    {
        $school = SchoolInfo::first();

        if (!$school) {
            $school = SchoolInfo::create([
                'name' => '',
                'motto' => '',
                'email' => '',
                'phone' => '',
                'address' => '',
                'website' => '',
                'logo' => null,
                'lock_results_for_guardians' => true,
                'lock_results_only_overdue' => false,
            ]);
        }

        return view('settings.school-info', compact('school'));
    }

public function update(Request $request)
{
    $request->validate([
        'name'                          => ['nullable', 'string', 'max:255'],
        'motto'                         => ['nullable', 'string', 'max:255'],
        'email'                         => ['nullable', 'email', 'max:255'],
        'phone'                         => ['nullable', 'string', 'max:50'],
        'address'                       => ['nullable', 'string', 'max:255'],
        'website'                       => ['nullable', 'string', 'max:255'],
        'logo'                          => ['nullable', 'image', 'max:2048'],
        'lock_results_for_guardians'    => ['nullable', 'boolean'],
        'lock_results_only_overdue'     => ['nullable', 'boolean'],
    ]);

    $school = SchoolInfo::firstOrNew();

    $data = [
        'name'                          => $request->input('name'),
        'motto'                         => $request->input('motto'),
        'email'                         => $request->input('email'),
        'phone'                         => $request->input('phone'),
        'address'                       => $request->input('address'),
        'website'                       => $request->input('website'),
        'lock_results_for_guardians'    => $request->boolean('lock_results_for_guardians'),
        'lock_results_only_overdue'     => $request->boolean('lock_results_only_overdue'),
    ];

    if ($request->hasFile('logo')) {
        if ($school->logo) {
            Storage::disk('public')->delete($school->logo);
        }
        $schoolId = $school->school_id ?? (app()->bound('currentSchool') ? app('currentSchool')->id : 'unassigned');
        $data['logo'] = $request->file('logo')->store("schools/{$schoolId}/logos", 'public');
    } else {
        $data['logo'] = $school->logo;
    }

    $school->fill($data);
    $school->save();

    return redirect()->route('settings.school.info.index')
        ->with('success', 'School information updated successfully.');
}
}