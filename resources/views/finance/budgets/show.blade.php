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
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No items added.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('finance.budgets.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>

        @if($budget->status == 'pending')
            <a href="{{ route('finance.budgets.approve.form', $budget->id) }}" class="btn btn-primary">
                <i class="fas fa-check"></i> Approve / Reject
            </a>
        @endif
    </div>
</div>
@stop
