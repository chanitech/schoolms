@extends('adminlte::page')

@section('title', 'Class Results')

@section('content_header')
    <h1 class="text-center text-success">Class Results</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        {{-- ================= Filter Form ================= --}}
        <form method="GET" action="{{ route('results.class') }}" id="filterForm" class="mb-3 row g-2">
            <div class="col-md-3">
                <select name="academic_session_id" id="academic_session_id" class="form-control" required>
                    <option value="">-- Select Academic Session --</option>
                    @foreach($academicSessions as $session)
                        <option value="{{ $session->id }}" {{ $selectedAcademicSessionId == $session->id ? 'selected' : '' }}>
                            {{ $session->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="department_id" id="department_id" class="form-control">
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ $selectedDepartmentId == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="class_id" id="class_id" class="form-control" required>
                    <option value="">-- Select Class --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <select name="exam_id" id="exam_id" class="form-control" required>
                    <option value="">-- Select Exam --</option>
                </select>
            </div>

            <div class="col-md-1">
                <button type="submit" class="btn btn-success w-100">View</button>
            </div>
        </form>

        {{-- ================= Export Buttons ================= --}}
        @if(!empty($studentsData) && $studentsData->count())
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('results.export.excel', request()->query()) }}" class="btn btn-outline-success me-2">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('results.export.pdf', request()->query()) }}" class="btn btn-outline-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
        @endif

        @if(!empty($studentsData) && $studentsData->count())
        @php
            // The controller returns $studentsData already sorted according to NECTA points ranking.
            $studentsCollection = collect($studentsData);
            $totalStudents      = $studentsCollection->count();

            // Summary stats
            $averageGPA    = $totalStudents ? number_format($studentsCollection->avg('gpa'), 2) : 'N/A';
            $bestPoints    = $studentsCollection->min('total_points');
            $worstPoints   = $studentsCollection->max('total_points');

            // Division counts (including '0' for unclassified)
            $divisionCounts = $studentsCollection->groupBy('division')->map->count();

            // Separately, the ranked-only subset (eligible students with a numeric position)
            $rankedOnly = $studentsCollection->filter(fn($s) => is_numeric($s['position'] ?? null));

            // Top 10 = first 10 eligible students (already in correct order)
            $topTen = $rankedOnly->take(10);

            // Bottom 10 = students NOT in top 10, with highest total points (worst performance)
            $bottomTen = $studentsCollection
                ->whereNotIn('student.id', $topTen->pluck('student.id'))
                ->sortByDesc('total_points')
                ->take(10)
                ->sortBy('total_points')
                ->values();

            // Top performer = first eligible student (rank 1)
            $topPerformer = $rankedOnly->first();
            $topAverage   = $topPerformer['average_mark'] ?? 0;

            // Gender breakdown
            $boys       = $studentsCollection->filter(fn($s) => ($s['student']->gender ?? '') === 'male');
            $girls      = $studentsCollection->filter(fn($s) => ($s['student']->gender ?? '') === 'female');
            $boysCount  = $boys->count();
            $girlsCount = $girls->count();
            $boysAvgGPA  = $boysCount  ? number_format($boys->avg('gpa'),  2) : 'N/A';
            $girlsAvgGPA = $girlsCount ? number_format($girls->avg('gpa'), 2) : 'N/A';
            $boysDivisionCounts  = $boys->groupBy('division')->map->count();
            $girlsDivisionCounts = $girls->groupBy('division')->map->count();

            // Grade distribution across ALL subjects
            $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
            foreach ($studentsData as $student) {
                foreach ($student['subjectsData'] as $subject) {
                    $grade = $subject['grade'] ?? '';
                    if (array_key_exists($grade, $gradeCounts)) {
                        $gradeCounts[$grade]++;
                    }
                }
            }
            $totalGradeEntries = array_sum($gradeCounts);

            // Subject averages
            $allSubjectRows = $studentsCollection->flatMap(fn($s) => $s['subjectsData']);
            $subjectAverages = $allSubjectRows->groupBy('name')->map(fn($group) => [
                'average_mark' => number_format($group->whereNotNull('mark')->avg('mark'), 2),
                'average_gpa'  => number_format($group->avg('point'), 2),
                'type'         => $group->first()['type'] ?? '—',
            ])->sortKeys();

            $rankedSubjects = $subjectAverages->sortByDesc('average_mark');
            $bestSubject    = $rankedSubjects->keys()->first();
            $worstSubject   = $rankedSubjects->keys()->last();

            // Subject grade counts
            $subjectGradeCounts = [];
            foreach ($studentsData as $student) {
                foreach ($student['subjectsData'] as $subject) {
                    $name  = $subject['name'];
                    $grade = $subject['grade'] ?? '';
                    if (!isset($subjectGradeCounts[$name])) {
                        $subjectGradeCounts[$name] = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
                    }
                    if (array_key_exists($grade, $subjectGradeCounts[$name])) {
                        $subjectGradeCounts[$name][$grade]++;
                    }
                }
            }

            // Unique subjects for columns (core first, then alpha)
            $allSubjects = $studentsCollection
                ->flatMap(fn($s) => $s['subjectsData'])
                ->unique('name')
                ->sortBy([
                    fn($s) => $s['type'] !== 'core',
                    fn($s) => $s['name'],
                ])->values();

            $gradeColors = [
                'A' => '#d4edda', 'B' => '#d1ecf1',
                'C' => '#fff3cd', 'D' => '#ffe5b4',
                'F' => '#f8d7da',
            ];
        @endphp

        {{-- ================= Top Performer (Rank 1) ================= --}}
        @if($topPerformer)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-gradient-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0"><i class="fas fa-trophy"></i> Top Performer</h3>
                                <h2 class="mt-2">{{ $topPerformer['student']->first_name }} {{ $topPerformer['student']->last_name }}</h2>
                                <p class="mb-0">Average Mark (Best 7): <strong>{{ number_format($topAverage, 2) }}%</strong></p>
                                <p>Division: <strong>{{ $topPerformer['division'] ?? 'N/A' }}</strong>
                                   | GPA: <strong>{{ number_format($topPerformer['gpa'] ?? 0, 2) }}</strong>
                                   | Total Points: <strong>{{ $topPerformer['total_points'] }}</strong>
                                </p>
                            </div>
                            <div class="text-right">
                                <i class="fas fa-award fa-4x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ================= Summary Boxes ================= --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner"><h3>{{ $totalStudents }}</h3><p>Total Students</p></div>
                    <div class="icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner"><h3>{{ $averageGPA }}</h3><p>Average GPA</p></div>
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $bestPoints }}</h3>
                        <p>Best Total Points (Rank 1)</p>
                    </div>
                    <div class="icon"><i class="fas fa-trophy"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $worstPoints }}</h3>
                        <p>Highest Total Points (Last)</p>
                    </div>
                    <div class="icon"><i class="fas fa-arrow-down"></i></div>
                </div>
            </div>
            <div class="col-md-12 mt-2">
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

        {{-- ================= Grade Distribution & Gender Performance ================= --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Grade Distribution (All Subjects)</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($gradeCounts as $grade => $count)
                                @php
                                    $color = match($grade) {
                                        'A' => 'success', 'B' => 'primary',
                                        'C' => 'info',    'D' => 'warning',
                                        'F' => 'danger',  default => 'secondary'
                                    };
                                    $pct = $totalGradeEntries
                                        ? round(($count / $totalGradeEntries) * 100, 1)
                                        : 0;
                                @endphp
                                <div class="col-md-2 col-sm-4 col-6 mb-2">
                                    <div class="small-box bg-{{ $color }}">
                                        <div class="inner">
                                            <h3>{{ $count }}</h3>
                                            <p>Grade {{ $grade }}</p>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-{{ $color }}" style="width:{{ $pct }}%"></div>
                                        </div>
                                        <div class="small-box-footer">{{ $pct }}% of grades</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Gender Performance Summary</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead>
                                    <tr>
                                        <th>Gender</th><th>Count</th><th>Average GPA</th>
                                        <th>Div I</th><th>Div II</th><th>Div III</th><th>Div IV</th><th>Div 0</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="bg-info">
                                        <td>Male</td>
                                        <td>{{ $boysCount }}</td>
                                        <td>{{ $boysAvgGPA }}</td>
                                        @foreach(['I','II','III','IV','0'] as $div)
                                            <td>{{ $boysDivisionCounts[$div] ?? 0 }}</td>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-success">
                                        <td>Female</td>
                                        <td>{{ $girlsCount }}</td>
                                        <td>{{ $girlsAvgGPA }}</td>
                                        @foreach(['I','II','III','IV','0'] as $div)
                                            <td>{{ $girlsDivisionCounts[$div] ?? 0 }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TOP 10 STUDENTS (by rank) ================= --}}
        <div class="card mt-3">
            <div class="card-header bg-gradient-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Top 10 Students</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Student Name</th>
                                <th>Average Mark (Best 7)</th>
                                <th>Total Points</th>
                                <th>GPA</th>
                                <th>Division</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topTen as $student)
                                <tr class="{{ $loop->first ? 'table-warning' : ($loop->index == 1 ? 'table-secondary' : ($loop->index == 2 ? 'table-success' : '')) }}">
                                    <td>
                                        <span class="badge {{ $loop->first ? 'badge-warning' : ($loop->index == 1 ? 'badge-secondary' : ($loop->index == 2 ? 'badge-success' : 'badge-primary')) }}"
                                              style="font-size:14px; padding:6px 12px;">
                                            {{ $student['position'] }}
                                        </span>
                                    </td>
                                    <td class="text-left">
                                        {{ $student['student']->first_name }} {{ $student['student']->last_name }}
                                    </td>
                                    <td>{{ number_format($student['average_mark'], 2) }}%</td>
                                    <td>{{ $student['total_points'] }}</td>
                                    <td>{{ number_format($student['gpa'], 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ in_array($student['division'], ['I','II']) ? 'success' : (in_array($student['division'], ['III']) ? 'warning' : (in_array($student['division'], ['IV']) ? 'secondary' : 'dark')) }}">
                                            {{ $student['division'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No eligible students found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= BOTTOM 10 STUDENTS (no overlap with top 10) ================= --}}
        <div class="card mt-3">
            <div class="card-header bg-gradient-danger text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Lowest Performance (Bottom 10 by Points)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Student Name</th>
                                <th>Average Mark (Best 7)</th>
                                <th>Total Points</th>
                                <th>GPA</th>
                                <th>Division</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bottomTen as $student)
                                <tr>
                                    <td>
                                        <span class="badge badge-danger" style="font-size:14px; padding:6px 12px;">
                                            {{ is_numeric($student['position'] ?? null) ? $student['position'] : '–' }}
                                        </span>
                                    </td>
                                    <td class="text-left">
                                        {{ $student['student']->first_name }} {{ $student['student']->last_name }}
                                    </td>
                                    <td>{{ number_format($student['average_mark'], 2) }}%</td>
                                    <td>{{ $student['total_points'] }}</td>
                                    <td>{{ number_format($student['gpa'], 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ in_array($student['division'], ['I','II']) ? 'success' : (in_array($student['division'], ['III']) ? 'warning' : (in_array($student['division'], ['IV']) ? 'secondary' : 'dark')) }}">
                                            {{ $student['division'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No students found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= Detailed Student Results (alphabetical) ================= --}}
        <div class="card mt-3">
            <div class="card-header bg-gradient-success text-white">
                <h5 class="mb-0"><i class="fas fa-table"></i> Detailed Student Results</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped text-center align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                @foreach($allSubjects as $subject)
                                    <th title="{{ ucfirst($subject['type']) }}">
                                        {{ $subject['name'] }}
                                        <small class="d-block text-muted" style="font-weight:normal;">
                                            {{ $subject['type'] === 'core' ? 'Core' : 'Elective' }}
                                        </small>
                                    </th>
                                @endforeach
                                <th>Avg Mark (Best 7)</th>
                                <th>Total Points</th>
                                <th>GPA</th>
                                <th>Division</th>
                                <th>Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentsCollection->sortBy(fn($d) => $d['student']->first_name . ' ' . $d['student']->last_name)->values() as $i => $data)
                                @php
                                    $studentSubjects = collect($data['subjectsData'])->keyBy('name');
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="text-left font-weight-bold">
                                        {{ $data['student']->first_name }} {{ $data['student']->last_name }}
                                    </td>

                                    @foreach($allSubjects as $subject)
                                        @php
                                            $sd    = $studentSubjects->get($subject['name']);
                                            $grade = $sd['grade'] ?? '—';
                                            $mark  = $sd['mark']  ?? '—';
                                            $bg    = $gradeColors[$grade] ?? '#f9f9f9';
                                        @endphp
                                        <td style="background-color:{{ $bg }}">
                                            <strong>{{ $mark }}</strong>
                                            <small class="d-block text-muted">({{ $grade }})</small>
                                        </td>
                                    @endforeach

                                    <td><strong>{{ number_format($data['average_mark'], 2) }}%</strong></td>
                                    <td>{{ $data['total_points'] }}</td>
                                    <td>{{ number_format($data['gpa'], 2) }}</td>
                                    <td>
                                        <span class="badge badge-{{ in_array($data['division'], ['I','II']) ? 'success' : (in_array($data['division'], ['III']) ? 'warning' : (in_array($data['division'], ['IV']) ? 'secondary' : 'dark')) }}">
                                            {{ $data['division'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ is_numeric($data['position'] ?? null) ? $data['position'].'/'.$totalStudents : '–' }}</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ================= Subject Performance Summary ================= --}}
        <div class="card mt-3">
            <div class="card-header bg-gradient-info text-white">
                <h5 class="mb-0"><i class="fas fa-book-open"></i> Subject Performance Summary (Ranked by Average Mark)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Average Mark</th>
                                <th>Average GPA</th>
                                <th>A</th><th>B</th><th>C</th><th>D</th><th>F</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rankedSubjects as $subject => $stats)
                                @php
                                    $gc = $subjectGradeCounts[$subject] ?? ['A'=>0,'B'=>0,'C'=>0,'D'=>0,'F'=>0];
                                @endphp
                                <tr class="{{ $subject === $bestSubject ? 'table-success' : ($subject === $worstSubject ? 'table-danger' : '') }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $subject }}</td>
                                    <td>{{ ucfirst($stats['type']) }}</td>
                                    <td>{{ $stats['average_mark'] }}</td>
                                    <td>{{ $stats['average_gpa'] }}</td>
                                    <td>{{ $gc['A'] }}</td>
                                    <td>{{ $gc['B'] }}</td>
                                    <td>{{ $gc['C'] }}</td>
                                    <td>{{ $gc['D'] }}</td>
                                    <td>{{ $gc['F'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @else
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No results found. Please select filters above.
            </div>
        @endif
    </div>
</div>

<style>
    table.table td, table.table th { vertical-align: middle; padding: 6px; }
    .small-box .progress { margin-top: 5px; height: 5px; }
    .bg-gradient-warning { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
    .bg-gradient-primary { background: linear-gradient(135deg, #1F7A63 0%, #166c58 100%); }
    .bg-gradient-danger  { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
    .bg-gradient-info    { background: linear-gradient(135deg, #17a2b8 0%, #0f6b7a 100%); }
    .badge-primary   { background-color: #1F7A63; }
    .badge-warning   { background-color: #f39c12; }
    .badge-secondary { background-color: #6c757d; }
    .badge-success   { background-color: #28a745; }
    .badge-danger    { background-color: #dc3545; }
    .badge-dark      { background-color: #343a40; }
    .table-warning  { background-color: #fff3cd !important; }
    .table-secondary { background-color: #e9ecef !important; }
    .table-success  { background-color: #d4edda !important; }
</style>
@stop

@section('js')
<script>
$(document).ready(function () {
    function loadExams() {
        let sessionId   = $('#academic_session_id').val();
        let examSelect  = $('#exam_id');
        let selectedExam = "{{ $selectedExamId }}";

        if (sessionId) {
            examSelect.html('<option value="">Loading...</option>');
            $.ajax({
                url: "{{ route('marks.exams.by.session') }}",
                type: 'GET',
                data: { session_id: sessionId },
                success: function (exams) {
                    let options = '<option value="">-- Select Exam --</option>';
                    if (exams.length > 0) {
                        exams.forEach(function (exam) {
                            let sel = (selectedExam == exam.id) ? 'selected' : '';
                            options += `<option value="${exam.id}" ${sel}>${exam.name}</option>`;
                        });
                    } else {
                        options = '<option value="" disabled>No exams found for this session</option>';
                    }
                    examSelect.html(options);
                },
                error: function () {
                    examSelect.html('<option value="">Error loading exams</option>');
                }
            });
        } else {
            examSelect.html('<option value="">-- Select Exam --</option>');
        }
    }

    $('#academic_session_id').change(loadExams);

    if ($('#academic_session_id').val()) {
        loadExams();
    }
});
</script>
@stop