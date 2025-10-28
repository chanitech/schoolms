@extends('adminlte::page')

@section('title', 'Student Results')

@section('content_header')
    <h1 class="text-center text-success">Student Result Summary</h1>
@stop

@section('content')
<div class="container-fluid">

    {{-- Flash Messages --}}
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Student Info --}}
    <div class="card shadow mb-4 border-success">
        <div class="card-body d-flex align-items-center">
            <div class="me-4">
                <img src="{{ $student->photo ? asset('storage/'.$student->photo) : asset('vendor/adminlte/dist/img/user2-160x160.jpg') }}"
                     alt="Student Photo"
                     class="img-thumbnail rounded-circle border border-success"
                     style="width:120px; height:120px; object-fit:cover;">
            </div>
            <div>
                <h4 class="mb-1 text-uppercase fw-bold">{{ $student->name }}</h4>
                <p class="mb-1"><strong>Admission No:</strong> {{ $student->admission_no ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Class:</strong> {{ $student->class->name ?? 'N/A' }}</p>
                <p class="mb-1"><strong>Exam:</strong> {{ $exam->name ?? 'All Exams' }}</p>
                <p class="mb-1"><strong>Department:</strong> {{ $departments->firstWhere('id', $selected_department_id)->name ?? 'All Departments' }}</p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form action="{{ route('results.show', $student->id) }}" method="GET" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="department_id" class="form-control border-success">
                    <option value="">-- Select Department --</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $selected_department_id == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <select name="exam_id" class="form-control border-success">
                    <option value="">-- Select Exam --</option>
                    @foreach ($exams as $ex)
                        <option value="{{ $ex->id }}" {{ $selected_exam_id == $ex->id ? 'selected' : '' }}>
                            {{ $ex->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <button class="btn btn-success w-100">
                    <i class="fas fa-search"></i> View Results
                </button>
            </div>
        </div>
    </form>

    {{-- Subject Results --}}
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list"></i> Subject Results</h5>
            @if($selected_department_id)
                <span class="badge bg-light text-success">
                    Department: <strong>{{ $departments->firstWhere('id', $selected_department_id)->name ?? 'N/A' }}</strong>
                </span>
            @endif
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Subject</th>
                        <th>Mark</th>
                        <th>Grade</th>
                        <th>Point</th>
                        <th>Remark</th>
                        <th>Position</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($subjectsData as $subject)
                        @php
                            $badgeClass = match($subject['grade'] ?? '-') {
                                'A' => 'bg-success',
                                'B' => 'bg-primary',
                                'C' => 'bg-warning text-dark',
                                'D' => 'bg-orange text-dark',
                                'E', 'F' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $subject['subject'] }}</td>
                            <td>{{ number_format($subject['mark'], 2) }}</td>
                            <td><span class="badge {{ $badgeClass }} fs-6">{{ $subject['grade'] ?? '-' }}</span></td>
                            <td>{{ $subject['point'] }}</td>
                            <td>{{ $subject['remark'] }}</td>
                            <td>{{ $subject['subject_position'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No results found for this exam or department.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-success">
                    <tr>
                        <th colspan="4" class="text-end">Total Points:</th>
                        <th colspan="3">{{ $totalPoints }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-success">
                <div class="card-body">
                    <h6 class="text-muted">GPA</h6>
                    <h3 class="fw-bold text-success">{{ number_format($result['gpa'], 2) }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center shadow-sm border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Division</h6>
                    <h3 class="fw-bold text-primary">{{ $result['division'] }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-center shadow-sm border-danger">
                <div class="card-body">
                    <h6 class="text-muted">Class Position</h6>
                    <h3 class="fw-bold text-danger">{{ $rank }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    @if(!empty($subjectTrend) || !empty($bestSubjectsOverall))
    <div class="row mt-4">
        {{-- Subject Trend Chart --}}
        @if(!empty($subjectTrend))
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Subject Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="subjectTrendChart" height="250"></canvas>
                </div>
            </div>
        </div>
        @endif

        {{-- Best Subjects Overall Chart --}}
        @if(!empty($bestSubjectsOverall))
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-star"></i> Best Subjects Overall</h5>
                </div>
                <div class="card-body">
                    <canvas id="bestSubjectsChart" height="250"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

</div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    @if(!empty($subjectTrend))
    const subjectTrendData = {
        labels: {!! json_encode($exams->pluck('name')) !!},
        datasets: [
            @foreach($subjectTrend as $subjectName => $marks)
            {
                label: "{{ $subjectName }}",
                data: {!! json_encode($marks->pluck('mark')->toArray()) !!},
                borderWidth: 2,
                fill: false,
                tension: 0.3
            },
            @endforeach
        ]
    };
    const subjectTrendChart = new Chart(
        document.getElementById('subjectTrendChart'),
        {
            type: 'line',
            data: subjectTrendData,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        }
    );
    @endif

    @if(!empty($bestSubjectsOverall))
    const bestSubjectsData = {
        labels: {!! json_encode(collect($bestSubjectsOverall)->pluck('subject')->toArray()) !!},
        datasets: [{
            label: 'Average Mark',
            data: {!! json_encode(collect($bestSubjectsOverall)->pluck('average')->toArray()) !!},
            backgroundColor: 'rgba(255, 206, 86, 0.7)',
            borderColor: 'rgba(255, 206, 86, 1)',
            borderWidth: 1
        }]
    };
    const bestSubjectsChart = new Chart(
        document.getElementById('bestSubjectsChart'),
        {
            type: 'bar',
            data: bestSubjectsData,
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        }
    );
    @endif
</script>
@endpush
