@extends('adminlte::page')

@section('title', 'Departments')

@section('content_header')
<h1 class="text-center text-success">Departments</h1>
@stop

@section('content')
<div class="container-fluid">

    @can('create departments')
        <a href="{{ route('departments.create') }}" class="btn btn-success mb-3">Add Department</a>
    @endcan

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Head</th>
                        <th>Requires 7 Subjects for Ranking</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $dept)
                    <tr>
                        <td>{{ $loop->iteration + ($departments->currentPage() - 1) * $departments->perPage() }}</td>
                        <td>{{ $dept->name }}</td>
                        <td>{{ $dept->head?->name ?? '-' }}</td>
                        <td>
                            @if($dept->rank_requires_7_subjects)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            @can('edit departments')
                                <a href="{{ route('departments.edit', $dept->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            @endcan
                            @can('delete departments')
                                <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this department?')">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No departments found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $departments->links() }}
        </div>
    </div>
</div>
@stop
