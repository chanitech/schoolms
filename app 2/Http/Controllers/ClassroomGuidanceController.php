<?php

namespace App\Http\Controllers;

use App\Models\ClassroomGuidance;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class ClassroomGuidanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ClassroomGuidance::with(['schoolClass', 'creator']);

        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        $guidances = $query->latest()->paginate(15);
        $classes = SchoolClass::all();

        return view('counseling.classroom_guidance.index', compact('guidances', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::all();
        return view('counseling.classroom_guidance.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'date' => 'required|date',
        ]);

        ClassroomGuidance::create([
            'class_id' => $request->class_id,
            'date' => $request->date,
            'tasks' => $request->tasks,
            'achievements' => $request->achievements,
            'challenges' => $request->challenges,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('classroom-guidances.index')
                         ->with('success', 'Classroom guidance added successfully.');
    }

    public function show(ClassroomGuidance $classroomGuidance)
    {
        return view('counseling.classroom_guidance.show', compact('classroomGuidance'));
    }

    public function edit(ClassroomGuidance $classroomGuidance)
    {
        $classes = SchoolClass::all();
        return view('counseling.classroom_guidance.edit', compact('classroomGuidance', 'classes'));
    }

    public function update(Request $request, ClassroomGuidance $classroomGuidance)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'date' => 'required|date',
        ]);

        $classroomGuidance->update([
            'class_id' => $request->class_id,
            'date' => $request->date,
            'tasks' => $request->tasks,
            'achievements' => $request->achievements,
            'challenges' => $request->challenges,
        ]);

        return redirect()->route('classroom-guidances.index')
                         ->with('success', 'Classroom guidance updated successfully.');
    }

    public function destroy(ClassroomGuidance $classroomGuidance)
    {
        $classroomGuidance->delete();
        return redirect()->route('classroom-guidances.index')
                         ->with('success', 'Classroom guidance deleted successfully.');
    }
}
