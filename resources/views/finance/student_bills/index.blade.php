@extends('adminlte::page')

@section('title', 'Student Bills')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-file-invoice-dollar"></i> Student Bills</h1>
        <a href="{{ route('finance.student-bills.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Bill
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow">
        <div class="card-body">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="student-bills-table">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Bill No</th>
                            <th>Amount (TZS)</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentBills as $bill)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($bill->student)->first_name }} {{ optional($bill->student)->last_name }}</td>
                                <td>{{ $bill->bill_number ?? 'N/A' }}</td>
                                <td>{{ number_format($bill->amount, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $bill->status === 'paid' ? 'success' : ($bill->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($bill->status) }}
                                    </span>
                                </td>
                                <td>{{ $bill->due_date ? $bill->due_date->format('d M Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('finance.student-bills.show', $bill) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="{{ route('finance.student-bills.edit', $bill) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('finance.student-bills.destroy', $bill) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this bill?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No student bills found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#student-bills-table').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 25,
        });
    });
</script>
@stop