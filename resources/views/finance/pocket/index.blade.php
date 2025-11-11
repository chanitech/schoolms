@extends('adminlte::page')

@section('title', 'Pocket Money Transactions')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-wallet"></i> Pocket Money Transactions</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Filter Form -->
        <form method="GET" action="{{ route('finance.pocket.index') }}" class="mb-4 row g-2">
            <div class="col-md-4">
                <select name="student_id" class="form-control">
                    <option value="">-- Filter by Student --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_no }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('finance.pocket.create') }}" class="btn btn-success w-100"><i class="fas fa-plus"></i> New Transaction</a>
            </div>
        </form>

        <!-- Transactions Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                        <th>Performed By</th>
                        <th>Date</th>
                        <th>Note</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td>{{ $loop->iteration + ($transactions->currentPage() - 1) * $transactions->perPage() }}</td>
                        <td>{{ $tx->student->first_name }} {{ $tx->student->last_name }}</td>
                        <td>
                            <span class="badge {{ $tx->type == 'deposit' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($tx->type) }}
                            </span>
                        </td>
                        <td>{{ number_format($tx->amount, 2) }}</td>
                        <td>{{ number_format($tx->balance_after, 2) }}</td>
                        <td>{{ $tx->performedBy->name ?? 'System' }}</td>
                        <td>{{ $tx->created_at->format('d M, Y H:i') }}</td>
                        <td>{{ $tx->note ?? '-' }}</td>
                        <td>
                            <a href="{{ route('finance.pocket.show', $tx->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No transactions found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@stop
