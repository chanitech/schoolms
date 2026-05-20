@extends('adminlte::page')

@section('title', 'Edit Dormitory')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Edit Dormitory</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Dormitory Information</h3>
        <div class="card-tools">
            <a href="{{ route('dormitories.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('dormitories.update', $dormitory) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dormitory Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $dormitory->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', $dormitory->capacity) }}" required min="1">
                        @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', $dormitory->gender) == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $dormitory->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dorm Master (Staff)</label>
                        <select name="dorm_master_id" class="form-control @error('dorm_master_id') is-invalid @enderror">
                            <option value="">Select Dorm Master</option>
                            @foreach($dormMasters as $master)
                                <option value="{{ $master->id }}" {{ old('dorm_master_id', $dormitory->dorm_master_id) == $master->id ? 'selected' : '' }}>
                                    {{ $master->first_name }} {{ $master->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('dorm_master_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $dormitory->location) }}" placeholder="e.g., Block A, Near Football Field">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Contact Phone (Optional)</label>
                        <input type="text" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" value="{{ old('contact_phone', $dormitory->contact_phone) }}">
                        @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $dormitory->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Dormitory</button>
                <a href="{{ route('dormitories.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>

{{-- Quick Stats Card --}}
<div class="card mt-3">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Dormitory Statistics</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-door-open"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Rooms</span>
                        <span class="info-box-number">{{ $dormitory->rooms->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-bed"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Beds</span>
                        <span class="info-box-number">{{ $dormitory->beds()->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box">
                    <span class="info-box-icon bg-danger"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Occupied Beds</span>
                        <span class="info-box-number">{{ $dormitory->beds()->where('status', 'occupied')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="progress" style="height: 25px;">
            @php
                $totalBeds = $dormitory->beds()->count();
                $occupied = $dormitory->beds()->where('status', 'occupied')->count();
                $percent = $totalBeds > 0 ? round(($occupied / $totalBeds) * 100, 1) : 0;
            @endphp
            <div class="progress-bar bg-success" style="width: {{ $percent }}%">
                {{ $percent }}% Occupied
            </div>
        </div>
    </div>
</div>
@stop