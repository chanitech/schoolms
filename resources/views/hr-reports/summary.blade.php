@extends('adminlte::page')

@section('title', 'HR Summary Dashboard')

@section('content_header')
    <h1><i class="fas fa-chart-pie mr-2"></i>HR Summary Dashboard</h1>
@stop

@section('content')
<div class="row">
    <div class="col-6 col-md-4 col-lg">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
            <div class="info-box-content"><span class="info-box-text">Total Staff</span><span class="info-box-number">{{ $totalStaff }}</span></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-building"></i></span>
            <div class="info-box-content"><span class="info-box-text">Departments</span><span class="info-box-number">{{ $totalDepartments }}</span></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-plane-departure"></i></span>
            <div class="info-box-content"><span class="info-box-text">Leave Requests</span><span class="info-box-number">{{ $totalLeaves }}</span></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-briefcase"></i></span>
            <div class="info-box-content"><span class="info-box-text">Job Cards</span><span class="info-box-number">{{ $totalJobCards }}</span></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="info-box">
            <span class="info-box-icon bg-primary"><i class="fas fa-calendar-alt"></i></span>
            <div class="info-box-content"><span class="info-box-text">Events</span><span class="info-box-number">{{ $totalEvents }}</span></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header"><h3 class="card-title">Leaves by Type</h3></div>
            <div class="card-body">
                @if($leavesByType->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">No leave requests recorded yet.</p>
                @else
                    <canvas id="leavesChart" height="200"></canvas>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header"><h3 class="card-title">Staff Attendance Breakdown</h3></div>
            <div class="card-body">
                @if($attendanceStats->isEmpty())
                    <p class="text-muted text-center py-4 mb-0">No attendance records yet.</p>
                @else
                    <canvas id="attendanceChart" height="200"></canvas>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    @if($leavesByType->isNotEmpty())
    new Chart(document.getElementById('leavesChart'), {
        type: 'doughnut',
        data: {
            labels: @json($leavesByType->pluck('type')),
            datasets: [{ data: @json($leavesByType->pluck('total')), backgroundColor: ['#0d6efd','#ffc107','#dc3545','#20c997','#6f42c1','#fd7e14'] }],
        },
    });
    @endif

    @if($attendanceStats->isNotEmpty())
    new Chart(document.getElementById('attendanceChart'), {
        type: 'bar',
        data: {
            labels: @json($attendanceStats->pluck('status')),
            datasets: [{ label: 'Days', data: @json($attendanceStats->pluck('total')), backgroundColor: '#198754' }],
        },
        options: { plugins: { legend: { display: false } } },
    });
    @endif
</script>
@stop
