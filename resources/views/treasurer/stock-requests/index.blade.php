@extends('adminlte::page')

@section('title', 'Stock Requests')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Stock Requests</h1>
        <a href="{{ route('treasurer.reports.stock-requests', request()->query()) }}" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-file-signature mr-1"></i> Export Signed PDF
        </a>
        @can('create stock requests')
        <a href="{{ route('treasurer.stock-requests.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Request Stock
        </a>
        @endcan
    </div>
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

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Reason</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th>Procurement Request</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->item }}</td>
                            <td>{{ $request->quantity }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($request->reason, 60) }}</td>
                            <td>{{ $request->requestedBy->name ?? '—' }}</td>
                            <td>
                                @switch($request->status)
                                    @case('pending') <span class="badge badge-warning">Pending</span> @break
                                    @case('approved') <span class="badge badge-info">Approved</span> @break
                                    @case('rejected') <span class="badge badge-danger">Rejected</span> @break
                                    @case('converted') <span class="badge badge-success">Converted</span> @break
                                @endswitch
                            </td>
                            <td>
                                @if($request->procurementRequest)
                                    <a href="{{ route('treasurer.procurement.index') }}">#{{ $request->procurementRequest->id }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @can('review stock requests')
                                    @if($request->status === 'pending')
                                        <form action="{{ route('treasurer.stock-requests.approve', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger reject-btn" data-id="{{ $request->id }}">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted">No stock requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-ban"></i> Reject Stock Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="notes">Reason for rejection</label>
                        <textarea name="notes" id="notes" rows="3" class="form-control"></textarea>
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
        $('.reject-btn').click(function() {
            let id = $(this).data('id');
            $('#rejectForm').attr('action', '{{ url("treasurer/stock-requests") }}/' + id + '/reject');
            $('#rejectModal').modal('show');
        });
    });
</script>
@stop
