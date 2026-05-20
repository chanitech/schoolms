@extends('adminlte::page')

@section('title', 'Add Room')

@section('content_header')
    <h1><i class="fas fa-plus"></i> Add Room to {{ $dormitory->name }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('dormitories.rooms.store') }}" method="POST">
            @csrf
            <input type="hidden" name="dormitory_id" value="{{ $dormitory->id }}">
            
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Room Number <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" class="form-control @error('room_number') is-invalid @enderror" value="{{ old('room_number') }}" required>
                        @error('room_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Floor</label>
                        <input type="text" name="floor" class="form-control" value="{{ old('floor') }}" placeholder="e.g., Ground, 1st, 2nd">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Room Type <span class="text-danger">*</span></label>
                        <select name="room_type" class="form-control @error('room_type') is-invalid @enderror" required>
                            <option value="">Select Type</option>
                            <option value="single" {{ old('room_type') == 'hall' ? 'selected' : '' }}>Hall</option>
                            <option value="double" {{ old('room_type') == 'double' ? 'selected' : '' }}>Double</option>
                            <option value="triple" {{ old('room_type') == 'triple' ? 'selected' : '' }}>Triple</option>
                            <option value="quad" {{ old('room_type') == 'quad' ? 'selected' : '' }}>Quad (4 beds)</option>
                            <option value="dormitory" {{ old('room_type') == 'dormitory' ? 'selected' : '' }}>Dormitory (8+ beds)</option>
                        </select>
                        @error('room_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', 2) }}" required>
                        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Facilities</label>
                        <input type="text" name="facilities" class="form-control" value="{{ old('facilities') }}" placeholder="e.g., Desk, Wardrobe, Fan">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mt-4">
                        <div class="form-check">
                            <input type="checkbox" name="has_attached_bathroom" class="form-check-input" value="1" {{ old('has_attached_bathroom') ? 'checked' : '' }}>
                            <label class="form-check-label">Has Attached Bathroom</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="has_balcony" class="form-check-input" value="1" {{ old('has_balcony') ? 'checked' : '' }}>
                            <label class="form-check-label">Has Balcony</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Room</button>
                <a href="{{ route('dormitories.rooms', $dormitory->id) }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop