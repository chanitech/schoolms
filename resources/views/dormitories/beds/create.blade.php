@extends('adminlte::page')

@section('title', 'Add Bed')

@section('content_header')
    <h1><i class="fas fa-plus"></i> Add Bed to Room {{ $room->room_number }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add Single Bed</h3>
        <div class="card-tools">
            <a href="{{ route('dormitories.beds.bulk.form', $room->id) }}" class="btn btn-info btn-sm">
                <i class="fas fa-layer-group"></i> Bulk Create Beds
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('dormitories.beds.store') }}" method="POST">
            @csrf
            <input type="hidden" name="room_id" value="{{ $room->id }}">

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bed Number <span class="text-danger">*</span></label>
                        <input type="text" name="bed_number" class="form-control @error('bed_number') is-invalid @enderror" value="{{ old('bed_number') }}" required>
                        @error('bed_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Bed Type</label>
                        <select name="bed_type" class="form-control @error('bed_type') is-invalid @enderror">
                            <option value="single" {{ old('bed_type') == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="bunk_upper" {{ old('bed_type') == 'bunk_upper' ? 'selected' : '' }}>Bunk Upper</option>
                            <option value="bunk_lower" {{ old('bed_type') == 'bunk_lower' ? 'selected' : '' }}>Bunk Lower</option>
                        </select>
                        @error('bed_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Features (optional)</label>
                        <input type="text" name="features" class="form-control" value="{{ old('features') }}" placeholder="e.g., Window, Power outlet">
                        @error('features')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Bed</button>
                <a href="{{ route('dormitories.beds', $room->id) }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Optional: Show existing beds summary --}}
<div class="card mt-3">
    <div class="card-header bg-info">
        <h3 class="card-title">Existing Beds in this Room</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped">
            <thead>
                <tr>
                    <th>Bed Number</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Student</th>
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
                <tr><td colspan="4" class="text-center">No beds yet. Add your first bed above.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop