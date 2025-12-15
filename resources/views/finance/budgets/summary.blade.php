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

            {{-- Department Filter --}}
            <div class="form-group mr-2">
                <select name="department_id" class="form-control">
                    @if(auth()->user()->hasRole('hod'))
                        <option value="{{ auth()->user()->department_id }}" selected>
                            {{ auth()->user()->department->name }}
                        </option>
                    @else
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Month Filter --}}
            <div class="form-group mr-2">
                <select name="month" class="form-control">
                    <option value="">All Months</option>
                    @foreach($months as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ $m }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Year Filter --}}
            <div class="form-group mr-2">
                <input type="number" name="year" class="form-control" placeholder="Year" value="{{ request('year') }}">
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        </form>

        {{-- Budgets Table --}}
        @php
            $statusClasses = [
                'approved' => 'success',
                'partially_approved' => 'warning',
                'declined' => 'danger',
                'pending' => 'secondary'
            ];
        @endphp

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
                    <td>{{ $budget->department->name ?? 'N/A' }}</td>
                    <td>{{ $budget->staff->first_name ?? 'N/A' }} {{ $budget->staff->last_name ?? '' }}</td>
                    <td>{{ $budget->month }}/{{ $budget->year }}</td>
                    <td>{{ number_format($budget->items->where('status', 'approved')->sum('price'), 2) }}</td>
                    <td>{{ $budget->items->where('status', 'approved')->count() }}</td>
                    <td>{{ $budget->items->where('status', 'rejected')->count() }}</td>
                    <td>
                        <span class="badge badge-{{ $statusClasses[$budget->status] ?? 'secondary' }}">
                            {{ ucfirst($budget->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('finance.budgets.show', $budget->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        @if(auth()->user()->can('approve budgets') && $budget->status == 'pending')
                            <a href="{{ route('finance.budgets.approve', $budget->id) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-check"></i> Approve
                            </a>
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
