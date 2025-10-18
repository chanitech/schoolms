@extends('adminlte::page')

@section('title', 'HR Evaluation Report')

@section('content_header')
    <h1 class="text-primary">HR Evaluation Report</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Overall Staff Performance Evaluation</h4>
        <div>
            <a href="{{ route('hr.reports.export.evaluation', ['type' => 'excel']) }}" class="btn btn-success btn-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('hr.reports.export.evaluation', ['type' => 'pdf']) }}" class="btn btn-danger btn-sm">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Staff Name</th>
                    <th>Department</th>
                    <th>Attendance (%)</th>
                    <th>Job Card Completion (%)</th>
                    <th>Overall Score</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluations as $index => $eval)
                    <tr @if($loop->first) class="table-success fw-bold" @endif>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $eval->staff_name }}</td>
                        <td>{{ $eval->department }}</td>
                        <td>{{ $eval->attendance }}%</td>
                        <td>{{ $eval->job_card_rate }}%</td>
                        <td><strong>{{ $eval->score }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted">No evaluation data available</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Department Performance Summary --}}
<div class="card mt-4 shadow">
    <div class="card-header bg-info text-white">
        <h4 class="mb-0">Department Performance Summary</h4>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-info">
                <tr>
                    <th>#</th>
                    <th>Department</th>
                    <th>Average Score (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentScores as $index => $dept)
                    <tr @if($loop->first) class="table-success fw-bold" @endif>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $dept->department }}</td>
                        <td>{{ $dept->average_score }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Charts --}}
<div class="card mt-4 shadow">
    <div class="card-header bg-secondary text-white">
        <h4 class="mb-0">Visual Analysis</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <canvas id="staffPerformanceChart" height="180"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="departmentPerformanceChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== Staff Performance Chart =====
    const staffCtx = document.getElementById('staffPerformanceChart').getContext('2d');
    new Chart(staffCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($evaluations->pluck('staff_name')) !!},
            datasets: [{
                label: 'Overall Score',
                data: {!! json_encode($evaluations->pluck('score')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Staff Performance Scores' }
            },
            scales: { x: { beginAtZero: true, max: 100 } }
        }
    });

    // ===== Department Performance Chart =====
    const deptCtx = document.getElementById('departmentPerformanceChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($departmentScores->pluck('department')) !!},
            datasets: [{
                label: 'Average Score',
                data: {!! json_encode($departmentScores->pluck('average_score')) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Department Average Scores' }
            },
            scales: { x: { beginAtZero: true, max: 100 } }
        }
    });
});
</script>
@stop
