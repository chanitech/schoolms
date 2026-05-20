@extends('adminlte::page')

@section('title', 'My Loans')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">My Loan Applications</h1>
        <a href="{{ route('staff.loans.create') }}" class="btn btn-success">
            <i class="fas fa-plus-circle"></i> Apply for New Loan
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-hand-holding-usd mr-2"></i> Loan History
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Loan Type</th>
                            <th>Amount (TZS)</th>
                            <th>Status</th>
                            <th>Approval Stage</th>
                            <th>Application Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->category->name }}</td>
                            <td>{{ number_format($loan->amount_applied, 2) }}</td>
                            <td>
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
                            </td>
                            <td>
                                @if($loan->status == 'pending')
                                    @switch($loan->approval_level)
                                        @case(0) Chief Accountant @break
                                        @case(1) Accountant @break
                                        @case(2) Treasurer @break
                                        @default Waiting
                                    @endswitch
                                @elseif(in_array($loan->status, ['approved', 'active']))
                                    Fully Approved
                                @else
                                    --
                                @endif
                            </td>
                            <td>{{ $loan->application_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('staff.loans.show', $loan) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($loan->status == 'active')
                                    <a href="{{ route('staff.loans.statement', $loan) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-invoice"></i> Statement
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No loan applications found. 
                                    <a href="{{ route('staff.loans.create') }}">Apply now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Optional pagination links if using paginate -->
            @if(method_exists($loans, 'links'))
                <div class="mt-3">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-sm i {
            margin-right: 4px;
        }
    </style>
@stop