@extends('adminlte::page')

@section('title', 'Finance Office Dashboard')

@section('content_header')
    <h1 class="m-0 text-dark">Finance Office Oversight</h1>
@stop

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $officeSummary['pending_loans'] }}</h3>
                    <p>Pending Loans</p>
                </div>
                <i class="icon fas fa-hand-holding-usd"></i>
                <a href="{{ route('treasurer.loans.pending') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $officeSummary['pending_budgets'] }}</h3>
                    <p>Pending Budgets</p>
                </div>
                <i class="icon fas fa-file-invoice"></i>
                <a href="{{ route('finance.budgets.pending') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $officeSummary['pending_invoices'] }}</h3>
                    <p>Pending Invoices</p>
                </div>
                <i class="icon fas fa-receipt"></i>
                <a href="{{ route('finance.invoices.do') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $officeSummary['pending_procurement'] }}</h3>
                    <p>Pending Procurement</p>
                </div>
                <i class="icon fas fa-shopping-cart"></i>
                <a href="{{ route('treasurer.procurement.pending') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $officeSummary['payments_needing_review'] }}</h3>
                    <p>Payments to Review</p>
                </div>
                <i class="icon fas fa-check-double"></i>
                <a href="{{ route('finance.payments.review') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>{{ $officeSummary['overdue_tasks'] }}</h3>
                    <p>Overdue Tasks</p>
                </div>
                <i class="icon fas fa-exclamation-triangle"></i>
                <a href="{{ route('treasurer.tasks.index') }}" class="small-box-footer">View <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-2"></i> Staff Performance Overview</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Staff</th>
                            <th>Tasks Completed On Time</th>
                            <th>Overdue Tasks</th>
                            <th>Payments Verified</th>
                            <th>Payments Flagged</th>
                            <th>Recognition</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffOverview as $row)
                        <tr>
                            <td>{{ $row['user']->name ?? '—' }}</td>
                            <td>{{ $row['on_time'] }} / {{ $row['total_tasks'] }}</td>
                            <td>
                                @if($row['overdue'] > 0)
                                    <span class="badge badge-danger">{{ $row['overdue'] }}</span>
                                @else
                                    <span class="badge badge-success">0</span>
                                @endif
                            </td>
                            <td>{{ $row['verified'] }} / {{ $row['total_payments'] }}</td>
                            <td>
                                @if($row['flagged'] > 0)
                                    <span class="badge badge-warning">{{ $row['flagged'] }}</span>
                                @else
                                    <span class="badge badge-success">0</span>
                                @endif
                            </td>
                            <td>
                                @if($row['exceeds_flags'] > 0)
                                    <span class="badge badge-success"><i class="fas fa-star"></i> Exceeds Expectations</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">No Finance Office activity recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-outline card-danger shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i> Compliance Issues Needing Consultation</h3>
        </div>
        <div class="card-body">
            @if($complianceIssues->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Staff</th>
                                <th>Task</th>
                                <th>Deadline</th>
                                <th>Justification</th>
                                <th>Follow-up Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($complianceIssues as $task)
                            <tr>
                                <td>{{ $task->user->name ?? '—' }}</td>
                                <td>{{ $task->task_description }}</td>
                                <td>{{ $task->deadline->format('Y-m-d H:i') }}</td>
                                <td>
                                    @forelse($task->justifications as $justification)
                                        <div class="mb-1">{{ $justification->reason }}</div>
                                    @empty
                                        <span class="text-muted">None submitted</span>
                                    @endforelse
                                </td>
                                <td>
                                    @php $pending = $task->justifications->whereNull('treasurer_reviewed_at')->first(); @endphp
                                    @if($pending)
                                        <span class="badge badge-warning">Awaiting Review</span>
                                    @else
                                        <span class="badge badge-secondary">Reviewed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($pending)
                                        <form action="{{ route('treasurer.justifications.review', $pending) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">Mark Reviewed</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> No compliance issues flagged.
                </div>
            @endif
        </div>
    </div>

</div>
@stop
