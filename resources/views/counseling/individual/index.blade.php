@extends('adminlte::page')

@section('title', 'Individual Counseling Reports')

@section('content_header')
    <h1><i class="fas fa-user-edit"></i> Individual Counseling Reports</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="card-title mb-0">All Individual Counseling Reports</h3>
        </div>
        <div>
            <a href="{{ route('counseling.individual.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus-circle"></i> New Report
            </a>
        </div>
    </div>

    <div class="card-body">
        {{-- Search Form --}}
        <form method="GET" action="{{ route('counseling.individual.index') }}" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by student name...">
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
                        <th>Student</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Session Number</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $index => $report)
                        <tr>
                            <td>{{ $reports->firstItem() + $index }}</td>
                            <td>{{ $report->student->first_name ?? '' }} {{ $report->student->last_name ?? '' }}</td>
                            <td>{{ $report->date->format('d M, Y') }}</td>
                            <td>{{ $report->time }}</td>
                            <td>{{ $report->session_number ?? '-' }}</td>
                            <td>
                                <a href="{{ route('counseling.individual.show', $report->id) }}" class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('counseling.individual.edit', $report->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('counseling.individual.destroy', $report->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this report?');">
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
                            <td colspan="6" class="text-center text-muted">No individual counseling reports found.</td>
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
