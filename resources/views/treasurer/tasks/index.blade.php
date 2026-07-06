@extends('adminlte::page')

@section('title', 'Finance Office Tasks')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Finance Office Tasks</h1>
        @can('manage tasks')
        <a href="{{ route('treasurer.tasks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Assign Task
        </a>
        @endcan
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th>Assignee</th>
                            <th>Task</th>
                            <th>Deadline</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                        <tr>
                            <td>{{ $task->user->name ?? '—' }} {!! $task->is_flagged_exceeds ? '<span class="badge badge-success">Exceeds Expectations</span>' : '' !!}</td>
                            <td>{{ $task->task_description }}</td>
                            <td>{{ $task->deadline->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="progress" style="height: 18px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $task->percent_complete }}%;">
                                        {{ $task->percent_complete }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @switch($task->status)
                                    @case('pending') <span class="badge badge-secondary">Pending</span> @break
                                    @case('in_progress') <span class="badge badge-info">In Progress</span> @break
                                    @case('submitted') <span class="badge badge-primary">Submitted</span> @break
                                    @case('approved') <span class="badge badge-success">Approved</span> @break
                                    @case('overdue') <span class="badge badge-danger">Overdue</span> @break
                                @endswitch
                            </td>
                            <td>
                                @if($task->user_id === auth()->id())
                                    @if(!in_array($task->status, ['submitted', 'approved']))
                                        <form action="{{ route('treasurer.tasks.progress', $task) }}" method="POST" class="form-inline d-inline">
                                            @csrf
                                            <input type="number" name="percent_complete" min="0" max="100" value="{{ $task->percent_complete }}" class="form-control form-control-sm" style="width: 70px;">
                                            <button type="submit" class="btn btn-sm btn-outline-primary ml-1">Update</button>
                                        </form>
                                        <form action="{{ route('treasurer.tasks.submit', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-paper-plane"></i> Submit</button>
                                        </form>
                                    @endif
                                    @if($task->status === 'overdue' && $task->justifications->isEmpty())
                                        <button type="button" class="btn btn-sm btn-warning justify-btn" data-id="{{ $task->id }}">
                                            <i class="fas fa-file-alt"></i> Justify
                                        </button>
                                    @endif
                                @endif
                                @can('manage tasks')
                                    @if($task->status === 'submitted')
                                        <form action="{{ route('treasurer.tasks.approve', $task) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('treasurer.tasks.toggle-exceeds', $task) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            {{ $task->is_flagged_exceeds ? 'Unflag' : 'Flag Exceeds' }}
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted">No tasks yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="justifyModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Submit Justification</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="justifyForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reason">Reason for missing the deadline <span class="text-danger">*</span></label>
                        <textarea name="reason" id="reason" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.justify-btn').click(function() {
            let taskId = $(this).data('id');
            $('#justifyForm').attr('action', '{{ url("treasurer/tasks") }}/' + taskId + '/justification');
            $('#justifyModal').modal('show');
        });
    });
</script>
@stop
