@extends('adminlte::page')

@section('title', 'Exams')

@section('content_header')
    <h1>Exams</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <a href="{{ route('exams.create') }}" class="btn btn-primary mb-3">Add Exam</a>

            @if($exams->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Term</th>
                            <th>Academic Session</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                            <tr>
                                <td>{{ $exam->name }}</td>
                                <td>{{ $exam->term }}</td>
                                <td>{{ $exam->academicSession->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('exams.edit', $exam) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this exam?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{ $exams->links() }}
            @else
                <p>No exams found.</p>
            @endif
        </div>
    </div>
@stop
