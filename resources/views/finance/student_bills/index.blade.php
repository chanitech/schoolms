@extends('adminlte::page')

@section('title', 'Student Bills')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">Student Bills</h1>
    <a href="{{ route('finance.student_bills.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Assign Custom Bill
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
                    <th>Description</th>
                    <th>Total Amount (TZS)</th>
                    <th>Paid (TZS)</th>
                    <th>Balance (TZS)</th>
                    <th>Status</th>
                    <th>Created On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentBills as $sb)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $sb->student->full_name ?? 'N/A' }} ({{ $sb->student->admission_no ?? '' }})</td>
                    <td>{{ $sb->notes ?? 'Standard Fee' }}</td>
                    <td>{{ number_format($sb->total_amount, 2) }}</td>
                    <td>{{ number_format($sb->amount_paid, 2) }}</td>
                    <td>{{ number_format($sb->balance, 2) }}</td>
                    <td>
                        @php
                            $statusClass = match($sb->status) {
                                'paid' => 'success',
                                'partial' => 'info',
                                default => 'warning'
                            };
                            $statusLabel = match($sb->status) {
                                'paid' => 'Paid',
                                'partial' => 'Partial',
                                default => 'Unpaid'
                            };
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td>{{ $sb->created_at ? $sb->created_at->format('d M, Y') : '-' }}</td>
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
            "order": [[0, 'desc']]
        });
    });
</script>
@endpush
@stop