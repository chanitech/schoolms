<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AptitudeQuestion;
use Illuminate\Support\Facades\Storage;

class AptitudeQuestionController extends Controller
{
    public function index() {
        $questions = AptitudeQuestion::latest()->paginate(15);
        return view('counseling.psychometric_assessment.aptitude_questions.index', compact('questions'));
    }

    public function create() {
        $sections = ['Verbal Reasoning', 'Numerical Reasoning', 'Abstract/Analytical Reasoning'];
        $types = [
            'mcq' => 'MCQ',
            'true_false' => 'True/False',
            'numerical' => 'Numerical Input'
        ];
        return view('counseling.psychometric_assessment.aptitude_questions.create', compact('sections','types'));
    }

    public function store(Request $request) {
        $request->validate([
            'section' => 'required|string',
            'question_text' => 'required|string',
            'type' => 'required|string',
            'options' => 'nullable|array',
            'correct_answer' => 'required',
            'marks' => 'required|numeric|min:1',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['section','question_text','type','correct_answer','marks']);

        if($request->hasFile('image')){
            $data['image'] = $request->file('image')->store('aptitude_questions','public');
        }

        // Only MCQs have options
        if($request->type == 'mcq') {
            $data['options'] = json_encode($request->options);
        } else {
            $data['options'] = null;
        }

        AptitudeQuestion::create($data);

        return redirect()->route('aptitude.questions.index')->with('success','Question created successfully.');
    }

    public function edit(AptitudeQuestion $aptitudeQuestion) {
        $sections = ['Verbal Reasoning', 'Numerical Reasoning', 'Abstract/Analytical Reasoning'];
        $types = [
            'mcq' => 'MCQ',
            'true_false' => 'True/False',
            'numerical' => 'Numerical Input'
        ];
        return view('counseling.psychometric_assessment.aptitude_questions.edit', compact('aptitudeQuestion','sections','types'));
    }

    public function update(Request $request, AptitudeQuestion $aptitudeQuestion) {
        $request->validate([
            'section' => 'required|string',
            'question_text' => 'required|string',
            'type' => 'required|string',
            'options' => 'nullable|array',
            'correct_answer' => 'required',
            'marks' => 'required|numeric|min:1',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['section','question_text','type','correct_answer','marks']);

        if($request->hasFile('image')){
            if($aptitudeQuestion->image) {
                Storage::disk('public')->delete($aptitudeQuestion->image);
            }
            $data['image'] = $request->file('image')->store('aptitude_questions','public');
        }

        if($request->type == 'mcq') {
            $data['options'] = json_encode($request->options);
        } else {
            $data['options'] = null;
        }

        $aptitudeQuestion->update($data);

        return redirect()->route('aptitude.questions.index')->with('success','Question updated successfully.');
    }

    public function destroy(AptitudeQuestion $aptitudeQuestion) {
        if($aptitudeQuestion->image) {
            Storage::disk('public')->delete($aptitudeQuestion->image);
        }
        $aptitudeQuestion->delete();
        return redirect()->route('aptitude.questions.index')->with('success','Question deleted successfully.');
    }
}
