<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\BudgetItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List all invoices (Admin/Finance)
     */
    public function index()
{
    $user = auth()->user();

    if ($user->hasRole('hod')) {

        // HOD sees invoices only from budgets in his department
        $invoices = Invoice::whereHas('budgetItem', function ($q) use ($user) {
            $q->whereHas('budget', function ($q2) use ($user) {
                $q2->where('department_id', $user->department_id);
            });
        })
        ->with('budgetItem.budget', 'approvedBy')
        ->latest()
        ->paginate(20);

    } else {

        // Admin/Finance see all invoices
        $invoices = Invoice::with('budgetItem.budget', 'approvedBy')
            ->latest()
            ->paginate(20);
    }

    return view('finance.invoices.index', compact('invoices'));
}


    /**
     * DO Dashboard: invoices pending DO approval
     */
    public function doDashboard()
    {
        $invoices = Invoice::with('budgetItem.budget')
                    ->where('status', 'pending')
                    ->latest()->get();

        return view('finance.invoices.do', compact('invoices'));
    }

    /**
     * Finance Dashboard: invoices pending payment
     */
    public function financeDashboard()
    {
        $invoices = Invoice::with('budgetItem.budget')
                    ->where('status', 'approved_by_do')
                    ->latest()->get();

        return view('finance.invoices.finance', compact('invoices'));
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice)
{
    $user = auth()->user();

    // Prevent HOD from viewing other department’s invoice
    if ($user->hasRole('hod')) {
        $invoiceDept = $invoice->budgetItem->budget->department_id;

        if ($invoiceDept != $user->department_id) {
            abort(403, 'Unauthorized: You cannot access another department invoice.');
        }
    }

    // Load relationships
    $invoice->load(['budgetItem.budget', 'approvedBy', 'paidBy']);

    // Filter out rejected items
    $invoiceItems = collect([$invoice->budgetItem])->filter(function ($item) {
        return $item->status !== 'Rejected';
    });

    // Calculate totals
    $totalAmount = $invoiceItems->sum('price');
    $paidAmount = $invoice->status === 'paid' ? $invoice->amount : 0;
    $remainingAmount = $totalAmount - $paidAmount;

    return view('finance.invoices.show', [
        'invoice' => $invoice,
        'invoiceItems' => $invoiceItems,
        'totalAmount' => $totalAmount,
        'paidAmount' => $paidAmount,
        'remainingAmount' => $remainingAmount,
    ]);
}






    /**
     * DO approves or rejects invoice
     */
    public function approve(Request $request, Invoice $invoice)
{
    $request->validate([
        'status' => 'required|in:approved_by_do,rejected_by_do',
    ]);

    if ($invoice->status !== 'pending') {
        return redirect()->back()->with('error', 'Invoice has already been processed.');
    }

    // Correct column name
    $invoice->status = $request->status;
    $invoice->approved_by_do_id = Auth::id();
    $invoice->save();

    // Update related BudgetItem status if rejected
    if ($request->status == 'rejected_by_do') {
        $invoice->budgetItem->update(['status' => 'rejected']);
    }

    return redirect()->back()->with('success', 'Invoice processed successfully.');
}


    /**
     * Finance pays invoice
     */
    public function pay(Request $request, Invoice $invoice)
{
    // Only allow payment if invoice is approved by DO
    if ($invoice->status !== 'approved_by_do') {
        return redirect()->back()->with('error', 'Invoice not approved by DO yet.');
    }

    // Wrap in transaction to ensure both updates succeed together
    DB::transaction(function () use ($invoice) {

        // Mark invoice as paid
        $invoice->update([
            'status' => 'paid',
            'paid_by_finance_id' => Auth::id(),
            'payment_date' => now(),
        ]);

        // Update the related budget item status to 'used', overriding withdrawn/pending
        if ($invoice->budgetItem) {
            $invoice->budgetItem->update(['status' => 'used']);
        }
    });

    return redirect()->back()->with('success', 'Invoice marked as paid and budget item updated.');
}


}
