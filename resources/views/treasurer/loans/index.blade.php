@extends('adminlte::page')

@section('title', 'All Loan Applications')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">All Loan Applications</h1>
        <div>
            <a href="{{ route('treasurer.loans.pending') }}" class="btn btn-warning">
                <i class="fas fa-clock"></i> Pending Approvals
            </a>
            <a href="{{ route('treasurer.loan-categories.index') }}" class="btn btn-info">
                <i class="fas fa-tags"></i> Loan Categories
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i> All Loan Records
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters (optional) -->
            <form method="GET" action="{{ route('treasurer.loans.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Filter by Status</label>
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Filter by Category</label>
                            <select name="category_id" class="form-control" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <a href="{{ route('treasurer.loans.index') }}" class="btn btn-secondary">Clear Filters</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Staff</th>
                            <th>Category</th>
                            <th>Amount (TZS)</th>
                            <th>Status</th>
                            <th>Approval Stage</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->id }}</td>
                            <td>{{ $loan->staff->name ?? 'N/A' }}</td>
                            <td>{{ $loan->category->name ?? 'N/A' }}</td>
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
                                        @default Level {{ $loan->approval_level }}
                                    @endswitch
                                @else
                                    Finalized
                                @endif
                            </td>
                            <td>{{ $loan->application_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('staff.loans.show', $loan) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($loan->status == 'pending')
                                    <button type="button" class="btn btn-sm btn-success approve-btn" data-id="{{ $loan->id }}">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger reject-btn" data-id="{{ $loan->id }}">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                @endif
                                @if($loan->status == 'approved')
                                    <a href="{{ route('treasurer.loans.disburse.form', $loan) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-money-bill-wave"></i> Disburse
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No loan applications found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(method_exists($loans, 'links'))
                <div class="mt-3">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal (reuse from pending page) -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Confirm Approval</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to approve this loan?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="approveForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-ban"></i> Reject Loan</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Reason for rejection <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="3" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        let approveModal = $('#approveModal');
        let rejectModal = $('#rejectModal');
        let approveForm = $('#approveForm');
        let rejectForm = $('#rejectForm');

        $('.approve-btn').click(function() {
            let loanId = $(this).data('id');
            approveForm.attr('action', '{{ url("treasurer/loans") }}/' + loanId + '/approve');
            approveModal.modal('show');
        });

        $('.reject-btn').click(function() {
            let loanId = $(this).data('id');
            rejectForm.attr('action', '{{ url("treasurer/loans") }}/' + loanId + '/reject');
            rejectModal.modal('show');
        });
    });
</script>
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