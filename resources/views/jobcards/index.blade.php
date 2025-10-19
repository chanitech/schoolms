@extends('adminlte::page')

@section('title', 'Job Cards')

@section('content_header')
    <h1>Job Cards</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        @can('create jobcards')
            <a href="{{ route('jobcards.create') }}" class="btn btn-primary">Create New Job Card</a>
        @endcan
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Assigner</th>
                    <th>Assignee</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobCards as $job)
                <tr>
                    <td>{{ $loop->iteration + ($jobCards->currentPage() - 1) * $jobCards->perPage() }}</td>
                    <td>{{ $job->title }}</td>
                    <td>{{ $job->description ?? '-' }}</td>
                    <td>{{ $job->assigner?->name ?? 'N/A' }}</td>
                    <td>{{ $job->assignee?->name ?? 'N/A' }}</td>
                    <td>{{ ucfirst($job->status) }}</td>
                    <td>{{ $job->due_date?->format('d M Y') ?? '-' }}</td>
                    <td>
                        @if($job->status === 'completed')
                            @if($job->rating)
                                ⭐ {{ $job->rating }}/5
                            @elseif(Auth::user()->staff && Auth::user()->staff->id === $job->assigned_by && auth()->user()->can('rate jobcards'))
                                <form action="{{ route('jobcards.rateTask', $job->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="rating" class="form-select form-select-sm" required>
                                        <option value="">Rate</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <button type="submit" class="btn btn-success btn-sm mt-1">Save</button>
                                </form>
                            @else
                                <span class="text-muted">Awaiting rating</span>
                            @endif
                        @else
                            <span class="text-muted">Pending</span>
                        @endif
                    </td>
                    <td>
                        @can('edit jobcards')
                            <a href="{{ route('jobcards.edit', $job->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        @endcan
                        @can('delete jobcards')
                            <form action="{{ route('jobcards.destroy', $job->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this job card?')">Delete</button>
                            </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">No job cards found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $jobCards->links() }}
    </div>
</div>
@stop
