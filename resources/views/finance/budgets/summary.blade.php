@extends('adminlte::page')

@section('title', 'Budget Summary')

@section('content_header')
<h1 class="mb-3"><i class="fas fa-chart-bar"></i> Budget Summary</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">

        {{-- Filters --}}
        <form action="{{ route('finance.budgets.summary') }}" method="GET" class="form-inline mb-3">
            <div class="form-group mr-2">
                <select name="department_id" class="form-control">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mr-2">
                <input type="number" name="month" class="form-control" placeholder="Month" value="{{ request('month') }}">
            </div>

            <div class="form-group mr-2">
                <input type="number" name="year" class="form-control" placeholder="Year" value="{{ request('year') }}">
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        </form>

        {{-- Budgets Table --}}
        <table class="table table-bordered table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Submitted By</th>
                    <th>Month/Year</th>
                    <th>Total Amount</th>
                    <th>Approved Items</th>
                    <th>Rejected Items</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budgets as $budget)
                <tr>
                    <td>{{ $budget->id }}</td>
                    <td>{{ $budget->department->name }}</td>
                    <td>{{ $budget->staff->name }}</td>
                    <td>{{ $budget->month }}/{{ $budget->year }}</td>
                    <td>{{ number_format($budget->items->sum('price'), 2) }}</td>
                    <td>{{ $budget->items->where('status', 'approved')->count() }}</td>
                    <td>{{ $budget->items->where('status', 'rejected')->count() }}</td>
                    <td>
                        @if($budget->status == 'approved')
                            <span class="badge badge-success">{{ ucfirst($budget->status) }}</span>
                        @elseif($budget->status == 'partially_approved')
                            <span class="badge badge-warning">{{ ucfirst($budget->status) }}</span>
                        @elseif($budget->status == 'declined')
                            <span class="badge badge-danger">{{ ucfirst($budget->status) }}</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($budget->status) }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('finance.budgets.show', $budget->id) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
                        @if(auth()->user()->can('approve budget') && $budget->status == 'pending')
                            <a href="{{ route('finance.budgets.approve', $budget->id) }}" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $budgets->links() }}
        </div>
    </div>
</div>
@stop
