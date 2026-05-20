@extends('adminlte::page')

@section('title', 'Pending Loan Approvals')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Pending Loan Approvals</h1>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clock mr-2"></i> Loans Awaiting Your Approval
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
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                </div>
            @endif

            @if($loans->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Staff</th>
                                <th>Category</th>
                                <th>Amount (TZS)</th>
                                <th>Installments</th>
                                <th>Salary (TZS)</th>
                                <th>Current Stage</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $loan)
                            <tr>
                                <td>{{ $loan->staff->name }}</td>
                                <td>{{ $loan->category->name }}</td>
                                <td>{{ number_format($loan->amount_applied, 2) }}</td>
                                <td>{{ $loan->installments }} months</td>
                                <td>{{ number_format($loan->salary_at_application, 2) }}</td>
                                <td>
                                    @switch($loan->approval_level)
                                        @case(0) <span class="badge badge-info">Chief Accountant</span> @break
                                        @case(1) <span class="badge badge-primary">Accountant</span> @break
                                        @case(2) <span class="badge badge-warning">Treasurer</span> @break
                                    @endswitch
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success approve-btn" data-id="{{ $loan->id }}">
                                        <i class="fas fa-thumbs-up"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger reject-btn" data-id="{{ $loan->id }}">
                                        <i class="fas fa-thumbs-down"></i> Reject
                                    </button>
                                    <a href="{{ route('staff.loans.show', $loan) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No pending loans for your role.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
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