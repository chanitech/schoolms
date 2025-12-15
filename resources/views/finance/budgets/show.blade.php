@extends('adminlte::page')

@section('title', 'View Budget')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-eye"></i> Budget Details</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">
        <h5><strong>Department:</strong> {{ $budget->department->name ?? '-' }}</h5>
        <p><strong>Submitted By:</strong> {{ $budget->staff->name ?? '-' }}</p>
        <p><strong>Month / Year:</strong> {{ $budget->month ?? '-' }} / {{ $budget->year ?? '-' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($budget->status) }}</p>
        <p><strong>Total Amount:</strong> TZS {{ number_format($budget->total_amount, 2) }}</p>
        <p><strong>Note:</strong> {{ $budget->note ?? '-' }}</p>

        <hr>

        <h5>Budget Items</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Price (TZS)</th>
                    <th>Status</th>
                    <th>Approved By</th>
                    <th>Note / Comment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budget->items as $item)
                <tr>
                    <td>{{ $item->item }}</td>
                    <td>{{ $item->description ?? '-' }}</td>
                    <td>{{ number_format($item->price, 2) }}</td>
                    <td>{{ ucfirst($item->status ?? 'pending') }}</td>
                    <td>{{ $item->approvedBy->name ?? '-' }}</td>
                    <td>{{ $item->note ?? $item->comment ?? '-' }}</td>
                    <td>
                        @if(Auth::user()->role == 'hod' && $item->status == 'approved')
                            <form method="POST" action="{{ route('finance.budgets.withdrawItem', $item->id) }}">
                                @csrf
                                <button class="btn btn-success btn-sm">
                                    <i class="fas fa-hand-holding-usd"></i> Use / Withdraw
                                </button>
                            </form>
                        @elseif($item->invoice)
                            <a href="{{ route('finance.invoices.show', $item->invoice->id) }}" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-file-invoice"></i> Invoice ({{ ucfirst($item->invoice->status) }})
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No items added.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            <a href="{{ route('finance.budgets.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>

            @if(Auth::user()->role == 'do' && $budget->current_step == 'do')
                <a href="{{ route('finance.budgets.approve.form', $budget->id) }}" class="btn btn-primary">
                    <i class="fas fa-check"></i> Approve / Reject Items
                </a>
            @endif
        </div>
    </div>
</div>
@stop
