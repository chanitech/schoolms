<?php
namespace App\Http\Controllers;

use App\Models\CounselingIntakeForm;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class CounselingIntakeFormController extends Controller
{
    public function __construct()
    {
        // Apply Spatie permissions
        $this->middleware('permission:view counseling intake forms')->only(['index', 'show', 'selectClass', 'studentsByClass']);
        $this->middleware('permission:create counseling intake forms')->only(['create', 'store']);
        $this->middleware('permission:edit counseling intake forms')->only(['edit', 'update']);
        $this->middleware('permission:delete counseling intake forms')->only('destroy');
    }

    // Step 0: Select class
    public function selectClass()
    {
        $classes = SchoolClass::all();
        return view('counseling.intake.select_class', compact('classes'));
    }

    // Show intake form directly without class filter
    public function create()
    {
        $students = Student::all(); // all students
        $form = null;                // prevent undefined variable

        return view('counseling.intake.create', compact('students', 'form'));
    }

    // Step 1: Show students for selected class
    public function studentsByClass($class_id)
    {
        $students = Student::where('class_id', $class_id)->get();
        $form = null; // prevent undefined variable
        return view('counseling.intake.create', compact('students', 'class_id', 'form'));
    }

    // Store intake form
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $data = $request->all();
        if (isset($data['counseling_type'])) {
            $data['counseling_type'] = json_encode($data['counseling_type']);
        }

        CounselingIntakeForm::create($data);

        return redirect()->route('counseling.intake.index')
            ->with('success', 'Intake form submitted successfully.');
    }

    // List all intake forms
    public function index()
    {
        $forms = CounselingIntakeForm::with('student')->latest()->paginate(15);
        return view('counseling.intake.index', compact('forms'));
    }

    // Show a specific intake form
    public function show(CounselingIntakeForm $form)
    {
        return view('counseling.intake.show', compact('form'));
    }

    public function edit($id)
    {
        $form = CounselingIntakeForm::findOrFail($id);
        $students = Student::all(); // for dropdown

        return view('counseling.intake.edit', compact('form', 'students'));
    }

    public function update(Request $request, $id)
    {
        $form = CounselingIntakeForm::findOrFail($id);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'gender' => 'nullable|string|max:10',
            'age' => 'nullable|numeric',
            'stream' => 'nullable|string|max:255',
            'education_program' => 'nullable|string|max:255',
            'g_performance' => 'nullable|string|max:255',
            'living_situation' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'father_address' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'father_age' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:20',
            'mother_name' => 'nullable|string|max:255',
            'mother_address' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'mother_age' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_relationship' => 'nullable|string|max:255',
            'parents_relationship' => 'nullable|string|max:255',
            'siblings_brothers' => 'nullable|string|max:255',
            'siblings_sisters' => 'nullable|string|max:255',
            'birth_order' => 'nullable|string|max:255',
            'referred_by' => 'nullable|string|max:255',
            'health_problems' => 'nullable|string|max:255',
            'previous_counseling' => 'nullable|string|max:255',
            'reason_for_counseling' => 'nullable|string',
            'chief_complaint' => 'nullable|string',
            'understanding_of_services' => 'nullable|string',
            'counseling_type' => 'nullable|array',
        ]);

        $form->update($validated);

        return redirect()->route('counseling.intake.index')
            ->with('success', 'Counseling Intake Form updated successfully.');
    }

    public function destroy(CounselingIntakeForm $form)
    {
        $form->delete();

        return redirect()->route('counseling.intake.index')
                         ->with('success', 'Counseling intake form deleted successfully.');
    }
}
