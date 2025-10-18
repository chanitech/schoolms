@extends('adminlte::page')

@section('title', 'Staff Report')

@section('content_header')
    <h1><i class="fas fa-users"></i> Staff Report</h1>
@stop

@section('content')
<div class="row">
    <!-- Staff by Department -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Staff by Department</h3>
            </div>
            <div class="card-body">
                <canvas id="deptChart" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- Roles Summary -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h3 class="card-title">Staff Roles Summary</h3>
            </div>
            <div class="card-body">
                <canvas id="rolesChart" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-dark text-white">
        <h3 class="card-title">Staff Table</h3>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Total Staff</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($staffByDept as $item)
                    <tr>
                        <td>{{ $item->department->name ?? 'N/A' }}</td>
                        <td>{{ $item->total }}</td>
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
    // Staff by Department
    const deptLabels = @json($staffByDept->pluck('department.name'));
    const deptData = @json($staffByDept->pluck('total'));
    new Chart(document.getElementById('deptChart'), {
        type: 'bar',
        data: {
            labels: deptLabels,
            datasets: [{
                label: 'Staff Count',
                data: deptData,
                backgroundColor: '#007bff'
            }]
        }
    });

    // Roles Chart
    const roleLabels = @json($rolesCount->pluck('name'));
    const roleData = @json($rolesCount->pluck('total'));
    new Chart(document.getElementById('rolesChart'), {
        type: 'doughnut',
        data: {
            labels: roleLabels,
            datasets: [{
                data: roleData,
                backgroundColor: ['#28a745','#ffc107','#dc3545','#17a2b8','#6f42c1']
            }]
        }
    });
</script>
@stop
