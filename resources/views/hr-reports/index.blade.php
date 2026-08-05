@extends('adminlte::page')

@section('title', 'HR Reports')

@section('content_header')
    <h1><i class="fas fa-chart-line mr-2"></i>HR Reports</h1>
@stop

@section('content')
<div class="row">
    @can('view hr summary dashboard')
    <div class="col-md-4 mb-3">
        <a href="{{ route('hr-reports.summary') }}" class="text-decoration-none">
            <div class="card card-outline card-primary shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-chart-pie fa-2x text-primary mb-2"></i>
                    <h5 class="text-dark mb-0">Summary Dashboard</h5>
                    <small class="text-muted">Staff, leaves, job cards &amp; events at a glance</small>
                </div>
            </div>
        </a>
    </div>
    @endcan
    @can('view staff report')
    <div class="col-md-4 mb-3">
        <a href="{{ route('hr-reports.staff') }}" class="text-decoration-none">
            <div class="card card-outline card-info shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-users fa-2x text-info mb-2"></i>
                    <h5 class="text-dark mb-0">Staff Report</h5>
                    <small class="text-muted">Headcount by department and role</small>
                </div>
            </div>
        </a>
    </div>
    @endcan
    @can('view attendance report')
    <div class="col-md-4 mb-3">
        <a href="{{ route('hr-reports.attendance') }}" class="text-decoration-none">
            <div class="card card-outline card-success shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                    <h5 class="text-dark mb-0">Attendance Report</h5>
                    <small class="text-muted">Presence and absence rate by department</small>
                </div>
            </div>
        </a>
    </div>
    @endcan
    @can('view leave report')
    <div class="col-md-4 mb-3">
        <a href="{{ route('hr-reports.leaves') }}" class="text-decoration-none">
            <div class="card card-outline card-warning shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-plane-departure fa-2x text-warning mb-2"></i>
                    <h5 class="text-dark mb-0">Leave Report</h5>
                    <small class="text-muted">Leave requests received and their status</small>
                </div>
            </div>
        </a>
    </div>
    @endcan
    @can('view job cards report')
    <div class="col-md-4 mb-3">
        <a href="{{ route('hr-reports.jobcards') }}" class="text-decoration-none">
            <div class="card card-outline card-secondary shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-briefcase fa-2x text-secondary mb-2"></i>
                    <h5 class="text-dark mb-0">Job Cards Report</h5>
                    <small class="text-muted">Task assignment and completion</small>
                </div>
            </div>
        </a>
    </div>
    @endcan
    @can('view evaluation report')
    <div class="col-md-4 mb-3">
        <a href="{{ route('hr-reports.evaluation') }}" class="text-decoration-none">
            <div class="card card-outline card-danger shadow-sm h-100">
                <div class="card-body text-center py-4">
                    <i class="fas fa-star fa-2x text-danger mb-2"></i>
                    <h5 class="text-dark mb-0">Evaluation Report</h5>
                    <small class="text-muted">Staff performance scoring</small>
                </div>
            </div>
        </a>
    </div>
    @endcan
</div>
@stop
