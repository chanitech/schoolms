@extends('adminlte::page')

@section('title', 'Edit Room')

@section('content_header')
    <h1><i class="fas fa-door-open"></i> Edit Room</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Room {{ $room->room_number }}</h3>
        <div class="card-tools">
            <a href="{{ route('dormitories.rooms', $room->dormitory_id) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Rooms
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('dormitories.rooms.update', $room) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="dormitory_id" value="{{ $room->dormitory_id }}">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Room Number <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" value="{{ old('room_number', $room->room_number) }}" required>
                        @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Floor</label>
                        <input type="text" name="floor" class="form-control @error('floor') is-invalid @enderror" value="{{ old('floor', $room->floor) }}" placeholder="e.g., Ground, 1st, 2nd">
                        @error('floor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Room Type <span class="text-danger">*</span></label>
                        <select name="room_type" class="form-control @error('room_type') is-invalid @enderror" required>
                            <option value="single" {{ old('room_type', $room->room_type) == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="double" {{ old('room_type', $room->room_type) == 'double' ? 'selected' : '' }}>Double</option>
                            <option value="triple" {{ old('room_type', $room->room_type) == 'triple' ? 'selected' : '' }}>Triple</option>
                            <option value="quad" {{ old('room_type', $room->room_type) == 'quad' ? 'selected' : '' }}>Quad (4 beds)</option>
                            <option value="dormitory" {{ old('room_type', $room->room_type) == 'dormitory' ? 'selected' : '' }}>Dormitory (8+ beds)</option>
                        </select>
                        @error('room_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', $room->capacity) }}" required min="1">
                        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Facilities</label>
                        <input type="text" name="facilities" class="form-control @error('facilities') is-invalid @enderror" value="{{ old('facilities', $room->facilities) }}" placeholder="e.g., Desk, Wardrobe, Fan">
                        @error('facilities')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mt-4">
                        <div class="form-check">
                            <input type="checkbox" name="has_attached_bathroom" class="form-check-input" value="1" {{ old('has_attached_bathroom', $room->has_attached_bathroom) ? 'checked' : '' }}>
                            <label class="form-check-label">Has Attached Bathroom</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="has_balcony" class="form-check-input" value="1" {{ old('has_balcony', $room->has_balcony) ? 'checked' : '' }}>
                            <label class="form-check-label">Has Balcony</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="is_available" class="form-check-input" value="1" {{ old('is_available', $room->is_available) ? 'checked' : '' }}>
                            <label class="form-check-label">Room Available</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Description (optional)</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $room->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Room</button>
                <a href="{{ route('dormitories.rooms', $room->dormitory_id) }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Current beds summary (optional) --}}
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title">Beds in this room</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Bed Number</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Current Student</th>
                </tr>
            </thead>
            <tbody>
                @forelse($room->beds as $bed)
                <tr>
                    <td>{{ $bed->bed_number }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $bed->bed_type)) }}</td>
                    <td>{{ ucfirst($bed->status) }}</td>
                    <td>{{ $bed->currentStudent->full_name ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">No beds added yet. <a href="{{ route('dormitories.beds.create', $room->id) }}">Add beds</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop