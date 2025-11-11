@extends('adminlte::page')

@section('title', 'Leave Report')

@section('content_header')
    <h1><i class="fas fa-calendar-times"></i> Leave Report</h1>
@stop

@section('content')
<div class="row">
    <!-- Leave Summary Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title">Leave by Type</h3>
            </div>
            <div class="card-body">
                <canvas id="leaveTypeChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- Leave Status Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Leave Status</h3>
            </div>
            <div class="card-body">
                <canvas id="leaveStatusChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Leave Table -->
<div class="card mt-3">
    <div class="card-header bg-dark text-white">
        <h3 class="card-title">Detailed Leave Records</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaves as $leave)
                    <tr>
                        <td>{{ $leave->staff->name ?? 'N/A' }}</td>
                        <td>{{ $leave->staff->department->name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($leave->type) }}</td>
                        <td>{{ ucfirst($leave->status) }}</td>
                        <td>{{ $leave->start_date->format('Y-m-d') }}</td>
                        <td>{{ $leave->end_date->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')


@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<script>
    // Helper function to generate random pastel colors
    function randomColor() {
        const r = Math.floor(Math.random() * 155 + 100);
        const g = Math.floor(Math.random() * 155 + 100);
        const b = Math.floor(Math.random() * 155 + 100);
        return `rgb(${r}, ${g}, ${b})`;
    }

    // -----------------------------
    // Leave by Type Chart
    // -----------------------------
    const leaveTypeLabels = @json($leaveSummaryByType->keys());
    const leaveTypeData = @json($leaveSummaryByType->values());
    const leaveTypeColors = leaveTypeLabels.map(() => randomColor());

    new Chart(document.getElementById('leaveTypeChart'), {
        type: 'bar',
        data: {
            labels: leaveTypeLabels,
            datasets: [{
                label: 'Total Leaves',
                data: leaveTypeData,
                backgroundColor: leaveTypeColors
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${value} (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: (value) => value,
                    font: { weight: 'bold' }
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // -----------------------------
    // Leave Status Chart
    // -----------------------------
    const leaveStatusLabels = @json($leaveSummaryByStatus->keys());
    const leaveStatusData = @json($leaveSummaryByStatus->values());
    const leaveStatusColors = leaveStatusLabels.map(() => randomColor());

    new Chart(document.getElementById('leaveStatusChart'), {
        type: 'doughnut',
        data: {
            labels: leaveStatusLabels,
            datasets: [{
                data: leaveStatusData,
                backgroundColor: leaveStatusColors
            }]
        },
        options: {
            plugins: {
                legend: { position: 'right' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${value} (${percentage}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    formatter: (value, context) => {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return `${percentage}%`;
                    },
                    font: { weight: 'bold', size: 14 }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
</script>
@stop




@stop
