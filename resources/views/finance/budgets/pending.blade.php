@extends('adminlte::page')

@section('title', 'Pending Budget Approvals')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-clock"></i> Pending Budget Approvals</h1>
@stop

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Pending</span>
                <span class="info-box-number">{{ $pendingBudgets->count() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Pending Budgets List</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Department</th>
                    <th>Amount</th>
                    <th>Submitted By</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingBudgets as $budget)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $budget->title }}</td>
                    <td>{{ $budget->department->name ?? '-' }}</td>
                    <td>{{ number_format($budget->amount, 2) }}</td>
                    <td>{{ $budget->submitted_by->name ?? '-' }}</td>
                    <td>{{ $budget->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('finance.budgets.show', $budget) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('finance.budgets.approve.form', $budget) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-check"></i> Approve
                        </a>
                        <form action="{{ route('finance.budgets.approve.item', $budget) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="action" value="decline">
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Decline this budget?')">
                                <i class="fas fa-times"></i> Decline
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach

                @if($pendingBudgets->isEmpty())
                <tr>
                    <td colspan="7" class="text-center text-muted">No pending budgets found.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@stop
