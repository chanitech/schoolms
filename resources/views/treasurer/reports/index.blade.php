@extends('adminlte::page')

@section('title', 'Reports Center')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-file-signature mr-2"></i>Reports Center — Signed Exports</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="callout callout-info py-2">
        <i class="fas fa-shield-alt mr-1"></i>
        Every exported PDF carries the full approval trail and a <strong>digital signature block</strong> with a
        verification code. Anyone can confirm a copy's authenticity at
        <a href="{{ route('verify.document') }}" target="_blank">{{ route('verify.document') }}</a>.
    </div>

    <div class="row">
        @php
            $reports = [
                ['route' => 'treasurer.reports.loans', 'title' => 'Staff Loans', 'icon' => 'fa-hand-holding-usd',
                 'desc' => 'Applications with the chief accountant, accountant, and treasurer approval chain.',
                 'statuses' => ['pending', 'approved', 'rejected', 'active', 'closed'], 'can' => null],
                ['route' => 'treasurer.reports.procurement', 'title' => 'Procurement Requests', 'icon' => 'fa-shopping-cart',
                 'desc' => 'Requests with treasurer approval, Head Master sign-off, and cashier disbursement.',
                 'statuses' => ['pending', 'treasurer_approved', 'approved', 'completed', 'rejected', 'returned'], 'can' => null],
                ['route' => 'treasurer.reports.stock-requests', 'title' => 'Stock Requests', 'icon' => 'fa-boxes',
                 'desc' => 'Storekeeper needs and the procurement officer\'s review decisions.',
                 'statuses' => ['pending', 'approved', 'rejected'], 'can' => null],
                ['route' => 'treasurer.reports.expenses', 'title' => 'Expense Log', 'icon' => 'fa-receipt',
                 'desc' => 'Disbursed expenses with recording cashier and linked procurement.',
                 'statuses' => [], 'can' => null],
                ['route' => 'treasurer.reports.payments', 'title' => 'Payments Reconciliation', 'icon' => 'fa-money-check-alt',
                 'desc' => 'Student payments with who recorded and who verified each one.',
                 'statuses' => ['pending', 'verified', 'flagged'], 'can' => 'view payments'],
                ['route' => 'treasurer.reports.invoices', 'title' => 'Invoices', 'icon' => 'fa-file-invoice',
                 'desc' => 'Budget-item invoices with Head Master decisions and payment records.',
                 'statuses' => ['pending', 'approved_by_do', 'rejected_by_do', 'paid'], 'can' => 'view invoices'],
                ['route' => 'treasurer.reports.budgets', 'title' => 'Budgets', 'icon' => 'fa-file-signature',
                 'desc' => 'Budget items with the Head Master\'s per-item decisions and notes.',
                 'statuses' => ['pending', 'approved', 'partially_approved', 'rejected'], 'can' => 'view budgets'],
            ];
        @endphp

        @foreach($reports as $r)
            @if(!$r['can'] || auth()->user()->can($r['can']))
            <div class="col-lg-6 mb-3">
                <div class="card card-outline card-dark h-100 shadow-sm">
                    <div class="card-header py-2">
                        <h3 class="card-title"><i class="fas {{ $r['icon'] }} mr-2"></i><strong>{{ $r['title'] }}</strong></h3>
                    </div>
                    <div class="card-body py-2">
                        <p class="text-muted small mb-2">{{ $r['desc'] }}</p>
                        <form method="GET" action="{{ route($r['route']) }}" class="form-inline">
                            @if(count($r['statuses']))
                            <select name="status" class="form-control form-control-sm mr-2 mb-1">
                                <option value="">All statuses</option>
                                @foreach($r['statuses'] as $st)
                                    <option value="{{ $st }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                                @endforeach
                            </select>
                            @endif
                            <input type="date" name="from" class="form-control form-control-sm mr-1 mb-1" title="From">
                            <input type="date" name="to" class="form-control form-control-sm mr-2 mb-1" title="To">
                            <button type="submit" class="btn btn-sm btn-dark mb-1">
                                <i class="fas fa-file-pdf mr-1"></i> Export Signed PDF
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

        {{-- Inventory reports --}}
        @if(auth()->user()->can('manage stock') || auth()->user()->can('view inventory') || auth()->user()->can('manage settings'))
        <div class="col-lg-6 mb-3">
            <div class="card card-outline card-dark h-100 shadow-sm">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-warehouse mr-2"></i><strong>Stock Items (Inventory on Hand)</strong></h3>
                </div>
                <div class="card-body py-2">
                    <p class="text-muted small mb-2">Every item with quantities, minimum levels, unit costs, and total stock value.</p>
                    <form method="GET" action="{{ route('treasurer.reports.inventory-items') }}" class="form-inline">
                        <select name="category_id" class="form-control form-control-sm mr-2 mb-1">
                            <option value="">All categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <select name="stock" class="form-control form-control-sm mr-2 mb-1">
                            <option value="">All stock levels</option>
                            <option value="low">Low stock only</option>
                            <option value="out">Out of stock only</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-dark mb-1">
                            <i class="fas fa-file-pdf mr-1"></i> Export Signed PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card card-outline card-dark h-100 shadow-sm">
                <div class="card-header py-2">
                    <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i><strong>Stock Transactions (Ledger)</strong></h3>
                </div>
                <div class="card-body py-2">
                    <p class="text-muted small mb-2">Full movement ledger — purchases, issues, returns, damage — with running balances and who recorded each.</p>
                    <form method="GET" action="{{ route('treasurer.reports.inventory-transactions') }}" class="form-inline">
                        <select name="type" class="form-control form-control-sm mr-2 mb-1">
                            <option value="">All types</option>
                            @foreach(['purchase', 'issue', 'return', 'adjustment', 'damage', 'disposal'] as $t)
                                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="from" class="form-control form-control-sm mr-1 mb-1" title="From">
                        <input type="date" name="to" class="form-control form-control-sm mr-2 mb-1" title="To">
                        <button type="submit" class="btn btn-sm btn-dark mb-1">
                            <i class="fas fa-file-pdf mr-1"></i> Export Signed PDF
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@stop
