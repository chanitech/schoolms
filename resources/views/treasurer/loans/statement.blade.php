@extends('adminlte::page')

@section('title', 'Record Repayments')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Record Repayments</h1>
        <a href="{{ route('treasurer.loans.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to All Loans
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-hand-holding-usd mr-2"></i> Repayment Schedule
                <small class="text-muted">Loan #{{ $loan->id }}</small>
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
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Staff</span>
                            <span class="info-box-number font-weight-bold">{{ $loan->staff->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Loan Amount</span>
                            <span class="info-box-number font-weight-bold">TZS {{ number_format($loan->amount_approved ?? $loan->amount_applied, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Interest Rate</span>
                            <span class="info-box-number font-weight-bold">{{ $loan->interest_rate_applied }}% p.a.</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <div class="info-box-content">
                            <span class="info-box-text text-muted">Remaining Balance</span>
                            <span class="info-box-number font-weight-bold text-danger">TZS {{ number_format($loan->remaining_balance, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">×</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">×</button>
                </div>
            @endif

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
                            <th>Payment Reference</th>
                            <th>Action</th>
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
                                <td>{{ $repayment->payment_reference ?? '-' }}</td>
                                <td>
                                    @if($repayment->status == 'pending')
                                        <button type="button" class="btn btn-sm btn-success pay-btn"
                                                data-id="{{ $repayment->id }}"
                                                data-loan="{{ $loan->id }}">
                                            <i class="fas fa-money-bill-wave"></i> Record Payment
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No repayment schedule found. Loan may not be disbursed yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i> 
                Only pending installments can be marked as paid. Overdue installments should be paid immediately.
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-receipt"></i> Record Repayment</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="paymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="payment_reference">Payment Reference / Receipt Number</label>
                        <input type="text" name="payment_reference" id="payment_reference" class="form-control" placeholder="e.g., REC-2026-001">
                        <small class="form-text text-muted">Optional – for tracking purposes.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        let paymentModal = $('#paymentModal');
        let paymentForm = $('#paymentForm');

        $('.pay-btn').click(function() {
            let repaymentId = $(this).data('id');
            let loanId = $(this).data('loan');
            let actionUrl = '{{ url("treasurer/loans") }}/' + loanId + '/repayments/' + repaymentId + '/pay';
            paymentForm.attr('action', actionUrl);
            paymentModal.modal('show');
        });
    });
</script>
@stop

@section('css')
    <style>
        .info-box {
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        .info-box-number {
            font-size: 1.25rem;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-sm i {
            margin-right: 4px;
        }
    </style>
@stop