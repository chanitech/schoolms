@extends('adminlte::page')

@section('title', 'View Classroom Guidance')

@section('content_header')
    <h1><i class="fas fa-eye"></i> Classroom Guidance Details</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <tr>
                <th>Class</th>
                <td>{{ $classroomGuidance->schoolClass?->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ $classroomGuidance->date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Tasks</th>
                <td>{{ $classroomGuidance->tasks }}</td>
            </tr>
            <tr>
                <th>Achievements</th>
                <td>{{ $classroomGuidance->achievements }}</td>
            </tr>
            <tr>
                <th>Challenges</th>
                <td>{{ $classroomGuidance->challenges }}</td>
            </tr>
            <tr>
                <th>Created By</th>
                <td>{{ $classroomGuidance->creator?->name ?? 'N/A' }}</td>
            </tr>
        </table>

        <a href="{{ route('classroom-guidances.index') }}" class="btn btn-secondary mt-3">Back</a>
    </div>
</div>
@stop
