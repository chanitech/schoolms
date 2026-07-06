<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\StockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:create stock requests')->only(['create', 'store']);
        $this->middleware('permission:review stock requests')->only(['approve', 'reject']);
    }

    /**
     * Anyone who can review (Procurement Officer/Treasurer/Admin) sees every
     * request, for oversight. Everyone else (Storekeeper) sees only their own.
     */
    public function index()
    {
        $user = Auth::user();

        $query = StockRequest::with(['requestedBy', 'reviewedBy', 'inventoryItem', 'procurementRequest'])->latest();

        if (! $user->can('review stock requests')) {
            $query->where('requested_by', $user->id);
        }

        $requests = $query->paginate(20);

        return view('treasurer.stock-requests.index', compact('requests'));
    }

    public function create()
    {
        $inventoryItems = InventoryItem::with('category')->orderBy('name')->get();
        $lowStockItems = $inventoryItems->filter(fn (InventoryItem $item) => $item->isLowStock());

        return view('treasurer.stock-requests.create', compact('inventoryItems', 'lowStockItems'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_item_id' => 'nullable|exists:inventory_items,id',
            'item' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:1000',
        ]);

        StockRequest::create([
            ...$validated,
            'requested_by' => Auth::id(),
            'status' => 'pending',
        ]);

        return redirect()->route('treasurer.stock-requests.index')
            ->with('success', 'Stock request submitted to the Procurement Officer.');
    }

    /**
     * Approving doesn't finish the job — the Procurement Officer still has
     * to supply the estimated cost/supplier a Storekeeper wouldn't know, so
     * this hands off into the procurement request form pre-filled from here.
     */
    public function approve(StockRequest $stockRequest)
    {
        if ($stockRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $stockRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('treasurer.procurement.create', ['stock_request' => $stockRequest->id])
            ->with('success', 'Stock request approved — now create the procurement request for the Treasurer.');
    }

    public function reject(Request $request, StockRequest $stockRequest)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($stockRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        $stockRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'notes' => $validated['notes'] ?? $stockRequest->notes,
        ]);

        return back()->with('success', 'Stock request rejected.');
    }
}
