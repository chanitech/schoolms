<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Dormitory;
use App\Models\DormitoryRoom;
use App\Models\DormitoryBed;
use App\Models\DormitoryBedAllocation;
use App\Models\AcademicSession;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsTemplateExport;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view students')->only(['index']);
        $this->middleware('permission:create students')->only(['create', 'store']);
        $this->middleware('permission:edit students')->only(['edit', 'update']);
        $this->middleware('permission:delete students')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Student::with(['guardian', 'class', 'dormitory', 'academicSession', 'department']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhereHas('class', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $students = $query->paginate(10)->withQueryString();
        return view('students.index', compact('students'));
    }

    public function create()
    {
        $guardians   = Guardian::all();
        $classes     = SchoolClass::all();
        $dormitories = Dormitory::all();
        $sessions    = AcademicSession::all();
        $departments = Department::all();

        return view('students.create', compact('guardians', 'classes', 'dormitories', 'sessions', 'departments'));
    }

    public function store(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'admission_no'        => ['required', Rule::unique('students', 'admission_no')->where('school_id', $schoolId)],
            'first_name'          => 'required',
            'last_name'           => 'required',
            'gender'              => 'required|in:male,female',
            'date_of_birth'       => 'required|date',
            'guardian_id'         => 'nullable|exists:guardians,id',
            'class_id'            => 'nullable|exists:school_classes,id',
            'department_id'       => 'nullable|exists:departments,id',
            'dormitory_id'        => 'nullable|exists:dormitories,id',
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'email'               => 'nullable|email|unique:students',
            'photo'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string',
            'national_id'         => 'nullable|string',
            'admission_date'      => 'nullable|date',
            'status'              => 'nullable|in:active,inactive,graduated,suspended',
        ]);

        $data = $request->all();

        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        if (!isset($data['admission_date'])) {
            $data['admission_date'] = now();
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store("schools/{$schoolId}/students/photos", 'public');
        }

        $student = Student::create($data);

        if ($request->has('allocate_bed') && $request->bed_id) {
            $this->allocateBedToStudent($student, $request);
        }

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }

    public function edit(Student $student)
    {
        $guardians   = Guardian::all();
        $classes     = SchoolClass::all();
        $dormitories = Dormitory::all();
        $sessions    = AcademicSession::all();
        $departments = Department::all();

        $currentAllocation = $student->activeBedAllocation;
        $currentBed        = $currentAllocation ? $currentAllocation->bed : null;
        $currentRoom       = $currentBed ? $currentBed->room : null;

        return view('students.edit', compact(
            'student', 'guardians', 'classes', 'dormitories',
            'sessions', 'departments', 'currentAllocation', 'currentBed', 'currentRoom'
        ));
    }

    public function update(Request $request, Student $student)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $request->validate([
            'admission_no'        => ['required', Rule::unique('students', 'admission_no')->ignore($student->id)->where('school_id', $schoolId)],
            'first_name'          => 'required',
            'last_name'           => 'required',
            'gender'              => 'required|in:male,female',
            'date_of_birth'       => 'required|date',
            'guardian_id'         => 'nullable|exists:guardians,id',
            'class_id'            => 'nullable|exists:school_classes,id',
            'department_id'       => 'nullable|exists:departments,id',
            'dormitory_id'        => 'nullable|exists:dormitories,id',
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'email'               => 'nullable|email|unique:students,email,' . $student->id,
            'photo'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone'               => 'nullable|string|max:20',
            'address'             => 'nullable|string',
            'national_id'         => 'nullable|string',
            'admission_date'      => 'nullable|date',
            'status'              => 'nullable|in:active,inactive,graduated,suspended',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $data['photo'] = $request->file('photo')->store("schools/{$student->school_id}/students/photos", 'public');
        }

        $student->update($data);

        if ($request->has('reallocate_bed') && $request->bed_id) {
            // Swap — release current bed then assign new one
            if ($student->activeBedAllocation) {
                $this->deallocateBedFromStudent($student);
            }
            $this->allocateBedToStudent($student, $request);

        } elseif ($request->has('deallocate_bed') && $request->deallocate_bed == '1') {
            // Release current bed only
            if ($student->activeBedAllocation) {
                $this->deallocateBedFromStudent($student);
            }

        } elseif ($request->has('allocate_bed') && $request->bed_id) {
            // First-time allocation from edit page (student had no bed)
            $this->allocateBedToStudent($student, $request);
        }

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    public function show(Student $student)
    {
        $student->load(['guardian', 'class', 'dormitory', 'academicSession', 'department', 'activeBedAllocation.bed.room.dormitory']);

        $bedDetails = null;
        if ($student->activeBedAllocation) {
            $bed  = $student->activeBedAllocation->bed;
            $room = $bed->room;
            $bedDetails = (object)[
                'dormitory'   => $room->dormitory->name,
                'room_number' => $room->room_number,
                'bed_number'  => $bed->bed_number,
                'bed_type'    => $bed->bed_type,
                'floor'       => $room->floor,
            ];
        }

        return view('students.show', compact('student', 'bedDetails'));
    }

    public function destroy(Student $student)
    {
        if ($student->activeBedAllocation) {
            $this->deallocateBedFromStudent($student);
        }

        if ($student->photo && Storage::disk('public')->exists($student->photo)) {
            Storage::disk('public')->delete($student->photo);
        }

        $student->delete();
        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    // ==================== EXCEL IMPORT / EXPORT ====================

    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file'      => 'required|file|mimes:xlsx,xls',
            'skip_duplicates' => 'nullable|boolean',
        ]);

        try {
            $skip   = $request->boolean('skip_duplicates', true);
            $import = new StudentsImport($skip);
            Excel::import($import, $request->file('excel_file'));

            $successCount = $import->getSuccessCount();
            $errors       = $import->getErrors();

            if ($successCount == 0 && count($errors) > 0) {
                return back()->with('error', 'Import failed. ' . implode(', ', array_slice($errors, 0, 5)));
            } elseif (count($errors) > 0) {
                return back()->with('warning', "Imported $successCount students. Issues: " . implode(', ', array_slice($errors, 0, 3)));
            }

            return back()->with('success', "Successfully imported $successCount students.");
        } catch (\Exception $e) {
            Log::error('Student import error: ' . $e->getMessage());
            return back()->with('error', 'System error: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new StudentsTemplateExport(), 'students_import_template.xlsx');
    }

    // ==================== PRIVATE BED HELPERS ====================

    /**
     * Allocate a bed to a student.
     * Uses a live count to sync the room's occupied_beds column
     * instead of increment() which can drift out of sync.
     */
    private function allocateBedToStudent(Student $student, Request $request)
    {
        // Lock row to prevent double-allocation on concurrent requests
        $bed = DormitoryBed::lockForUpdate()->find($request->bed_id);

        if (!$bed || $bed->status !== 'available') {
            Log::warning('Bed allocation failed: Bed not available', ['bed_id' => $request->bed_id]);
            return;
        }

        DormitoryBedAllocation::create([
            'bed_id'              => $bed->id,
            'student_id'          => $student->id,
            'academic_session_id' => $request->academic_session_id,
            'allocation_date'     => now(),
            'start_date'          => $request->start_date ?? now(),
            'status'              => 'active',
            'notes'               => $request->allocation_notes ?? 'Allocated during student registration',
            'allocated_by'        => Auth::id(),
        ]);

        // Mark the bed as occupied
        $bed->update([
            'status'             => 'occupied',
            'current_student_id' => $student->id,
        ]);

        // Sync room occupied_beds from a live count — never drifts
        $room         = $bed->room;
        $liveOccupied = $room->beds()->where('status', 'occupied')->count();
        $room->update(['occupied_beds' => $liveOccupied]);

        // Keep student dormitory_id for backward compatibility
        $student->update(['dormitory_id' => $room->dormitory_id]);
    }

    /**
     * Deallocate a bed from a student.
     * Also syncs the room's occupied_beds column via live count.
     */
    private function deallocateBedFromStudent(Student $student)
    {
        $allocation = $student->activeBedAllocation;
        if (!$allocation) {
            return;
        }

        $bed = $allocation->bed;

        $allocation->update([
            'status'   => 'cancelled',
            'end_date' => now(),
        ]);

        if ($bed) {
            $bed->update([
                'status'             => 'available',
                'current_student_id' => null,
            ]);

            // Sync room occupied_beds from a live count
            $room         = $bed->room;
            $liveOccupied = $room->beds()->where('status', 'occupied')->count();
            $room->update(['occupied_beds' => $liveOccupied]);
        }

        // Refresh to check for any remaining active allocations
        $student->refresh();
        if (!$student->activeBedAllocation) {
            $student->update(['dormitory_id' => null]);
        }
    }

    // ==================== AJAX ENDPOINTS ====================

    /**
     * Get rooms for a dormitory (AJAX).
     * Only returns rooms that are available AND have at least one available bed.
     */
    public function getRooms(Request $request)
    {
        try {
            $request->validate([
                'dormitory_id' => 'required|exists:dormitories,id',
            ]);

            $rooms = DormitoryRoom::where('dormitory_id', $request->dormitory_id)
                ->where('is_available', true)
                ->whereHas('beds', function ($q) {
                    $q->where('status', 'available');
                })
                ->get(['id', 'room_number', 'floor', 'capacity', 'occupied_beds', 'room_type']);

            $rooms->transform(function ($room) {
                $room->available_beds = $room->capacity - $room->occupied_beds;
                return $room;
            });

            return response()->json($rooms);

        } catch (\Exception $e) {
            Log::error('getRooms error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get beds for a room (AJAX).
     * Only returns beds with status = available.
     */
    public function getBeds(Request $request)
    {
        try {
            $request->validate([
                'room_id' => 'required|exists:dormitory_rooms,id',
            ]);

            $beds = DormitoryBed::where('room_id', $request->room_id)
                ->where('status', 'available')
                ->get(['id', 'bed_number', 'bed_type', 'status']);

            return response()->json($beds);

        } catch (\Exception $e) {
            Log::error('getBeds error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}