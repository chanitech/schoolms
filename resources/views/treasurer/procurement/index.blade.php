@extends('adminlte::page')

@section('title', 'Procurement Requests')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Procurement Requests</h1>
        <a href="{{ route('treasurer.reports.procurement', request()->query()) }}" class="btn btn-outline-dark btn-sm">
            <i class="fas fa-file-signature mr-1"></i> Export Signed PDF
        </a>
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
                                    @case('returned') <span class="badge badge-secondary">Returned — amount insufficient</span> @break
                                @endswitch
                                @if($request->notes && in_array($request->status, ['rejected', 'returned']))
                                    <div class="small text-muted mt-1" style="max-width: 220px;">
                                        <i class="fas fa-comment-alt"></i> {{ $request->notes }}
                                    </div>
                                @endif
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
                                        <button type="button" class="btn btn-sm btn-danger reject-btn"
                                            data-action="{{ route('treasurer.procurement.reject', $request) }}"
                                            data-item="{{ $request->item }}">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                @endcan
                                @can('headmaster approve procurement requests')
                                    @if($request->status === 'treasurer_approved')
                                        <form action="{{ route('treasurer.procurement.headmaster-approve', $request) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check-double"></i> Head Master Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger reject-btn"
                                            data-action="{{ route('treasurer.procurement.headmaster-reject', $request) }}"
                                            data-item="{{ $request->item }}">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    @endif
                                @endcan
                                @can('disburse payments')
                                    @if($request->status === 'approved')
                                        <button type="button" class="btn btn-sm btn-primary disburse-btn"
                                            data-id="{{ $request->id }}" data-item="{{ $request->item }}"
                                            data-amount="{{ number_format($request->estimated_cost, 2) }}">
                                            <i class="fas fa-money-bill-wave"></i> Disburse
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning return-btn"
                                            data-action="{{ route('treasurer.procurement.return', $request) }}"
                                            data-item="{{ $request->item }}"
                                            data-amount="{{ number_format($request->estimated_cost, 2) }}">
                                            <i class="fas fa-undo"></i> Amount not enough
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
                        <textarea name="notes" id="notes" rows="2" class="form-control"
                            placeholder="Optional remarks for the expense log — e.g. receipt/reference number, supplier paid, payment method…"></textarea>
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

{{-- Reject with reason (Treasurer & Head Master stages) --}}
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

{{-- Cashier: return because the approved amount is insufficient --}}
<div class="modal fade" id="returnModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-undo"></i> Return — Amount Not Enough</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="returnForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p id="returnItemLabel" class="font-weight-bold"></p>
                    <p class="text-muted small mb-3">
                        The approved amount cannot be changed at disbursement. Returning sends the request
                        back so a corrected request can be submitted and re-approved by the Treasurer and Head Master.
                    </p>
                    <div class="form-group">
                        <label for="return-notes">Why is the approved amount not enough? <span class="text-danger">*</span></label>
                        <textarea name="notes" id="return-notes" rows="3" class="form-control" required maxlength="1000"
                            placeholder="e.g. Supplier price has increased to TZS …, transport cost was not included, quantity quoted no longer available at this price…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Return Request</button>
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

        $('.reject-btn').click(function() {
            $('#rejectForm').attr('action', $(this).data('action'));
            $('#rejectItemLabel').text('Item: ' + $(this).data('item'));
            $('#rejectModal').modal('show');
        });

        $('.return-btn').click(function() {
            $('#returnForm').attr('action', $(this).data('action'));
            $('#returnItemLabel').text('Item: ' + $(this).data('item') + ' — approved amount: TZS ' + $(this).data('amount'));
            $('#returnModal').modal('show');
        });
    });
</script>
@stop
