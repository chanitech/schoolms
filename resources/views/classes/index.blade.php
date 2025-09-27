@extends('adminlte::page')

@section('title', 'Classes')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-chalkboard"></i> Classes</h1>
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
        <h3 class="card-title"><i class="fas fa-list"></i> Class Records</h3>
        <a href="{{ route('classes.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Class
        </a>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Section</th>
                    <th>Capacity</th>
                    <th>Class Teacher</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $class)
                    <tr>
                        <td>{{ $loop->iteration + ($classes->currentPage()-1)*$classes->perPage() }}</td>
                        <td>{{ $class->name }}</td>
                        <td>{{ $class->level ?? '-' }}</td>
                        <td>{{ $class->section ?? '-' }}</td>
                        <td>{{ $class->capacity }}</td>
                        <td>{{ $class->teacher?->first_name ?? '-' }} {{ $class->teacher?->last_name ?? '' }}</td>
                        <td>
                            <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('classes.destroy', $class->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this class?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No classes found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        {{ $classes->links('pagination::bootstrap-5') }}
    </div>
</div>
@stop
