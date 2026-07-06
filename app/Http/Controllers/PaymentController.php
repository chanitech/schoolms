<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\StudentBill;
use App\Models\Student;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:record payments')->only(['create', 'store', 'createIndividual', 'storeIndividual']);
        $this->middleware('permission:view payments')->only(['index', 'show', 'receipt']);
        $this->middleware('permission:verify payments')->only(['pendingReview', 'verify']);
        $this->middleware('permission:flag payments')->only(['flag']);
    }

    /**
     * Step 1: Show filter page (Session → Class → Bill)
     */
    public function create()
    {
        $sessions = AcademicSession::all();
        $classes = SchoolClass::all();
        $bills = Bill::all();

        return view('finance.payments.create', compact('sessions', 'classes', 'bills'));
    }

    /**
     * Step 2: AJAX: Get students based on class & session
     */
    public function getStudents(Request $request)
    {
        $students = Student::query()
            ->when($request->class_id, fn($q) => $q->where('class_id', $request->class_id))
            ->when($request->session_id, fn($q) => $q->where('academic_session_id', $request->session_id))
            ->where('status', 'active')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'admission_no']);

        return response()->json($students);
    }

    /**
     * Step 3: AJAX: Get all bills for a selected student
     */
    public function getStudentBills(Request $request)
    {
        $studentBills = StudentBill::with('bill')
            ->where('student_id', $request->student_id)
            ->get()
            ->map(function ($sb) {
                return [
                    'id' => $sb->id,
                    'bill_name' => $sb->bill->title ?? 'Unnamed Bill',
                    'total_amount' => $sb->total_amount,
                    'amount_paid' => $sb->amount_paid,
                    'balance' => $sb->balance,
                    'status' => ucfirst($sb->status),
                ];
            });

        return response()->json($studentBills);
    }

    /**
     * Step 4: Show individual payment form for a selected student bill
     */
    public function createIndividual(StudentBill $studentBill)
    {
        $studentBill->load('student', 'bill');
        return view('finance.payments.create_individual', compact('studentBill'));
    }

    /**
     * Step 5: Store individual payment
     */
    public function storeIndividual(Request $request)
    {
        $validated = $request->validate([
            'student_bill_id' => 'required|exists:student_bills,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        $studentBill = StudentBill::with('student', 'bill')->findOrFail($validated['student_bill_id']);

        if ($validated['amount'] > $studentBill->balance) {
            return back()->withErrors([
                'amount' => 'Payment exceeds remaining balance of ' . number_format($studentBill->balance, 2)
            ])->withInput();
        }

        // Record payment
        $payment = Payment::create([
            'student_id' => $studentBill->student_id,
            'student_bill_id' => $studentBill->id,
            'class_id' => $studentBill->bill->class_id ?? null,
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'payment_date' => now(),
            'recorded_by' => Auth::id(),
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
        ]);

        // Update StudentBill balance and status
        $studentBill->amount_paid += $validated['amount'];
        $studentBill->updateBalanceAndStatus();

        return redirect()
            ->route('finance.payments.receipt', $payment->id)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Step 6: List all payments
     */
    public function index()
    {
        $payments = Payment::with(['studentBill.student', 'studentBill.bill'])
            ->latest()
            ->paginate(15);

        return view('finance.payments.index', compact('payments'));
    }

    /**
     * Step 7: Show payment receipt
     */
    public function receipt($id)
    {
        $payment = Payment::with(['studentBill.student', 'studentBill.bill'])->findOrFail($id);
        return view('finance.payments.receipt', compact('payment'));
    }

    /**
     * Optional: Show single payment details
     */
    public function show(Payment $payment)
    {
        $payment->load('studentBill.student', 'studentBill.bill');
        return view('finance.payments.show', compact('payment'));
    }

    /**
     * Class accountants see only payments for their assigned classes;
     * treasurers (via 'view finance dashboard') see everything pending/flagged.
     */
    public function pendingReview()
    {
        $user = Auth::user();

        $query = Payment::with(['studentBill.student', 'studentBill.bill', 'schoolClass'])
            ->whereIn('status', ['pending', 'flagged']);

        if (!$user->can('view finance dashboard')) {
            $classIds = \App\Models\AccountantClassAssignment::where('user_id', $user->id)->pluck('class_id');
            $query->whereIn('class_id', $classIds);
        }

        $payments = $query->latest()->paginate(20);

        return view('finance.payments.review', compact('payments'));
    }

    public function verify(Payment $payment)
    {
        $user = Auth::user();

        if (!$user->can('view finance dashboard')) {
            $assigned = \App\Models\AccountantClassAssignment::where('user_id', $user->id)
                ->where('class_id', $payment->class_id)
                ->exists();

            if (!$assigned) {
                abort(403, 'You are not the assigned accountant for this class.');
            }
        }

        $payment->update([
            'status' => 'verified',
            'verified_by' => $user->id,
        ]);

        return back()->with('success', 'Payment verified.');
    }

    public function flag(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'status' => 'flagged',
            'verified_by' => Auth::id(),
            'note' => $validated['note'] ?? $payment->note,
        ]);

        return back()->with('success', 'Payment flagged for Treasurer review.');
    }
}
