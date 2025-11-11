@extends('adminlte::page')

@section('title', 'Student Bills')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">Student Bills</h1>
    <a href="{{ route('finance.student_bills.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Assign Bill
    </a>
</div>
@stop

@section('content')
@include('partials.alerts')

<div class="card shadow-sm">
    <div class="card-body table-responsive">
        <table class="table table-striped table-bordered table-hover" id="studentBillsTable">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Bill</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Paid On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentBills as $sb)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $sb->student->full_name ?? '-' }}</td>
                    <td>{{ $sb->bill->name ?? '-' }}</td>
                    <td>{{ number_format($sb->amount, 2) }}</td>
                    <td>
                        <span class="badge {{ $sb->is_paid ? 'bg-success' : 'bg-warning' }}">
                            {{ $sb->is_paid ? 'Paid' : 'Pending' }}
                        </span>
                    </td>
                    <td>{{ $sb->paid_at ? $sb->paid_at->format('d M, Y') : '-' }}</td>
                    <td>
                        <a href="{{ route('finance.student_bills.edit', $sb) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('finance.student_bills.destroy', $sb) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        $('#studentBillsTable').DataTable({
            "responsive": true,
            "autoWidth": false,
        });
    });
</script>
@endpush
@stop
