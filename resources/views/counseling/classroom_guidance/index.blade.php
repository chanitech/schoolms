@extends('adminlte::page')

@section('title', 'Classroom Guidance')

@section('content_header')
    <h1><i class="fas fa-chalkboard-teacher"></i> Classroom Guidance</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">All Classroom Guidance Records</h3>
        <a href="{{ route('classroom-guidances.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New Record
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('classroom-guidances.index') }}" method="GET" class="mb-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <select name="class_id" class="form-control">
                        <option value="">-- Filter by Class --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-info">Filter</button>
                </div>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Date</th>
                    <th>Tasks</th>
                    <th>Achievements</th>
                    <th>Challenges</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guidances as $guidance)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $guidance->schoolClass?->name ?? 'N/A' }}</td>
                    <td>{{ $guidance->date->format('d/m/Y') }}</td>
                    <td>{{ $guidance->tasks }}</td>
                    <td>{{ $guidance->achievements }}</td>
                    <td>{{ $guidance->challenges }}</td>
                    <td>{{ $guidance->creator?->name ?? 'N/A' }}</td>
                    <td>
                        <a href="{{ route('classroom-guidances.show', $guidance->id) }}" class="btn btn-sm btn-info">View</a>
                        <a href="{{ route('classroom-guidances.edit', $guidance->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('classroom-guidances.destroy', $guidance->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">
            {{ $guidances->withQueryString()->links() }}
        </div>
    </div>
</div>
@stop
