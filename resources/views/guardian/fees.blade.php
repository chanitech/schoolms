@extends('adminlte::page')

@section('title', 'Financial Details')

@section('content_header')
    <h1>Financial Details</h1>
    <a href="{{ route('guardian.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
@stop

@section('content')
<div class="container-fluid">
    @if($students->isEmpty())
        <div class="callout callout-info">
            <h5>No children linked.</h5>
        </div>
    @else
        @foreach($students as $student)
            <div class="card card-outline card-success mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <img src="{{ $student->photo ? asset('storage/'.$student->photo) : asset('images/default-avatar.png') }}"
                             class="img-circle mr-2" style="width:30px; height:30px; object-fit:cover;">
                        {{ $student->full_name }} ({{ $student->admission_no }})
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Fees Summary -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-file-invoice-dollar"></i> School Fees</h5>
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Outstanding Balance</span>
                                    <span class="info-box-number">TZS {{ number_format($student->outstanding, 2) }}</span>
                                    @if($student->outstanding > 0)
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-danger" style="width: 100%"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Paid</span>
                                    <span class="info-box-number">TZS {{ number_format($student->total_paid, 2) }}</span>
                                </div>
                            </div>

                            <h6 class="mt-3">Recent Bills</h6>
                            @if($student->studentBills->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Bill Name</th>
                                                <th>Amount</th>
                                                <th>Paid</th>
                                                <th>Due Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($student->studentBills->take(5) as $studentBill)
                                                <tr>
                                                    <td>{{ $studentBill->bill->title ?? 'N/A' }}</td>
                                                    <td>{{ number_format($studentBill->total_amount, 2) }}</td>
                                                    <td>{{ number_format($studentBill->amount_paid, 2) }}</td>
                                                    <td>{{ $studentBill->due_date ? $studentBill->due_date->format('d M Y') : 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No bills found.</p>
                            @endif
                        </div>

                        <!-- Pocket Money & Payments -->
                        <div class="col-md-6">
                            <h5><i class="fas fa-piggy-bank"></i> Pocket Money</h5>
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Current Balance</span>
                                    <span class="info-box-number">TZS {{ number_format($student->pocket_balance, 2) }}</span>
                                </div>
                            </div>
                            @if($student->pocketTransactions->isNotEmpty())
                                <h6>Recent Pocket Transactions</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($student->pocketTransactions->take(5) as $txn)
                                                <tr>
                                                    <td>{{ $txn->created_at->format('d M Y') }}</td>
                                                    <td>
                                                        @if($txn->type == 'deposit')
                                                            <span class="badge badge-success">Deposit</span>
                                                        @elseif($txn->type == 'withdrawal')
                                                            <span class="badge badge-danger">Withdrawal</span>
                                                        @else
                                                            {{ $txn->type }}
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($txn->amount, 2) }}</td>
                                                    <td>{{ number_format($txn->balance_after, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No pocket money transactions.</p>
                            @endif

                            <h5 class="mt-3"><i class="fas fa-receipt"></i> Payment History</h5>
                            @if($student->payments->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Method</th>
                                                <th>Reference</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($student->payments->take(5) as $payment)
                                                <tr>
                                                    <td>{{ $payment->created_at->format('d M Y') }}</td>
                                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                                    <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                                                    <td>
                                                        <a href="{{ route('guardian.payment.receipt', $payment) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-receipt"></i> Receipt
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No payment records found.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@stop

@push('css')
<style>
    .info-box {
        min-height: 60px;
        margin-bottom: 10px;
    }
    .table-sm td, .table-sm th {
        padding: .3rem;
    }
</style>
@endpush