<?php
// app/Http/Controllers/Staff/LoanApplicationController.php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanCategory;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class LoanApplicationController extends Controller
{
    public function index()
    {
        // Get logged-in staff (assuming staff is linked to user)
        $staff = Auth::user()->staff;
        $loans = $staff->loans()->latest()->get();
        return view('staff.loans.index', compact('loans'));
    }

    public function create()
    {
        $staff = Auth::user()->staff;
        $categories = LoanCategory::where('is_active', true)->get();
        return view('staff.loans.apply', compact('categories', 'staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'loan_category_id' => 'required|exists:loan_categories,id',
            'amount_applied' => 'required|numeric|min:0',
            'installments' => 'required|integer|min:1',
        ]);

        $staff = Auth::user()->staff;
        $category = LoanCategory::findOrFail($request->loan_category_id);

        // Check eligibility
        if (!$category->isStaffEligible($staff)) {
            return back()->with('error', 'You are not eligible for this loan category.');
        }

        // Check amount limits
        if (!$category->isAmountValid($request->amount_applied)) {
            return back()->with('error', 'Amount must be between ' . $category->min_amount . ' and ' . $category->max_amount);
        }

        // Create loan application
        $loan = Loan::create([
            'staff_id' => $staff->id,
            'loan_category_id' => $category->id,
            'amount_applied' => $request->amount_applied,
            'interest_rate_applied' => $category->interest_rate,
            'installments' => $request->installments,
            'salary_at_application' => $staff->basic_salary,
            'application_date' => now(),
            'status' => 'pending',
            'approval_level' => 0,
        ]);

        return redirect()->route('staff.loans.index')->with('success', 'Loan application submitted successfully.');
    }

    public function show(Loan $loan)
    {
        // Authorize that the loan belongs to the logged-in staff
        if ($loan->staff_id !== Auth::user()->staff->id) {
            abort(403);
        }
        return view('staff.loans.show', compact('loan'));
    }

    public function statement(Loan $loan)
    {
        // Generate repayment schedule statement
        if ($loan->staff_id !== Auth::user()->staff->id) {
            abort(403);
        }
        return view('staff.loans.statement', compact('loan'));
    }


    

public function downloadStatement(Loan $loan)
{
    if ($loan->staff_id !== Auth::user()->staff->id) {
        abort(403);
    }
    $pdf = Pdf::loadView('staff.loans.statement_pdf', compact('loan'));
    return $pdf->download("loan_statement_{$loan->id}.pdf");
}
}