@extends('adminlte::page')
@section('title','Job Cards')
@section('content_header')
    <h1 class="text-center text-success">Job Cards</h1>
@stop
@section('content')
<div class="container-fluid">
    <a href="{{ route('jobcards.create') }}" class="btn btn-success mb-3">Assign Job</a>
    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Assigned By</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobCards as $job)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $job->title }}</td>
                        <td>{{ $job->assigner->name ?? '-' }}</td>
                        <td>{{ $job->assignee->name ?? '-' }}</td>
                        <td>{{ ucfirst($job->status) }}</td>
                        <td>{{ $job->rating ?? '-' }}</td>
                        <td>{{ $job->due_date ?? '-' }}</td>
                        <td>
                            <a href="{{ route('jobcards.edit', $job->id) }}" class="btn btn-primary btn-sm">Edit</a>
                            <form action="{{ route('jobcards.destroy', $job->id) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Delete?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $jobCards->links() }}
        </div>
    </div>
</div>
@stop
