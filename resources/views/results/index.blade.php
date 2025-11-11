@extends('adminlte::page')

@section('title', 'Student Results')

@section('content_header')
    <h1>Student Results</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Filter Form --}}
        <form method="GET" class="row mb-3 g-2 align-items-end">
            <div class="col-md-4">
                <label for="class_id" class="form-label">Class</label>
                <select name="class_id" id="class_id" class="form-control">
                    <option value="">-- All Classes --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="session_id" class="form-label">Academic Session</label>
                <select name="session_id" id="session_id" class="form-control">
                    <option value="">-- All Sessions --</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('results.index') }}" class="btn btn-secondary">
                    <i class="fas fa-sync"></i> Reset
                </a>
            </div>
        </form>

        {{-- Student Table --}}
        @if($students->count())
            <table class="table table-bordered table-striped">
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
                            <a href="{{ route('results.show', $student) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View Result
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            {{ $students->links() }}
        @else
            <p class="text-muted">No students found.</p>
        @endif
    </div>
</div>
@stop
