@extends('adminlte::page')

@section('title', 'Departments')

@section('content_header')
<h1 class="text-center text-success">Departments</h1>
@stop

@section('content')
<div class="container-fluid">

    <a href="{{ route('departments.create') }}" class="btn btn-success mb-3">Add Department</a>

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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $dept)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $dept->name }}</td>
                        <td>{{ $dept->head->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('departments.edit', $dept->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this department?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $departments->links() }}
        </div>
    </div>
</div>
@stop
