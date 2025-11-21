@extends('adminlte::page')

@section('title', 'Session Reports')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>All Session Reports</h3>
        <a href="{{ route('counseling.session_reports.create') }}" class="btn btn-primary float-right">New Session Report</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Date</th>
                    <th>Session #</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                <tr>
                    <td>{{ $report->student->full_name }}</td>
                    <td>{{ $report->date }}</td>
                    <td>{{ $report->session_number }}</td>
                    <td>
                        <a href="{{ route('counseling.session_reports.show', $report) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('counseling.session_reports.edit', $report) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('counseling.session_reports.destroy', $report) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this report?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $reports->links() }}
    </div>
</div>
@endsection
