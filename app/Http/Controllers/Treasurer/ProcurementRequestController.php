<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\ExpenseLog;
use App\Models\InventoryItem;
use App\Models\ProcurementRequest;
use App\Models\StockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcurementRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create procurement requests')->only(['create', 'store']);
        $this->middleware('permission:approve procurement requests')->only(['pending', 'approve', 'reject']);
        $this->middleware('permission:headmaster approve procurement requests')->only(['headmasterApprove', 'headmasterReject']);
        $this->middleware('permission:disburse payments')->only(['disburse']);
    }

    /**
     * Every Finance Office member (Treasurer, Chief Accountant, Accountant,
     * Class Accountant, Procurement Officer, Cashier, Storekeeper, Admin —
     * the 'is-finance-office' Gate) sees every request and its status, for
     * shared office-wide oversight. This also fixes a real bug: Cashier
     * held neither approval permission, so they were being restricted to
     * "requests I submitted" — but Cashier never submits requests, only
     * disburses ones Procurement Officer/Storekeeper created, so they
     * could never actually find anything to disburse. Anyone outside the
     * Finance Office (shouldn't normally reach this page at all) still
     * only sees their own, as a defensive fallback.
     */
    public function index()
    {
        $user = Auth::user();

        $query = ProcurementRequest::with(['requestedBy', 'approvedBy', 'headmasterApprovedBy', 'inventoryItem'])->latest();

        if (! $user->can('is-finance-office')) {
            $query->where('requested_by', $user->id);
        }

        $requests = $query->paginate(20);

        return view('treasurer.procurement.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $inventoryItems = InventoryItem::with('category')->orderBy('name')->get();
        $lowStockItems = $inventoryItems->filter(fn (InventoryItem $item) => $item->isLowStock());

        // Arriving from an approved Stock Request (Storekeeper -> Procurement
        // Officer) — pre-fill what the Storekeeper already specified.
        $stockRequest = null;
        if ($request->filled('stock_request')) {
            $stockRequest = StockRequest::where('status', 'approved')->find($request->input('stock_request'));
        }

        return view('treasurer.procurement.create', compact('inventoryItems', 'lowStockItems', 'stockRequest'));
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
            'stock_request_id' => 'nullable|exists:stock_requests,id',
        ]);

        $threshold = (float) config('finance.procurement_approval_threshold');
        $stockRequestId = $validated['stock_request_id'] ?? null;
        unset($validated['stock_request_id']);

        $procurementRequest = ProcurementRequest::create([
            ...$validated,
            'requested_by' => Auth::id(),
            'status' => 'pending',
            'threshold_flag' => $threshold > 0 && $validated['estimated_cost'] > $threshold,
        ]);

        if ($stockRequestId) {
            StockRequest::where('id', $stockRequestId)->where('status', 'approved')->update([
                'status' => 'converted',
                'procurement_request_id' => $procurementRequest->id,
            ]);
        }

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

    /**
     * Treasurer's approval — first stage. Doesn't clear the request for
     * disbursement yet; it now needs the Head Master's sign-off too.
     */
    public function approve(ProcurementRequest $procurementRequest)
    {
        if ($procurementRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $procurementRequest->update([
            'status' => 'treasurer_approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Procurement request approved by Treasurer — awaiting Head Master approval.');
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
     * Head Master's approval — final stage. Only after this does the
     * request become visible to Cashier for disbursement.
     */
    public function headmasterApprove(ProcurementRequest $procurementRequest)
    {
        if ($procurementRequest->status !== 'treasurer_approved') {
            return back()->with('error', 'This request has not been approved by the Treasurer yet.');
        }

        $procurementRequest->update([
            'status' => 'approved',
            'headmaster_approved_by' => Auth::id(),
            'headmaster_approved_at' => now(),
        ]);

        return back()->with('success', 'Procurement request approved by Head Master. Cashier can now disburse payment.');
    }

    public function headmasterReject(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($procurementRequest->status !== 'treasurer_approved') {
            return back()->with('error', 'This request has not been approved by the Treasurer yet.');
        }

        $procurementRequest->update([
            'status' => 'rejected',
            'headmaster_approved_by' => Auth::id(),
            'headmaster_approved_at' => now(),
            'notes' => $validated['notes'] ?? $procurementRequest->notes,
        ]);

        return back()->with('success', 'Procurement request rejected by Head Master.');
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
            return back()->with('error', 'This request has not been fully approved (Treasurer + Head Master) yet.');
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
