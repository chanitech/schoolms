@extends('adminlte::page')

@section('title', 'HR Evaluation Report')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap">
    <div class="d-flex align-items-center">
        <a href="{{ route('hr-reports.index') }}" class="btn btn-secondary btn-sm mr-3">
            <i class="fas fa-arrow-left mr-1"></i>Back
        </a>
        <h1 class="mb-0 text-primary">HR Evaluation Report</h1>
    </div>
    <div class="mt-1 mt-md-0">
        <a href="{{ route('hr.reports.export.evaluation', ['type' => 'excel']) }}" class="btn btn-success btn-sm mr-1">
            <i class="fas fa-file-excel mr-1"></i>Export Excel
        </a>
        <a href="{{ route('hr.reports.export.evaluation', ['type' => 'pdf']) }}" class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf mr-1"></i>Export PDF
        </a>
    </div>
</div>
@stop

@section('content')

{{-- Scoring key --}}
<div class="alert alert-light border small mb-3">
    <i class="fas fa-info-circle text-info mr-1"></i>
    <strong>Scoring weights:</strong>
    <strong>Attendance 30%</strong> + <strong>Job Cards 20%</strong> + <strong>Topic Coverage 20%</strong> + <strong>Session Attendance 30%</strong>
    &nbsp;·&nbsp; Missing metrics are excluded and weights redistributed proportionally.
    <a href="{{ route('topic-coverage.index') }}" class="ml-2">
        <i class="fas fa-tasks mr-1"></i>Topic Coverage
    </a>
    <a href="{{ route('timetables.my-sessions') }}" class="ml-2">
        <i class="fas fa-chalkboard-teacher mr-1"></i>Session Logs
    </a>
</div>

{{-- Staff Evaluation Table --}}
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Overall Staff Performance Evaluation</h4>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0" id="eval-table">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Staff Name</th>
                        <th>Department</th>
                        <th class="text-center">Attendance</th>
                        <th class="text-center">Job Cards</th>
                        <th class="text-center">Topic Coverage</th>
                        <th class="text-center">Session Attendance</th>
                        <th class="text-center">Overall Score</th>
                        <th class="text-center">Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($evaluations as $i => $eval)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="font-weight-bold">{{ $eval->staff_name }}</td>
                        <td>{{ $eval->department }}</td>
                        <td class="text-center">
                            @php $ac = $eval->attendance; @endphp
                            <span class="badge badge-{{ $ac >= 80 ? 'success' : ($ac >= 60 ? 'warning' : 'danger') }}">
                                {{ $ac }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @php $jc = $eval->job_card_rate; @endphp
                            <span class="badge badge-{{ $jc >= 80 ? 'success' : ($jc >= 60 ? 'warning' : 'danger') }}">
                                {{ $jc }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($eval->lesson_rate !== null)
                                @php $lr = $eval->lesson_rate; @endphp
                                <span class="badge badge-{{ $lr >= 80 ? 'success' : ($lr >= 60 ? 'warning' : 'danger') }}">
                                    {{ $lr }}%
                                </span>
                                <br>
                                <small class="text-muted">{{ $eval->lesson_covered }}/{{ $eval->lesson_total }} subtopics</small>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($eval->session_rate !== null)
                                @php $sr = $eval->session_rate; @endphp
                                <span class="badge badge-{{ $sr >= 80 ? 'success' : ($sr >= 60 ? 'warning' : 'danger') }}">
                                    {{ $sr }}%
                                </span>
                                <br>
                                <small class="text-muted">{{ $eval->sessions_taught }}/{{ $eval->sessions_total }} sessions</small>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $sc = $eval->score; @endphp
                            <strong class="text-{{ $sc >= 80 ? 'success' : ($sc >= 60 ? 'warning' : 'danger') }}">
                                {{ $sc }}
                            </strong>
                        </td>
                        <td class="text-center">
                            @if($sc >= 80)
                                <span class="badge badge-success"><i class="fas fa-star mr-1"></i>Excellent</span>
                            @elseif($sc >= 65)
                                <span class="badge badge-primary">Good</span>
                            @elseif($sc >= 50)
                                <span class="badge badge-warning">Average</span>
                            @else
                                <span class="badge badge-danger">Needs Improvement</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No evaluation data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Department Performance Summary --}}
