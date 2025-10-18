@extends('adminlte::page')

@section('title', 'Received Leaves')

@section('content_header')
    <h1>Received Leaves</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Leave Summary --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $summary['approved'] ?? 0 }}</h3>
                        <p>Approved</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $summary['pending'] ?? 0 }}</h3>
                        <p>Pending</p>
                    </div>
                    <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $summary['rejected'] ?? 0 }}</h3>
                        <p>Rejected</p>
                    </div>
                    <div class="icon"><i class="fas fa-times-circle"></i></div>
                </div>
            </div>
        </div>

        {{-- Top buttons --}}
        <div class="d-flex justify-content-between mb-3">
            <div>
                <a href="{{ route('leaves.index') }}" class="btn btn-primary">My Leaves</a>
            </div>
            <div>
                <a href="{{ route('leaves.received.export.excel', request()->query()) }}" class="btn btn-outline-success">Export Excel</a>
                <a href="{{ route('leaves.received.export.pdf', request()->query()) }}" class="btn btn-outline-danger">Export PDF</a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('leaves.received') }}" class="mb-3">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="staff_name" class="form-control" placeholder="Search Staff" value="{{ request('staff_name') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date_from" class="form-control" value="{{ request('start_date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date_to" class="form-control" value="{{ request('start_date_to') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
                        <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="department_id" class="form-control">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-block">Filter</button>
                </div>
            </div>
        </form>

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Leaves table --}}
        @if($leaves->count())
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaves as $leave)
                        <tr>
                            <td>{{ $leave->requester->name }}</td>
                            <td>{{ $leave->requester->department->name ?? '-' }}</td>
                            <td>{{ ucfirst($leave->type) }}</td>
                            <td>{{ $leave->start_date->format('Y-m-d') }}</td>
                            <td>{{ $leave->end_date->format('Y-m-d') }}</td>
                            <td>
                                @php
                                    $badge = match($leave->status) {
                                        'approved' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ ucfirst($leave->status) }}</span>
                            </td>
                            <td>{{ $leave->reason }}</td>
                            <td>
                                @if($leave->status == 'pending')
                                    @can('approve received leaves')
                                        <form action="{{ route('leaves.approve', $leave) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success" onclick="return confirm('Approve this leave?')">Approve</button>
                                        </form>
                                        <form action="{{ route('leaves.reject', $leave) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Reject this leave?')">Reject</button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="text-muted">No actions</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $leaves->links() }}
        @else
            <p>No received leave requests found.</p>
        @endif

    </div>
</div>
@stop
