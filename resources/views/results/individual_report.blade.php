@extends('adminlte::page')

@section('title', 'Student Report')

@section('content')
<div class="card">
    <div class="card-header bg-success text-white">
        <h3 class="mb-0 text-center">{{ $student->first_name }} {{ $student->last_name }} — {{ $exam->name }}</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped text-center">
            <thead class="table-light">
                <tr>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Mark</th>
                    <th>Grade</th>
                    <th>Point</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjectsData as $subject)
                    <tr>
                        <td>{{ $subject['name'] }}</td>
                        <td>{{ ucfirst($subject['type']) }}</td>
                        <td>{{ $subject['mark'] }}</td>
                        <td>{{ $subject['grade'] }}</td>
                        <td>{{ $subject['point'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">
            <p><strong>Total Points (Best 7):</strong> {{ $totalPoints }}</p>
            <p><strong>GPA:</strong> {{ number_format($gpa, 2) }}</p>
            <p><strong>Division:</strong> {{ $division }}</p>
            <p><strong>Position in Class:</strong> {{ $position }}/{{ $totalStudents }}</p>
        </div>
    </div>
</div>
@stop

<style>
    table.table td, table.table th {
        vertical-align: middle;
        padding: 6px;
    }
</style>
