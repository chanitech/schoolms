@extends('adminlte::page')

@section('title', 'Student Results')

@section('content_header')
    <h1>Student Results</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            @if($students->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Admission No</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        <tr>
                            <td>{{ $student->admission_no }}</td>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->class->name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('results.show', $student) }}" class="btn btn-sm btn-primary">View Result</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $students->links() }}
            @else
                <p>No students found.</p>
            @endif
        </div>
    </div>
@stop
