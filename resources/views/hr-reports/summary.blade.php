@extends('adminlte::page')

@section('title', 'HR Summary Dashboard')

@section('content_header')
    <h1><i class="fas fa-chart-pie mr-2"></i>HR Summary Dashboard</h1>
@stop

@section('content')
<div class="row">
    <div class="col-6 col-md-4 col-lg">
        <div class="small-box-custom sb-blue">
            <div class="sb-inner"><h3>{{ $totalStaff }}</h3><p>Total Staff</p></div>
            <div class="sb-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="small-box-custom sb-green">
            <div class="sb-inner"><h3>{{ $totalDepartments }}</h3><p>Departments</p></div>
            <div class="sb-icon"><i class="fas fa-building"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="small-box-custom sb-orange">
            <div class="sb-inner"><h3>{{ $totalLeaves }}</h3><p>Leave Requests</p></div>
            <div class="sb-icon"><i class="fas fa-plane-departure"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="small-box-custom sb-red">
            <div class="sb-inner"><h3>{{ $totalJobCards }}</h3><p>Job Cards</p></div>
            <div class="sb-icon"><i class="fas fa-briefcase"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg">
        <div class="small-box-custom sb-purple">
            <div class="sb-inner"><h3>{{ $totalEvents }}</h3><p>Events</p></div>
            <div class="sb-icon"><i class="fas fa-calendar-alt"></i></div>
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

@section('css')
@include('partials.dashboard-widgets-css')
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
