@extends('adminlte::page')

@section('title', 'Dormitory Reports')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-chart-line mr-2"></i> Dormitory Reports</h1>
        <a href="{{ route('dormitories.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dormitories
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ $totalDormitories ?? $dormitories->count() }}</h3>
                <p>Total Dormitories</p>
            </div>
            <div class="icon"><i class="fas fa-building"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ $totalRooms ?? $dormitories->sum(function($d) { return $d->rooms_count ?? $d->rooms->count(); }) }}</h3>
                <p>Total Rooms</p>
            </div>
            <div class="icon"><i class="fas fa-door-open"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ $totalBeds ?? $dormitories->sum(function($d) { return $d->beds_count ?? $d->beds->count(); }) }}</h3>
                <p>Total Beds</p>
            </div>
            <div class="icon"><i class="fas fa-bed"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ $occupancyRate ?? (($totalBeds = $dormitories->sum(fn($d)=>$d->beds_count ?? $d->beds->count())) ? round(($dormitories->sum(fn($d)=>$d->beds()->where('status','occupied')->count()) / $totalBeds) * 100, 1) : 0) }}%</h3>
                <p>Occupancy Rate</p>
            </div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Bed Distribution by Dormitory</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="dormitoryBedsChart" style="min-height: 300px; height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-table"></i> Dormitory Detailed Report</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Dormitory</th>
                            <th>Gender</th>
                            <th>Rooms</th>
                            <th>Total Beds</th>
                            <th>Occupied Beds</th>
                            <th>Available Beds</th>
                            <th>Occupancy %</th>
                            <th>Dorm Master</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dormitories as $index => $dorm)
                        @php
                            $totalBeds = $dorm->beds_count ?? $dorm->beds->count();
                            $occupied = $dorm->beds()->where('status', 'occupied')->count();
                            $available = $totalBeds - $occupied;
                            $occRate = $totalBeds ? round(($occupied / $totalBeds) * 100, 1) : 0;
                            $progressColor = $occRate >= 80 ? 'danger' : ($occRate >= 50 ? 'warning' : 'success');
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $dorm->name }}</strong></td>
                            <td><span class="badge {{ $dorm->gender == 'male' ? 'badge-primary' : 'badge-danger' }}">{{ ucfirst($dorm->gender) }}</span></td>
                            <td>{{ $dorm->rooms_count ?? $dorm->rooms->count() }}</td>
                            <td>{{ $totalBeds }}</td>
                            <td>{{ $occupied }}</td>
                            <td>{{ $available }}</td>
                            <td>
                                <div class="progress" style="height: 20px; min-width: 80px;">
                                    <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $occRate }}%">
                                        {{ $occRate }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($dorm->dormMaster)
                                    {{ $dorm->dormMaster->first_name }} {{ $dorm->dormMaster->last_name }}
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    const dormNames = @json($dormitories->pluck('name'));
    const totalBedsData = @json($dormitories->map(function($d) {
        return $d->beds_count ?? $d->beds->count();
    }));
    const occupiedBedsData = @json($dormitories->map(function($d) {
        return $d->beds()->where('status', 'occupied')->count();
    }));
    const availableBedsData = totalBedsData.map((total, idx) => total - occupiedBedsData[idx]);

    const ctx = document.getElementById('dormitoryBedsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: dormNames,
            datasets: [
                {
                    label: 'Total Beds',
                    data: totalBedsData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Occupied Beds',
                    data: occupiedBedsData,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Available Beds',
                    data: availableBedsData,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Number of Beds' } },
                x: { title: { display: true, text: 'Dormitories' } }
            },
            plugins: { legend: { position: 'top' }, tooltip: { mode: 'index', intersect: false } }
        }
    });
});
</script>
@stop