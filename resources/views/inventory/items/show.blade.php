@extends('adminlte::page')

@section('title', $item->name)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-box mr-2 text-primary"></i>{{ $item->name }}</h1>
    <div>
        <a href="{{ route('inventory.transactions.create', ['item'=>$item->id]) }}" class="btn btn-success btn-sm">
            <i class="fas fa-exchange-alt mr-1"></i>New Transaction
        </a>
        <a href="{{ route('inventory.items.edit', $item) }}" class="btn btn-warning btn-sm ml-1">
            <i class="fas fa-edit mr-1"></i>Edit
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">

    <div class="row">
        {{-- Item Details Card --}}
        <div class="col-lg-4">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Item Details</h3>
                </div>
                <div class="card-body" style="font-size:.9rem">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th width="120" class="text-muted">Category</th><td>{{ $item->category->name }}</td></tr>
                        <tr><th class="text-muted">Code</th><td>{{ $item->code ?: '—' }}</td></tr>
                        <tr><th class="text-muted">Unit</th><td>{{ $item->unit }}</td></tr>
                        <tr><th class="text-muted">Location</th><td>{{ $item->location ?: '—' }}</td></tr>
                        <tr><th class="text-muted">Unit Cost</th><td>TZS {{ number_format($item->unit_cost, 0) }}</td></tr>
                        <tr>
                            <th class="text-muted">Condition</th>
                            <td>
                                @php $c = ['good'=>'success','fair'=>'warning','poor'=>'danger'][$item->condition]; @endphp
                                <span class="badge badge-{{ $c }}">{{ ucfirst($item->condition) }}</span>
                            </td>
                        </tr>
                        @if($item->description)
                        <tr><th class="text-muted">Description</th><td>{{ $item->description }}</td></tr>
                        @endif
                        @if($item->notes)
                        <tr><th class="text-muted">Notes</th><td>{{ $item->notes }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Stock Summary --}}
        <div class="col-lg-8">
            <div class="row">
                <div class="col-6 col-md-3">
                    @php $sb = $item->quantity_in_stock == 0 ? 'danger' : ($item->isLowStock() ? 'warning' : 'success'); @endphp
                    <div class="info-box">
                        <span class="info-box-icon bg-{{ $sb }}"><i class="fas fa-cubes"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">In Stock</span>
                            <span class="info-box-number">{{ $item->quantity_in_stock }}</span>
                            <span class="progress-description">{{ $item->unit }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-arrow-down"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Min Level</span>
                            <span class="info-box-number">{{ $item->minimum_stock }}</span>
                            <span class="progress-description">{{ $item->unit }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-wallet"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Stock Value</span>
                            <span class="info-box-number" style="font-size:1rem">TZS {{ number_format($item->quantity_in_stock * $item->unit_cost, 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-history"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Txns</span>
                            <span class="info-box-number">{{ $transactions->total() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Low stock alert --}}
            @if($item->quantity_in_stock == 0)
            <div class="alert alert-danger py-2"><i class="fas fa-times-circle mr-1"></i>Out of stock! Please restock immediately.</div>
            @elseif($item->isLowStock())
            <div class="alert alert-warning py-2"><i class="fas fa-exclamation-triangle mr-1"></i>Stock is low. Consider restocking soon.</div>
            @endif

            {{-- Transaction History --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-history mr-1"></i>Transaction History</h3>
                    <a href="{{ route('inventory.transactions.create', ['item'=>$item->id]) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus mr-1"></i>Add Transaction
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0" style="font-size:.85rem">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Balance</th>
                                <th>Ref No</th>
                                <th>Issued To</th>
                                <th>Remarks</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                            @php
                                $tc = ['purchase'=>'success','issue'=>'warning','return'=>'info','adjustment'=>'secondary','damage'=>'danger','disposal'=>'dark'][$tx->type] ?? 'secondary';
                                $isOut = in_array($tx->type, ['issue','damage','disposal']);
                            @endphp
                            <tr>
                                <td>{{ $tx->transaction_date->format('d M Y') }}</td>
                                <td><span class="badge badge-{{ $tc }}">{{ ucfirst($tx->type) }}</span></td>
                                <td class="{{ $isOut ? 'text-danger' : 'text-success' }} font-weight-bold">
                                    {{ $isOut ? '-' : '+' }}{{ $tx->quantity }}
                                </td>
                                <td>{{ $tx->balance_after }}</td>
                                <td class="text-muted">{{ $tx->reference_no ?: '—' }}</td>
                                <td class="text-muted">{{ $tx->issued_to ?: '—' }}</td>
                                <td class="text-muted">{{ Str::limit($tx->remarks, 40) ?: '—' }}</td>
                                <td class="text-muted">{{ $tx->user->name ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center text-muted">No transactions yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                <div class="card-footer">{{ $transactions->links() }}</div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
