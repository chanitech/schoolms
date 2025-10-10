@extends('adminlte::page')

@section('title', 'Marks List')

@section('content_header')
    <h1>Marks List</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Marks</span>
        <a href="{{ route('marks.create') }}" class="btn btn-success btn-sm">Add Mark</a>
    </div>
    <div class="card-body">

        {{-- Filters --}}
        <form method="GET" action="{{ route('marks.index') }}" class="mb-3 row">
            <div class="col-md-3">
                <label for="session">Academic Session</label>
                <select name="academic_session_id" id="session" class="form-control">
                    <option value="">All Sessions</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="class">Class</label>
                <select name="class_id" id="class" class="form-control">
                    <option value="">All Classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="subject">Subject</label>
                <select name="subject_id" id="subject" class="form-control">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mt-4">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>

        {{-- Marks Table --}}
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Exam</th>
                    <th>Mark</th>
                    <th>Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($marks as $mark)
                    <tr>
                        <td>{{ $mark->student->first_name }} {{ $mark->student->last_name }}</td>
                        <td>{{ $mark->student->class->name ?? '-' }}</td>
                        <td>{{ $mark->subject->name }}</td>
                        <td>{{ $mark->exam->name }}</td>
                        <td>{{ $mark->mark }}</td>
                        <td>{{ $mark->grade->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('marks.edit', $mark->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('marks.destroy', $mark->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this mark?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No marks found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center">
            {{ $marks->links() }}
        </div>
    </div>
</div>
@stop
