@extends('adminlte::page')

@section('title', 'Edit Bed')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Edit Bed</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('dormitories.beds.update', $bed) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="room_id" value="{{ $bed->room_id }}">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bed Number <span class="text-danger">*</span></label>
                        <input type="text" name="bed_number" class="form-control @error('bed_number') is-invalid @enderror" value="{{ old('bed_number', $bed->bed_number) }}" required>
                        @error('bed_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bed Type</label>
                        <select name="bed_type" class="form-control @error('bed_type') is-invalid @enderror">
                            <option value="single" {{ old('bed_type', $bed->bed_type) == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="bunk_upper" {{ old('bed_type', $bed->bed_type) == 'bunk_upper' ? 'selected' : '' }}>Bunk Upper</option>
                            <option value="bunk_lower" {{ old('bed_type', $bed->bed_type) == 'bunk_lower' ? 'selected' : '' }}>Bunk Lower</option>
                        </select>
                        @error('bed_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="available" {{ old('status', $bed->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="occupied" {{ old('status', $bed->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                            <option value="maintenance" {{ old('status', $bed->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            <option value="reserved" {{ old('status', $bed->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Features (optional)</label>
                        <input type="text" name="features" class="form-control" value="{{ old('features', $bed->features) }}" placeholder="e.g., Window, Power outlet">
                        @error('features')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Bed</button>
                <a href="{{ route('dormitories.beds', $bed->room_id) }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop