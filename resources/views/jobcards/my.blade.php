@extends('adminlte::page')

@section('title','My Job Cards')

@section('content_header')
    <h1 class="text-center text-success">My Job Cards</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Assigned By</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobcards as $job)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $job->title }}</td>
                        <td>{{ $job->assigner?->name ?? '-' }}</td>
                        <td>{{ ucfirst($job->status) }}</td>
                        <td>
                            @if($job->rating)
                                {{ $job->rating }} / 5
                            @elseif(Auth::user()->staff?->id === $job->assigned_by && $job->status === 'completed' && auth()->user()->can('rate jobcards'))
                                <!-- Rating form for assigner -->
                                <form action="{{ route('jobcards.rateTask', $job->id) }}" method="POST" class="d-flex justify-content-center">
                                    @csrf
                                    @method('PATCH')
                                    <select name="rating" class="form-control form-control-sm me-1" required>
                                        <option value="">Rate</option>
                                        @for($i=1;$i<=5;$i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Rate</button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $job->due_date?->format('Y-m-d') ?? '-' }}</td>
                        <td>
                            @if($job->assigned_to === Auth::user()->staff?->id && $job->status !== 'completed' && auth()->user()->can('update job status'))
                                <!-- Status update form for assignee -->
                                <form action="{{ route('jobcards.updateStatus', $job->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-control mb-2">
                                        <option value="pending" {{ $job->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="in_progress" {{ $job->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ $job->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                                </form>
                            @else
                                <span class="text-muted">No actions</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $jobcards->links() }}
        </div>
    </div>
</div>
@stop
