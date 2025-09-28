<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Exam;

class MarkController extends Controller
{
    public function index()
    {
        $marks = Mark::with(['student','subject','exam'])->paginate(10);
        return view('marks.index', compact('marks'));
    }

    public function create()
    {
        $students = Student::all();
        $subjects = Subject::all();
        $exams = Exam::all();
        return view('marks.create', compact('students','subjects','exams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id' => 'required|exists:exams,id',
            'mark' => 'required|numeric|min:0|max:100',
        ]);

        Mark::create($request->all());

        return redirect()->route('marks.index')->with('success','Mark added successfully.');
    }

    public function edit(Mark $mark)
    {
        $students = Student::all();
        $subjects = Subject::all();
        $exams = Exam::all();
        return view('marks.edit', compact('mark','students','subjects','exams'));
    }

    public function update(Request $request, Mark $mark)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_id' => 'required|exists:exams,id',
            'mark' => 'required|numeric|min:0|max:100',
        ]);

        $mark->update($request->all());

        return redirect()->route('marks.index')->with('success','Mark updated successfully.');
    }

    public function destroy(Mark $mark)
    {
        $mark->delete();
        return redirect()->route('marks.index')->with('success','Mark deleted successfully.');
    }
}
