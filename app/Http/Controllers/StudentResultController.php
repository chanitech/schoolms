<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Exam;
use App\Services\StudentResultService;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    // Show all students with links to view results
    public function index()
    {
        $students = Student::paginate(10);
        return view('results.index', compact('students'));
    }

    // Show results for a specific student
    public function show(Student $student)
    {
        $exams = Exam::all(); // Optional: list exams to filter results

        // Fetch marks for this student
        $marks = $student->marks()->get()->mapWithKeys(function ($mark) {
            return [$mark->subject->name => $mark->mark]; // ['Math' => 75, 'Biology' => 60]
        })->toArray();

        // Calculate GPA and Division
        $result = StudentResultService::calculateGpaAndDivision($marks);

        return view('results.show', compact('student', 'marks', 'result', 'exams'));
    }
}
