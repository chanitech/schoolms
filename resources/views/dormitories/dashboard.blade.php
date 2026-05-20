@extends('adminlte::page')

@section('title', 'Dormitory Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building mr-2"></i> Dormitory Dashboard</h1>
        <a href="{{ route('dormitories.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dormitories
        </a>
    </div>
@stop

@section('content')
{{-- Stats Row --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ $totalDormitories }}</h3>
                <p>Total Dormitories</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="{{ route('dormitories.index') }}" class="small-box-footer">
                Manage <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ $totalRooms }}</h3>
                <p>Total Rooms</p>
            </div>
            <div class="icon">
                <i class="fas fa-door-open"></i>
            </div>
            <a href="#" class="small-box-footer">
                View Rooms <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning">
            <div class="inner">
                <h3>{{ $totalBeds }}</h3>
                <p>Total Beds</p>
            </div>
            <div class="icon">
                <i class="fas fa-bed"></i>
            </div>
            <a href="#" class="small-box-footer">
                View Beds <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ $occupancyRate }}%</h3>
                <p>Occupancy Rate</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <a href="#" class="small-box-footer">
                Details <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Bed Distribution per Dormitory</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="dormitoryBedsChart" style="min-height: 300px; height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Overall Bed Status</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="bedStatusPieChart" style="min-height: 250px; height: 250px;"></canvas>
                <div class="text-center mt-3">
                    <span class="badge badge-success">Available: {{ $availableBeds }}</span>
                    <span class="badge badge-danger ml-2">Occupied: {{ $occupiedBeds }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bed Status & Quick Stats Row --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bed"></i> Bed Status</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-bed"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Available Beds</span>
                                <span class="info-box-number h3">{{ $availableBeds }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Occupied Beds</span>
                                <span class="info-box-number h3">{{ $occupiedBeds }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 30px;">
                    @php
                        $availablePercent = $totalBeds > 0 ? ($availableBeds / $totalBeds) * 100 : 0;
                        $occupiedPercent = $totalBeds > 0 ? ($occupiedBeds / $totalBeds) * 100 : 0;
                    @endphp
                    <div class="progress-bar bg-success progress-bar-striped" style="width: {{ $availablePercent }}%">
                        Available {{ $availableBeds }}
                    </div>
                    <div class="progress-bar bg-danger progress-bar-striped" style="width: {{ $occupiedPercent }}%">
                        Occupied {{ $occupiedBeds }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Quick Stats</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 60%">Active Allocations</th>
                        <td class="text-right"><span class="badge badge-success">{{ $activeAllocations }}</span></td>
                    </tr>
                    <tr>
                        <th>Avg Beds per Dormitory</th>
                        <td class="text-right">{{ $totalDormitories > 0 ? round($totalBeds / $totalDormitories, 1) : 0 }}</td>
                    </tr>
                    <tr>
                        <th>Avg Rooms per Dormitory</th>
                        <td class="text-right">{{ $totalDormitories > 0 ? round($totalRooms / $totalDormitories, 1) : 0 }}</td>
                    </tr>
                    <tr>
                        <th>Occupancy Trend</th>
                        <td class="text-right">
                            @if($occupancyRate >= 80)
                                <span class="text-danger"><i class="fas fa-arrow-up"></i> High</span>
                            @elseif($occupancyRate >= 50)
                                <span class="text-warning"><i class="fas fa-minus"></i> Medium</span>
                            @else
                                <span class="text-success"><i class="fas fa-arrow-down"></i> Low</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Recent Allocations --}}
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-clock"></i> Recent Allocations</h3>
                <div class="card-tools">
                    <a href="{{ route('dormitories.allocations') }}" class="btn btn-sm btn-primary">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Dormitory</th>
                            <th>Room</th>
                            <th>Bed</th>
                            <th>Allocation Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAllocations as $allocation)
                        <tr>
                            <td><i class="fas fa-user-graduate mr-1"></i> {{ $allocation->student->full_name ?? 'N/A' }}</td>
                            <td>{{ $allocation->bed->room->dormitory->name ?? 'N/A' }}</td>
                            <td>{{ $allocation->bed->room->room_number ?? 'N/A' }}</td>
                            <td>{{ $allocation->bed->bed_number ?? 'N/A' }} （{{ ucfirst(str_replace('_', ' ', $allocation->bed->bed_type ?? '')) }}）</td>
                            <td>{{ $allocation->allocation_date->format('d M Y') }}</td>
                            <td><span class="badge badge-success">Active</span></td>
                        </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No recent allocations found.</td></table>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Dormitory Overview --}}
<div class="row">
    <div class="col-md-12">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-building"></i> Dormitory Overview</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Dormitory</th>
                            <th>Gender</th>
                            <th>Rooms</th>
                            <th>Total Beds</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Occupancy</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dormitories as $dorm)
                        @php
                            $totalBedsInDorm = $dorm->beds_count ?? 0;
                            $occupiedBedsInDorm = $dorm->beds()->where('status', 'occupied')->count();
                            $availableBedsInDorm = $totalBedsInDorm - $occupiedBedsInDorm;
                            $occRate = $totalBedsInDorm > 0 ? round(($occupiedBedsInDorm / $totalBedsInDorm) * 100, 1) : 0;
                            $progressColor = $occRate >= 80 ? 'danger' : ($occRate >= 50 ? 'warning' : 'success');
                        @endphp
                        <tr>
                            <td><strong>{{ $dorm->name }}</strong></td>
                            <td><span class="badge {{ $dorm->gender == 'male' ? 'badge-primary' : 'badge-danger' }}">
                                    {{ ucfirst($dorm->gender) }}
                                </span></td>
                            <td>{{ $dorm->rooms_count ?? 0 }}</td>
                            <td>{{ $totalBedsInDorm }}</td>
                            <td>{{ $occupiedBedsInDorm }}</td>
                            <td>{{ $availableBedsInDorm }}</td>
                            <td>
                                <div class="progress" style="height: 20px; min-width: 80px;">
                                    <div class="progress-bar bg-{{ $progressColor }}" style="width: {{ $occRate }}%">
                                        {{ $occRate }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('dormitories.show', $dorm) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
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
    // Bar chart: Bed distribution per dormitory
    const dormNames = @json($dormitories->pluck('name'));
    const totalBedsData = @json($dormitories->map(function($d) {
        return $d->beds_count ?? 0;
    }));
    const occupiedBedsData = @json($dormitories->map(function($d) {
        return $d->beds()->where('status', 'occupied')->count() ?? 0;
    }));
    const availableBedsData = totalBedsData.map((total, idx) => total - occupiedBedsData[idx]);

    const ctxBar = document.getElementById('dormitoryBedsChart').getContext('2d');
    new Chart(ctxBar, {
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
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Beds'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Dormitories'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });

    // Pie chart: Overall bed status
    const ctxPie = document.getElementById('bedStatusPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: ['Available Beds', 'Occupied Beds'],
            datasets: [{
                data: [{{ $availableBeds }}, {{ $occupiedBeds }}],
                backgroundColor: ['#28a745', '#dc3545'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = {{ $totalBeds }};
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
});
</script>
@stop