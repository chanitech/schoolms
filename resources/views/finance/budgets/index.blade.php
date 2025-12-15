@extends('adminlte::page')

@section('title', 'Budgets')

@section('content_header')
    <h1 class="mb-4">Budget List</h1>
@endsection

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">All Budgets</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Month / Year</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Step</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($budgets as $budget)
                    <tr>
                        <td>{{ $budget->id }}</td>
                        <td>{{ $budget->department->name ?? 'N/A' }}</td>
                        <td>{{ $budget->month }} / {{ $budget->year }}</td>
                        <td>{{ number_format($budget->total_amount, 2) }} TZS</td>
                        <td>
                            @if($budget->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($budget->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($budget->status == 'partially_approved')
                                <span class="badge badge-info">Partially Approved</span>
                            @elseif($budget->status == 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-secondary">Unknown</span>
                            @endif
                        </td>
                        <td>
                            @if($budget->current_step == 'hod')
                                <span class="badge badge-primary">HOD</span>
                            @elseif($budget->current_step == 'do')
                                <span class="badge badge-dark">Director</span>
                            @else
                                <span class="badge badge-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('finance.budgets.show', $budget->id) }}" class="btn btn-sm btn-info">View</a>

                            @can('edit budgets')
                                <a href="{{ route('finance.budgets.edit', $budget->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @endcan

                            @can('delete budgets')
                                <form action="{{ route('finance.budgets.destroy', $budget->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this budget?')">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No budgets found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer clearfix">
        {{ $budgets->links() }}
    </div>
</div>
@endsection
