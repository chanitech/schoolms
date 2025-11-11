@extends('adminlte::page')

@section('title', 'Edit Subject')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-primary mb-0">Edit Subject</h1>
        <a href="{{ route('subjects.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">
        <form action="{{ route('subjects.update', $subject->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Subject Name -->
            <div class="form-group mb-3">
                <label class="fw-bold">Subject Name</label>
                <input type="text" name="name" class="form-control" 
                       value="{{ old('name', $subject->name) }}" required>
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <!-- Subject Code -->
            <div class="form-group mb-3">
                <label class="fw-bold">Subject Code</label>
                <input type="text" name="code" class="form-control" 
                       value="{{ old('code', $subject->code) }}">
                @error('code') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <!-- Subject Type -->
            <div class="form-group mb-3">
                <label class="fw-bold">Subject Type</label>
                <select name="type" class="form-control" required>
                    <option value="core" {{ old('type', $subject->type) == 'core' ? 'selected' : '' }}>Core</option>
                    <option value="elective" {{ old('type', $subject->type) == 'elective' ? 'selected' : '' }}>Elective</option>
                </select>
                @error('type') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <!-- Department -->
            <div class="form-group mb-3">
                <label class="fw-bold">Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" 
                            {{ old('department_id', $subject->department_id) == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <!-- Assign to Classes -->
            <div class="form-group mb-3">
                <label class="fw-bold">Assign to Classes</label>
                <select name="classes[]" class="form-control" multiple>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" 
                            {{ collect(old('classes', $subject->classes->pluck('id')))->contains($class->id) ? 'selected' : '' }}>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">
                    Hold <strong>Ctrl</strong> (Windows) or <strong>Cmd</strong> (Mac) to select multiple.
                </small>
                @error('classes') <small class="text-danger d-block">{{ $message }}</small> @enderror
            </div>

            <!-- Assign Subject Teacher -->
            <div class="form-group mb-3">
                <label class="fw-bold">Assign Subject Teacher</label>
                <select name="teacher_id" class="form-control" required>
                    <option value="">-- Select Teacher --</option>
                    @forelse($teachers as $teacher)
                        <option value="{{ $teacher->id }}" 
                            {{ old('teacher_id', $subject->teacher_id) == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->email }})
                        </option>
                    @empty
                        <option value="">⚠️ No teachers available</option>
                    @endforelse
                </select>
                @error('teacher_id') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Update Subject
            </button>
        </form>
    </div>
</div>
@stop
