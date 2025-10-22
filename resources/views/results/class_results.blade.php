@extends('adminlte::page')

@section('title', 'Class Results')

@section('content_header')
    <h1 class="text-center text-success">Class Results</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- ================= Filter Form ================= --}}
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

        {{-- ================= Export Buttons ================= --}}
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

        {{-- ================= Summary Boxes ================= --}}
        @php
            $totalStudents = count($studentsData);
            $averageGPA = $totalStudents ? number_format(collect($studentsData)->avg('gpa'), 2) : 'N/A';
            $highestPoints = $totalStudents ? max(collect($studentsData)->pluck('total_points')->toArray()) : 0;
            $divisionCounts = collect($studentsData)->groupBy('division')->map->count();

            // Subject averages
            $subjectAverages = [];
            if ($totalStudents) {
                $allSubjects = collect($studentsData)->flatMap(fn($s) => $s['subjectsData']);
                $subjectAverages = $allSubjects->groupBy('name')->map(fn($group) => [
                    'average_mark' => number_format($group->avg('mark'), 2),
                    'average_gpa' => number_format($group->avg('point') / 4, 2),
                    'type' => $group->first()['type'] ?? '—'
                ])->sortKeys();

                $bestSubject = $subjectAverages->sortByDesc('average_mark')->keys()->first();
                $worstSubject = $subjectAverages->sortBy('average_mark')->keys()->first();
            }
        @endphp

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $totalStudents }}</h3>
                        <p>Total Students</p>
                    </div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $averageGPA }}</h3>
                        <p>Average GPA</p>
                    </div>
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $highestPoints }}</h3>
                        <p>Highest Total Points</p>
                    </div>
                    <div class="icon"><i class="fas fa-trophy"></i></div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h4>Divisions</h4>
                        <p>
                            I: {{ $divisionCounts['I'] ?? 0 }} |
                            II: {{ $divisionCounts['II'] ?? 0 }} |
                            III: {{ $divisionCounts['III'] ?? 0 }} |
                            IV: {{ $divisionCounts['IV'] ?? 0 }} |
                            0: {{ $divisionCounts['0'] ?? 0 }}
                        </p>
                    </div>
                    <div class="icon"><i class="fas fa-medal"></i></div>
                </div>
            </div>
        </div>

        {{-- ================= Best & Weakest Subjects ================= --}}
        @if(!empty($subjectAverages))
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="small-box bg-gradient-success">
                    <div class="inner">
                        <h4>🏆 Best Performing Subject</h4>
                        <p><strong>{{ $bestSubject }}</strong> — Avg: {{ $subjectAverages[$bestSubject]['average_mark'] ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="small-box bg-gradient-danger">
                    <div class="inner">
                        <h4>⚠️ Weakest Subject</h4>
                        <p><strong>{{ $worstSubject }}</strong> — Avg: {{ $subjectAverages[$worstSubject]['average_mark'] ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ================= Results Table ================= --}}
        @if(!empty($studentsData))
            @php
                $allSubjects = collect($studentsData)
                    ->flatMap(fn($s) => $s['subjectsData'])
                    ->unique('name')
                    ->values();

                $sortedSubjects = $allSubjects->sortBy([
                    fn($s) => $s['type'] !== 'core',
                    fn($s) => $s['name']
                ])->values();

                $gradeColors = [
                    'A' => '#d4edda',
                    'B' => '#d1ecf1',
                    'C' => '#fff3cd',
                    'D' => '#ffe5b4',
                    'F' => '#f8d7da',
                ];
            @endphp

            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="table-success">
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            @foreach($sortedSubjects as $subject)
                                <th>{{ $subject['name'] }}</th>
                            @endforeach
                            <th>Total Marks (Best 7)</th>
                            <th>Average (Best 7)</th>
                            <th>Division</th>
                            <th>Total Points</th>
                            <th>GPA</th>
                            <th>Position</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentsData as $i => $data)
                            @php
                                $studentSubjects = collect($data['subjectsData'])->keyBy('name');
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $data['student']->first_name }} {{ $data['student']->last_name }}</td>

                                {{-- Subject Columns --}}
                                @foreach($sortedSubjects as $subject)
                                    @php
                                        $subjectData = $studentSubjects->get($subject['name']);
                                        $grade = $subjectData['grade'] ?? '—';
                                        $mark = $subjectData['mark'] ?? '—';
                                        $bg = $gradeColors[$grade] ?? '#f9f9f9';
                                    @endphp
                                    <td style="background-color: {{ $bg }}">
                                        <div style="display:flex;flex-direction:column;align-items:center;">
                                            <span><strong>{{ $mark }}</strong></span>
                                            <small class="text-muted">({{ $grade }})</small>
                                        </div>
                                    </td>
                                @endforeach

                                {{-- Totals --}}
                                @php
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
                                <td>{{ $data['position'] }}/{{ $totalStudents }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ================= Subject Performance Summary Table ================= --}}
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-book-open"></i> Subject Performance Summary</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped table-bordered text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Average Mark</th>
                                <th>Average GPA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjectAverages as $subject => $stats)
                                <tr class="{{ $subject === $bestSubject ? 'table-success' : ($subject === $worstSubject ? 'table-danger' : '') }}">
                                    <td>{{ $subject }}</td>
                                    <td>{{ ucfirst($stats['type']) }}</td>
                                    <td>{{ $stats['average_mark'] }}</td>
                                    <td>{{ $stats['average_gpa'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="text-center text-muted">No results found. Please select class and exam above.</p>
        @endif
    </div>
</div>

<style>
    table.table td, table.table th {
        vertical-align: middle;
        padding: 6px;
    }
</style>
@stop
