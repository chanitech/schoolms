@extends('adminlte::page')

@section('title', 'Bill Details #' . $studentBill->id)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-file-invoice-dollar"></i> Bill Details</h1>
        <a href="{{ route('finance.student-bills.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Bills
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow">
                <div class="card-header">
                    <h3 class="card-title">Bill #{{ $studentBill->id }}</h3>
                </div>
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

                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 30%;">Student</th>
                            <td>
                                @if($studentBill->student)
                                    {{ $studentBill->student->first_name }} {{ $studentBill->student->last_name }}
                                    ({{ $studentBill->student->admission_no ?? 'N/A' }})
                                @else
                                    <span class="text-muted">Student record deleted</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Bill Number</th>
                            <td>{{ $studentBill->bill_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $studentBill->description ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Total Amount</th>
                            <td><strong>TZS {{ number_format($studentBill->total_amount ?? $studentBill->amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Amount Paid</th>
                            <td>TZS {{ number_format($studentBill->amount_paid ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Balance</th>
                            <td>
                                <span class="badge badge-{{ ($studentBill->balance ?? ($studentBill->total_amount - $studentBill->amount_paid)) > 0 ? 'danger' : 'success' }}">
                                    TZS {{ number_format($studentBill->balance ?? ($studentBill->total_amount - $studentBill->amount_paid), 2) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge badge-{{ $studentBill->status === 'paid' ? 'success' : ($studentBill->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($studentBill->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Due Date</th>
                            <td>{{ optional($studentBill->due_date)->format('d M Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Notes</th>
                            <td>{{ $studentBill->notes ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $studentBill->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    </table>

                    <div class="mt-4">
                        <a href="{{ route('finance.student-bills.edit', $studentBill) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('finance.student-bills.destroy', $studentBill) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this bill?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                        <a href="{{ route('finance.student-bills.index') }}" class="btn btn-default ml-2">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>

            {{-- Related Payments --}}
            @if(isset($studentBill->payments) && $studentBill->payments->isNotEmpty())
                <div class="card card-outline card-success shadow mt-4">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-money-check-alt"></i> Payments</h3>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Amount (TZS)</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($studentBill->payments as $payment)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->created_at->format('d M Y') }}</td>
                                        <td>{{ $payment->method ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('finance.payments.receipt', $payment->id) }}" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-receipt"></i> Receipt
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@stop