@extends('adminlte::page')

@section('title', 'Loan Disbursement')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Loan Disbursement</h1>
        <a href="{{ route('treasurer.loans.pending') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Pending
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-money-bill-wave mr-2"></i> Disburse Loan Funds
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Loan Summary Card -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h5 class="card-title text-primary">
                        <i class="fas fa-info-circle"></i> Loan Summary
                    </h5>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Staff:</strong> {{ $loan->staff->name }}<br>
                            <strong>Approved Amount:</strong> TZS {{ number_format($loan->amount_approved ?? $loan->amount_applied, 2) }}<br>
                            <strong>Interest Rate:</strong> {{ $loan->interest_rate_applied }}% p.a.
                        </div>
                        <div class="col-md-6">
                            <strong>Installments:</strong> {{ $loan->installments }} months<br>
                            <strong>Application Date:</strong> {{ $loan->application_date->format('d M Y') }}<br>
                            <strong>Approval Date:</strong> {{ $loan->approval_date ? $loan->approval_date->format('d M Y') : 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disbursement Form -->
            <form action="{{ route('treasurer.loans.disburse', $loan) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="disbursement_date">Disbursement Date <span class="text-danger">*</span></label>
                    <input type="date" name="disbursement_date" id="disbursement_date" 
                           class="form-control @error('disbursement_date') is-invalid @enderror" 
                           value="{{ old('disbursement_date', date('Y-m-d')) }}" required>
                    @error('disbursement_date')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Important:</strong> Once disbursed, the repayment schedule will be generated automatically 
                    and the loan status will change to <strong>Active</strong>. This action cannot be undone.
                </div>

                <div class="form-group text-right">
                    <a href="{{ route('treasurer.loans.pending') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Confirm Disbursement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .bg-light .card-title {
            font-weight: 600;
        }
        .alert-warning i {
            margin-right: 8px;
        }
    </style>
@stop