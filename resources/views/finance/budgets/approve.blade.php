@extends('adminlte::page')

@section('title', 'Approve Budget Items')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-check-circle"></i> Approve Budget Items</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">
        <h5><strong>Budget #{{ $budget->id }} - {{ $budget->month }}/{{ $budget->year }}</strong></h5>
        <p>Department: {{ $budget->department->name ?? '-' }}</p>
        <p>Submitted by: {{ $budget->staff->name ?? '-' }}</p>

        <hr>

        <form action="{{ route('finance.budgets.approve', $budget->id) }}" method="POST">
            @csrf

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Description</th>
                        <th>Price (TZS)</th>
                        <th>Status</th>
                        <th>Note / Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budget->items as $item)
                    <tr>
                        <td>{{ $item->item }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>
                            <select name="items[{{ $item->id }}][status]" class="form-control form-control-sm" required>
                                <option value="approved" @if($item->status=='approved') selected @endif>Approve</option>
                                <option value="rejected" @if($item->status=='rejected') selected @endif>Reject</option>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="items[{{ $item->id }}][note]" 
                                   value="{{ $item->note ?? '' }}" class="form-control form-control-sm" placeholder="Optional note">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                <a href="{{ route('finance.budgets.show', $budget->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check"></i> Submit Approval
                </button>
            </div>
        </form>
    </div>
</div>
@stop
