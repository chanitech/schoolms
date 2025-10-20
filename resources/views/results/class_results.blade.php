@extends('adminlte::page')

@section('title', 'Class Results')

@section('content_header')
    <h1 class="text-center text-success">Class Results</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- Filter Form --}}
        <form method="GET" action="{{ route('results.class') }}" class="mb-3 row g-2">
            <div class="col-md-4">
                <select name="class_id" class="form-control" required>
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <select name="exam_id" class="form-control" required>
                    <option value="">-- Select Exam --</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" {{ $selectedExamId == $exam->id ? 'selected' : '' }}>
                            {{ $exam->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">View Results</button>
            </div>
        </form>

        {{-- Export Buttons --}}
        @if(!empty($studentsData))
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('results.export.excel', request()->query()) }}" class="btn btn-outline-success me-2">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('results.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
        @endif

        {{-- Summary Boxes --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ count($studentsData) ?: 0 }}</h3>
                        <p>Total Students</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ !empty($studentsData) ? number_format(collect($studentsData)->avg('gpa'), 2) : 'N/A' }}</h3>
                        <p>Average GPA</p>
                    </div>
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ !empty($studentsData) ? max(collect($studentsData)->pluck('total_points')->toArray()) : 0 }}</h3>
                        <p>Highest Total Points</p>
                    </div>
                    <div class="icon"><i class="fas fa-trophy"></i></div>
                </div>
            </div>
        </div>

        {{-- Results Table --}}
        @if(!empty($studentsData))
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            @php
                                $subjects = collect($studentsData)->first()['subjectsData'] ?? [];
                            @endphp
                            @foreach($subjects as $subject)
                                <th>{{ $subject['name'] }}</th>
                            @endforeach
                            <th>Total Marks (Best 7)</th>
                            <th>Average (Best 7)</th>
                            <th>Division</th>
                            <th>Total Points (Best 7)</th>
                            <th>GPA</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsData as $i => $data)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $data['student']->first_name }} {{ $data['student']->last_name }}</td>
                            @foreach($data['subjectsData'] as $subject)
                                <td>{{ $subject['mark'] }} ({{ $subject['grade'] }})</td>
                            @endforeach
                            @php
                                // NECTA Best 7 logic
                                $coreSubjects = collect($data['subjectsData'])->where('type', 'core')->sortByDesc('mark');
                                $electives = collect($data['subjectsData'])->where('type', 'elective')->sortByDesc('mark');

                                $bestSubjects = $coreSubjects->take(7);
                                if ($bestSubjects->count() < 7) {
                                    $bestSubjects = $bestSubjects->merge($electives->take(7 - $bestSubjects->count()));
                                }

                                $totalMarks = $bestSubjects->sum('mark');
                                $subjectCount = $bestSubjects->count();
                                $average = $subjectCount ? number_format($totalMarks / $subjectCount, 2) : 0;
                                $totalPoints = $bestSubjects->sum('point');
                            @endphp
                            <td>{{ $totalMarks }}</td>
                            <td>{{ $average }}</td>
                            <td>{{ $data['division'] }}</td>
                            <td>{{ $totalPoints }}</td>
                            <td>{{ number_format($data['gpa'], 2) }}</td>
                            <td>{{ $data['position'] }}/{{ count($studentsData) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-muted">No results found. Please select class and exam above.</p>
        @endif
    </div>
</div>
@stop
