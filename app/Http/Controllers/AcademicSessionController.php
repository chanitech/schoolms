<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicSession;


class AcademicSessionController extends Controller
{
    /**
     * Display a listing of sessions.
     */
    public function index()
    {
        $sessions = AcademicSession::orderBy('start_date', 'desc')->paginate(10);
        return view('sessions.index', compact('sessions'));
    }

    /**
     * Show the form for creating a new session.
     */
    public function create()
    {
        return view('sessions.create');
    }

    /**
     * Store a newly created session in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:academic_sessions,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        // Ensure only one session is current
        if ($request->has('is_current')) {
            AcademicSession::where('is_current', true)->update(['is_current' => false]);
            $validated['is_current'] = true;
        }

        AcademicSession::create($validated);

        return redirect()->route('sessions.index')->with('success', 'Academic session created successfully.');
    }

    /**
     * Show the form for editing the specified session.
     */
    public function edit(AcademicSession $session)
    {
        return view('sessions.edit', compact('session'));
    }

    /**
     * Update the specified session in storage.
     */
    public function update(Request $request, AcademicSession $session)
    {
        $validated = $request->validate([
            'name' => 'required|unique:academic_sessions,name,' . $session->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_current' => 'nullable|boolean',
        ]);

        if ($request->has('is_current')) {
            AcademicSession::where('is_current', true)->where('id', '!=', $session->id)->update(['is_current' => false]);
            $validated['is_current'] = true;
        } else {
            $validated['is_current'] = false;
        }

        $session->update($validated);

        return redirect()->route('sessions.index')->with('success', 'Academic session updated successfully.');
    }

    /**
     * Remove the specified session from storage.
     */
    public function destroy(AcademicSession $session)
    {
        $session->delete();
        return redirect()->route('sessions.index')->with('success', 'Academic session deleted successfully.');
    }
}
