@extends('adminlte::page')

@section('title', 'Approve Budget')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-check-circle"></i> Budget Approval</h1>
@stop

@section('content')
<form action="{{ route('finance.budgets.approve', $budget->id) }}" method="POST">
    @csrf

    <div class="card shadow">
        <div class="card-body">
            <h5>Budget Details</h5>
            <p><strong>Department:</strong> {{ $budget->department->name }}</p>
            <p><strong>Month / Year:</strong> {{ $budget->month }} / {{ $budget->year }}</p>
            <p><strong>Submitted By:</strong> {{ $budget->staff->name }}</p>
            <p><strong>Total Amount:</strong> TZS {{ number_format($budget->total_amount, 2) }}</p>
            @if($budget->note)
                <p><strong>Note:</strong> {{ $budget->note }}</p>
            @endif

            <hr>

            <h5>Budget Items</h5>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Price (TZS)</th>
                        <th>Status</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budget->items as $item)
                    <tr>
                        <td>{{ $item->item }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>
                            <select name="items[{{ $item->id }}][status]" class="form-control" required>
                                <option value="approved" {{ $item->status == 'approved' ? 'selected' : '' }}>Approve</option>
                                <option value="rejected" {{ $item->status == 'rejected' ? 'selected' : '' }}>Reject</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="items[{{ $item->id }}][note]" class="form-control" value="{{ $item->note }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="d-flex justify-content-between mt-3">
                <a href="{{ route('finance.budgets.show', $budget->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Submit Approval
                </button>
            </div>
        </div>
    </div>
</form>
@stop
