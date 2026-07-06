<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\ExpenseLog;
use App\Models\InventoryItem;
use App\Models\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcurementRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create procurement requests')->only(['create', 'store']);
        $this->middleware('permission:approve procurement requests')->only(['pending', 'approve', 'reject']);
        $this->middleware('permission:disburse payments')->only(['disburse']);
    }

    public function index()
    {
        $requests = ProcurementRequest::with(['requestedBy', 'approvedBy', 'inventoryItem'])
            ->latest()
            ->paginate(20);

        return view('treasurer.procurement.index', compact('requests'));
    }

    public function create()
    {
        $inventoryItems = InventoryItem::with('category')->orderBy('name')->get();
        $lowStockItems = $inventoryItems->filter(fn (InventoryItem $item) => $item->isLowStock());

        return view('treasurer.procurement.create', compact('inventoryItems', 'lowStockItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'nullable|exists:inventory_items,id',
            'item' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'estimated_cost' => 'required|numeric|min:0.01',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $threshold = (float) config('finance.procurement_approval_threshold');

        ProcurementRequest::create([
            ...$validated,
            'requested_by' => Auth::id(),
            'status' => 'pending',
            'threshold_flag' => $threshold > 0 && $validated['estimated_cost'] > $threshold,
        ]);

        return redirect()->route('treasurer.procurement.index')
            ->with('success', 'Procurement request submitted for Treasurer approval.');
    }

    public function pending()
    {
        $requests = ProcurementRequest::with(['requestedBy', 'inventoryItem'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->get();

        return view('treasurer.procurement.pending', compact('requests'));
    }

    public function approve(ProcurementRequest $procurementRequest)
    {
        if ($procurementRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $procurementRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Procurement request approved. Cashier can now disburse payment.');
    }

    public function reject(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($procurementRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $procurementRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'notes' => $validated['notes'] ?? $procurementRequest->notes,
        ]);

        return back()->with('success', 'Procurement request rejected.');
    }

    /**
     * Cashier disburses the approved payment — the only role that touches
     * actual cash/bank movement — and the expense is logged for the
     * relevant accountant to reconcile.
     */
    public function disburse(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'actual_cost' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($procurementRequest->status !== 'approved') {
            return back()->with('error', 'This request has not been approved by the Treasurer yet.');
        }

        $procurementRequest->update([
            'status' => 'completed',
            'actual_cost' => $validated['actual_cost'],
        ]);

        ExpenseLog::create([
            'recorded_by' => Auth::id(),
            'linked_procurement_id' => $procurementRequest->id,
            'category' => $validated['category'],
            'amount' => $validated['actual_cost'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('treasurer.procurement.index')
            ->with('success', 'Payment disbursed and expense recorded.');
    }
}
