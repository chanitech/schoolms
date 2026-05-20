<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardianDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user->is_guardian) {
            abort(403);
        }
        $guardian = $user->guardian;
        $students = $guardian->students()
            ->where('results_locked', false)
            ->get();
        return view('guardian.dashboard', compact('students'));
    }
}