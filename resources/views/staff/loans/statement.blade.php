@extends('adminlte::page')

@section('title', 'Loan Repayment Statement')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Loan Repayment Statement</h1>
        <div>
            <a href="{{ route('staff.loans.show', $loan) }}" class="btn btn-info">
                <i class="fas fa-arrow-left"></i> Loan Details
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-invoice-dollar mr-2"></i> Repayment Schedule
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Loan Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Loan Amount</span>
                            <span class="info-box-number font-weight-bold">TZS {{ number_format($loan->amount_approved ?? $loan->amount_applied, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Interest Rate</span>
                            <span class="info-box-number font-weight-bold">{{ $loan->interest_rate_applied }}% p.a.</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Remaining Balance</span>
                            <span class="info-box-number font-weight-bold text-danger">TZS {{ number_format($loan->remaining_balance, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repayment Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>#</th>
                            <th>Due Date</th>
                            <th>Amount (TZS)</th>
                            <th>Status</th>
                            <th>Paid Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loan->repayments as $repayment)
                        <tr>
                            <td>{{ $repayment->installment_number }}</td>
                            <td>{{ $repayment->due_date->format('d M Y') }}</td>
                            <td>{{ number_format($repayment->amount, 2) }}</td>
                            <td>
                                @if($repayment->status == 'paid')
                                    <span class="badge badge-success">Paid</span>
                                @elseif($repayment->status == 'overdue')
                                    <span class="badge badge-danger">Overdue</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ $repayment->paid_date ? $repayment->paid_date->format('d M Y') : '-' }}</td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No repayment schedule found. Loan may not be disbursed yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i> 
                Overdue installments may incur additional charges as per college policy.
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        @media print {
            .main-header, .main-sidebar, .card-tools, .btn, .alert-info {
                display: none !important;
            }
            .content-wrapper {
                margin-left: 0 !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
        .info-box {
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        .info-box-number {
            font-size: 1.5rem;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
@stop