@extends('adminlte::page')

@section('title', 'Student Result')

@section('content_header')
    <h1>Student Result</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <h3>{{ $student->first_name }} {{ $student->last_name }}</h3>
        <p>Class: {{ $student->class->name ?? '-' }}</p>
        <p>Academic Session: {{ $student->academicSession->name ?? '-' }}</p>

        <hr>

        <h4>GPA & Division</h4>
        <p>Total Points (Best 7): <strong>{{ $result['total_points'] }}</strong></p>
        <p>GPA: <strong>{{ $result['gpa'] }}</strong></p>
        <p>Division: <strong>{{ $result['division'] }}</strong></p>
    </div>
</div>
@stop
