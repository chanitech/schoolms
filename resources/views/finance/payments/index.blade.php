@extends('adminlte::page')

@section('title', 'Payments')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-money-bill-wave"></i> Payments</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="mb-3">
            <a href="{{ route('finance.payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Record Payment
            </a>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Bill</th>
                    <th>Amount Paid</th>
                    <th>Payment Method</th>
                    <th>Date</th>
                    <th>Recorded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                        <td>{{ $payment->studentBill->student->full_name ?? 'N/A' }}</td>
                        <td>{{ $payment->studentBill->bill->title ?? 'N/A' }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ $payment->payment_method ?? '-' }}</td>
                        <td>{{ $payment->payment_date->format('d M Y') }}</td>
                        <td>{{ $payment->recordedBy->name ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('finance.payments.receipt', $payment->id) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-receipt"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No payments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $payments->links() }}
        </div>

    </div>
</div>
@stop
