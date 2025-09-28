@extends('adminlte::page')

@section('title', 'Student Result')

@section('content_header')
    <h1>Result for {{ $student->first_name }} {{ $student->last_name }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Mark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($marks as $subject => $mark)
                    <tr>
                        <td>{{ $subject }}</td>
                        <td>{{ $mark }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h4>Total Points: {{ $result['total_points'] }}</h4>
        <h4>GPA: {{ $result['gpa'] }}</h4>
        <h4>Division: {{ $result['division'] }}</h4>

        <a href="{{ route('results.index') }}" class="btn btn-secondary mt-2">Back to Students</a>
    </div>
</div>
@stop
