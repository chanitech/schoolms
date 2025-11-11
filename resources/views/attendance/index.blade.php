@extends('adminlte::page')

@section('title', 'Attendance')

@section('content_header')
    <h1>Attendance</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $summary['present'] ?? 0 }}</h3>
                    <p>Present</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $summary['absent'] ?? 0 }}</h3>
                    <p>Absent</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $summary['leave'] ?? 0 }}</h3>
                    <p>Leave</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-clock"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Buttons --}}
    <div class="d-flex justify-content-between mb-3">
        <div>
            <a href="{{ route('attendance.create') }}" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Mark Attendance
            </a>
            <a href="{{ route('attendance.bulk.create') }}" class="btn btn-success">
                <i class="fas fa-users"></i> Bulk Mark
            </a>
        </div>
        <div>
            <a href="{{ route('attendance.export.excel', request()->query()) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('attendance.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('attendance.filter') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <input type="text" name="staff_name" class="form-control" placeholder="Search Staff" value="{{ request('staff_name') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="present" {{ request('status')=='present'?'selected':'' }}>Present</option>
                    <option value="absent" {{ request('status')=='absent'?'selected':'' }}>Absent</option>
                    <option value="leave" {{ request('status')=='leave'?'selected':'' }}>Leave</option>
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Attendance Table --}}
    <div class="card shadow">
        <div class="card-body table-responsive">
            @if($attendances->count())
                <table class="table table-bordered table-hover text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                            <tr>
                                <td>{{ $attendance->staff->name }}</td>
                                <td>{{ $attendance->date->format('Y-m-d') }}</td>
                                <td>
                                    @if($attendance->status == 'present')
                                        <span class="badge bg-success">Present</span>
                                    @elseif($attendance->status == 'absent')
                                        <span class="badge bg-danger">Absent</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Leave</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('attendance.edit', $attendance) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('attendance.destroy', $attendance) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div class="mt-3 d-flex justify-content-center">
                    {{ $attendances->links('pagination::bootstrap-5') }}
                </div>
            @else
                <p class="text-center text-muted">No attendance records found.</p>
            @endif
        </div>
    </div>

</div>
@stop
