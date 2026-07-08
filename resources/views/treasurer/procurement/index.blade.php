@extends('adminlte::page')

@section('title', 'Procurement Requests')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Procurement Requests</h1>
        @can('create procurement requests')
        <a href="{{ route('treasurer.procurement.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Request
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
                            <th>Estimated Cost</th>
                            <th>Actual Cost</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th>Disbursed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $request)
                        <tr>
                            <td>{{ $request->item }}{{ $request->threshold_flag ? ' ⚠️' : '' }}</td>
                            <td>{{ $request->quantity }}</td>
                            <td>{{ number_format($request->estimated_cost, 2) }}</td>
                            <td>{{ $request->actual_cost ? number_format($request->actual_cost, 2) : '—' }}</td>
                            <td>{{ $request->requestedBy->name ?? '—' }}</td>
                            <td>
                                @switch($request->status)
                                    @case('pending') <span class="badge badge-warning">Awaiting Treasurer</span> @break
                                    @case('treasurer_approved') <span class="badge badge-primary">Awaiting Head Master</span> @break
                                    @case('approved') <span class="badge badge-info">Awaiting Cashier</span> @break
                                    @case('rejected') <span class="badge badge-danger">Rejected</span> @break
                                    @case('completed') <span class="badge badge-success">Completed</span> @break
                                @endswitch
                            </td>
                            <td>
                                {{ $request->disbursedBy->name ?? '—' }}
                                @if($request->disbursed_at)
                                    <div class="small text-muted">{{ $request->disbursed_at->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td>
                                @can('approve procurement requests')
                                    @if($request->status === 'pending')
                                        <form action="{{ route('treasurer.procurement.approve', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                        <form action="{{ route('treasurer.procurement.reject', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('headmaster approve procurement requests')
                                    @if($request->status === 'treasurer_approved')
                                        <form action="{{ route('treasurer.procurement.headmaster-approve', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check-double"></i> Head Master Approve</button>
                                        </form>
                                        <form action="{{ route('treasurer.procurement.headmaster-reject', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    @endif
                                @endcan
                                @can('disburse payments')
                                    @if($request->status === 'approved')
                                        <button type="button" class="btn btn-sm btn-primary disburse-btn"
                                            data-id="{{ $request->id }}" data-item="{{ $request->item }}"
                                            data-amount="{{ number_format($request->estimated_cost, 2) }}">
                                            <i class="fas fa-money-bill-wave"></i> Disburse
                                        </button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted">No procurement requests yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $requests->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="disburseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Disburse Payment</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="disburseForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p id="disburseItemLabel"></p>
                    <div class="form-group">
                        <label class="mb-1">Amount to Disburse (TZS)</label>
                        <div class="form-control-plaintext font-weight-bold" id="disburseAmountLabel" style="font-size:1.15rem"></div>
                        <small class="form-text text-muted">Fixed to the Treasurer + Head Master-approved amount — not editable here.</small>
                    </div>
                    <div class="form-group">
                        <label for="category">Expense Category <span class="text-danger">*</span></label>
                        <input type="text" name="category" id="category" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea name="notes" id="notes" rows="2" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Disburse</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.disburse-btn').click(function() {
            let id     = $(this).data('id');
            let item   = $(this).data('item');
            let amount = $(this).data('amount');
            $('#disburseForm').attr('action', '{{ url("treasurer/procurement") }}/' + id + '/disburse');
            $('#disburseItemLabel').text('Item: ' + item);
            $('#disburseAmountLabel').text(amount);
            $('#disburseModal').modal('show');
        });
    });
</script>
@stop
