<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;

class StudentBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view student bills')->only(['index', 'show']);
        $this->middleware('permission:create student bills')->only(['create', 'store']);
    }

    public function index()
    {
        $studentBills = StudentBill::with(['student'])->latest()->paginate(15);
        return view('finance.student_bills.index', compact('studentBills'));
    }

    public function create()
    {
        $students = Student::orderBy('first_name')->orderBy('last_name')->get();
        return view('finance.student_bills.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'  => 'required|exists:students,id',
            'description' => 'nullable|string|max:255',
            'amount'      => 'required|numeric|min:0',
        ]);

        StudentBill::create([
            'student_id'   => $validated['student_id'],
            'bill_id'      => null,                           // now allowed
            'status'       => 'unpaid',
            'total_amount' => $validated['amount'],
            'amount_paid'  => 0,
            'balance'      => $validated['amount'],
            'notes'        => $validated['description'] ?? 'Custom fee',
        ]);

        return redirect()->route('finance.student_bills.index')
                         ->with('success', 'Custom bill assigned successfully.');
    }

    public function show(StudentBill $studentBill)
    {
        $studentBill->load(['student', 'payments']);
        return view('finance.student_bills.show', compact('studentBill'));
    }

    public function updateBalance(StudentBill $studentBill, $amountPaid)
    {
        $studentBill->amount_paid += $amountPaid;
        $studentBill->balance = $studentBill->total_amount - $studentBill->amount_paid;

        if ($studentBill->balance <= 0) {
            $studentBill->status = 'paid';
            $studentBill->balance = 0;
        } elseif ($studentBill->amount_paid > 0) {
            $studentBill->status = 'partial';
        } else {
            $studentBill->status = 'unpaid';
        }

        $studentBill->save();
    }
}