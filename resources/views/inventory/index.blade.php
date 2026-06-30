@extends('adminlte::page')

@section('title', 'Inventory')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-boxes mr-2 text-primary"></i>Inventory Management</h1>
    <div>
        <a href="{{ route('inventory.transactions.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-exchange-alt mr-1"></i>New Transaction
        </a>
        <a href="{{ route('inventory.items.create') }}" class="btn btn-primary btn-sm ml-1">
            <i class="fas fa-plus mr-1"></i>Add Item
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
    @endif

    {{-- KPI Cards --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ $totalItems }}</h3><p>Total Items</p></div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <a href="{{ route('inventory.items') }}" class="small-box-footer">View Items <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $totalCategories }}</h3><p>Categories</p></div>
                <div class="icon"><i class="fas fa-tags"></i></div>
                <a href="{{ route('inventory.categories') }}" class="small-box-footer">View Categories <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box {{ $lowStockItems > 0 ? 'bg-warning' : 'bg-info' }}">
                <div class="inner"><h3>{{ $lowStockItems }}</h3><p>Low Stock Alerts</p></div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('inventory.items', ['stock'=>'low']) }}" class="small-box-footer">View Low Stock <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>TZS {{ number_format($totalValue, 0) }}</h3><p>Total Stock Value</p></div>
                <div class="icon"><i class="fas fa-wallet"></i></div>
                <a href="{{ route('inventory.transactions') }}" class="small-box-footer">Transactions <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Low Stock Alerts --}}
        <div class="col-lg-5">
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i>Low Stock Alerts</h3>
                </div>
                <div class="card-body p-0">
                    @if($lowStock->isEmpty())
                    <div class="p-3 text-muted text-center"><i class="fas fa-check-circle text-success mr-1"></i>All items are adequately stocked.</div>
                    @else
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr><th>Item</th><th>In Stock</th><th>Minimum</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($lowStock as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('inventory.items.show', $item) }}">{{ $item->name }}</a>
                                    <small class="text-muted d-block">{{ $item->category->name }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->quantity_in_stock == 0 ? 'danger' : 'warning' }}">
                                        {{ $item->quantity_in_stock }} {{ $item->unit }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $item->minimum_stock }}</td>
                                <td>
                                    <a href="{{ route('inventory.transactions.create', ['item'=>$item->id]) }}" class="btn btn-xs btn-success">Restock</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>

            {{-- Category Breakdown --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tags mr-1"></i>Stock by Category</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="thead-light"><tr><th>Category</th><th>Items</th><th>Total Qty</th></tr></thead>
                        <tbody>
                            @forelse($categoryStats as $cat)
                            <tr>
                                <td><i class="{{ $cat->icon }} mr-1 text-primary"></i>{{ $cat->name }}</td>
                                <td>{{ $cat->items_count }}</td>
                                <td>{{ number_format($cat->items_sum_quantity_in_stock) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">No categories yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="col-lg-7">
            <div class="card card-outline card-info">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-history mr-1"></i>Recent Transactions</h3>
                    <a href="{{ route('inventory.transactions') }}" class="btn btn-sm btn-outline-info">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0" style="font-size:.85rem">
                        <thead class="thead-light">
                            <tr><th>Date</th><th>Item</th><th>Type</th><th>Qty</th><th>Balance</th><th>By</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $tx)
                            @php
                                $typeColors = ['purchase'=>'success','issue'=>'warning','return'=>'info','adjustment'=>'secondary','damage'=>'danger','disposal'=>'dark'];
                                $tc = $typeColors[$tx->type] ?? 'secondary';
                            @endphp
                            <tr>
                                <td class="text-muted">{{ $tx->transaction_date->format('d M') }}</td>
                                <td>
                                    <a href="{{ route('inventory.items.show', $tx->item_id) }}">{{ Str::limit($tx->item->name, 25) }}</a>
                                </td>
                                <td><span class="badge badge-{{ $tc }}">{{ ucfirst($tx->type) }}</span></td>
                                <td>
                                    <span class="{{ in_array($tx->type,['issue','damage','disposal']) ? 'text-danger' : 'text-success' }}">
                                        {{ in_array($tx->type,['issue','damage','disposal']) ? '-' : '+' }}{{ $tx->quantity }}
                                    </span>
                                </td>
                                <td>{{ $tx->balance_after }}</td>
                                <td class="text-muted">{{ $tx->user->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
