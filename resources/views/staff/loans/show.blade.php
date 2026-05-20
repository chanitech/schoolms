@extends('adminlte::page')

@section('title', 'Loan Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Loan Application Details</h1>
        <a href="{{ route('staff.loans.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to My Loans
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-invoice-dollar mr-2"></i> Loan Information
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Loan Info Grid -->
            <div class="row">
                <a href="{{ route('staff.loans.download-statement', $loan) }}" class="btn btn-sm btn-primary">
    <i class="fas fa-download"></i> Download PDF
</a>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-5 text-muted">Loan Type</dt>
                        <dd class="col-sm-7 font-weight-bold">{{ $loan->category->name }}</dd>

                        <dt class="col-sm-5 text-muted">Amount Applied</dt>
                        <dd class="col-sm-7">TZS {{ number_format($loan->amount_applied, 2) }}</dd>

                        <dt class="col-sm-5 text-muted">Interest Rate</dt>
                        <dd class="col-sm-7">{{ $loan->interest_rate_applied }}% p.a.</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-5 text-muted">Installments</dt>
                        <dd class="col-sm-7">{{ $loan->installments }} months</dd>

                        <dt class="col-sm-5 text-muted">Status</dt>
                        <dd class="col-sm-7">
                            @php
                                $statusBadge = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'active' => 'primary',
                                    'closed' => 'secondary',
                                ];
                            @endphp
                            <span class="badge badge-{{ $statusBadge[$loan->status] ?? 'secondary' }}">
                                {{ ucfirst($loan->status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-5 text-muted">Application Date</dt>
                        <dd class="col-sm-7">{{ $loan->application_date->format('d M Y') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Approval Chain Progress (only for pending loans) -->
            @if($loan->status == 'pending')
                <div class="mt-4">
                    <h5 class="font-weight-bold">Approval Progress</h5>
                    <div class="row text-center mt-3">
                        @php
                            $levels = [
                                1 => 'Chief Accountant',
                                2 => 'Accountant',
                                3 => 'Treasurer',
                            ];
                        @endphp
                        @foreach($levels as $level => $name)
                            <div class="col-md-4">
                                <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center 
                                    {{ $loan->approval_level >= $level ? 'bg-success' : 'bg-secondary' }} text-white"
                                    style="width: 50px; height: 50px; font-size: 20px;">
                                    {{ $loan->approval_level >= $level ? '✓' : $level }}
                                </div>
                                <p class="mt-2 mb-0">{{ $name }}</p>
                                @if($loan->approval_level >= $level)
                                    <small class="text-success">Approved</small>
                                @elseif($loan->approval_level == $level - 1)
                                    <small class="text-warning">Pending</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Rejection Reason -->
            @if($loan->status == 'rejected')
                <div class="alert alert-danger mt-4">
                    <i class="fas fa-ban"></i> <strong>Rejection Reason:</strong>
                    <p class="mb-0">{{ $loan->rejection_reason }}</p>
                </div>
            @endif

            <!-- Active Loan Details & Statement Link -->
            @if($loan->status == 'active' && $loan->disbursement_date)
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Disbursement Date:</strong> {{ $loan->disbursement_date->format('d M Y') }}<br>
                    <strong>Expected End Date:</strong> {{ $loan->expected_end_date->format('d M Y') }}
                </div>
                <div class="mt-3 text-right">
                    <a href="{{ route('staff.loans.statement', $loan) }}" class="btn btn-success">
                        <i class="fas fa-file-invoice"></i> View Loan Statement
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .rounded-circle {
            width: 50px;
            height: 50px;
            line-height: 50px;
            font-size: 20px;
        }
        dl.row {
            margin-bottom: 0;
        }
        dt, dd {
            margin-bottom: 8px;
        }
    </style>
@stop