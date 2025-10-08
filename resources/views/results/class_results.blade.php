@extends('adminlte::page')
@section('title', 'Class Results')

@section('content_header')
    <h1 class="text-center text-success">Class Results</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Class & Exam Selector --}}
    <form action="{{ route('results.class') }}" method="GET" class="mb-4 row g-2">
        <div class="col-md-4">
            <select name="class_id" class="form-control border-success">
                <option value="">-- Select Class --</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <select name="exam_id" class="form-control border-success">
                <option value="">-- Select Exam --</option>
                @foreach($exams as $exam)
                    <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                        {{ $exam->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success">View Results</button>
        </div>
    </form>

    {{-- Results Table --}}
    @if(!empty($studentsData))
        <div class="card shadow">
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Total Points</th>
                            <th>GPA</th>
                            <th>Division</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsData as $i => $data)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $data['student']->name }}</td>
                            <td>{{ $data['total_points'] }}</td>
                            <td>{{ number_format($data['gpa'], 2) }}</td>
                            <td>{{ $data['division'] }}</td>
                            <td>{{ $data['position'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@stop
