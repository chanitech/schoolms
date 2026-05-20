@extends('adminlte::page')

@section('title', 'Add Dormitory')

@section('content_header')
    <h1><i class="fas fa-plus"></i> Add Dormitory</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Dormitory Information</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('dormitories.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Dormitory Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" value="{{ old('capacity', 50) }}" required>
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
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
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
                                <option value="{{ $master->id }}" {{ old('dorm_master_id') == $master->id ? 'selected' : '' }}>
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
                        <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" placeholder="e.g., Block A, Near Football Field">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Contact Phone (Optional)</label>
                        <input type="text" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror" value="{{ old('contact_phone') }}">
                        @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Any additional information about the dormitory">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Dormitory</button>
                <a href="{{ route('dormitories.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
</div>
@stop