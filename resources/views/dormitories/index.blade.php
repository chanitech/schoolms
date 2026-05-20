@extends('adminlte::page')

@section('title', 'Dormitories')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building"></i> Dormitories</h1>
        <div>
            <a href="{{ route('dormitories.dashboard') }}" class="btn btn-info btn-sm mr-1">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="{{ route('dormitories.allocations') }}" class="btn btn-primary btn-sm mr-1">
                <i class="fas fa-user-check"></i> Allocations
            </a>
            <a href="{{ route('dormitories.reports') }}" class="btn btn-secondary btn-sm mr-1">
                <i class="fas fa-file-alt"></i> Reports
            </a>
        </div>
    </div>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Dormitory List</h3>
        <div class="card-tools">
            <a href="{{ route('dormitories.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Dormitory
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Capacity</th>
                    <th>Rooms</th>
                    <th>Beds</th>
                    <th>Occupancy</th>
                    <th>Dorm Master</th>
                    <th width="200">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dormitories as $index => $dorm)
                @php
                    // Pre‑loaded counts (ensure controller passes these)
                    $roomsCount = $dorm->rooms_count ?? $dorm->rooms->count();
                    $bedsCount = $dorm->beds_count ?? $dorm->beds->count();
                    $occupiedBeds = $dorm->occupied_beds_count ?? $dorm->beds()->where('status', 'occupied')->count();
                    $occupancyRate = $bedsCount > 0 ? round(($occupiedBeds / $bedsCount) * 100, 1) : 0;
                    $progressClass = $occupancyRate >= 80 ? 'bg-danger' : ($occupancyRate >= 50 ? 'bg-warning' : 'bg-success');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $dorm->name }}</strong></td>
                    <td><span class="badge {{ $dorm->gender == 'male' ? 'badge-primary' : 'badge-danger' }}">
                            {{ ucfirst($dorm->gender) }}
                        </span>
                    </td>
                    <td>{{ $dorm->capacity }}</td>
                    <td>{{ $roomsCount }}</td>
                    <td>{{ $bedsCount }}</td>
                    <td>
                        <div class="progress" style="height: 20px; width: 120px;">
                            <div class="progress-bar {{ $progressClass }}" style="width: {{ $occupancyRate }}%">
                                {{ $occupancyRate }}%
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
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('dormitories.show', $dorm) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('dormitories.edit', $dorm) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('dormitories.rooms', $dorm->id) }}" class="btn btn-sm btn-success" title="Rooms">
                                <i class="fas fa-door-open"></i>
                            </a>
                            {{-- Dropdown for more actions --}}
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-cog"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <!-- Removed the broken "Beds" link – use Rooms → Beds instead -->
                                    <a class="dropdown-item" href="{{ route('dormitories.allocations') }}?dormitory_id={{ $dorm->id }}">
                                        <i class="fas fa-user-check"></i> Allocations
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('dormitories.destroy', $dorm) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item text-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <td>
                    <td colspan="9" class="text-center text-muted">No dormitories found. <a href="{{ route('dormitories.create') }}">Add one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $dormitories->links() }}
    </div>
</div>
@stop