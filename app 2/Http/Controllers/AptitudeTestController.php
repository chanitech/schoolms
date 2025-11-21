<?php 

namespace App\Http\Controllers;

use App\Models\AptitudeQuestion;
use App\Models\AptitudeAttempt;
use App\Models\AptitudeAnswer;
use App\Models\Student;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // ✅ Use the Pdf facade
use Illuminate\Support\Facades\Auth;

class AptitudeTestController extends Controller
{
    public function index() {
        $attempts = AptitudeAttempt::with('student', 'counselor')->latest()->paginate(15);
        return view('counseling/psychometric_assessment/aptitude_attempts/index', compact('attempts'));
    }

    public function create() {
        $students = Student::all();
        $questions = AptitudeQuestion::all()->groupBy('section');
        return view('counseling/psychometric_assessment/aptitude_attempts/create', compact('students', 'questions'));
    }

    public function store(Request $request) {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'answers' => 'required|array'
        ]);

        /** @var \App\Models\AptitudeAttempt $attempt */
        $attempt = AptitudeAttempt::create([
            'student_id' => $request->student_id,
            'counselor_id' => Auth::id(),
            'total_score' => 0
        ]);

        $totalScore = 0;
        foreach($request->answers as $qId => $answer) {
            $question = AptitudeQuestion::find($qId);
            if(!$question) continue;

            $obtained = ($answer == $question->correct_answer) ? $question->marks : 0;
            $totalScore += $obtained;

            AptitudeAnswer::create([
                'attempt_id' => $attempt->id, // ✅ Safe now
                'question_id' => $qId,
                'student_answer' => $answer,
                'obtained_marks' => $obtained
            ]);
        }

        $attempt->update(['total_score' => $totalScore]);

        return redirect()->route('aptitude.index')->with('success', 'Attempt saved successfully.');
    }

    public function show(AptitudeAttempt $aptitudeAttempt) {
        $aptitudeAttempt->load('answers.question', 'student', 'counselor');
        return view('counseling/psychometric_assessment/aptitude_attempts/show', compact('aptitudeAttempt'));
    }

    public function pdf(AptitudeAttempt $aptitudeAttempt) {
        $aptitudeAttempt->load('answers.question', 'student', 'counselor');
        $pdf = Pdf::loadView('counseling/psychometric_assessment/aptitude_attempts/pdf', compact('aptitudeAttempt'));
        return $pdf->download('Aptitude_Test_Report_'.$aptitudeAttempt->student->name.'.pdf');
    }
}
