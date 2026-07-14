<?php

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
        $staff = Auth::user()->staff;
        if (!$staff) {
            return redirect()->route('dashboard')->with('error', 'Your user account is not linked to a staff profile, so it has no loan records. Ask the Admin to open your staff record (HR → Staff) and link it to your user account.');
        }
        $loans = $staff->loans()->latest()->get();
        return view('staff.loans.index', compact('loans'));
    }

    public function create()
    {
        $staff = Auth::user()->staff;
        if (!$staff) {
            return redirect()->route('dashboard')->with('error', 'Your user account is not linked to a staff profile, so it has no loan records. Ask the Admin to open your staff record (HR → Staff) and link it to your user account.');
        }
        $categories = LoanCategory::where('is_active', true)->get();
        return view('staff.loans.apply', compact('categories', 'staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'loan_category_id' => 'required|exists:loan_categories,id',
            'amount_applied'   => 'required|numeric|min:0',
            'installments'     => 'required|integer|min:1',
        ]);

        $staff = Auth::user()->staff;
        if (!$staff) {
            return back()->withInput()->with('error', 'No staff profile found for your account.');
        }

        $category = LoanCategory::findOrFail($request->loan_category_id);

        // Check eligibility (if method exists; otherwise you can use inline logic)
        if (method_exists($category, 'isStaffEligible') && !$category->isStaffEligible($staff)) {
            return back()->withInput()->with('error', 'You are not eligible for this loan category.');
        }

        // Check amount limits
        if (method_exists($category, 'isAmountValid') && !$category->isAmountValid($request->amount_applied)) {
            return back()->withInput()->with('error', 'Amount must be between '
                . number_format($category->min_amount) . ' and '
                . number_format($category->max_amount));
        }

        // Fallback if the methods don't exist (direct check)
        if (!method_exists($category, 'isAmountValid')) {
            if ($request->amount_applied < $category->min_amount || $request->amount_applied > $category->max_amount) {
                return back()->withInput()->with('error', 'Amount must be between '
                    . number_format($category->min_amount) . ' and '
                    . number_format($category->max_amount));
            }
        }

        // Create loan application
        $loan = Loan::create([
            'staff_id'              => $staff->id,
            'loan_category_id'       => $category->id,
            'amount_applied'         => $request->amount_applied,
            'interest_rate_applied'  => $category->interest_rate,
            'installments'           => $request->installments,
            'salary_at_application'  => $staff->basic_salary,
            'application_date'       => now(),
            'status'                 => 'pending',
            'approval_level'         => 0,
        ]);

        return redirect()->route('staff.loans.index')
                         ->with('success', 'Loan application submitted successfully.');
    }

    public function show(Loan $loan)
    {
        $staff = Auth::user()->staff;
        if (!$staff || $loan->staff_id !== $staff->id) {
            abort(403);
        }
        return view('staff.loans.show', compact('loan'));
    }

    public function statement(Loan $loan)
    {
        $staff = Auth::user()->staff;
        if (!$staff || $loan->staff_id !== $staff->id) {
            abort(403);
        }
        return view('staff.loans.statement', compact('loan'));
    }

    public function downloadStatement(Loan $loan)
    {
        $staff = Auth::user()->staff;
        if (!$staff || $loan->staff_id !== $staff->id) {
            abort(403);
        }
        $pdf = Pdf::loadView('staff.loans.statement_pdf', compact('loan'));
        return $pdf->download("loan_statement_{$loan->id}.pdf");
    }
}