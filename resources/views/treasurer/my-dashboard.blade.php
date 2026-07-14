@extends('adminlte::page')

@section('title', 'My Finance Office Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">My Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">

    <div class="row">
        @if(!is_null($pendingClassPayments))
        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $pendingClassPayments }}</h3>
                    <p>Payments Awaiting Your Verification</p>
                </div>
                <i class="icon fas fa-check-double"></i>
                <a href="{{ route('finance.payments.review') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif

        @if(!is_null($awaitingDisbursement))
        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $awaitingDisbursement }}</h3>
                    <p>Approved Requests Awaiting Disbursement</p>
                </div>
                <i class="icon fas fa-money-bill-wave"></i>
                <a href="{{ route('treasurer.procurement.index') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif

        @if(!is_null($lowStockCount))
        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $lowStockCount }}</h3>
                    <p>Low Stock Items</p>
                </div>
                <i class="icon fas fa-boxes"></i>
                <a href="{{ route('treasurer.procurement.create') }}" class="small-box-footer">Request Restock <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif

        <div class="col-lg-3 col-md-6 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $tasks->where('status', 'overdue')->count() }}</h3>
                    <p>My Overdue Tasks</p>
                </div>
                <i class="icon fas fa-exclamation-triangle"></i>
                <a href="#my-tasks" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Quick Actions — shortcuts matched to the user's permissions/job description --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bolt mr-2"></i> Quick Actions</h3>
        </div>
        <div class="card-body pb-2">
            {{-- Storekeeper / stock management --}}
            @can('manage stock')
                <a href="{{ route('inventory.transactions.create') }}" class="btn btn-outline-primary mb-2 mr-1">
                    <i class="fas fa-exchange-alt"></i> Record Stock In / Out
                </a>
                <a href="{{ route('inventory.items') }}" class="btn btn-outline-primary mb-2 mr-1">
                    <i class="fas fa-boxes"></i> Stock Items
                </a>
                <a href="{{ route('inventory.items', ['stock' => 'low']) }}" class="btn btn-outline-danger mb-2 mr-1">
                    <i class="fas fa-battery-quarter"></i> Low Stock
                    @if($lowStockCount) <span class="badge badge-danger">{{ $lowStockCount }}</span> @endif
                </a>
                <a href="{{ route('inventory.transactions') }}" class="btn btn-outline-primary mb-2 mr-1">
                    <i class="fas fa-history"></i> Stock Ledger
                </a>
            @endcan
            @can('create stock requests')
                <a href="{{ route('treasurer.stock-requests.create') }}" class="btn btn-outline-info mb-2 mr-1">
                    <i class="fas fa-hand-paper"></i> Flag Stock Need
                </a>
                <a href="{{ route('treasurer.stock-requests.index') }}" class="btn btn-outline-info mb-2 mr-1">
                    <i class="fas fa-list"></i> My Stock Requests
                    @if($queues['my_stock_requests']) <span class="badge badge-info">{{ $queues['my_stock_requests'] }}</span> @endif
                </a>
            @endcan

            {{-- Procurement Officer --}}
            @can('review stock requests')
                <a href="{{ route('treasurer.stock-requests.index') }}" class="btn btn-outline-warning mb-2 mr-1">
                    <i class="fas fa-inbox"></i> Stock Requests to Review
                    @if($queues['stock_requests_pending']) <span class="badge badge-warning">{{ $queues['stock_requests_pending'] }}</span> @endif
                </a>
            @endcan
            @can('create procurement requests')
                <a href="{{ route('treasurer.procurement.create') }}" class="btn btn-outline-primary mb-2 mr-1">
                    <i class="fas fa-cart-plus"></i> New Procurement Request
                </a>
            @endcan

            {{-- Treasurer --}}
            @can('approve procurement requests')
                <a href="{{ route('treasurer.procurement.pending') }}" class="btn btn-outline-success mb-2 mr-1">
                    <i class="fas fa-stamp"></i> Procurement Approvals
                    @if($queues['procurement_pending']) <span class="badge badge-success">{{ $queues['procurement_pending'] }}</span> @endif
                </a>
            @endcan
            @can('approve loans')
                <a href="{{ route('treasurer.loans.pending') }}" class="btn btn-outline-success mb-2 mr-1">
                    <i class="fas fa-hand-holding-usd"></i> Loan Approvals
                    @if($queues['loans_pending']) <span class="badge badge-success">{{ $queues['loans_pending'] }}</span> @endif
                </a>
            @endcan

            {{-- Head Master (DO) --}}
            @can('approve budget items')
                <a href="{{ route('finance.budgets.pending') }}" class="btn btn-outline-success mb-2 mr-1">
                    <i class="fas fa-file-signature"></i> Budget Approvals
                    @if($queues['budgets_pending']) <span class="badge badge-success">{{ $queues['budgets_pending'] }}</span> @endif
                </a>
            @endcan
            @can('approve invoices')
                <a href="{{ route('finance.invoices.do') }}" class="btn btn-outline-success mb-2 mr-1">
                    <i class="fas fa-file-invoice"></i> Invoice Approvals
                    @if($queues['invoices_to_approve']) <span class="badge badge-success">{{ $queues['invoices_to_approve'] }}</span> @endif
                </a>
            @endcan

            {{-- Cashier --}}
            @can('pay invoices')
                <a href="{{ route('finance.invoices.finance') }}" class="btn btn-outline-primary mb-2 mr-1">
                    <i class="fas fa-money-check-alt"></i> Invoices to Pay
                    @if($queues['invoices_to_pay']) <span class="badge badge-primary">{{ $queues['invoices_to_pay'] }}</span> @endif
                </a>
            @endcan
            @can('record payments')
                <a href="{{ route('finance.pocket.transactions.create') }}" class="btn btn-outline-primary mb-2 mr-1">
                    <i class="fas fa-wallet"></i> Pocket Money Entry
                </a>
            @endcan

            {{-- Class accountants --}}
            @if(!is_null($pendingClassPayments))
                <a href="{{ route('finance.payments.review') }}" class="btn btn-outline-warning mb-2 mr-1">
                    <i class="fas fa-check-double"></i> Verify Payments
                    @if($pendingClassPayments) <span class="badge badge-warning">{{ $pendingClassPayments }}</span> @endif
                </a>
            @endif

            {{-- Treasurer office-wide overview --}}
            @can('view finance dashboard')
                <a href="{{ route('treasurer.dashboard') }}" class="btn btn-outline-dark mb-2 mr-1">
                    <i class="fas fa-chart-line"></i> Office Overview
                </a>
            @endcan
        </div>
    </div>

    @if($assignedClasses->isNotEmpty())
    <div class="card card-outline card-info shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-school mr-2"></i> My Assigned Classes</h3>
        </div>
        <div class="card-body">
            @foreach($assignedClasses as $assignment)
                <span class="badge badge-info mr-1">{{ $assignment->schoolClass->name ?? '—' }}</span>
            @endforeach
        </div>
    </div>
    @endif

    @if($myProcurementRequests->isNotEmpty())
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-shopping-cart mr-2"></i> My Procurement Requests</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Estimated Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myProcurementRequests as $request)
                        <tr>
                            <td>{{ $request->item }}</td>
                            <td>{{ $request->quantity }}</td>
                            <td>{{ number_format($request->estimated_cost, 2) }}</td>
                            <td>
                                @switch($request->status)
                                    @case('pending') <span class="badge badge-warning">Awaiting Treasurer</span> @break
                                    @case('treasurer_approved') <span class="badge badge-primary">Awaiting Head Master</span> @break
                                    @case('approved') <span class="badge badge-info">Awaiting Cashier</span> @break
                                    @case('rejected') <span class="badge badge-danger">Rejected</span> @break
                                    @case('completed') <span class="badge badge-success">Completed</span> @break
                                @endswitch
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($jobDescriptions->isNotEmpty())
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-id-card mr-2"></i> My Job Description</h3>
        </div>
        <div class="card-body">
            @foreach($jobDescriptions as $jd)
                <p class="mb-2"><strong class="text-capitalize">{{ str_replace(['-', '_'], ' ', $jd->role_name) }}:</strong> {{ $jd->description }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <div class="card card-outline card-dark shadow-sm" id="my-tasks">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tasks mr-2"></i> My Tasks</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif

            @if($tasks->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Task</th>
                                <th>Deadline</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                            <tr>
                                <td>{{ $task->task_description }}</td>
                                <td>{{ $task->deadline->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="progress" style="height: 18px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $task->percent_complete }}%;">
                                            {{ $task->percent_complete }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @switch($task->status)
                                        @case('pending') <span class="badge badge-secondary">Pending</span> @break
                                        @case('in_progress') <span class="badge badge-info">In Progress</span> @break
                                        @case('submitted') <span class="badge badge-primary">Submitted</span> @break
                                        @case('approved') <span class="badge badge-success">Approved</span> @break
                                        @case('overdue') <span class="badge badge-danger">Overdue</span> @break
                                    @endswitch
                                </td>
                                <td>
                                    @if(!in_array($task->status, ['submitted', 'approved']))
                                        <form action="{{ route('treasurer.tasks.progress', $task) }}" method="POST" class="form-inline d-inline">
                                            @csrf
                                            <input type="number" name="percent_complete" min="0" max="100" value="{{ $task->percent_complete }}" class="form-control form-control-sm" style="width: 70px;">
                                            <button type="submit" class="btn btn-sm btn-outline-primary ml-1">Update</button>
                                        </form>
                                        <form action="{{ route('treasurer.tasks.submit', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-paper-plane"></i> Submit</button>
                                        </form>
                                    @endif
                                    @if($task->status === 'overdue' && $task->justifications->isEmpty())
                                        <button type="button" class="btn btn-sm btn-warning justify-btn" data-id="{{ $task->id }}">
                                            <i class="fas fa-file-alt"></i> Justify
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> No tasks assigned to you yet.
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="justifyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Submit Justification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="justifyForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Reason for missing the deadline <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.justify-btn').click(function() {
            let taskId = $(this).data('id');
            $('#justifyForm').attr('action', '{{ url("treasurer/tasks") }}/' + taskId + '/justification');
            $('#justifyModal').modal('show');
        });
    });
</script>
@stop
