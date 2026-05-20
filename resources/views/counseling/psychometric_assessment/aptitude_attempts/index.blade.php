@extends('adminlte::page')

@section('title', 'Aptitude Test Attempts')

@section('content_header')
    <h1><i class="fas fa-file-alt"></i> Aptitude Test Attempts</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">All Attempts</h3>
        <a href="{{ route('aptitude.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New Attempt
        </a>
    </div>

    <div class="card-body">
        @if($attempts->count() > 0)
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Counselor</th>
                        <th>Total Score</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $attempt)
                        <tr>
                            <td>{{ $loop->iteration + ($attempts->currentPage()-1) * $attempts->perPage() }}</td>
                            <td>{{ $attempt->student->name }} ({{ $attempt->student->admission_no }})</td>
                            <td>{{ $attempt->counselor->name }}</td>
                            <td>{{ $attempt->total_score }}</td>
                            <td>{{ $attempt->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                <a href="{{ route('aptitude.show', $attempt->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="{{ route('aptitude.pdf', $attempt->id) }}" class="btn btn-danger btn-sm">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $attempts->links() }}
            </div>
        @else
            <div class="alert alert-warning">
                No aptitude test attempts found. <a href="{{ route('aptitude.create') }}">Create one now</a>.
            </div>
        @endif
    </div>
</div>
@stop
