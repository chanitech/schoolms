<?php

namespace App\Http\Controllers;

use App\Models\Dormitory;
use App\Models\DormitoryRoom;
use App\Models\DormitoryBed;
use App\Models\DormitoryBedAllocation;
use App\Models\Staff;
use App\Models\Student;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DormitoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view dormitories')->only(['index', 'show', 'rooms', 'beds', 'dashboard', 'reports']);
        $this->middleware('permission:create dormitories')->only(['create', 'store', 'createRoom', 'storeRoom', 'createBed', 'storeBed']);
        $this->middleware('permission:edit dormitories')->only(['edit', 'update', 'editRoom', 'updateRoom', 'editBed', 'updateBed']);
        $this->middleware('permission:delete dormitories')->only(['destroy', 'deleteRoom', 'deleteBed']);
        $this->middleware('permission:allocate dormitories')->only(['allocateBed', 'storeAllocation', 'deallocateBed']);
    }

    // ==================== DORMITORY CRUD ====================

    public function index()
    {
        $dormitories = Dormitory::with(['dormMaster', 'rooms'])->paginate(10);
        return view('dormitories.index', compact('dormitories'));
    }

    public function create()
    {
        $dormMasters = Staff::role('Dorm Master')->get();
        return view('dormitories.create', compact('dormMasters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'capacity'      => 'required|integer|min:1',
            'gender'        => 'nullable|in:male,female',
            'dorm_master_id'=> 'nullable|exists:staff,id',
        ]);

        Dormitory::create($request->all());

        return redirect()->route('dormitories.index')->with('success', 'Dormitory created successfully.');
    }

    public function edit(Dormitory $dormitory)
    {
        $dormMasters = Staff::role('Dorm Master')->get();
        return view('dormitories.edit', compact('dormitory', 'dormMasters'));
    }

    public function update(Request $request, Dormitory $dormitory)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'capacity'      => 'required|integer|min:1',
            'gender'        => 'nullable|in:male,female',
            'dorm_master_id'=> 'nullable|exists:staff,id',
        ]);

        $dormitory->update($request->all());

        return redirect()->route('dormitories.index')->with('success', 'Dormitory updated successfully.');
    }

    public function destroy(Dormitory $dormitory)
    {
        $dormitory->delete();
        return redirect()->route('dormitories.index')->with('success', 'Dormitory deleted successfully.');
    }

    /**
     * Show a single dormitory with live-synced room counters.
     */
    public function show(Dormitory $dormitory)
    {
        $dormitory->load(['rooms.beds', 'dormMaster']);

        // Re-sync every room's occupied_beds from actual bed status.
        // This fixes any drift caused by missed increments/decrements.
        foreach ($dormitory->rooms as $room) {
            $liveOccupied = $room->beds->where('status', 'occupied')->count();
            if ($room->occupied_beds !== $liveOccupied) {
                $room->update(['occupied_beds' => $liveOccupied]);
                $room->occupied_beds = $liveOccupied; // update in-memory value for the view
            }
        }

        $occupiedBeds   = $dormitory->beds()->where('status', 'occupied')->count();
        $availableBeds  = $dormitory->beds()->where('status', 'available')->count();
        $availableRooms = $dormitory->rooms->where('is_available', true)->count();

        return view('dormitories.show', compact('dormitory', 'availableRooms', 'occupiedBeds', 'availableBeds'));
    }

    // ==================== ROOM MANAGEMENT ====================

    public function rooms($dormitoryId)
    {
        $dormitory = Dormitory::findOrFail($dormitoryId);
        $rooms     = $dormitory->rooms()->withCount('beds')->paginate(15);

        // Sync occupied_beds for each room on this listing page too
        foreach ($rooms as $room) {
            $liveOccupied = $room->beds()->where('status', 'occupied')->count();
            if ($room->occupied_beds !== $liveOccupied) {
                $room->update(['occupied_beds' => $liveOccupied]);
                $room->occupied_beds = $liveOccupied;
            }
        }

        return view('dormitories.rooms.index', compact('dormitory', 'rooms'));
    }

    public function createRoom($dormitoryId)
    {
        $dormitory = Dormitory::findOrFail($dormitoryId);
        return view('dormitories.rooms.create', compact('dormitory'));
    }

    public function storeRoom(Request $request)
    {
        $request->validate([
            'dormitory_id'        => 'required|exists:dormitories,id',
            'room_number'         => 'required|string',
            'floor'               => 'nullable|string',
            'capacity'            => 'required|integer|min:1',
            'room_type'           => 'required|in:single,double,triple,quad,dormitory',
            'has_attached_bathroom' => 'boolean',
            'has_balcony'         => 'boolean',
            'facilities'          => 'nullable|string',
        ]);

        DormitoryRoom::create($request->all());

        return redirect()->route('dormitories.rooms', $request->dormitory_id)
            ->with('success', 'Room created successfully.');
    }

    public function editRoom(DormitoryRoom $room)
    {
        return view('dormitories.rooms.edit', compact('room'));
    }

    public function updateRoom(Request $request, DormitoryRoom $room)
    {
        $request->validate([
            'room_number'           => 'required|string',
            'floor'                 => 'nullable|string',
            'capacity'              => 'required|integer|min:1',
            'room_type'             => 'required|in:single,double,triple,quad,dormitory',
            'has_attached_bathroom' => 'boolean',
            'has_balcony'           => 'boolean',
            'facilities'            => 'nullable|string',
        ]);

        $room->update($request->all());

        return redirect()->route('dormitories.rooms', $room->dormitory_id)
            ->with('success', 'Room updated successfully.');
    }

    public function deleteRoom(DormitoryRoom $room)
    {
        $dormitoryId = $room->dormitory_id;

        if ($room->beds()->where('status', 'occupied')->exists()) {
            return back()->with('error', 'Cannot delete room with occupied beds.');
        }

        $room->delete();

        return redirect()->route('dormitories.rooms', $dormitoryId)
            ->with('success', 'Room deleted successfully.');
    }

    // ==================== BED MANAGEMENT ====================

    public function beds($roomId)
    {
        $room = DormitoryRoom::with('dormitory')->findOrFail($roomId);
        $beds = $room->beds()->with('currentStudent')->paginate(20);

        return view('dormitories.beds.index', compact('room', 'beds'));
    }

    public function createBed($roomId)
    {
        $room = DormitoryRoom::findOrFail($roomId);
        return view('dormitories.beds.create', compact('room'));
    }

    public function storeBed(Request $request)
    {
        $request->validate([
            'room_id'   => 'required|exists:dormitory_rooms,id',
            'bed_number'=> 'required|string',
            'bed_type'  => 'required|in:single,bunk_upper,bunk_lower',
            'features'  => 'nullable|string',
        ]);

        DormitoryBed::create($request->all());

        return redirect()->route('dormitories.beds', $request->room_id)
            ->with('success', 'Bed created successfully.');
    }

    public function editBed(DormitoryBed $bed)
    {
        return view('dormitories.beds.edit', compact('bed'));
    }

    public function updateBed(Request $request, DormitoryBed $bed)
    {
        $request->validate([
            'bed_number'=> 'required|string',
            'bed_type'  => 'required|in:single,bunk_upper,bunk_lower',
            'status'    => 'required|in:available,occupied,maintenance,reserved',
            'features'  => 'nullable|string',
        ]);

        $bed->update($request->all());

        // Keep room counter in sync when a bed status is manually changed
        $room         = $bed->room;
        $liveOccupied = $room->beds()->where('status', 'occupied')->count();
        $room->update(['occupied_beds' => $liveOccupied]);

        return redirect()->route('dormitories.beds', $bed->room_id)
            ->with('success', 'Bed updated successfully.');
    }

    public function deleteBed(DormitoryBed $bed)
    {
        if ($bed->status === 'occupied') {
            return back()->with('error', 'Cannot delete a bed that is currently occupied.');
        }

        $roomId = $bed->room_id;
        $bed->delete();

        return redirect()->route('dormitories.beds', $roomId)
            ->with('success', 'Bed deleted successfully.');
    }

    // ==================== ALLOCATION MANAGEMENT ====================

    public function allocations()
    {
        $allocations = DormitoryBedAllocation::with(['student', 'bed.room.dormitory', 'academicSession'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dormitories.allocations.index', compact('allocations'));
    }

    public function allocateBed()
    {
        $students = Student::whereDoesntHave('activeBedAllocation')->get();
        $beds     = DormitoryBed::with(['room.dormitory'])->where('status', 'available')->get();
        $sessions = AcademicSession::all();

        return view('dormitories.allocations.create', compact('students', 'beds', 'sessions'));
    }

    public function storeAllocation(Request $request)
    {
        $request->validate([
            'student_id'          => 'required|exists:students,id',
            'bed_id'              => 'required|exists:dormitory_beds,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'start_date'          => 'required|date',
            'end_date'            => 'nullable|date|after:start_date',
            'notes'               => 'nullable|string',
        ]);

        $bed = DormitoryBed::lockForUpdate()->findOrFail($request->bed_id);

        if ($bed->status !== 'available') {
            return back()->with('error', 'This bed is not available.');
        }

        $existingAllocation = DormitoryBedAllocation::where('student_id', $request->student_id)
            ->where('status', 'active')
            ->first();

        if ($existingAllocation) {
            return back()->with('error', 'Student already has an active bed allocation.');
        }

        DormitoryBedAllocation::create([
            'bed_id'              => $request->bed_id,
            'student_id'          => $request->student_id,
            'academic_session_id' => $request->academic_session_id,
            'allocation_date'     => now(),
            'start_date'          => $request->start_date,
            'end_date'            => $request->end_date,
            'status'              => 'active',
            'notes'               => $request->notes,
            'allocated_by'        => Auth::id(),
        ]);

        $bed->update([
            'status'             => 'occupied',
            'current_student_id' => $request->student_id,
        ]);

        // Sync room counter from live count
        $room         = $bed->room;
        $liveOccupied = $room->beds()->where('status', 'occupied')->count();
        $room->update(['occupied_beds' => $liveOccupied]);

        $student = Student::find($request->student_id);
        $student->update(['dormitory_id' => $room->dormitory_id]);

        return redirect()->route('dormitories.allocations')
            ->with('success', 'Bed allocated successfully.');
    }

    public function deallocateBed(DormitoryBedAllocation $allocation)
    {
        $allocation->update([
            'status'   => 'cancelled',
            'end_date' => now(),
        ]);

        $bed = $allocation->bed;
        $bed->update([
            'status'             => 'available',
            'current_student_id' => null,
        ]);

        // Sync room counter from live count
        $room         = $bed->room;
        $liveOccupied = $room->beds()->where('status', 'occupied')->count();
        $room->update(['occupied_beds' => $liveOccupied]);

        $student = $allocation->student;
        if ($student && !$student->activeBedAllocation()->exists()) {
            $student->update(['dormitory_id' => null]);
        }

        return redirect()->route('dormitories.allocations')
            ->with('success', 'Bed deallocated successfully.');
    }

    // ==================== DASHBOARD ====================

    public function dashboard()
    {
        $totalDormitories = Dormitory::count();
        $totalRooms       = DormitoryRoom::count();
        $totalBeds        = DormitoryBed::count();
        $occupiedBeds     = DormitoryBed::where('status', 'occupied')->count();
        $availableBeds    = DormitoryBed::where('status', 'available')->count();
        $activeAllocations = DormitoryBedAllocation::where('status', 'active')->count();

        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 2) : 0;

        $recentAllocations = DormitoryBedAllocation::with(['student', 'bed.room.dormitory'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $dormitories = Dormitory::withCount([
                'rooms',
                'beds',
                'beds as occupied_beds_count' => fn($q) => $q->where('status', 'occupied'),
            ])
            ->with(['rooms' => function ($q) {
                $q->withCount('beds');
            }])
            ->get();

        return view('dormitories.dashboard', compact(
            'totalDormitories', 'totalRooms', 'totalBeds', 'occupiedBeds',
            'availableBeds', 'activeAllocations', 'occupancyRate',
            'recentAllocations', 'dormitories'
        ));
    }

    // ==================== REPORTS ====================

    public function reports()
    {
        $dormitories = Dormitory::all();
        return view('dormitories.reports', compact('dormitories'));
    }

    
    /**
 * Show form to bulk create beds
 */
public function bulkCreateBedsForm($roomId)
{
    $room = DormitoryRoom::findOrFail($roomId);
    return view('dormitories.beds.bulk_create', compact('room'));
}

/**
 * Store multiple beds at once
 */
public function bulkStoreBeds(Request $request, $roomId)
{
    $request->validate([
        'start_number' => 'required|integer|min:1',
        'quantity'     => 'required|integer|min:1|max:200',
        'bed_type'     => 'required|in:single,bunk_upper,bunk_lower',
    ]);

    $room = DormitoryRoom::findOrFail($roomId);
    $start = $request->start_number;
    $quantity = $request->quantity;

    $created = 0;
    for ($i = 0; $i < $quantity; $i++) {
        $bedNumber = $start + $i;
        if (!DormitoryBed::where('room_id', $roomId)->where('bed_number', $bedNumber)->exists()) {
            DormitoryBed::create([
                'room_id'    => $roomId,
                'bed_number' => (string) $bedNumber,
                'bed_type'   => $request->bed_type,
                'status'     => 'available',
            ]);
            $created++;
        }
    }

    return redirect()->route('dormitories.beds', $roomId)
        ->with('success', "Successfully created {$created} new beds.");
}






    // ==================== AJAX ENDPOINTS ====================

    /**
     * Get rooms for a dormitory (AJAX) — available rooms with at least one available bed.
     */
    public function getRooms(Request $request)
    {
        $request->validate([
            'dormitory_id' => 'required|exists:dormitories,id',
        ]);

        $rooms = DormitoryRoom::where('dormitory_id', $request->dormitory_id)
            ->where('is_available', true)
            ->whereHas('beds', function ($q) {
                $q->where('status', 'available');
            })
            ->get()
            ->map(function ($room) {
                return [
                    'id'            => $room->id,
                    'room_number'   => $room->room_number,
                    'floor'         => $room->floor ?? 'Ground',
                    'room_type'     => $room->room_type,
                    'capacity'      => $room->capacity,
                    'occupied_beds' => $room->occupied_beds,
                    'available_beds'=> $room->capacity - $room->occupied_beds,
                ];
            });

        return response()->json($rooms);
    }

    /**
     * Get beds for a room (AJAX) — available beds only.
     */
    public function getBeds(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:dormitory_rooms,id',
        ]);

        $beds = DormitoryBed::where('room_id', $request->room_id)
            ->where('status', 'available')
            ->get()
            ->map(function ($bed) {
                return [
                    'id'         => $bed->id,
                    'bed_number' => $bed->bed_number,
                    'bed_type'   => $bed->bed_type,
                    'status'     => $bed->status,
                ];
            });

        return response()->json($beds);
    }
}