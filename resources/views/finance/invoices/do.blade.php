@extends('adminlte::page')

@section('title', 'DO Invoice Approvals')

@section('content_header')
    <h1>Invoices Pending DO Approval</h1>
@stop

@section('content')
<div class="container-fluid">

    @include('partials.flash')
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        </div>
    @endif

    <div class="card card-warning card-outline">
        <div class="card-header"><h3 class="card-title">Pending Invoices</h3></div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Budget Item</th>
                        <th>Budget</th>
                        <th>Amount (TZS)</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoices as $invoice)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $invoice->budgetItem->item ?? '-' }}</td>
                        <td>#{{ $invoice->budgetItem->budget->id ?? '-' }}</td>
                        <td>{{ number_format($invoice->amount, 2) }}</td>
                        <td>
                            <span class="badge badge-warning">{{ ucfirst(str_replace('_',' ',$invoice->status)) }}</span>
                        </td>
                        <td>
                            <form action="{{ route('finance.invoices.approve', $invoice->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="approved_by_do">
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-danger reject-btn"
                                data-action="{{ route('finance.invoices.approve', $invoice->id) }}"
                                data-item="{{ $invoice->budgetItem->item ?? 'Invoice #' . $invoice->id }}">
                                Reject
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Reject with reason --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle"></i> Reject Invoice</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="rejected_by_do">
                <div class="modal-body">
                    <p id="rejectItemLabel" class="font-weight-bold"></p>
                    <div class="form-group">
                        <label for="reject-notes">Reason for rejection <span class="text-danger">*</span></label>
                        <textarea name="notes" id="reject-notes" rows="3" class="form-control" required maxlength="1000"
                            placeholder="Write the reason for rejecting this invoice — it will be visible to the HOD and the Finance Office…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.reject-btn').click(function() {
            $('#rejectForm').attr('action', $(this).data('action'));
            $('#rejectItemLabel').text('Item: ' + $(this).data('item'));
            $('#rejectModal').modal('show');
        });
    });
</script>
@stop
