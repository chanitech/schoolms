@extends('adminlte::page')

@section('title', 'Attendance Report')

@section('content_header')
    <h1><i class="fas fa-calendar-check"></i> Attendance Report</h1>
@stop

@section('content')
<div class="row">
    <!-- Attendance Rate -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Attendance Rate</h3>
            </div>
            <div class="card-body">
                <canvas id="attendanceChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- Absenteeism -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">Absenteeism Rate</h3>
            </div>
            <div class="card-body">
                <canvas id="absentChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Table -->
<div class="card mt-3">
    <div class="card-header bg-dark text-white">
        <h3 class="card-title">Detailed Attendance Summary</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Department</th>
                    <th>Days Present</th>
                    <th>Days Absent</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceSummary as $item)
                    <tr>
                        <td>{{ $item->staff->name ?? 'N/A' }}</td>
                        <td>{{ $item->staff->department->name ?? 'N/A' }}</td>
                        <td>{{ $item->present_days }}</td>
                        <td>{{ $item->absent_days }}</td>
                        <td>{{ number_format($item->attendance_percent, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Attendance Rate Chart
    const deptLabels = @json($attendanceRate->pluck('department_name'));
    const deptData = @json($attendanceRate->pluck('attendance_percent'));
    new Chart(document.getElementById('attendanceChart'), {
        type: 'bar',
        data: {
            labels: deptLabels,
            datasets: [{
                label: 'Attendance %',
                data: deptData,
                backgroundColor: '#28a745'
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });

    // Absenteeism Chart
    const absentLabels = @json($absentRate->pluck('department_name'));
    const absentData = @json($absentRate->pluck('absent_percent'));
    new Chart(document.getElementById('absentChart'), {
        type: 'doughnut',
        data: {
            labels: absentLabels,
            datasets: [{
                data: absentData,
                backgroundColor: ['#dc3545','#ffc107','#6c757d']
            }]
        }
    });
</script>
@stop
