<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    // ─────────────────── Dashboard ───────────────────

    public function index()
    {
        $totalItems      = InventoryItem::count();
        $totalCategories = InventoryCategory::count();
        $lowStockItems   = InventoryItem::whereColumn('quantity_in_stock', '<=', 'minimum_stock')
                            ->where('minimum_stock', '>', 0)->count();
        $totalValue      = InventoryItem::selectRaw('SUM(quantity_in_stock * unit_cost) as val')->value('val') ?? 0;

        $recentTransactions = InventoryTransaction::with(['item', 'user'])
            ->orderByDesc('created_at')->limit(10)->get();

        $lowStock = InventoryItem::with('category')
            ->whereColumn('quantity_in_stock', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->orderBy('quantity_in_stock')->limit(8)->get();

        $categoryStats = InventoryCategory::withCount('items')
            ->withSum('items', 'quantity_in_stock')->get();

        return view('inventory.index', compact(
            'totalItems', 'totalCategories', 'lowStockItems', 'totalValue',
            'recentTransactions', 'lowStock', 'categoryStats'
        ));
    }

    // ─────────────────── Categories ───────────────────

    public function categories()
    {
        $categories = InventoryCategory::withCount('items')->orderBy('name')->get();
        return view('inventory.categories.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('inventory_categories', 'name')->where('school_id', $schoolId)],
            'icon'        => 'nullable|string|max:60',
            'description' => 'nullable|string|max:255',
        ]);
        $data['icon'] = $data['icon'] ?: 'fas fa-box';
        InventoryCategory::create($data);
        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, InventoryCategory $category)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', Rule::unique('inventory_categories', 'name')->ignore($category->id)->where('school_id', $schoolId)],
            'icon'        => 'nullable|string|max:60',
            'description' => 'nullable|string|max:255',
        ]);
        $data['icon'] = $data['icon'] ?: 'fas fa-box';
        $category->update($data);
        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(InventoryCategory $category)
    {
        if ($category->items()->exists()) {
            return back()->with('error', 'Cannot delete category with existing items.');
        }
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    // ─────────────────── Items ───────────────────

    public function items(Request $request)
    {
        $query = InventoryItem::with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%"));
        }
        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->whereColumn('quantity_in_stock', '<=', 'minimum_stock')->where('minimum_stock', '>', 0);
            } elseif ($request->stock === 'out') {
                $query->where('quantity_in_stock', 0);
            }
        }

        $items      = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = InventoryCategory::orderBy('name')->get();

        return view('inventory.items.index', compact('items', 'categories'));
    }

    public function createItem()
    {
        $categories = InventoryCategory::orderBy('name')->get();
        return view('inventory.items.create', compact('categories'));
    }

    public function storeItem(Request $request)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $data = $request->validate([
            'category_id'      => 'required|exists:inventory_categories,id',
            'name'             => 'required|string|max:150',
            'code'             => ['nullable', 'string', 'max:50', Rule::unique('inventory_items', 'code')->where('school_id', $schoolId)],
            'description'      => 'nullable|string',
            'unit'             => 'required|string|max:30',
            'quantity_in_stock'=> 'required|integer|min:0',
            'minimum_stock'    => 'required|integer|min:0',
            'unit_cost'        => 'required|numeric|min:0',
            'condition'        => 'required|in:good,fair,poor',
            'location'         => 'nullable|string|max:150',
            'notes'            => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $item = InventoryItem::create($data);

            if ($data['quantity_in_stock'] > 0) {
                InventoryTransaction::create([
                    'item_id'          => $item->id,
                    'type'             => 'purchase',
                    'quantity'         => $data['quantity_in_stock'],
                    'balance_after'    => $data['quantity_in_stock'],
                    'remarks'          => 'Opening stock',
                    'user_id'          => Auth::id(),
                    'transaction_date' => now()->toDateString(),
                ]);
            }
        });

        return redirect()->route('inventory.items')->with('success', 'Item added to inventory.');
    }

    public function editItem(InventoryItem $item)
    {
        $categories = InventoryCategory::orderBy('name')->get();
        return view('inventory.items.edit', compact('item', 'categories'));
    }

    public function updateItem(Request $request, InventoryItem $item)
    {
        $schoolId = app()->bound('currentSchool') ? app('currentSchool')->id : null;

        $data = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'name'        => 'required|string|max:150',
            'code'        => ['nullable', 'string', 'max:50', Rule::unique('inventory_items', 'code')->ignore($item->id)->where('school_id', $schoolId)],
            'description' => 'nullable|string',
            'unit'        => 'required|string|max:30',
            'minimum_stock' => 'required|integer|min:0',
            'unit_cost'   => 'required|numeric|min:0',
            'condition'   => 'required|in:good,fair,poor',
            'location'    => 'nullable|string|max:150',
            'notes'       => 'nullable|string',
        ]);
        $item->update($data);
        return redirect()->route('inventory.items')->with('success', 'Item updated.');
    }

    public function showItem(InventoryItem $item)
    {
        $item->load('category');
        $transactions = $item->transactions()->with('user')->orderByDesc('transaction_date')->orderByDesc('id')->paginate(15);
        return view('inventory.items.show', compact('item', 'transactions'));
    }

    public function destroyItem(InventoryItem $item)
    {
        $item->delete();
        return redirect()->route('inventory.items')->with('success', 'Item removed.');
    }

    // ─────────────────── Transactions ───────────────────

    public function transactions(Request $request)
    {
        $query = InventoryTransaction::with(['item.category', 'user'])->orderByDesc('transaction_date')->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('item')) {
            $query->where('item_id', $request->item);
        }
        if ($request->filled('from')) {
            $query->whereDate('transaction_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('transaction_date', '<=', $request->to);
        }

        $transactions = $query->paginate(25)->withQueryString();
        $items        = InventoryItem::orderBy('name')->get(['id', 'name']);

        return view('inventory.transactions.index', compact('transactions', 'items'));
    }

    public function createTransaction(Request $request)
    {
        $items = InventoryItem::with('category')->orderBy('name')->get();
        $selectedItem = $request->filled('item') ? InventoryItem::find($request->item) : null;
        return view('inventory.transactions.create', compact('items', 'selectedItem'));
    }

    public function storeTransaction(Request $request)
    {
        $data = $request->validate([
            'item_id'          => 'required|exists:inventory_items,id',
            'type'             => 'required|in:purchase,issue,return,adjustment,damage,disposal',
            'quantity'         => 'required|integer|min:1',
            'reference_no'     => 'nullable|string|max:100',
            'issued_to'        => 'nullable|string|max:150',
            'remarks'          => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        DB::transaction(function () use ($data) {
            $item = InventoryItem::lockForUpdate()->find($data['item_id']);

            $deduction = in_array($data['type'], ['issue', 'damage', 'disposal']);
            $addition  = in_array($data['type'], ['purchase', 'return', 'adjustment']);

            if ($deduction) {
                if ($item->quantity_in_stock < $data['quantity']) {
                    throw new \Exception("Insufficient stock. Available: {$item->quantity_in_stock}");
                }
                $newBalance = $item->quantity_in_stock - $data['quantity'];
            } else {
                $newBalance = $item->quantity_in_stock + $data['quantity'];
            }

            $item->update(['quantity_in_stock' => $newBalance]);

            InventoryTransaction::create([
                'item_id'          => $item->id,
                'type'             => $data['type'],
                'quantity'         => $data['quantity'],
                'balance_after'    => $newBalance,
                'reference_no'     => $data['reference_no'] ?? null,
                'issued_to'        => $data['issued_to'] ?? null,
                'remarks'          => $data['remarks'] ?? null,
                'user_id'          => Auth::id(),
                'transaction_date' => $data['transaction_date'],
            ]);
        });

        return redirect()->route('inventory.transactions')->with('success', 'Transaction recorded.');
    }
}
