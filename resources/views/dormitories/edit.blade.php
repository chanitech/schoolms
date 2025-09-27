@extends('adminlte::page')

@section('title', 'Edit Dormitory')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-building"></i> Edit Dormitory</h1>
@stop

@section('content')
    <div class="card shadow">
        <div class="card-body">
            <form action="{{ route('dormitories.update', $dormitory->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="name" class="form-label">Dormitory Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $dormitory->name) }}">
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="capacity" class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $dormitory->capacity) }}">
                        @error('capacity') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" class="form-control">
                            <option value="male" {{ old('gender', $dormitory->gender)=='male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender', $dormitory->gender)=='female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dorm_master_id" class="form-label">Dorm Master</label>
                        <select name="dorm_master_id" class="form-control">
                            <option value="">Select Dorm Master</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ old('dorm_master_id', $dormitory->dorm_master_id)==$teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('dorm_master_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Dormitory</button>
            </form>
        </div>
    </div>
@stop
