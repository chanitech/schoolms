@extends('adminlte::page')

@section('title', 'Grades')

@section('content_header')
    <h1>Grades</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <a href="{{ route('grades.create') }}" class="btn btn-primary mb-3">Add Grade</a>

            @if($grades->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Min Mark</th>
                            <th>Max Mark</th>
                            <th>Point</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grades as $grade)
                            <tr>
                                <td>{{ $grade->name }}</td>
                                <td>{{ $grade->min_mark }}</td>
                                <td>{{ $grade->max_mark }}</td>
                                <td>{{ $grade->point }}</td>
                                <td>{{ $grade->description ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('grades.edit', $grade) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('grades.destroy', $grade) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this grade?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No grades found.</p>
            @endif
        </div>
    </div>
@stop
