@extends('adminlte::page')

@section('title', 'Class Bills')

@section('content_header')
    <h1>Class Bills</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        @can('create bills')
        <a href="{{ route('finance.bills.create') }}" class="btn btn-primary">Create Bill</a>
        @endcan
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Class</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bills as $bill)
                    <tr>
                        <td>{{ $bill->title }}</td>
                        <td>{{ $bill->schoolClass?->name ?? 'N/A' }}</td>
                        <td>{{ number_format($bill->amount, 2) }}</td>
                        <td>{{ $bill->status }}</td>
                        <td>{{ $bill->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('finance.bills.show', $bill->id) }}" class="btn btn-sm btn-info">View</a>
                            @can('edit bills')
                            <a href="{{ route('finance.bills.edit', $bill->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            @endcan
                            @can('delete bills')
                            <form action="{{ route('finance.bills.destroy', $bill->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this bill?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $bills->links() }}
    </div>
</div>
@stop
