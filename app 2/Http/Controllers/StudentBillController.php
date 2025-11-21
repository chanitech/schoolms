<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\Bill;
use Illuminate\Http\Request;

class StudentBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage student bills');
    }

    // List all student bills
    public function index()
    {
        $studentBills = StudentBill::with(['student', 'bill'])->latest()->paginate(15);
        return view('finance.student_bills.index', compact('studentBills'));
    }

    // Show form to assign bill to student
    public function create()
    {
        $students = Student::orderBy('name')->get();
        $bills = Bill::orderBy('title')->get();
        return view('finance.student_bills.create', compact('students', 'bills'));
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'bill_id' => 'required|exists:bills,id',
    ]);

    $bill = Bill::findOrFail($validated['bill_id']);

    // Calculate balance automatically
    $amount_paid = 0;
    $balance = $bill->amount - $amount_paid;

    StudentBill::create([
        'student_id'   => $validated['student_id'],
        'bill_id'      => $bill->id,
        'status'       => 'unpaid',
        'total_amount' => $bill->amount,
        'amount_paid'  => $amount_paid,
        'balance'      => $balance,
    ]);

    return redirect()->route('student_bills.index')
                     ->with('success', 'Student bill assigned successfully.');
}



    // Show details for a specific student bill
    public function show(StudentBill $studentBill)
    {
        $studentBill->load(['student', 'bill', 'payments']);
        return view('finance.student_bills.show', compact('studentBill'));
    }

    // Optional: update bill status or amounts (useful after recording payments)
    public function updateBalance(StudentBill $studentBill, $amountPaid)
    {
        $studentBill->amount_paid += $amountPaid;
        $studentBill->balance = $studentBill->total_amount - $studentBill->amount_paid;

        // Update status based on balance
        if ($studentBill->balance <= 0) {
            $studentBill->status = 'paid';
            $studentBill->balance = 0; // ensure no negative balance
        } elseif ($studentBill->amount_paid > 0) {
            $studentBill->status = 'partial';
        } else {
            $studentBill->status = 'unpaid';
        }

        $studentBill->save();
    }
}
