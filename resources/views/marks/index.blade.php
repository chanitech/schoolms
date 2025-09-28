@extends('adminlte::page')

@section('title', 'Marks')

@section('content_header')
    <h1>Marks</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <a href="{{ route('marks.create') }}" class="btn btn-primary mb-3">Add Mark</a>

        @if($marks->count())
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Subject</th>
                        <th>Exam</th>
                        <th>Mark</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marks as $mark)
                        <tr>
                            <td>{{ $mark->student->first_name }} {{ $mark->student->last_name }}</td>
                            <td>{{ $mark->subject->name }}</td>
                            <td>{{ $mark->exam->name }}</td>
                            <td>{{ $mark->mark }}</td>
                            <td>
                                <a href="{{ route('marks.edit', $mark) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('marks.destroy', $mark) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this mark?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $marks->links() }}
        @else
            <p>No marks found.</p>
        @endif
    </div>
</div>
@stop
