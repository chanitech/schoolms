<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Invoice;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:view budgets')->only(['index', 'show', 'summary']);
    $this->middleware('permission:create budgets')->only(['create', 'store']);
    $this->middleware('permission:edit budgets')->only(['edit', 'update']);
    $this->middleware('permission:delete budgets')->only(['destroy']); // if you have delete
    $this->middleware('permission:approve budget items')->only(['approveForm', 'approveItem', 'approve']);
    $this->middleware('permission:view pending approvals')->only(['pending']);
    $this->middleware('permission:withdraw budget items')->only(['withdrawItem']);
    $this->middleware('permission:view hod dashboard')->only(['hodBudgets']);
}

    /**
     * List all budgets
     */
    public function index()
    {
        $user = Auth::user();
        $staff = $user->staff;

        $budgetsQuery = Budget::with('staff', 'department')->latest();

        // Restrict HOD to only their department. Uses the real Spatie role
        // on the User — staff.role is a free-text legacy field that isn't
        // reliably kept in sync (it's what caused Head Master/DO to be
        // silently blocked from approving invoices elsewhere in this file).
        if ($user->hasRole('HOD')) {
            $budgetsQuery->where('department_id', $staff->department_id);
        }

        $budgets = $budgetsQuery->paginate(20);

        return view('finance.budgets.index', compact('budgets'));
    }

    /**
     * Show form to create a new budget
     */
    public function create()
    {
        $departments = Department::all();
        return view('finance.budgets.create', compact('departments'));
    }

    /**
     * Store budget + items
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'month' => 'required|string',
            'year' => 'required|integer',
            'note' => 'nullable|string|max:500',
            'items.*.item' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $budget = Budget::create([
            'staff_id' => Auth::id(),
            'department_id' => $validated['department_id'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'note' => $validated['note'] ?? null,
            'status' => 'pending',
            'current_step' => 'do', // pending DO approval
        ]);

        foreach ($validated['items'] as $item) {
            $budget->items()->create([
                'item' => $item['item'],
                'description' => $item['description'] ?? null,
                'price' => $item['price'],
                'status' => 'pending',
            ]);
        }

        $budget->calculateTotal();

        return redirect()
            ->route('finance.budgets.show', $budget->id)
            ->with('success', 'Budget submitted successfully.');
    }

    /**
     * Show budget details
     */
    public function show(Budget $budget)
    {
        $user = Auth::user();
        $staff = $user->staff;

        // HOD cannot see other departments
        if ($user->hasRole('HOD') && $staff && $budget->department_id !== $staff->department_id) {
            abort(403, 'Unauthorized: You cannot access another department budget.');
        }

        $budget->load('staff', 'department', 'items.approvedBy', 'items.invoice');

        return view('finance.budgets.show', [
            'budget' => $budget,
            'userRole' => $staff->role ?? 'Unknown',
        ]);
    }

    /**
     * List pending budgets for DO approval
     */
    public function pending()
    {
        $pendingBudgets = Budget::with('staff', 'department')
            ->where('status', 'pending')
            ->where('current_step', 'do')
            ->latest()
            ->get();

        return view('finance.budgets.pending', compact('pendingBudgets'));
    }

    /**
     * Show DO approval form
     */
    public function approveForm(Budget $budget)
    {
        $budget->load('items', 'staff', 'department');

        if ($budget->current_step !== 'do') {
            return redirect()->back()->with('error', 'This budget is not ready for DO approval.');
        }

        return view('finance.budgets.approve', compact('budget'));
    }

    /**
     * Approve/reject individual budget item
     */
    public function approveItem(Request $request, Budget $budget)
    {
        $request->validate([
            'item_id' => 'required|exists:budget_items,id',
            'action'  => 'required|in:approved,rejected',
            'note'    => 'nullable|string',
        ]);

        $user = Auth::user();
        $item = $budget->items()->findOrFail($request->input('item_id'));

        $item->update([
            'status' => $request->input('action'),
            'note' => $request->input('note'),
            'approved_by' => $user->id,
        ]);

        $budget->updateStatusBasedOnItems();

        return redirect()->back()->with('success', 'Item updated and budget status refreshed.');
    }

    /**
     * Approve all budget items at once
     */
    public function approve(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'items.*.status' => 'required|in:approved,rejected',
            'items.*.note' => 'nullable|string|max:500',
        ]);

        foreach ($budget->items as $item) {
            // Skip items the form didn't submit a decision for, instead of
            // crashing on the missing array key.
            if (!isset($validated['items'][$item->id]['status'])) {
                continue;
            }

            $item->update([
                'status' => $validated['items'][$item->id]['status'],
                'note' => $validated['items'][$item->id]['note'] ?? null,
                'approved_by' => Auth::id(),
            ]);
        }

        $budget->status = $budget->items()->where('status', 'rejected')->exists()
            ? 'partially_approved'
            : 'approved';

        $budget->current_step = 'hod'; // back to HOD for withdrawal
        $budget->save();

        return redirect()
            ->route('finance.budgets.show', $budget->id)
            ->with('success', 'Budget items processed successfully.');
    }

    /**
     * HOD withdraw/Use approved items → create invoice
     */
    public function withdrawItem(Request $request, BudgetItem $item)
{
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $staff = $user->staff;

    // Only HOD or Admin can withdraw. Roles are assigned to the User, never
    // to the Staff model directly — $staff->hasAnyRole(...) always checked
    // an empty pivot and silently blocked every HOD from ever withdrawing
    // an item (so no invoice could ever be created for DO/Finance to act on).
    if (!$user->hasAnyRole(['HOD', 'Admin'])) {
        abort(403, 'You are not authorized to withdraw this item.');
    }

    // HOD can only withdraw items from their department
    if ($user->hasRole('HOD') && (!$staff || $staff->department_id !== $item->budget->department_id)) {
        abort(403, 'You are not authorized to withdraw this item.');
    }

    // Only approved items can be withdrawn
    if ($item->status !== 'approved') {
        return redirect()->back()->with('error', 'Item must be approved before withdrawal.');
    }

    // Create Invoice
    $invoice = Invoice::create([
        'budget_id'      => $item->budget_id,
        'budget_item_id' => $item->id,
        'amount'         => $item->price,
        'status'         => 'pending', // pending approval by DO/Finance
    ]);

    // Update item status
    $item->update(['status' => 'withdrawn']);

    // Optional: update budget totals or workflow step if needed
    if (method_exists($item->budget, 'updateStatusBasedOnItems')) {
        $item->budget->updateStatusBasedOnItems();
    }

    return redirect()->back()->with('success', 'Invoice created and sent to DO successfully.');
}


    public function hodBudgets()
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    /** @var \App\Models\Staff|null $staff */
    $staff = $user->staff;

    // Only HOD or Admin can access
    if (!$user->hasAnyRole(['HOD', 'Admin'])) {
        abort(403, 'Unauthorized');
    }

    // Base query: budgets at HOD step
    $budgetsQuery = Budget::with(['staff', 'department', 'items'])
        ->where('current_step', 'hod');

    // Restrict to HOD's department if user is HOD
    if ($staff && $user->hasRole('HOD')) {
        $budgetsQuery->where('department_id', $staff->department_id);
    }

    // Optional: filter by month/year
    if (request()->filled('month')) {
        $budgetsQuery->where('month', request('month'));
    }
    if (request()->filled('year')) {
        $budgetsQuery->where('year', request('year'));
    }

    $budgets = $budgetsQuery->latest()->get();

    return view('finance.budgets.hod', compact('budgets'));
}
    /**
     * Show edit form
     */
    public function edit(Budget $budget)
    {
        $this->authorize('edit budgets');
        $departments = Department::all();
        return view('finance.budgets.edit', compact('budget', 'departments'));
    }

    /**
     * Handle update
     */
    public function update(Request $request, Budget $budget)
    {
        $this->authorize('edit budgets');

        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'month' => 'required|string',
            'year' => 'required|integer',
            'note' => 'nullable|string|max:500',
        ]);

        $budget->update($validated);

        return redirect()->route('finance.budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    /**
     * Budget summary
     */
    public function summary(Request $request)
{
    $user = Auth::user();
    $staff = $user->staff;

    // Base query
    $query = Budget::with('staff', 'department', 'items');

    // Restrict HOD to their own department — every other role holding
    // 'view budgets' (Admin, Treasurer, Chief Accountant, Accountant,
    // Principal) is a school-wide oversight role, not tied to one
    // department, matching index()/show() above. The old check inverted
    // this (restrict everyone except literal 'admin'), so Treasurer/
    // Principal/accountants were wrongly limited to a single department
    // here despite seeing every department everywhere else in this module.
    if ($user->hasRole('HOD') && $staff) {
        $query->where('department_id', $staff->department_id);
    }

    // Apply filters
    if ($request->filled('department_id')) {
        $query->where('department_id', $request->department_id);
    }

    if ($request->filled('month')) {
        $query->where('month', $request->month); // month stored as string like "January"
    }

    if ($request->filled('year')) {
        $query->where('year', $request->year);
    }

    // Paginate results
    $budgets = $query->latest()->paginate(10)->withQueryString();

    // Get departments for dropdown
    $departments = ($user->hasRole('HOD') && $staff)
        ? Department::where('id', $staff->department_id)->get()
        : Department::all();

    // Months array for filter dropdown
    $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    return view('finance.budgets.summary', compact('budgets', 'departments', 'months'));
}



}
