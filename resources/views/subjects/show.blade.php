@extends('adminlte::page')
@section('title', 'Subject Details')
@section('content_header')
<h1>Subject Details</h1>
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3"><strong>Name</strong><div>{{ $subject->name }}</div></div>
            <div class="col-md-3"><strong>Code</strong><div>{{ $subject->code ?? '—' }}</div></div>
            <div class="col-md-3"><strong>Type</strong><div>{{ ucfirst($subject->type) }}</div></div>
            <div class="col-md-3"><strong>Department</strong><div>{{ $subject->department?->name ?? '—' }}</div></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3"><strong>Active Students</strong><div>{{ $activeStudentsCount }}</div></div>
            <div class="col-md-3"><strong>Withdrawn Students</strong><div>{{ $withdrawnStudentsCount }}</div></div>
        </div>

        <h5>Classes &amp; Teachers</h5>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>Class</th>
                    <th>Teacher</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subject->classes as $class)
                    @php
                        $teacherId = $class->pivot->teacher_id;
                        $teacher = $teachers[$teacherId] ?? null;
                    @endphp
                    <tr>
                        <td>{{ $class->name }}</td>
                        <td>{{ $teacher ? $teacher->first_name.' '.$teacher->last_name : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-muted">No classes assigned.</td></tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('subjects.index') }}" class="btn btn-secondary">Back to Subjects</a>
        @can('edit subjects')
        <a href="{{ route('subjects.edit', $subject->id) }}" class="btn btn-warning">Edit</a>
        @endcan
        @can('view subject assignments')
        <a href="{{ route('subjects.assign_students', $subject->id) }}" class="btn btn-info">Assign Students</a>
        @endcan
    </div>
</div>
@endsection
