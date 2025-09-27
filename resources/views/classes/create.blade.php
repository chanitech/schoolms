@extends('adminlte::page')

@section('title', 'Add Class')

@section('content_header')
    <h1 class="mb-3"><i class="fas fa-chalkboard"></i> Add Class</h1>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">
        <form action="{{ route('classes.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="name" class="form-label">Class Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="level" class="form-label">Level</label>
                    <input type="text" name="level" class="form-control" value="{{ old('level') }}">
                    @error('level') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="section" class="form-label">Section</label>
                    <input type="text" name="section" class="form-control" value="{{ old('section') }}">
                    @error('section') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="capacity" class="form-label">Capacity</label>
                    <input type="number" name="capacity" class="form-control" value="{{ old('capacity', 30) }}">
                    @error('capacity') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-8">
                    <label for="class_teacher_id" class="form-label">Class Teacher</label>
                    <select name="class_teacher_id" class="form-control">
                        <option value="">Select Teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('class_teacher_id')==$teacher->id ? 'selected' : '' }}>
                                {{ $teacher->first_name }} {{ $teacher->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_teacher_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Class</button>
        </form>
    </div>
</div>
@stop
