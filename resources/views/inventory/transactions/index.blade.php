@extends('adminlte::page')

@section('title', 'Inventory Transactions')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-exchange-alt mr-2 text-info"></i>Inventory Transactions</h1>
    <a href="{{ route('inventory.transactions.create') }}" class="btn btn-success btn-sm">
        <i class="fas fa-plus mr-1"></i>New Transaction
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <div class="card card-outline card-info">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:.5rem">
                <select name="type" class="form-control form-control-sm">
                    <option value="">All Types</option>
                    @foreach(['purchase','issue','return','adjustment','damage','disposal'] as $t)
                    <option value="{{ $t }}" {{ request('type')==$t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
                <select name="item" class="form-control form-control-sm" style="min-width:180px">
                    <option value="">All Items</option>
                    @foreach($items as $it)
                    <option value="{{ $it->id }}" {{ request('item')==$it->id ? 'selected' : '' }}>{{ $it->name }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From date">
                <input type="date" name="to"   class="form-control form-control-sm" value="{{ request('to') }}"   placeholder="To date">
                <button class="btn btn-info btn-sm">Filter</button>
                <a href="{{ route('inventory.transactions') }}" class="btn btn-secondary btn-sm">Clear</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0" style="font-size:.87rem">
                <thead class="thead-light">
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Qty</th>
                        <th>Balance After</th>
                        <th>Ref No</th>
                        <th>Issued To</th>
                        <th>Remarks</th>
                        <th>Recorded By</th>
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
                        <td><a href="{{ route('inventory.items.show', $tx->item_id) }}">{{ $tx->item->name }}</a></td>
                        <td class="text-muted">{{ $tx->item->category->name ?? '—' }}</td>
                        <td><span class="badge badge-{{ $tc }}">{{ ucfirst($tx->type) }}</span></td>
                        <td class="{{ $isOut ? 'text-danger' : 'text-success' }} font-weight-bold">
                            {{ $isOut ? '−' : '+' }}{{ $tx->quantity }}
                        </td>
                        <td>{{ $tx->balance_after }}</td>
                        <td class="text-muted">{{ $tx->reference_no ?: '—' }}</td>
                        <td class="text-muted">{{ $tx->issued_to ?: '—' }}</td>
                        <td class="text-muted">{{ Str::limit($tx->remarks, 35) ?: '—' }}</td>
                        <td class="text-muted">{{ $tx->user->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="card-footer">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
