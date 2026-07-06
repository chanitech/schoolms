<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanCategory;
use App\Models\LoanRepayment;
use App\Notifications\LoanStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanApprovalController extends Controller
{
    /**
     * Display loans pending approval for the current user's role.
     *
     * @return \Illuminate\View\View
     */
    public function pending()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $loans = collect();

        if ($user->hasRole('Admin')) {
            $loans = Loan::where('status', 'pending')
                         ->orderBy('application_date', 'asc')
                         ->get();
        } elseif ($user->hasRole('chief-accountant')) {
            $loans = Loan::where('status', 'pending')
                         ->where('approval_level', 0)
                         ->orderBy('application_date', 'asc')
                         ->get();
        } elseif ($user->hasRole('accountant')) {
            $loans = Loan::where('status', 'pending')
                         ->where('approval_level', 1)
                         ->orderBy('application_date', 'asc')
                         ->get();
        } elseif ($user->hasRole('treasurer')) {
            $loans = Loan::where('status', 'pending')
                         ->where('approval_level', 2)
                         ->orderBy('application_date', 'asc')
                         ->get();
        }

        return view('treasurer.loans.pending', compact('loans'));
    }

    /**
     * Approve a loan at the current user's level.
     *
     * @param \App\Models\Loan $loan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Loan $loan)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($loan->status !== 'pending') {
            return back()->with('error', 'This loan is no longer pending.');
        }

        if (!$loan->approveBy($user)) {
            return back()->with('error', 'You are not authorized to approve this loan at its current stage.');
        }

        $message = 'Loan approved successfully.';

        if ($loan->isFullyApproved()) {
            $message = 'Loan fully approved. You may now disburse the funds.';
            
            // Send notification to staff
            if ($loan->staff && $loan->staff->user) {
                $loan->staff->user->notify(new LoanStatusNotification(
                    $loan,
                    'Your loan has been fully approved and is ready for disbursement.'
                ));
            }
        }

        return redirect()->route('treasurer.loans.pending')->with('success', $message);
    }

    /**
     * Reject a loan with a reason.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Loan $loan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, Loan $loan)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($loan->status !== 'pending') {
            return back()->with('error', 'This loan cannot be rejected.');
        }

        if (!$loan->rejectBy($user, $request->reason)) {
            return back()->with('error', 'You are not authorized to reject this loan.');
        }

        return redirect()->route('treasurer.loans.pending')->with('success', 'Loan rejected successfully.');
    }

    /**
     * Show the disbursement form for a fully approved loan.
     *
     * @param \App\Models\Loan $loan
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function disburseForm(Loan $loan)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('treasurer')) {
            abort(403, 'Only the treasurer can disburse loans.');
        }

        if (!$loan->isFullyApproved() || $loan->status !== 'approved') {
            return redirect()->route('treasurer.loans.pending')
                ->with('error', 'This loan cannot be disbursed. It must be fully approved first.');
        }

        return view('treasurer.loans.disburse', compact('loan'));
    }

    /**
     * Process loan disbursement and generate repayment schedule.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Loan $loan
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disburse(Request $request, Loan $loan)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('treasurer')) {
            abort(403, 'Only the treasurer can disburse loans.');
        }

        $request->validate([
            'disbursement_date' => 'required|date',
        ]);

        if (!$loan->isFullyApproved() || $loan->status !== 'approved') {
            return back()->with('error', 'Loan is not ready for disbursement.');
        }

        try {
            $loan->disburse(now()->parse($request->disbursement_date));
            
            // Send notification to staff after successful disbursement
            if ($loan->staff && $loan->staff->user) {
                $loan->staff->user->notify(new LoanStatusNotification(
                    $loan,
                    'Your loan has been disbursed. Repayment schedule is now active.'
                ));
            }
            
            return redirect()->route('treasurer.loans.pending')
                ->with('success', 'Loan disbursed successfully. Repayment schedule generated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Disbursement failed: ' . $e->getMessage());
        }
    }

    /**
     * List all loan applications (with optional filters).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Loan::with(['staff', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('loan_category_id', $request->category_id);
        }

        $loans = $query->latest()->paginate(20);
        $categories = LoanCategory::all();

        return view('treasurer.loans.index', compact('loans', 'categories'));
    }

    /**
     * Display all active loans (disbursed, not closed) for recording repayments.
     *
     * @return \Illuminate\View\View
     */
    public function activeLoans()
    {
        $loans = Loan::with('staff', 'category')
                    ->where('status', 'active')
                    ->latest()
                    ->paginate(20);

        return view('treasurer.loans.active', compact('loans'));
    }

    /**
     * Show the treasurer version of the loan statement with repayment actions.
     *
     * @param \App\Models\Loan $loan
     * @return \Illuminate\View\View
     */
    public function treasurerStatement(Loan $loan)
    {
        // Ensure the loan is active (has repayments)
        if ($loan->status !== 'active') {
            return redirect()->route('treasurer.loans.index')
                ->with('error', 'Only active loans have repayment schedules.');
        }

        return view('treasurer.loans.statement', compact('loan'));
    }

    /**
     * Record a repayment for a specific installment.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Loan $loan
     * @param \App\Models\LoanRepayment $repayment
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recordRepayment(Request $request, Loan $loan, LoanRepayment $repayment)
    {
        $request->validate([
            'payment_reference' => 'nullable|string|max:255',
        ]);

        // Security check
        if ($repayment->loan_id != $loan->id) {
            abort(404);
        }

        if ($repayment->status !== 'pending') {
            return back()->with('error', 'This installment has already been paid or is overdue.');
        }

        $repayment->markAsPaid($request->payment_reference);

        return back()->with('success', 'Repayment recorded successfully.');
    }
}