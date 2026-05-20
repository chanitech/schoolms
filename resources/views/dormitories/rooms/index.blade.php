@extends('adminlte::page')

@section('title', 'Rooms - ' . ($hall->name ?? 'Dormitory'))

@section('content_header')
    <h1><i class="fas fa-door-open"></i> Rooms in {{ $dormitory->name ?? 'Dormitory' }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Room List</h3>
        <div class="card-tools">
            <a href="{{ route('dormitories.rooms.create', $dormitory->id) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Room
            </a>
            <a href="{{ route('dormitories.show', $dormitory->id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Dormitory
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
                    <th>Occupied</th>
                    <th>Available</th>
                    <th>Facilities</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
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
                        <span class="badge {{ $room->is_available ? 'badge-success' : 'badge-danger' }}">
                            {{ $room->is_available ? 'Available' : 'Unavailable' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('dormitories.beds', $room->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-bed"></i> Beds
                        </a>
                        <a href="{{ route('dormitories.rooms.edit', $room) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('dormitories.rooms.delete', $room) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center">No rooms found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $rooms->links() }}
    </div>
</div>
@stop