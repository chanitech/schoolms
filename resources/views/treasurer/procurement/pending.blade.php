@extends('adminlte::page')

@section('title', 'Pending Procurement Approvals')

@section('content_header')
    <h1 class="m-0 text-dark">Pending Procurement Approvals</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ $errors->first() }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Estimated Cost</th>
                            <th>Requested By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->item }}{{ $request->threshold_flag ? ' ⚠️' : '' }}</td>
                            <td>{{ $request->quantity }}</td>
                            <td>{{ number_format($request->estimated_cost, 2) }}</td>
                            <td>{{ $request->requestedBy->name ?? '—' }}</td>
                            <td>
                                <form action="{{ route('treasurer.procurement.approve', $request) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                                </form>
                                <button type="button" class="btn btn-sm btn-danger reject-btn"
                                    data-action="{{ route('treasurer.procurement.reject', $request) }}"
                                    data-item="{{ $request->item }}">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Nothing pending approval.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Reject with reason --}}
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle"></i> Reject Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p id="rejectItemLabel" class="font-weight-bold"></p>
                    <div class="form-group">
                        <label for="reject-notes">Reason for rejection <span class="text-danger">*</span></label>
                        <textarea name="notes" id="reject-notes" rows="3" class="form-control" required maxlength="1000"
                            placeholder="Write the reason for rejecting this request — it will be visible to the requester and the Finance Office…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
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
