@extends('adminlte::page')

@section('title', 'Job Card Report')

@section('content_header')
    <h1><i class="fas fa-clipboard-list"></i> Job Card Report</h1>
@stop

@section('content')
<div class="row mb-3">
    <div class="col-md-12">
        <form method="GET" action="{{ route('hr-reports.jobcards') }}" class="form-inline">
            <div class="form-group mr-2">
                <label for="status" class="mr-2">Status:</label>
                <select name="status" id="status" class="form-control">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="form-group mr-2">
                <label for="assignee" class="mr-2">Assigned To:</label>
                <select name="assignee" id="assignee" class="form-control">
                    <option value="">All Staff</option>
                    @foreach($staff as $s)
                        <option value="{{ $s->id }}" {{ request('assignee') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Job Cards by Status Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Job Cards by Status</h3>
            </div>
            <div class="card-body">
                <canvas id="jobCardStatusChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- Job Cards by Staff Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title">Job Cards by Staff</h3>
            </div>
            <div class="card-body">
                <canvas id="jobCardStaffChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Job Card Table -->
<div class="card mt-3">
    <div class="card-header bg-dark text-white">
        <h3 class="card-title">Detailed Job Cards</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Assigned By</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jobCards as $job)
                    <tr>
                        <td>{{ $job->title ?? 'N/A' }}</td>
                        <td>{{ $job->assigner->name ?? 'N/A' }}</td>
                        <td>{{ $job->assignee->name ?? 'N/A' }}</td>
                        <td>{{ ucfirst($job->status) ?? 'N/A' }}</td>
                        <td>{{ $job->due_date ? $job->due_date->format('Y-m-d') : 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
    // Helper for random pastel colors
    function randomColor() {
        const r = Math.floor(Math.random() * 155 + 100);
        const g = Math.floor(Math.random() * 155 + 100);
        const b = Math.floor(Math.random() * 155 + 100);
        return `rgb(${r}, ${g}, ${b})`;
    }

    // Job Cards by Status Chart
    const statusLabels = @json($jobCardSummaryByStatus->keys());
    const statusData = @json($jobCardSummaryByStatus->values());
    const statusColors = statusLabels.map(() => randomColor());

    new Chart(document.getElementById('jobCardStatusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusColors
            }]
        },
        options: {
            plugins: {
                datalabels: { 
                    color: '#fff',
                    font: { weight: 'bold' },
                    formatter: (value) => value
                }
            }
        },
        plugins: [ChartDataLabels]
    });

    // Job Cards by Staff Chart
    const staffLabels = @json($jobCardSummaryByStaff->keys());
    const staffData = @json($jobCardSummaryByStaff->values());
    const staffColors = staffLabels.map(() => randomColor());

    new Chart(document.getElementById('jobCardStaffChart'), {
        type: 'bar',
        data: {
            labels: staffLabels,
            datasets: [{
                label: 'Job Cards',
                data: staffData,
                backgroundColor: staffColors
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    font: { weight: 'bold' },
                    formatter: (value) => value
                }
            }
        },
        plugins: [ChartDataLabels]
    });
</script>
@stop