<div class="card shadow mb-4">
    <div class="card-header bg-info text-white">
        <h4 class="mb-0">Department Performance Summary</h4>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Department</th>
                        <th class="text-center">Average Score</th>
                        <th class="text-center">Staff Count</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departmentScores as $i => $dept)
                    @php $ds = $dept->average_score; @endphp
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="font-weight-bold">{{ $dept->department }}</td>
                        <td class="text-center">
                            <strong class="text-{{ $ds >= 80 ? 'success' : ($ds >= 60 ? 'warning' : 'danger') }}">
                                {{ $ds }}
                            </strong>
                        </td>
                        <td class="text-center">{{ $dept->staff_count ?? '—' }}</td>
                        <td>
                            <div class="progress" style="height:8px;min-width:100px;">
                                <div class="progress-bar bg-{{ $ds >= 80 ? 'success' : ($ds >= 60 ? 'warning' : 'danger') }}"
                                     style="width:{{ $ds }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="card shadow">
    <div class="card-header bg-secondary text-white">
        <h4 class="mb-0">Visual Analysis</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <canvas id="staffPerformanceChart" height="200"></canvas>
            </div>
            <div class="col-md-6 mb-3">
                <canvas id="lessonCompletionChart" height="200"></canvas>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <canvas id="sessionAttendanceChart" height="200"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="departmentPerformanceChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const staffNames    = {!! json_encode($evaluations->pluck('staff_name')) !!};
    const staffScores   = {!! json_encode($evaluations->pluck('score')) !!};
    const lessonRates   = {!! json_encode($evaluations->map(fn($e) => $e->lesson_rate)) !!};
    const sessionRates  = {!! json_encode($evaluations->map(fn($e) => $e->session_rate)) !!};
    const deptNames     = {!! json_encode($departmentScores->pluck('department')) !!};
    const deptScores    = {!! json_encode($departmentScores->pluck('average_score')) !!};

    // Staff Overall Scores
    new Chart(document.getElementById('staffPerformanceChart'), {
        type: 'bar',
        data: {
            labels: staffNames,
            datasets: [{
                label: 'Overall Score',
                data: staffScores,
                backgroundColor: staffScores.map(s => s >= 80 ? 'rgba(40,167,69,.7)' : s >= 60 ? 'rgba(255,193,7,.7)' : 'rgba(220,53,69,.7)'),
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false }, title: { display: true, text: 'Staff Overall Scores' } },
            scales: { x: { beginAtZero: true, max: 100 } }
        }
    });

    // Topic Coverage (only staff with data)
    const staffWithLesson = staffNames.filter((_, i) => lessonRates[i] !== null);
    const lessonValues    = lessonRates.filter(r => r !== null);
    new Chart(document.getElementById('lessonCompletionChart'), {
        type: 'bar',
        data: {
            labels: staffWithLesson.length ? staffWithLesson : ['No data'],
            datasets: [{
                label: 'Topic Coverage %',
                data: staffWithLesson.length ? lessonValues : [0],
                backgroundColor: lessonValues.map(r => r >= 80 ? 'rgba(40,167,69,.7)' : r >= 60 ? 'rgba(255,193,7,.7)' : 'rgba(220,53,69,.7)'),
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false }, title: { display: true, text: 'Topic Coverage by Teacher' } },
            scales: { x: { beginAtZero: true, max: 100 } }
        }
    });

    // Session Attendance per Teacher
    const staffWithSession = staffNames.filter((_, i) => sessionRates[i] !== null);
    const sessionValues    = sessionRates.filter(r => r !== null);
    new Chart(document.getElementById('sessionAttendanceChart'), {
        type: 'bar',
        data: {
            labels: staffWithSession.length ? staffWithSession : ['No data'],
            datasets: [{
                label: 'Session Attendance %',
                data: staffWithSession.length ? sessionValues : [0],
                backgroundColor: sessionValues.map(r => r >= 80 ? 'rgba(40,167,69,.7)' : r >= 60 ? 'rgba(255,193,7,.7)' : 'rgba(220,53,69,.7)'),
                borderWidth: 1,
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false }, title: { display: true, text: 'Session Attendance by Teacher' } },
            scales: { x: { beginAtZero: true, max: 100 } }
        }
    });

    // Department Performance
    new Chart(document.getElementById('departmentPerformanceChart'), {
        type: 'bar',
        data: {
            labels: deptNames,
            datasets: [{
                label: 'Avg Score',
                data: deptScores,
                backgroundColor: deptScores.map(s => s >= 80 ? 'rgba(40,167,69,.7)' : s >= 60 ? 'rgba(255,193,7,.7)' : 'rgba(220,53,69,.7)'),
                borderWidth: 1,
            }]
        },
        options: {
            plugins: { legend: { display: false }, title: { display: true, text: 'Department Average Scores' } },
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });
});
</script>
@stop
