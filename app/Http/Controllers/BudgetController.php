<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Budget;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Add permissions if needed
    }

    /**
     * List all budgets (index)
     */
    public function index()
    {
        $budgets = Budget::with('staff', 'department')->latest()->paginate(20);
        return view('finance.budgets.index', compact('budgets'));
    }

    /**
     * Show form to create a new budget (HoD)
     */
    public function create()
    {
        $departments = \App\Models\Department::all();
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
        ]);

        foreach ($validated['items'] as $item) {
            $budget->items()->create($item);
        }

        $budget->calculateTotal();

        return redirect()
            ->route('finance.budgets.show', $budget->id)
            ->with('success', 'Budget submitted successfully.');
    }



    

    

    /**
     * Show budget details (show)
     */
    public function show(Budget $budget)
    {
        $budget->load('staff', 'department', 'items');

        return view('finance.budgets.show', compact('budget'));
    }

    /**
     * Show approval form
     */
    public function approveForm(Budget $budget)
    {
        $budget->load('items', 'staff', 'department');

        if ($budget->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This budget has already been processed.');
        }

        return view('finance.budgets.approve', compact('budget'));
    }

    /**
     * Handle budget approval
     */
    public function approve(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'items.*.status' => 'required|in:approved,rejected',
            'items.*.note' => 'nullable|string|max:500',
        ]);

        foreach ($budget->items as $item) {
            $item->update([
                'status' => $validated['items'][$item->id]['status'],
                'note' => $validated['items'][$item->id]['note'] ?? null,
                'approved_by' => Auth::id(),
            ]);
        }

        // Update overall budget status
        if ($budget->items()->where('status', 'rejected')->exists()) {
            $budget->status = 'partially_approved';
        } else {
            $budget->status = 'approved';
        }
        $budget->save();

        return redirect()
            ->route('finance.budgets.show', $budget->id)
            ->with('success', 'Budget processed successfully.');
    }

    /**
     * Approve/reject individual item via AJAX
     */
    public function approveItem(Request $request, Budget $budget)
    {
        $request->validate([
            'item_id' => 'required|exists:budget_items,id',
            'status' => 'required|in:approved,rejected',
            'comment' => 'nullable|string|max:500',
        ]);

        $item = $budget->items()->findOrFail($request->item_id);
        $item->update([
            'status' => $request->status,
            'comment' => $request->comment,
            'approved_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item status updated.',
        ]);
    }


    public function pending()
{
    // Fetch all budgets with 'pending' status, latest first
    $pendingBudgets = \App\Models\Budget::with(['department', 'submitted_by'])
                        ->where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->get();

    // Pass to the Blade view
    return view('finance.budgets.pending', compact('pendingBudgets'));
}

    /**
     * Show budget summary with filters
     */
    public function summary(Request $request)
    {
        $departments = \App\Models\Department::all();

        $query = Budget::with('staff', 'department', 'items');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $budgets = $query->latest()->paginate(20)->withQueryString();

        return view('finance.budgets.summary', compact('budgets', 'departments'));
    }
}
