<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Staff;
use App\Models\Event;
use App\Models\SchoolClass;
use App\Models\Department;
use App\Models\Subject;
use App\Models\Dormitory;
use App\Models\User;
use App\Models\FeePayment;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Summary counts
        $data = [
            'students' => Student::count(),
            'staff' => Staff::count(),
            'classes' => SchoolClass::count(),
            'departments' => Department::count(),
            'subjects' => Subject::count(),
            'dormitories' => Dormitory::count(),
            'users' => User::count(),
            
            'events' => Event::count(),
            'ongoing_events' => Event::whereDate('start_date', '<=', Carbon::today())
                                     ->whereDate('end_date', '>=', Carbon::today())
                                     ->count(),
        ];

        // Event summary for chart
        $eventStats = Event::selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        // Upcoming events
        $upcomingEvents = Event::whereDate('start_date', '>=', Carbon::today())
            ->orderBy('start_date', 'asc')
            ->take(5)
            ->get();

        // Recent students
        $recentStudents = Student::latest()->take(5)->get();

        return view('home', compact('data', 'eventStats', 'upcomingEvents', 'recentStudents'));
    }
}
