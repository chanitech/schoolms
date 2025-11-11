@extends('adminlte::page')

@section('title', 'Academic Sessions')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-calendar-alt"></i> Academic Sessions</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-list"></i> Sessions List</h3>
            <a href="{{ route('sessions.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Session
            </a>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Current</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        <tr>
                            <td>{{ $loop->iteration + ($sessions->currentPage()-1)*$sessions->perPage() }}</td>
                            <td>{{ $session->name }}</td>
                            <td>{{ optional($session->start_date)->format('d-m-Y') }}</td>
                            <td>{{ optional($session->end_date)->format('d-m-Y') }}</td>
                            <td>
                                @if($session->is_current)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('sessions.edit', $session->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('sessions.destroy', $session->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this session?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No sessions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $sessions->links('pagination::bootstrap-5') }}
        </div>
    </div>
@stop
