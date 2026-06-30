@extends('adminlte::page')

@section('title', 'Inventory Items')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-boxes mr-2 text-primary"></i>Inventory Items</h1>
    <a href="{{ route('inventory.items.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i>Add Item
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="card card-outline card-primary">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:.5rem">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name / code..."
                    value="{{ request('search') }}" style="min-width:200px">
                <select name="category" class="form-control form-control-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select name="stock" class="form-control form-control-sm">
                    <option value="">All Stock Levels</option>
                    <option value="low" {{ request('stock')=='low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock')=='out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
                <button class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('inventory.items') }}" class="btn btn-secondary btn-sm">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0" style="font-size:.88rem">
                <thead class="thead-light">
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Code</th>
                        <th>In Stock</th>
                        <th>Min Stock</th>
                        <th>Unit Cost</th>
                        <th>Condition</th>
                        <th>Location</th>
                        <th width="110">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    @php
                        $stockBadge = $item->quantity_in_stock == 0 ? 'danger'
                            : ($item->isLowStock() ? 'warning' : 'success');
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('inventory.items.show', $item) }}" class="font-weight-bold">{{ $item->name }}</a>
                            @if($item->isLowStock())
                            <span class="badge badge-warning ml-1" style="font-size:.65rem">Low</span>
                            @endif
                            @if($item->quantity_in_stock == 0)
                            <span class="badge badge-danger ml-1" style="font-size:.65rem">Out</span>
                            @endif
                        </td>
                        <td><i class="{{ $item->category->icon }} mr-1 text-muted"></i>{{ $item->category->name }}</td>
                        <td class="text-muted">{{ $item->code ?: '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $stockBadge }}">{{ $item->quantity_in_stock }} {{ $item->unit }}</span>
                        </td>
                        <td class="text-muted">{{ $item->minimum_stock }}</td>
                        <td>TZS {{ number_format($item->unit_cost, 0) }}</td>
                        <td>
                            @php $condColor = ['good'=>'success','fair'=>'warning','poor'=>'danger'][$item->condition]; @endphp
                            <span class="badge badge-{{ $condColor }}">{{ ucfirst($item->condition) }}</span>
                        </td>
                        <td class="text-muted">{{ $item->location ?: '—' }}</td>
                        <td>
                            <a href="{{ route('inventory.items.show', $item) }}" class="btn btn-xs btn-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('inventory.transactions.create', ['item'=>$item->id]) }}" class="btn btn-xs btn-success" title="Transaction"><i class="fas fa-exchange-alt"></i></a>
                            <a href="{{ route('inventory.items.edit', $item) }}" class="btn btn-xs btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('inventory.items.destroy', $item) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete this item?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
</div>
@endsection
