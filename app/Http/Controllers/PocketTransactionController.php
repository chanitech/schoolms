<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PocketTransaction;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Support\Facades\Auth;

class PocketTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view pocket money')->only(['index', 'show']);
        $this->middleware('permission:manage pocket money')->only(['create', 'store']);
    }

    /**
     * Display a listing of pocket money transactions with optional filter by class and student
     */
    public function index(Request $request)
    {
        $classes = SchoolClass::all();
        $students = Student::all();

        $query = PocketTransaction::with('student.schoolClass', 'performedBy');

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('finance.pocket.index', compact('transactions', 'classes', 'students'));
    }

    /**
     * Show the form for creating a new pocket money transaction
     */
    public function create()
    {
        $classes = SchoolClass::all();
        return view('finance.pocket.create', compact('classes'));
    }

    /**
     * Store a newly created pocket money transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        // Get last balance
        $lastTransaction = PocketTransaction::where('student_id', $student->id)
            ->latest()
            ->first();

        $currentBalance = $lastTransaction?->balance_after ?? 0;

        // Calculate new balance
        $newBalance = $validated['type'] === 'deposit'
            ? $currentBalance + $validated['amount']
            : $currentBalance - $validated['amount'];

        if ($newBalance < 0) {
            return back()->withErrors(['amount' => 'Withdrawal exceeds current balance.'])
                         ->withInput();
        }

        // Record transaction
        $transaction = PocketTransaction::create([
            'student_id' => $student->id,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'balance_after' => $newBalance,
            'performed_by' => Auth::id(),
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()
            ->route('finance.pocket.show', $transaction->id)
            ->with('success', 'Transaction recorded successfully.');
    }

    /**
     * Display the specified transaction (receipt)
     */
    public function show(PocketTransaction $transaction)
    {
        $transaction->load('student.schoolClass', 'performedBy');

        return view('finance.pocket.show', compact('transaction'));
    }

    /**
     * AJAX: Get students for selected class
     */
    public function getStudentsByClass(Request $request)
    {
        $students = Student::where('class_id', $request->class_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($students);
    }

    /**
     * AJAX: Get last balance for a student
     */
    public function getLastBalance(Request $request)
    {
        $studentId = $request->student_id;

        $lastTransaction = PocketTransaction::where('student_id', $studentId)
            ->latest()
            ->first();

        return response()->json([
            'balance' => $lastTransaction?->balance_after ?? 0
        ]);
    }
}
