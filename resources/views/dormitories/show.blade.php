@extends('adminlte::page')

@section('title', $dormitory->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-building"></i> {{ $dormitory->name }}</h1>
        <a href="{{ route('dormitories.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dormitories
        </a>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-door-open"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Rooms</span>
                <span class="info-box-number">{{ $dormitory->rooms->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-bed"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Beds</span>
                <span class="info-box-number">{{ $dormitory->beds()->count() }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Occupied Beds</span>
                <span class="info-box-number">{{ $occupiedBeds }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rooms in {{ $dormitory->name }}</h3>
        <div class="card-tools">
            <a href="{{ route('dormitories.rooms.create', $dormitory->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Room
            </a>
        </div>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Floor</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Occupied Beds</th>
                    <th>Available Beds</th>
                    <th>Facilities</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dormitory->rooms as $room)
                <tr>
                    <td><strong>{{ $room->room_number }}</strong></td>
                    <td>{{ $room->floor ?? 'Ground' }}</td>
                    <td>{{ ucfirst($room->room_type) }}</td>
                    <td>{{ $room->capacity }}</td>
                    <td>{{ $room->occupied_beds }}</td>
                    <td>{{ $room->capacity - $room->occupied_beds }}</td>
                    <td>
                        @if($room->has_attached_bathroom)<i class="fas fa-bath" title="Attached Bathroom"></i> @endif
                        @if($room->has_balcony)<i class="fas fa-tree" title="Balcony"></i> @endif
                    </td>
                    <td>
                        <a href="{{ route('dormitories.beds', $room->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-bed"></i> Beds
                        </a>
                        <a href="{{ route('dormitories.rooms.edit', $room) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center">No rooms found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Dormitory Information</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>Dorm Master</th><td>{{ $dormitory->dormMaster->full_name ?? 'Not assigned' }}</td></tr>
                    <tr><th>Gender</th><td>{{ ucfirst($dormitory->gender) }}</td></tr>
                    <tr><th>Capacity</th><td>{{ $dormitory->capacity }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>Created At</th><td>{{ $dormitory->created_at->format('d M Y') }}</td></tr>
                    <tr><th>Last Updated</th><td>{{ $dormitory->updated_at->format('d M Y') }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@stop