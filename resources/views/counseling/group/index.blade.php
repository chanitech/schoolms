@extends('adminlte::page')

@section('title', 'Group Counseling Reports')

@section('content_header')
    <h1><i class="fas fa-users"></i> Group Counseling Session Reports</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-0">All Group Counseling Reports</h3>
        </div>
        <div>
            <a href="{{ route('counseling.group.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus-circle"></i> New Report
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- Search Form --}}
        <form method="GET" action="{{ route('counseling.group.index') }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by group name or student...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-block">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Group Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Members</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $index => $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $index }}</td>
                            <td>{{ $report->group_name }}</td>
                            <td>{{ $report->date }}</td>
                            <td>{{ $report->time }}</td>
                            <td>
                                @foreach($report->students as $student)
                                    <span class="badge badge-info">{{ $student->first_name }} {{ $student->last_name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('counseling.group.show', $report->id) }}" class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('counseling.group.edit', $report->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('counseling.group.destroy', $report->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No group counseling reports found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $reports->withQueryString()->links() }}
        </div>
    </div>
</div>
@stop
