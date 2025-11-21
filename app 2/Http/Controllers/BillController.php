<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\SchoolClass;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view bills')->only('index', 'show');
        $this->middleware('permission:create bills')->only(['create', 'store']);
        $this->middleware('permission:edit bills')->only(['edit', 'update']);
        $this->middleware('permission:delete bills')->only('destroy');
    }

    /**
     * List all bills with optional filters
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::all();
        $sessions = AcademicSession::all();

        $query = Bill::query();

        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Filter by academic session (through students)
        if ($request->filled('academic_session_id')) {
            $query->whereHas('studentBills.student', function ($q) use ($request) {
                $q->where('academic_session_id', $request->academic_session_id);
            });
        }

        $bills = $query->latest()->paginate(10)->withQueryString();

        return view('finance.bills.index', compact('bills', 'classes', 'sessions'));
    }

    /**
     * Show form to create a new bill
     */
    public function create()
    {
        $classes = SchoolClass::all();
        return view('finance.bills.create', compact('classes'));
    }

    /**
     * Store new bill and assign to students
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'required|numeric|min:0',
            'due_date'    => 'nullable|date',
            'class_id'    => 'required|exists:school_classes,id',
        ]);

        // Create the bill
        $bill = Bill::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount'      => $validated['amount'],
            'due_date'    => $validated['due_date'] ?? null,
            'class_id'    => $validated['class_id'],
        ]);

        // Get all students in that class
        $students = Student::where('class_id', $validated['class_id'])->get();

        // Prepare student bills for batch insert
        $studentBills = $students->map(function ($student) use ($bill) {
            return [
                'bill_id'      => $bill->id,
                'student_id'   => $student->id,
                'total_amount' => $bill->amount,
                'amount_paid'  => 0.00,
                'balance'      => $bill->amount,
                'status'       => 'unpaid',
                'due_date'     => $bill->due_date,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        })->toArray();

        // Insert all at once
        StudentBill::insert($studentBills);

        return redirect()->route('finance.bills.index')
            ->with('success', 'Bill created and assigned to students successfully.');
    }

    /**
     * Show details of a single bill
     */
    public function show(Bill $bill)
    {
        return view('finance.bills.show', compact('bill'));
    }

    /**
     * Show form to edit a bill
     */
    public function edit(Bill $bill)
    {
        $classes = SchoolClass::all();
        return view('finance.bills.edit', compact('bill', 'classes'));
    }

    /**
     * Update a bill
     */
    public function update(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'required|numeric|min:0',
            'due_date'    => 'nullable|date',
            'class_id'    => 'required|exists:school_classes,id',
        ]);

        $bill->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'amount'      => $validated['amount'],
            'due_date'    => $validated['due_date'] ?? null,
            'class_id'    => $validated['class_id'],
        ]);

        return redirect()->route('finance.bills.index')
            ->with('success', 'Bill updated successfully.');
    }

    /**
     * Delete a bill and its assigned student bills
     */
    public function destroy(Bill $bill)
    {
        $bill->studentBills()->delete();
        $bill->delete();

        return redirect()->route('finance.bills.index')
            ->with('success', 'Bill deleted successfully.');
    }
}
